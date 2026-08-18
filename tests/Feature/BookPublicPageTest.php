<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #74 — ficha pública del libro.
 *
 * `$book->authors` es un HasMany: interpolarlo en Blade imprimía el JSON de la
 * relación. Además la vista pedía columnas inexistentes (`language`, `country`,
 * `subject_area`, `url`), así que la ficha salía casi vacía.
 */
class BookPublicPageTest extends TestCase
{
    use RefreshDatabase;

    private function listedBook(): Book
    {
        $book = Book::create([
            'user_id' => User::factory()->create()->id,
            'title' => ['es' => 'Cartografía de la ciencia abierta'],
            'slug' => 'cartografia-de-la-ciencia-abierta',
            'primary_locale' => 'es',
            'status' => 'listed',
            'book_type' => 'monograph',
            'primary_language' => 'es',
            'publisher' => 'Editorial Universitaria del Sur',
            'publisher_country' => 'PY',
            'main_discipline' => 'Estudios de la Ciencia',
            'landing_url' => 'https://example.org/libro',
            'isbn' => '978-3-16-148410-0',
            'publication_year' => 2026,
        ]);

        BookAuthor::create([
            'book_id' => $book->id,
            'full_name' => 'María Elena Ortega',
            'role' => 'author',
            'affiliation' => 'Universidad Nacional de Asunción',
            'country_code' => 'PY',
            'order' => 1,
        ]);

        BookAuthor::create([
            'book_id' => $book->id,
            'full_name' => 'Jorge Benítez',
            'role' => 'compiler',
            'order' => 2,
        ]);

        return $book;
    }

    public function test_la_ficha_publica_no_imprime_el_json_de_la_relacion_de_autores(): void
    {
        $book = $this->listedBook();

        $response = $this->get('/es/book/'.$book->slug);

        $response->assertOk();
        $response->assertDontSee('book_id', escape: false);
        $response->assertDontSee('"full_name"', escape: false);
    }

    public function test_la_ficha_publica_lista_los_autores_con_rol_y_afiliacion(): void
    {
        $book = $this->listedBook();

        $response = $this->get('/es/book/'.$book->slug);

        $response->assertSee('María Elena Ortega (Autor) — Universidad Nacional de Asunción', escape: false);
        // El segundo autor no tiene afiliación: no debe quedar el separador colgando.
        $response->assertSee('Jorge Benítez (Compilador)', escape: false);
        $response->assertDontSee('Jorge Benítez (Compilador) —', escape: false);
    }

    public function test_la_ficha_publica_muestra_idioma_pais_disciplina_y_enlace(): void
    {
        $book = $this->listedBook();

        $response = $this->get('/es/book/'.$book->slug);

        $response->assertSee('Español', escape: false);
        $response->assertSee('Paraguay', escape: false);
        $response->assertSee('Estudios de la Ciencia', escape: false);
        $response->assertSee('https://example.org/libro', escape: false);
    }
}
