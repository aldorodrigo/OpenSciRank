<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Support\Countries;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class BookSubmissionWizard extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;
    public int $totalSteps = 6;

    public ?Book $book = null;

    // Step 1: Identificación y Editorial
    public $title = '';
    public $subtitle = '';
    public $book_type = '';
    public $primary_language = '';
    public $secondary_language = '';
    public $publication_year = '';
    public $edition = '';
    public $isbn = '';
    public $doi = '';
    public $landing_url = '';
    public $publisher = '';
    public $publisher_country = '';
    public $publisher_city = '';
    public $collection_series = '';
    public $sponsor_entity = '';
    public $total_pages = '';
    public $format = '';
    public $exact_publication_date = '';

    // Step 2: Autores y Contenido Académico
    public $authors = [['full_name' => '', 'role' => '', 'affiliation' => '', 'country_code' => '', 'orcid' => '']];
    public $abstract = '';
    public $keywords = [];
    public $knowledge_areas = [];
    public $main_discipline = '';
    public $secondary_discipline = '';
    public $academic_level = '';
    public $table_of_contents = '';

    // Step 3: Acceso y Modelo de Negocio
    public $is_open_access = false;
    public $access_type = '';
    public $license_type = '';
    public $rights_holder = '';
    public $allows_reuse = false;
    public $allows_commercial_use = false;
    public $publication_model = '';
    public $access_cost = null;
    public $author_apc = null;
    public $funded_by = [];

    // Step 4: Calidad Editorial, Evaluación e Indexación
    public $has_peer_review = false;
    public $review_type = '';
    public $has_editorial_committee = false;
    public $has_editorial_standards = false;
    public $has_antiplagiarism = false;
    public $has_ethics_code = false;
    public $is_indexed = false;
    public $indexes = [];
    public $citation_count = null;
    public $available_metrics = '';

    // Step 5: Archivos
    public $main_file;
    public $cover_image;
    public $table_of_contents_file;
    public $chapter_files = [];
    public $supplementary_files = [];
    public $download_url = '';
    public $file_size = '';
    public $file_checksum = '';

    public function mount(?Book $book = null)
    {
        if ($book && $book->exists) {
            $this->book = $book;
            $this->title = $book->title ?? '';
            $this->subtitle = $book->subtitle ?? '';
            $this->book_type = $book->book_type ?? '';
            $this->primary_language = $book->primary_language ?? '';
            $this->secondary_language = $book->secondary_language ?? '';
            $this->publication_year = $book->publication_year ?? '';
            $this->edition = $book->edition ?? '';
            $this->isbn = $book->isbn ?? '';
            $this->doi = $book->doi ?? '';
            $this->landing_url = $book->landing_url ?? '';
            $this->publisher = $book->publisher ?? '';
            $this->publisher_country = $book->publisher_country ?? '';
            $this->publisher_city = $book->publisher_city ?? '';
            $this->collection_series = $book->collection_series ?? '';
            $this->sponsor_entity = $book->sponsor_entity ?? '';
            $this->total_pages = $book->total_pages ?? '';
            $this->format = $book->format ?? '';
            $this->exact_publication_date = $book->exact_publication_date ?? '';
            
            $this->abstract = $book->abstract ?? '';
            $this->keywords = is_array($book->keywords) ? $book->keywords : json_decode($book->keywords ?? '[]', true);
            $this->knowledge_areas = is_array($book->knowledge_areas) ? $book->knowledge_areas : json_decode($book->knowledge_areas ?? '[]', true);
            $this->main_discipline = $book->main_discipline ?? '';
            $this->secondary_discipline = $book->secondary_discipline ?? '';
            $this->academic_level = $book->academic_level ?? '';
            $this->table_of_contents = $book->table_of_contents ?? '';

            $this->is_open_access = $book->is_open_access ?? false;
            $this->access_type = $book->access_type ?? '';
            $this->license_type = $book->license_type ?? '';
            $this->rights_holder = $book->rights_holder ?? '';
            $this->allows_reuse = $book->allows_reuse ?? false;
            $this->allows_commercial_use = $book->allows_commercial_use ?? false;

            $this->publication_model = $book->publication_model ?? '';
            $this->access_cost = $book->access_cost;
            $this->author_apc = $book->author_apc;
            $this->funded_by = is_array($book->funded_by) ? $book->funded_by : json_decode($book->funded_by ?? '[]', true);

            $this->has_peer_review = $book->has_peer_review ?? false;
            $this->review_type = $book->review_type ?? '';
            $this->has_editorial_committee = $book->has_editorial_committee ?? false;
            $this->has_editorial_standards = $book->has_editorial_standards ?? false;
            $this->has_antiplagiarism = $book->has_antiplagiarism ?? false;
            $this->has_ethics_code = $book->has_ethics_code ?? false;

            $this->is_indexed = $book->is_indexed ?? false;
            $this->indexes = is_array($book->indexes) ? $book->indexes : json_decode($book->indexes ?? '[]', true);
            $this->citation_count = $book->citation_count;
            $this->available_metrics = $book->available_metrics ?? '';

            if ($book->authors()->count() > 0) {
                $this->authors = $book->authors->map(fn($author) => [
                    'full_name' => $author->full_name,
                    'role' => $author->role,
                    'affiliation' => $author->affiliation,
                    'country_code' => $author->country_code,
                    'orcid' => $author->orcid,
                ])->toArray();
            }

            // We do not load file objects back into Livewire variables since they are already saved,
            // the view relies on `$book->main_file` to show existing files.
        } else {
            $this->authors = [['full_name' => '', 'role' => '', 'affiliation' => '', 'country_code' => '', 'orcid' => '']];
            $this->keywords = [];
            $this->knowledge_areas = [];
        }
    }
    
    // Provide options arrays for Blade
    public array $bookTypes = [
        'monograph' => 'Monografía',
        'edited_volume' => 'Volumen Editado',
        'textbook' => 'Libro de Texto',
        'conference_proceedings' => 'Actas de Congreso',
        'reference_work' => 'Obra de Referencia',
        'other' => 'Otro'
    ];
    
    public array $authorRoles = [
        'author' => 'Autor',
        'editor' => 'Editor',
        'translator' => 'Traductor',
        'coordinator' => 'Coordinador',
        'compiler' => 'Compilador',
        'illustrator' => 'Ilustrador'
    ];

    public array $accessTypeOptions = [
        'gold' => 'Dorado (Gold)',
        'green' => 'Verde (Green)',
        'hybrid' => 'Híbrido (Hybrid)',
        'bronze' => 'Bronce (Bronze)',
        'diamond' => 'Diamante / Platino (Diamond/Platinum)'
    ];

    public array $licenseTypes = [
        'cc_by' => 'CC BY (Atribución)',
        'cc_by_sa' => 'CC BY-SA (Atribución-CompartirIgual)',
        'cc_by_nd' => 'CC BY-ND (Atribución-SinDerivadas)',
        'cc_by_nc' => 'CC BY-NC (Atribución-NoComercial)',
        'cc_by_nc_sa' => 'CC BY-NC-SA (Atribución-NoComercial-CompartirIgual)',
        'cc_by_nc_nd' => 'CC BY-NC-ND (Atribución-NoComercial-SinDerivadas)',
        'copyright_all_rights_reserved' => 'Copyright (Todos los derechos reservados)',
        'public_domain' => 'Dominio Público',
        'other' => 'Otra licensia...'
    ];

    public array $publicationModelOptions = [
        'open_apc' => 'Acceso Abierto (Con cargo por publicación - APC)',
        'open_no_apc' => 'Acceso Abierto Diamante (Sin cargo ni para autor ni lector)',
        'pay_download' => 'Pago por Descarga (Digital)',
        'pay_print' => 'Impreso bajo demanda (Pago por copia)',
        'subscription' => 'Suscripción institucional',
        'freemium' => 'Freemium (Básico gratis, avanzado de pago)'
    ];

    public array $fundingOptions = [
        'institution' => 'Institución del Autor',
        'grant' => 'Subvención / Proyecto de Investigación',
        'society' => 'Sociedad Científica',
        'government' => 'Gobierno',
        'crowdfunding' => 'Crowdfunding',
        'self_funded' => 'Autofinanciado',
        'none' => 'Sin financiación externa'
    ];

    public array $languageOptions = [
        'es' => 'Español',
        'en' => 'Inglés',
        'pt' => 'Portugués',
        'fr' => 'Francés',
        'de' => 'Alemán',
        'it' => 'Italiano',
    ];

    public array $knowledgeAreaOptions = [
        'ciencias_exactas_y_naturales' => 'Ciencias Exactas y Naturales',
        'ingenieria_y_tecnologia' => 'Ingeniería y Tecnología',
        'ciencias_medicas_y_de_la_salud' => 'Ciencias Médicas y de la Salud',
        'ciencias_agricolas' => 'Ciencias Agrícolas',
        'ciencias_sociales' => 'Ciencias Sociales',
        'humanidades' => 'Humanidades'
    ];

    public array $academicLevelOptions = [
        'pregrado' => 'Pregrado (Grado)',
        'postgrado' => 'Postgrado (Máster, Especialización)',
        'doctorado' => 'Doctorado',
        'investigadores' => 'Investigadores / Profesionales',
        'publico_general' => 'Público General / Divulgación'
    ];

    public array $formatOptions = [
        'digital' => 'Digital (PDF, EPUB, etc.)',
        'impreso' => 'Impreso',
        'hibrido' => 'Híbrido (Impreso y Digital)'
    ];

    public array $reviewTypeOptions = [
        'single_blind' => 'Ciego Simple',
        'double_blind' => 'Doble Ciego',
        'open_review' => 'Revisión Abierta',
        'post_publication' => 'Revisión Post-Publicación',
        'editorial_review' => 'Revisión Editorial Interna'
    ];

    public array $indexOptions = [
        'scopus_book_citation_index' => 'Scopus / Book Citation Index',
        'doab' => 'DOAB (Directory of Open Access Books)',
        'scielo_livros' => 'SciELO Livros',
        'latindex' => 'Latindex',
        'dialnet' => 'Dialnet',
        'redib' => 'REDIB',
        'google_scholar' => 'Google Scholar',
        'other' => 'Otro índice regional o internacional'
    ];

    public function addAuthor()
    {
        $this->authors[] = ['full_name' => '', 'role' => 'author', 'affiliation' => '', 'country_code' => '', 'orcid' => ''];
    }

    public function removeAuthor($index)
    {
        unset($this->authors[$index]);
        $this->authors = array_values($this->authors);
        if (empty($this->authors)) {
            $this->addAuthor();
        }
    }

    public function addChapterFile()
    {
        $this->chapter_files[] = ['chapter_name' => '', 'file' => null];
    }

    public function removeChapterFile($index)
    {
        unset($this->chapter_files[$index]);
        $this->chapter_files = array_values($this->chapter_files);
    }

    public function addSupplementaryFile()
    {
        $this->supplementary_files[] = ['name' => '', 'file' => null];
    }

    public function removeSupplementaryFile($index)
    {
        unset($this->supplementary_files[$index]);
        $this->supplementary_files = array_values($this->supplementary_files);
    }

    public function nextStep()
    {
        $this->validateCurrentStep();
        $this->saveDraft();
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep()
    {
        $rules = match($this->currentStep) {
            1 => [
                'title' => 'required|min:3|max:255',
                'book_type' => 'required|string',
                'primary_language' => 'required|string',
                'publisher' => 'required|string|max:255',
                'publisher_country' => 'required|string|max:100',
            ],
            2 => [
                'authors' => 'required|array|min:1',
                'authors.*.full_name' => 'required|string|max:255',
                'authors.*.role' => 'required|string',
                'abstract' => 'required|min:100|max:5000',
                'keywords' => 'required|array|min:3',
                'knowledge_areas' => 'required|array|min:1',
            ],
            3 => [
                'is_open_access' => 'required|boolean',
                'license_type' => 'required|string',
                'publication_model' => 'required|string',
            ],
            4 => [
                'has_peer_review' => 'required|boolean',
            ],
            5 => [
                // 'main_file' => 'required|file|mimes:pdf,epub|max:51200', // 50MB
                // Making main_file required only on first submit if not already present
             ],
            default => [],
        };
        
        // Conditional validation for Step 5
        if ($this->currentStep === 5 && !$this->book?->main_file && !$this->main_file) {
             // $rules['main_file'] = 'required|file|mimes:pdf,epub|max:51200';
             // We'll leave it optional for draft saving, but enforce logic if needed later
        }
        
        $this->validate($rules);
    }

    public function saveDraft()
    {
        $data = [
            // Step 1
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . Str::random(6),
            'subtitle' => $this->subtitle ?: null,
            'book_type' => $this->book_type ?: null,
            'primary_language' => $this->primary_language ?: null,
            'secondary_language' => $this->secondary_language ?: null,
            'publication_year' => $this->publication_year,
            'edition' => $this->edition ?: null,
            'isbn' => $this->isbn ?: null,
            'doi' => $this->doi ?: null,
            'landing_url' => $this->landing_url ?: null,

            // Step 3
            'publisher' => $this->publisher ?: null,
            'publisher_country' => $this->publisher_country ?: null,
            'publisher_city' => $this->publisher_city ?: null,
            'collection_series' => $this->collection_series ?: null,
            'sponsor_entity' => $this->sponsor_entity ?: null,
            'total_pages' => $this->total_pages,
            'format' => $this->format ?: null,
            'exact_publication_date' => $this->exact_publication_date ?: null,

            // Step 4
            'abstract' => $this->abstract ?: null,
            'keywords' => $this->keywords,
            'knowledge_areas' => $this->knowledge_areas,
            'main_discipline' => $this->main_discipline ?: null,
            'secondary_discipline' => $this->secondary_discipline ?: null,
            'academic_level' => $this->academic_level ?: null,
            'table_of_contents' => $this->table_of_contents ?: null,

            // Step 5
            'is_open_access' => $this->is_open_access,
            'access_type' => $this->is_open_access ? $this->access_type : null,
            'license_type' => $this->license_type ?: null,
            'rights_holder' => $this->rights_holder ?: null,
            'allows_reuse' => $this->allows_reuse,
            'allows_commercial_use' => $this->allows_commercial_use,

            // Step 6
            'publication_model' => $this->publication_model ?: null,
            'access_cost' => $this->access_cost,
            'author_apc' => $this->author_apc,
            'funded_by' => $this->funded_by,

            // Step 7
            'has_peer_review' => $this->has_peer_review,
            'review_type' => $this->has_peer_review ? $this->review_type : null,
            'has_editorial_committee' => $this->has_editorial_committee,
            'has_editorial_standards' => $this->has_editorial_standards,
            'has_antiplagiarism' => $this->has_antiplagiarism,
            'has_ethics_code' => $this->has_ethics_code,

            // Step 8
            'is_indexed' => $this->is_indexed,
            'indexes' => $this->indexes,
            'citation_count' => $this->citation_count,
            'available_metrics' => $this->available_metrics ?: null,

            // Step 9 - metadata
            'download_url' => $this->download_url ?: null,
            'file_size' => $this->file_size ?: null,
            'file_checksum' => $this->file_checksum ?: null,

            'status' => 'draft',
        ];

        // Handle file uploads
        if ($this->cover_image) {
            $data['cover_image'] = $this->cover_image->store('book-covers', 'public');
        }
        
        if ($this->table_of_contents_file) {
            $data['table_of_contents_file'] = $this->table_of_contents_file->store('book-toc', 'public');
        }

        if ($this->main_file) {
            $data['main_file'] = $this->main_file->store('books', 'public');
        }

        // We can't save recursive file arrays to the DB directly in the same way Filament does if usage is complex.
        // Assuming the model casts 'chapter_files' and 'supplementary_files' to array/json.
        // We'll process uploads here.
        if (!empty($this->chapter_files)) {
             $processedChapters = []; // You might need to merge with existing if partial updates are allowed
             foreach ($this->chapter_files as $cf) {
                 $item = ['chapter_name' => $cf['chapter_name']];
                 if (isset($cf['file']) && $cf['file'] instanceof \Illuminate\Http\UploadedFile) {
                     $item['file'] = $cf['file']->store('book-chapters', 'public');
                 } elseif (isset($cf['file']) && is_string($cf['file'])) {
                     $item['file'] = $cf['file']; // Keep existing path if not changed
                 }
                 $processedChapters[] = $item;
             }
             $data['chapter_files'] = $processedChapters;
        }

        if (!empty($this->supplementary_files)) {
             $processedSupp = [];
             foreach ($this->supplementary_files as $sf) {
                 $item = ['name' => $sf['name']];
                 if (isset($sf['file']) && $sf['file'] instanceof \Illuminate\Http\UploadedFile) {
                     $item['file'] = $sf['file']->store('book-supplementary', 'public');
                 } elseif (isset($sf['file']) && is_string($sf['file'])) {
                     $item['file'] = $sf['file'];
                 }
                 $processedSupp[] = $item;
             }
             $data['supplementary_files'] = $processedSupp;
        }

        if ($this->book) {
            unset($data['slug']);
            $this->book->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $this->book = Book::create($data);
        }

        // Save authors
        if ($this->book) {
            $this->book->authors()->delete();
            foreach ($this->authors as $index => $author) {
                if (!empty($author['full_name'])) {
                    $this->book->authors()->create([
                        'role' => $author['role'] ?? 'author',
                        'full_name' => $author['full_name'],
                        'orcid' => $author['orcid'] ?: null,
                        'affiliation' => $author['affiliation'] ?: null,
                        'country_code' => $author['country_code'] ?: null,
                        'order' => $index,
                    ]);
                }
            }
        }
    }

    public function submit()
    {
        $this->saveDraft();
        return redirect()->route('app.book.checkout', $this->book);
    }

    public function saveAndExit()
    {
        $this->saveDraft();
        return redirect()->route('app.dashboard');
    }

    public function render()
    {
        return view('livewire.book-submission-wizard', [
            'countries' => Countries::forSelect(),
        ])->layout('components.layouts.app', [
            'title' => $this->book ? 'Editar Libro - Editorial Standards Platform' : 'Registrar Libro - Editorial Standards Platform',
        ]);
    }
}
