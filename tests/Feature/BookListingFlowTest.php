<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Actions\BookListingActions;
use App\Models\AdminTask;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ChangesRequested;
use App\Notifications\ListingApproved;
use App\Notifications\ListingRejected;
use App\Notifications\TaskResubmitted;
use App\Support\BookListing;
use App\Support\CourtesyListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Issue #75 — ciclo de vida del listado de un libro.
 *
 * Cubre las dos mitades: la entrada a la cola de revisión (que ahora es la misma
 * para el pago y para la cortesía) y la resolución del admin en un click.
 */
class BookListingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin', 'web');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        Product::create([
            'slug' => 'book-listing',
            'primary_locale' => 'es',
            'price' => 49.00,
            'currency' => 'USD',
            'is_active' => true,
            'name' => ['es' => 'Listado de Libro Académico'],
            'description' => ['es' => 'Inclusión en el índice.'],
        ]);
    }

    private function superAdmin(): User
    {
        return tap(User::factory()->create())->assignRole('super_admin');
    }

    private function book(string $status = 'draft', array $attributes = []): Book
    {
        return Book::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'title' => ['es' => 'Ciencia abierta en el Cono Sur'],
            'slug' => 'ciencia-abierta-'.uniqid(),
            'primary_locale' => 'es',
            'book_type' => 'monograph',
            'status' => $status,
        ], $attributes));
    }

    /**
     * Libro en revisión con su tarea abierta, por el mismo camino que un pago:
     * la cortesía usa `BookListing::enterReviewQueue()` + `AdminTaskFactory`.
     */
    private function bookUnderReview(): Book
    {
        Notification::fake();

        $book = $this->book();
        CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $this->superAdmin());

        return $book->fresh();
    }

    private function openTaskFor(Book $book): ?AdminTask
    {
        return AdminTask::where('related_type', Book::class)
            ->where('related_id', $book->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_BOOK)
            ->first();
    }

    // ── Entrada a la cola ────────────────────────────────────────────────

    /** @return array<string, array{string}> */
    public static function entryStatuses(): array
    {
        return [
            'borrador' => ['draft'],
            'rechazado' => ['rejected'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('entryStatuses')]
    public function test_entra_a_la_cola_desde_los_estados_de_entrada(string $status): void
    {
        $book = $this->book($status);

        $this->assertTrue(BookListing::enterReviewQueue($book));

        $book->refresh();
        $this->assertSame('pending_listing', $book->status);
        $this->assertNotNull($book->submitted_at);
        $this->assertNotNull($book->submission_date);
    }

    /** @return array<string, array{string}> */
    public static function nonEntryStatuses(): array
    {
        return [
            'ya en revisión' => ['pending_listing'],
            'ya listado' => ['listed'],
            'esperando correcciones del editor' => ['requires_changes_listing'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonEntryStatuses')]
    public function test_no_reabre_un_libro_que_ya_salio_del_borrador(string $status): void
    {
        $book = $this->book($status);

        // Regresión del fallback del success URL: llegaba a degradar un libro ya
        // en revisión y a resetear uno listado que sólo compró el destacado.
        $this->assertFalse(BookListing::enterReviewQueue($book));
        $this->assertSame($status, $book->fresh()->status);
    }

    public function test_la_cortesia_usa_el_mismo_escritor_de_estado(): void
    {
        $book = $this->bookUnderReview();

        $this->assertSame('pending_listing', $book->status);
        $this->assertSame(BookListing::ENTRY_STATUSES, CourtesyListing::ELIGIBLE_BOOK_STATUSES);
    }

    // ── Resolución ───────────────────────────────────────────────────────

    public function test_listar_publica_el_libro_y_cierra_la_tarea_una_sola_vez(): void
    {
        $book = $this->bookUnderReview();
        $admin = $this->superAdmin();

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_LIST, null, $admin);

        $book->refresh();
        $this->assertSame('listed', $book->status);
        $this->assertNotNull($book->approval_date);

        // El cierre lo hace BookObserver; resolve() no debe duplicarlo.
        $task = $this->openTaskFor($book);
        $this->assertSame(AdminTask::STATUS_COMPLETED, $task->status);
        $this->assertSame(
            1,
            AdminTask::where('related_id', $book->id)->where('status', AdminTask::STATUS_COMPLETED)->count()
        );

        Notification::assertSentTo($book->user, ListingApproved::class);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Book::class,
            'subject_id' => $book->id,
            'description' => 'Listado aprobado: el libro es público en el directorio',
        ]);
    }

    public function test_pedir_correcciones_devuelve_el_libro_y_deja_la_tarea_abierta(): void
    {
        $book = $this->bookUnderReview();
        $motivo = 'Falta la portada y el resumen está incompleto.';

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REQUEST_CHANGES, $motivo, $this->superAdmin());

        $book->refresh();
        $this->assertSame('requires_changes_listing', $book->status);
        // El editor lo lee en el modal de observaciones de su panel.
        $this->assertSame($motivo, $book->evaluation_notes);
        $this->assertNull($book->approval_date);

        $task = $this->openTaskFor($book);
        $this->assertSame(AdminTask::STATUS_CHANGES_REQUESTED, $task->status);
        $this->assertContains($task->status, AdminTask::STATUSES_OPEN);

        Notification::assertSentTo($book->user, ChangesRequested::class);
    }

    public function test_rechazar_guarda_el_motivo_y_cierra_la_tarea(): void
    {
        $book = $this->bookUnderReview();

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REJECT, 'El libro no es una obra académica.', $this->superAdmin());

        $book->refresh();
        $this->assertSame('rejected', $book->status);
        $this->assertSame('El libro no es una obra académica.', $book->evaluation_notes);
        $this->assertSame(AdminTask::STATUS_COMPLETED, $this->openTaskFor($book)->status);

        Notification::assertSentTo($book->user, ListingRejected::class);
    }

    public function test_no_se_puede_resolver_un_libro_que_no_esta_en_revision(): void
    {
        $book = $this->book('draft');

        $this->expectException(\InvalidArgumentException::class);

        BookListing::resolve($book, BookListing::DECISION_LIST);
    }

    public function test_rechaza_una_decision_desconocida(): void
    {
        $book = $this->bookUnderReview();

        $this->expectException(\InvalidArgumentException::class);

        BookListing::resolve($book, 'certified');
    }

    // ── Advertencia de datos faltantes ───────────────────────────────────

    public function test_advierte_los_datos_que_le_faltan_a_la_ficha(): void
    {
        $book = $this->book();

        $missing = BookListing::missingForPublication($book);

        $this->assertContains(__('admin.book.missing_cover'), $missing);
        $this->assertContains(__('admin.book.missing_file'), $missing);
        $this->assertContains(__('admin.book.missing_abstract'), $missing);
        $this->assertContains(__('admin.book.missing_isbn'), $missing);
        $this->assertContains(__('admin.book.missing_authors'), $missing);
    }

    public function test_no_advierte_nada_en_un_libro_completo(): void
    {
        $book = $this->book('draft', [
            'cover_image' => 'book-covers/tapa.jpg',
            'main_file' => 'books/obra.pdf',
            'abstract' => ['es' => 'Resumen suficiente de la obra.'],
            'isbn' => '978-99967-45-321-0',
        ]);

        BookAuthor::create([
            'book_id' => $book->id,
            'full_name' => 'Silvia Ramírez',
            'role' => 'editor',
            'order' => 1,
        ]);

        $this->assertSame([], BookListing::missingForPublication($book->fresh()));
    }

    public function test_la_advertencia_no_bloquea_la_publicacion(): void
    {
        $book = $this->bookUnderReview();
        $this->assertNotEmpty(BookListing::missingForPublication($book));

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_LIST, null, $this->superAdmin());

        $this->assertSame('listed', $book->fresh()->status);
    }

    // ── Visibilidad de las acciones ──────────────────────────────────────

    public function test_las_acciones_solo_las_ve_un_super_admin_con_el_libro_en_revision(): void
    {
        $book = $this->bookUnderReview();
        $resolve = fn (): Book => $book;

        $this->actingAs(User::factory()->create());
        $this->assertFalse(BookListingActions::approve($resolve)->isVisible());
        $this->assertFalse(BookListingActions::requestChanges($resolve)->isVisible());
        $this->assertFalse(BookListingActions::reject($resolve)->isVisible());

        $this->actingAs($this->superAdmin());
        $this->assertTrue(BookListingActions::approve($resolve)->isVisible());
        $this->assertTrue(BookListingActions::requestChanges($resolve)->isVisible());
        $this->assertTrue(BookListingActions::reject($resolve)->isVisible());
    }

    public function test_la_previsualizacion_apunta_a_la_ficha_publica_en_cualquier_estado(): void
    {
        $book = $this->book('draft');
        $resolve = fn (): Book => $book;

        $this->actingAs(User::factory()->create());
        $this->assertFalse(BookListingActions::preview($resolve)->isVisible());

        $this->actingAs($this->superAdmin());
        $action = BookListingActions::preview($resolve);

        // A diferencia de las tres decisiones, previsualizar sirve en cualquier
        // estado: es para revisar el contenido antes de resolver.
        $this->assertTrue($action->isVisible());
        $this->assertSame(route('book.show', ['slug' => $book->slug]), $action->getUrl());
        $this->assertSame(__('admin.book.action_preview'), $action->getLabel());

        $book->update(['status' => 'listed']);
        $this->assertSame(__('admin.book.action_view_public'), BookListingActions::preview($resolve)->getLabel());
    }

    public function test_el_admin_puede_ver_la_ficha_de_un_libro_todavia_sin_publicar(): void
    {
        $book = $this->book('pending_listing');

        // Para cualquier otro visitante sigue siendo 404 hasta que esté listed.
        $this->get('/es/book/'.$book->slug)->assertNotFound();

        $this->actingAs($this->superAdmin());
        $this->get('/es/book/'.$book->slug)->assertOk();

        $this->actingAs($book->user);
        $this->get('/es/book/'.$book->slug)->assertOk();
    }

    public function test_las_acciones_desaparecen_cuando_el_libro_ya_esta_resuelto(): void
    {
        $this->actingAs($this->superAdmin());

        foreach (['draft', 'listed', 'rejected', 'requires_changes_listing'] as $status) {
            $book = $this->book($status);
            $this->assertFalse(
                BookListingActions::approve(fn (): Book => $book)->isVisible(),
                "La acción no debería estar visible en {$status}."
            );
        }
    }

    // ── Reenvío del editor ───────────────────────────────────────────────

    public function test_el_reenvio_desde_el_wizard_marca_la_tarea_y_avisa_al_revisor(): void
    {
        $book = $this->bookUnderReview();
        $admin = $this->superAdmin();

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REQUEST_CHANGES, 'Falta la portada del libro.', $admin);

        $task = $this->openTaskFor($book);
        $task->update(['assigned_to' => $admin->id]);

        // Lo que hace el editor: corrige en el wizard y reenvía. Es gratis.
        Notification::fake();
        $this->actingAs($book->user);
        \Livewire\Livewire::test(\App\Livewire\BookSubmissionWizard::class, ['book' => $book->fresh()])
            ->call('submit');

        $book->refresh();
        $this->assertSame('pending_listing', $book->status);
        $this->assertSame(AdminTask::STATUS_RESUBMITTED, $task->fresh()->status);
        $this->assertStringContainsString('↻', (string) $task->fresh()->notes);

        Notification::assertSentTo($admin, TaskResubmitted::class);
    }

    public function test_guardar_borrador_no_baja_de_estado_un_libro_con_correcciones(): void
    {
        $book = $this->bookUnderReview();

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REQUEST_CHANGES, 'Falta la portada del libro.', $this->superAdmin());

        $this->actingAs($book->user);
        \Livewire\Livewire::test(\App\Livewire\BookSubmissionWizard::class, ['book' => $book->fresh()])
            ->set('title.es', 'Título corregido por el editor')
            ->call('saveDraft');

        // Regresión: `saveDraft()` escribía `status => 'draft'` fijo, así que
        // corregir el libro lo sacaba de la cola y submit() ya no reconocía la
        // resubmisión gratuita.
        $this->assertSame('requires_changes_listing', $book->fresh()->status);
    }

    public function test_el_reenvio_desde_el_dashboard_tambien_avisa(): void
    {
        $book = $this->bookUnderReview();
        $admin = $this->superAdmin();

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REQUEST_CHANGES, 'Falta la portada del libro.', $admin);
        $this->openTaskFor($book)->update(['assigned_to' => $admin->id]);

        // Sin Livewire::test: renderizar EditorDashboard revienta en este entorno
        // por el componente anidado @livewire('my-payments'). El método sólo toca
        // la base y props públicas, así que lo ejercemos directo.
        Notification::fake();
        $this->actingAs($book->user);
        (new \App\Livewire\EditorDashboard)->resubmitBookForListing($book->id);

        $this->assertSame('pending_listing', $book->fresh()->status);
        $this->assertSame(AdminTask::STATUS_RESUBMITTED, $this->openTaskFor($book)->status);

        Notification::assertSentTo($admin, TaskResubmitted::class);
    }

    // ── El editor ve las observaciones ───────────────────────────────────

    public function test_el_editor_ve_las_observaciones_del_revisor_en_su_panel(): void
    {
        $book = $this->bookUnderReview();
        $motivo = 'Falta la portada y el archivo principal del libro.';

        Notification::fake();
        BookListing::resolve($book, BookListing::DECISION_REQUEST_CHANGES, $motivo, $this->superAdmin());

        // Antes de #75 `books` no tenía la columna y el modal decía siempre
        // "No observations recorded.".
        $this->actingAs($book->user);
        $dashboard = new \App\Livewire\EditorDashboard;
        $dashboard->showObservations($book->id, 'book');

        $this->assertSame($motivo, $dashboard->observationsNotes);
        $this->assertTrue($dashboard->showObservationsModal);
        $this->assertSame($book->id, $dashboard->observationsBookId);
    }
}
