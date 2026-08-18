<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Actions\BookCourtesyActions;
use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ListingRequested;
use App\Support\CourtesyListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Publicar libro de cortesía: el admin exonera el costo del listado
 * (`book-listing`, USD 49) y el libro entra a revisión con un Payment de
 * monto 0 que documenta la exoneración.
 */
class BookCourtesyListingTest extends TestCase
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

    private function book(string $status = 'draft', ?User $owner = null): Book
    {
        $owner ??= User::factory()->create();

        return Book::create([
            'user_id' => $owner->id,
            'title' => ['es' => 'Metodología de la investigación'],
            'slug' => 'metodologia-'.uniqid(),
            'primary_locale' => 'es',
            'book_type' => 'monograph',
            'status' => $status,
        ]);
    }

    public function test_cortesia_deja_el_libro_en_pending_listing(): void
    {
        $book = $this->book();

        CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $this->superAdmin());

        $book->refresh();
        $this->assertSame('pending_listing', $book->status);
        $this->assertNotNull($book->submitted_at);
        $this->assertNotNull($book->submission_date);
    }

    public function test_cortesia_crea_payment_de_monto_cero_con_motivo_y_autor(): void
    {
        $admin = $this->superAdmin();
        $book = $this->book();

        $payment = CourtesyListing::forBook($book, 'Canje por difusión en congreso.', $admin);

        $this->assertSame(Payment::PROVIDER_COURTESY, $payment->provider);
        $this->assertTrue($payment->isCourtesy());
        $this->assertSame(0.0, (float) $payment->amount);
        $this->assertSame('completed', $payment->status);
        $this->assertNull($payment->transaction_id);
        $this->assertSame($book->user_id, $payment->user_id);
        $this->assertSame(Book::class, $payment->payable_type);
        $this->assertSame($book->id, $payment->payable_id);

        $this->assertSame('Canje por difusión en congreso.', $payment->metadata['reason']);
        $this->assertSame($admin->id, $payment->metadata['granted_by_id']);
        // json_encode(49.0) → "49": el roundtrip del cast array lo devuelve int.
        $this->assertEquals(49.0, $payment->metadata['list_price']);
        $this->assertSame('draft', $payment->metadata['previous_status']);
    }

    public function test_cortesia_crea_admin_task_review_listing_book_ligada_al_pago(): void
    {
        $book = $this->book();

        $payment = CourtesyListing::forBook($book, 'Compensación por incidencia en el checkout.', $this->superAdmin());

        $task = AdminTask::where('related_type', Book::class)
            ->where('related_id', $book->id)
            ->first();

        $this->assertNotNull($task);
        $this->assertSame(AdminTask::TYPE_REVIEW_LISTING_BOOK, $task->type);
        $this->assertSame(AdminTask::STATUS_PENDING, $task->status);
        $this->assertSame($payment->id, $task->payment_id);
    }

    public function test_task_de_cortesia_sigue_marcada_como_complimentary(): void
    {
        $book = $this->book();

        CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $this->superAdmin());

        $task = AdminTask::where('related_id', $book->id)->firstOrFail();

        // Regresión: el Payment de monto 0 no debe hacer que la task parezca pagada.
        $this->assertTrue($task->isComplimentary());
        $this->assertSame(0.0, $task->taskAmount());
    }

    public function test_cortesia_notifica_al_editor_y_deja_activity_log(): void
    {
        Notification::fake();

        $book = $this->book();

        CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $this->superAdmin());

        Notification::assertSentTo($book->user, ListingRequested::class);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Book::class,
            'subject_id' => $book->id,
            'description' => 'Libro publicado de cortesía: Convenio institucional con la universidad.',
        ]);
    }

    public function test_pago_de_cortesia_queda_fuera_del_historial_del_editor(): void
    {
        $book = $this->book();

        CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $this->superAdmin());

        $this->assertSame(1, Payment::where('user_id', $book->user_id)->count());
        $this->assertSame(0, Payment::where('user_id', $book->user_id)->notCourtesy()->count());
    }

    public function test_no_es_elegible_un_libro_ya_listed_ni_en_correcciones(): void
    {
        $this->assertFalse(CourtesyListing::isEligible($this->book('listed')));
        $this->assertFalse(CourtesyListing::isEligible($this->book('pending_listing')));
        $this->assertFalse(CourtesyListing::isEligible($this->book('requires_changes_listing')));
        $this->assertFalse(CourtesyListing::isEligible($this->book('submitted')));

        $this->assertTrue(CourtesyListing::isEligible($this->book('draft')));
        $this->assertTrue(CourtesyListing::isEligible($this->book('rejected')));
    }

    public function test_forzar_cortesia_sobre_libro_no_elegible_lanza_excepcion(): void
    {
        $book = $this->book('listed');

        $this->expectException(\InvalidArgumentException::class);

        CourtesyListing::forBook($book, 'Motivo cualquiera de prueba.', $this->superAdmin());
    }

    /**
     * Issue #72: el modal de detalle del pago mostraba "Courtesy" (el valor
     * crudo de `provider`) y no decía el motivo ni quién había autorizado la
     * exoneración, pese a que la acción los exige y los persiste.
     */
    public function test_el_detalle_del_pago_muestra_la_cortesia_traducida_con_su_motivo(): void
    {
        $this->app->setLocale('es');

        $admin = $this->superAdmin();
        $book = $this->book();

        $payment = CourtesyListing::forBook($book, 'Convenio institucional con la universidad.', $admin);

        $html = view('filament.payments.payment-detail-modal', [
            'payment' => $payment->fresh(['user', 'product', 'payable']),
        ])->render();

        $this->assertStringContainsString('Cortesía', $html);
        $this->assertStringNotContainsString('Courtesy', $html);
        $this->assertStringContainsString('Convenio institucional con la universidad.', $html);
        $this->assertStringContainsString($admin->name, $html);
        // El desglose nombra la exoneración en vez de un descuento anónimo.
        $this->assertStringContainsString('Exoneración de cortesía', $html);
    }

    public function test_accion_no_visible_para_usuario_sin_super_admin(): void
    {
        $book = $this->book();

        $this->actingAs(User::factory()->create());
        $this->assertFalse(BookCourtesyActions::listing(fn (): Book => $book)->isVisible());

        $this->actingAs($this->superAdmin());
        $this->assertTrue(BookCourtesyActions::listing(fn (): Book => $book)->isVisible());
    }
}
