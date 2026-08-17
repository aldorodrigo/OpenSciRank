<?php

namespace Tests\Feature;

use App\Livewire\BookSubmissionWizard;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
