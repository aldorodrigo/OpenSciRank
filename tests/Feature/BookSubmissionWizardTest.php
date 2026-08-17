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
