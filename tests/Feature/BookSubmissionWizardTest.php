<?php

namespace Tests\Feature;

use App\Livewire\BookSubmissionWizard;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BookSubmissionWizardTest extends TestCase
{
    use RefreshDatabase;

    /** Paso 1 con solo los campos obligatorios: año y páginas quedan vacíos. */
    public function test_paso_1_guarda_borrador_con_campos_numericos_vacios(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Metodología de la investigación')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 2);

        $this->assertDatabaseCount('books', 1);
        $this->assertNull(Book::first()->publication_year);
    }

    /**
     * El select de rol del autor no tiene opción vacía: el navegador muestra "Autor"
     * pero la propiedad queda en '' si el editor no lo toca, y authors.*.role es
     * required. El paso 2 debe pasar con el rol que viene por defecto.
     */
    public function test_paso_2_pasa_con_el_rol_de_autor_por_defecto(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(BookSubmissionWizard::class)
            ->assertSet('authors.0.role', 'author')
            ->set('primary_locale', 'es')
            ->set('title.es', 'Metodología de la investigación')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->call('nextStep')
            ->set('authors.0.full_name', 'Ana Pérez')
            ->set('abstract.es', str_repeat('Resumen académico del libro. ', 10))
            ->set('keywords', ['metodología', 'investigación', 'ciencia'])
            ->set('knowledge_areas', ['ciencias_sociales'])
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 3);
    }

    /**
     * Un rol vacío en el estado (snapshot de Livewire viejo, fila antigua) no puede
     * bloquear el paso 2: el select muestra "Autor" y el editor no tiene forma de
     * corregir un campo que en pantalla ya se ve completo.
     */
    public function test_paso_2_no_se_bloquea_con_un_rol_vacio_heredado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Metodología de la investigación')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->call('nextStep')
            ->set('authors.0.role', '')
            ->set('authors.0.full_name', 'Ana Pérez')
            ->set('abstract.es', str_repeat('Resumen académico del libro. ', 10))
            ->set('keywords', ['metodología', 'investigación', 'ciencia'])
            ->set('knowledge_areas', ['ciencias_sociales'])
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 3)
            ->assertSet('authors.0.role', 'author');

        $this->assertSame('author', Book::first()->authors()->first()->role);
    }

    /** El input type=date exige yyyy-MM-dd; el cast del modelo devuelve un Carbon. */
    public function test_editar_un_borrador_carga_la_fecha_en_formato_de_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => ['es' => 'Libro con fecha'],
            'slug' => 'libro-con-fecha',
            'primary_locale' => 'es',
            'exact_publication_date' => '2026-03-20',
            'status' => 'draft',
        ]);

        Livewire::test(BookSubmissionWizard::class, ['book' => $book])
            ->assertSet('exact_publication_date', '2026-03-20');
    }

    /** La portada subida en el paso 1 debe quedar en el disco público y en el modelo. */
    public function test_la_portada_se_guarda_al_pasar_de_paso(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Libro con portada')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->set('cover_image', UploadedFile::fake()->image('portada.jpg', 600, 900))
            ->call('nextStep')
            ->assertHasNoErrors();

        $book = Book::first();

        $this->assertNotNull($book->cover_image, 'El borrador no guardó la portada.');
        Storage::disk('public')->assertExists($book->cover_image);
    }

    /** Volver a guardar sin tocar la portada no debe duplicar el archivo ni perderlo. */
    public function test_guardar_de_nuevo_no_duplica_ni_pierde_la_portada(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Libro con portada')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->set('cover_image', UploadedFile::fake()->image('portada.jpg', 600, 900))
            ->call('nextStep');

        $primera = Book::first()->cover_image;

        $component->call('previousStep')->call('nextStep');

        $this->assertSame($primera, Book::first()->cover_image);
        $this->assertCount(1, Storage::disk('public')->files('book-covers'));
    }

    /** Editar un borrador y subir un capítulo nuevo no puede borrar los ya guardados. */
    public function test_editar_no_pierde_los_capitulos_ya_guardados(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => ['es' => 'Libro con capítulos'],
            'slug' => 'libro-con-capitulos',
            'primary_locale' => 'es',
            'status' => 'draft',
            'chapter_files' => [
                ['chapter_name' => 'Capítulo 1', 'file' => 'book-chapters/cap1.pdf'],
                ['chapter_name' => 'Capítulo 2', 'file' => 'book-chapters/cap2.pdf'],
            ],
        ]);

        Livewire::test(BookSubmissionWizard::class, ['book' => $book])
            ->call('addChapterFile')
            ->set('chapter_files.2.chapter_name', 'Capítulo 3')
            ->set('chapter_files.2.file', UploadedFile::fake()->create('cap3.pdf', 100, 'application/pdf'))
            ->call('saveDraft');

        $guardados = $book->fresh()->chapter_files;

        $this->assertCount(3, $guardados);
        $this->assertSame('book-chapters/cap1.pdf', $guardados[0]['file']);
        $this->assertSame('book-chapters/cap2.pdf', $guardados[1]['file']);
        $this->assertSame('Capítulo 3', $guardados[2]['chapter_name']);
    }

    /** Recorrido completo del wizard sin llenar ningún campo numérico opcional. */
    public function test_wizard_completo_llega_al_checkout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Metodología de la investigación')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Prueba')
            ->set('publisher_country', 'PY')
            ->call('nextStep')
            ->set('authors', [[
                'full_name' => 'Ana Pérez',
                'role' => 'author',
                'affiliation' => '',
                'country_code' => '',
                'orcid' => '',
            ]])
            ->set('abstract.es', str_repeat('Resumen académico del libro. ', 10))
            ->set('keywords', ['metodología', 'investigación', 'ciencia'])
            ->set('knowledge_areas', ['ciencias_sociales'])
            ->call('nextStep')
            ->set('is_open_access', false)
            ->set('license_type', 'cc_by')
            ->set('publication_model', 'open_no_apc')
            ->call('nextStep')
            ->set('has_peer_review', true)
            ->call('nextStep')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 6)
            ->call('submit')
            ->assertRedirect(route('app.book.checkout', Book::first()));

        $book = Book::first();
        $this->assertSame('draft', $book->status);
        $this->assertNull($book->total_pages);
        $this->assertNull($book->citation_count);
        $this->assertSame('Ana Pérez', $book->authors()->first()->full_name);
    }
}
