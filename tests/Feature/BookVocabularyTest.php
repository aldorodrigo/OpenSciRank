<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BookResource;
use App\Livewire\BookSubmissionWizard;
use App\Models\Book;
use App\Models\User;
use App\Support\BookVocabulary;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Issue #71 — el wizard público y el admin comparten vocabulario.
 *
 * La regresión que cubre este test: un libro cargado por un editor no se podía
 * guardar en Filament porque el admin ofrecía otros valores para book_type,
 * academic_level, license_type y publication_model, y la validación de Select
 * rechazaba lo que había guardado el wizard.
 */
class BookVocabularyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin', 'web');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Recorre el wizard completo tocando todos los campos con vocabulario
     * controlado y devuelve el libro resultante.
     */
    private function bookFromWizard(): Book
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Cartografía de la ciencia abierta')
            ->set('book_type', 'monograph')
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Universitaria del Sur')
            ->set('publisher_country', 'PY')
            ->set('format', 'digital')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('authors.0.full_name', 'María Elena Ortega')
            ->set('authors.0.role', 'compiler')
            ->set('abstract.es', str_repeat('Análisis del acceso abierto en la región. ', 5))
            ->set('keywords', ['ciencia abierta', 'acceso abierto', 'américa latina'])
            ->set('knowledge_areas', ['ciencias_sociales'])
            ->set('academic_level', 'postgrado')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('is_open_access', true)
            ->set('access_type', 'diamond')
            ->set('license_type', 'cc_by_nc_sa')
            ->set('publication_model', 'open_no_apc')
            ->set('funded_by', ['institution'])
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('has_peer_review', true)
            ->set('review_type', 'open_review')
            ->set('is_indexed', true)
            ->set('indexes', ['doab', 'scielo_livros'])
            ->call('nextStep')
            ->assertHasNoErrors();

        return Book::firstOrFail();
    }

    /**
     * Opciones declaradas por cada Select/CheckboxList del formulario de
     * BookResource, recorriendo el schema (los campos viven dentro de tabs y de
     * un repeater, así que hay que bajar recursivamente).
     *
     * @return array<string, array<int, string>>
     */
    private function adminFormOptions(): array
    {
        $options = [];

        $walk = function (iterable $components) use (&$walk, &$options): void {
            foreach ($components as $component) {
                if (method_exists($component, 'getOptions') && method_exists($component, 'getName')) {
                    try {
                        $options[$component->getName()] = array_keys($component->getOptions());
                    } catch (\Throwable) {
                        // Componentes que resuelven opciones contra la BD (user_id).
                    }
                }

                foreach (['getDefaultChildComponents', 'getChildComponents', 'getComponents'] as $method) {
                    if (method_exists($component, $method)) {
                        try {
                            $walk($component->{$method}());
                        } catch (\Throwable) {
                        }
                    }
                }
            }
        };

        $walk(BookResource::form(Schema::make())->getComponents(true));

        return $options;
    }

    public function test_el_admin_acepta_todos_los_valores_que_guarda_el_wizard(): void
    {
        $book = $this->bookFromWizard();
        $options = $this->adminFormOptions();

        // Antes de #71 estos cuatro rompían el formulario con
        // "no está en la lista de valores permitidos".
        $singles = [
            'book_type' => 'monograph',
            'academic_level' => 'postgrado',
            'license_type' => 'cc_by_nc_sa',
            'publication_model' => 'open_no_apc',
            'access_type' => 'diamond',
            'format' => 'digital',
            'review_type' => 'open_review',
            'primary_language' => 'es',
        ];

        foreach ($singles as $field => $expected) {
            $this->assertSame($expected, $book->{$field}, "El wizard no guardó {$field}.");
            $this->assertContains(
                $expected,
                $options[$field] ?? [],
                "El formulario del admin no acepta {$field}={$expected}."
            );
        }

        $this->assertContains('compiler', $options['role'] ?? [], 'El admin no acepta el rol compiler.');
        $this->assertSame('compiler', $book->authors()->first()->role);

        foreach ($book->indexes ?? [] as $index) {
            $this->assertContains($index, $options['indexes'] ?? []);
        }

        foreach ($book->funded_by ?? [] as $funder) {
            $this->assertContains($funder, $options['funded_by'] ?? []);
        }
    }

    public function test_los_selects_del_admin_salen_del_vocabulario_compartido(): void
    {
        $options = $this->adminFormOptions();

        $vocabularyByField = [
            'book_type' => 'book_type',
            'primary_language' => 'language',
            'secondary_language' => 'language',
            'role' => 'author_role',
            'format' => 'format',
            'academic_level' => 'academic_level',
            'access_type' => 'access_type',
            'license_type' => 'license_type',
            'publication_model' => 'publication_model',
            'funded_by' => 'funded_by',
            'review_type' => 'review_type',
            'indexes' => 'index',
        ];

        foreach ($vocabularyByField as $field => $vocabulary) {
            $this->assertSame(
                BookVocabulary::values($vocabulary),
                $options[$field] ?? [],
                "El campo {$field} del admin no usa el vocabulario {$vocabulary}."
            );
        }
    }

    public function test_todos_los_vocabularios_tienen_etiquetas_en_los_tres_idiomas(): void
    {
        foreach (['es', 'en', 'pt'] as $locale) {
            $this->app->setLocale($locale);

            foreach (BookVocabulary::FIELDS as $field) {
                $options = BookVocabulary::options($field);

                $this->assertNotEmpty($options, "Falta el vocabulario {$field} en {$locale}.");

                foreach ($options as $value => $label) {
                    $this->assertIsString($label);
                    $this->assertNotSame($value, $label, "La opción {$field}.{$value} no está traducida en {$locale}.");
                }
            }
        }
    }

    public function test_los_tres_idiomas_declaran_exactamente_los_mismos_valores(): void
    {
        foreach (BookVocabulary::FIELDS as $field) {
            $this->app->setLocale('es');
            $reference = BookVocabulary::values($field);

            foreach (['en', 'pt'] as $locale) {
                $this->app->setLocale($locale);

                $this->assertSame(
                    $reference,
                    BookVocabulary::values($field),
                    "El vocabulario {$field} difiere entre es y {$locale}."
                );
            }
        }
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function legacyValues(): array
    {
        return [
            'tipo de obra del admin' => ['book_type', 'libro_academico', 'monograph'],
            'nivel académico del admin' => ['academic_level', 'posgrado', 'postgrado'],
            'licencia en mayúsculas' => ['license_type', 'CC-BY-NC-SA', 'cc_by_nc_sa'],
            'copyright abreviado' => ['license_type', 'copyright', 'copyright_all_rights_reserved'],
            'modelo gratuito' => ['publication_model', 'free', 'open_no_apc'],
            'acceso inmediato' => ['access_type', 'immediate', 'gold'],
            'formato pdf' => ['format', 'pdf', 'digital'],
            'área en inglés' => ['knowledge_area', 'social_sciences', 'ciencias_sociales'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legacyValues')]
    public function test_normalize_convierte_los_valores_viejos(string $field, string $legacy, string $expected): void
    {
        $this->assertSame($expected, BookVocabulary::normalize($field, $legacy));
    }

    public function test_normalize_respeta_los_valores_canonicos_y_los_desconocidos(): void
    {
        $this->assertSame('monograph', BookVocabulary::normalize('book_type', 'monograph'));
        $this->assertSame('valor_raro', BookVocabulary::normalize('book_type', 'valor_raro'));
        $this->assertNull(BookVocabulary::normalize('book_type', null));
        $this->assertSame('', BookVocabulary::normalize('book_type', ''));
    }

    public function test_normalize_many_deduplica_y_limpia(): void
    {
        $this->assertSame(
            ['scopus_book_citation_index', 'doab'],
            BookVocabulary::normalizeMany('index', ['scopus', 'scopus_book_citation_index', 'doab', '']),
        );
    }

    public function test_el_wizard_rechaza_un_valor_fuera_del_vocabulario(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BookSubmissionWizard::class)
            ->set('primary_locale', 'es')
            ->set('title.es', 'Cartografía de la ciencia abierta')
            ->set('book_type', 'libro_academico') // valor viejo del admin
            ->set('primary_language', 'es')
            ->set('publisher', 'Editorial Universitaria del Sur')
            ->set('publisher_country', 'PY')
            ->call('nextStep')
            ->assertHasErrors('book_type');
    }
}
