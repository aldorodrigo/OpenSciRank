<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Journal;
use App\Notifications\ListingRequested;
use App\Support\Countries;
use Illuminate\Support\Str;

class SubmissionWizard extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;
    public int $totalSteps = 7;

    // Editing mode
    public ?Journal $journal = null;

    // Step 1: Open Access Compliance
    public ?bool $is_open_access = null;
    public string $access_type = '';
    public ?bool $articles_accessible_without_registration = null;
    public ?bool $allows_self_archiving = null;
    public string $open_access_policy_url = '';
    public ?bool $has_embargo = null;
    public ?int $embargo_months = null;

    // Idioma primario para campos traducibles
    public string $primary_locale = 'es';

    // Step 2: About the Journal
    public array $title = ['es' => '', 'en' => '', 'pt' => ''];
    public array $abbreviated_name = ['es' => '', 'en' => '', 'pt' => ''];
    public array $description = ['es' => '', 'en' => '', 'pt' => ''];
    public array $subject_areas = [];
    public array $target_audience = [];
    public array $publication_languages = [];
    public ?int $start_year = null;
    public string $url = '';
    public $logo = null;
    public ?string $existing_logo = null;

    // Step 3: Copyright and Licenses
    public string $license_type = '';
    public string $license_url = '';
    public ?bool $authors_retain_copyright = null;
    public ?bool $allows_commercial_reuse = null;
    public array $copyright_policy = ['es' => '', 'en' => '', 'pt' => ''];
    public ?bool $licenses_visible_in_articles = null;

    // Step 4: Editorial
    public array $publishing_institution = ['es' => '', 'en' => '', 'pt' => ''];
    public string $country_code = '';
    public array $editor_name = ['es' => '', 'en' => '', 'pt' => ''];
    public string $institutional_email = '';
    public ?bool $editorial_board_visible = null;
    public string $editorial_board_url = '';
    public string $peer_review_type = '';
    public string $publication_frequency = '';

    // Step 5: Business Model
    public ?bool $charges_apc = null;
    public ?float $apc_amount = null;
    public string $apc_currency = 'USD';
    public ?bool $has_apc_waivers = null;
    public array $funding_sources = [];
    public ?bool $has_advertising = null;
    public ?bool $business_model_transparent = null;

    // Step 6: Best Practices
    public ?bool $has_ethics_policy = null;
    public ?bool $adheres_to_cope = null;
    public ?bool $has_antiplagiarism_policy = null;
    public string $antiplagiarism_tool = '';
    public ?bool $has_conflict_of_interest_policy = null;
    public ?bool $declares_ai_use = null;
    public ?bool $assigns_doi = null;

    // Step 7: Technical (existing fields)
    public string $issn_print = '';
    public string $issn_online = '';
    public string $publisher = '';

    // OAI-PMH
    public ?string $oai_base_url = '';
    public ?string $oai_set_spec = '';
    public string $oai_metadata_prefix = 'oai_dc';

    // Options for selects
    public array $accessTypes = [
        'full_oa' => 'Acceso Abierto completo',
        'hybrid' => 'Híbrido',
        'restricted' => 'Restringido',
    ];

    public array $licenseTypes = [
        'CC-BY' => 'CC BY (Atribución)',
        'CC-BY-SA' => 'CC BY-SA (Atribución-CompartirIgual)',
        'CC-BY-NC' => 'CC BY-NC (Atribución-NoComercial)',
        'CC-BY-NC-SA' => 'CC BY-NC-SA (Atribución-NoComercial-CompartirIgual)',
        'CC-BY-ND' => 'CC BY-ND (Atribución-SinDerivadas)',
        'CC-BY-NC-ND' => 'CC BY-NC-ND (Atribución-NoComercial-SinDerivadas)',
        'CC0' => 'CC0 (Dominio público)',
        'other' => 'Otra licencia',
    ];

    public array $peerReviewTypes = [
        'double_blind' => 'Doble ciego',
        'single_blind' => 'Simple ciego',
        'open' => 'Revisión abierta',
        'none' => 'Sin revisión por pares',
    ];

    public array $frequencies = [
        'continuous' => 'Publicación continua',
        'monthly' => 'Mensual',
        'bimonthly' => 'Bimestral',
        'quarterly' => 'Trimestral',
        'biannual' => 'Semestral',
        'annual' => 'Anual',
    ];

    public array $subjectAreaOptions = [
        'sciences' => 'Ciencias',
        'engineering' => 'Ingeniería',
        'medicine' => 'Medicina',
        'social_sciences' => 'Ciencias Sociales',
        'humanities' => 'Humanidades',
        'arts' => 'Artes',
        'education' => 'Educación',
        'law' => 'Derecho',
        'economics' => 'Economía',
        'agriculture' => 'Agricultura',
    ];

    public array $audienceOptions = [
        'researchers' => 'Investigadores',
        'professors' => 'Docentes',
        'students' => 'Estudiantes',
        'professionals' => 'Profesionales',
        'general_public' => 'Público general',
    ];

    public array $languageOptions = [
        'es' => 'Español',
        'en' => 'Inglés',
        'pt' => 'Portugués',
        'fr' => 'Francés',
        'de' => 'Alemán',
        'it' => 'Italiano',
    ];

    public array $fundingOptions = [
        'university' => 'Universidad',
        'government' => 'Gobierno',
        'private' => 'Sector privado',
        'foundations' => 'Fundaciones',
        'associations' => 'Asociaciones científicas',
        'other' => 'Otro',
    ];

    public function mount(?Journal $journal = null)
    {
        // Default primary_locale based on app locale
        $appLocale = app()->getLocale();
        $this->primary_locale = in_array($appLocale, ['es', 'en', 'pt'], true) ? $appLocale : 'es';

        if ($journal && $journal->exists) {
            $this->journal = $journal;
            $this->loadFromJournal($journal);
        }
    }

    protected function loadFromJournal(Journal $journal)
    {
        // Step 1
        $this->is_open_access = $journal->is_open_access;
        $this->access_type = $journal->access_type ?? '';
        $this->articles_accessible_without_registration = $journal->articles_accessible_without_registration;
        $this->allows_self_archiving = $journal->allows_self_archiving;
        $this->open_access_policy_url = $journal->open_access_policy_url ?? '';
        $this->has_embargo = $journal->has_embargo;
        $this->embargo_months = $journal->embargo_months;

        // Idioma primario
        $this->primary_locale = $journal->primary_locale ?? 'es';

        // Step 2 (campos traducibles hidratados desde JSON)
        $emptyLocales = ['es' => '', 'en' => '', 'pt' => ''];
        $this->title = array_merge($emptyLocales, $journal->getTranslations('title') ?? []);
        $this->abbreviated_name = array_merge($emptyLocales, $journal->getTranslations('abbreviated_name') ?? []);
        $this->description = array_merge($emptyLocales, $journal->getTranslations('description') ?? []);
        $this->subject_areas = $journal->subject_areas ?? [];
        $this->target_audience = $journal->target_audience ?? [];
        $this->publication_languages = $journal->publication_languages ?? [];
        $this->start_year = $journal->start_year;
        $this->url = $journal->url ?? '';
        $this->existing_logo = $journal->logo;

        // Step 3
        $this->license_type = $journal->license_type ?? '';
        $this->license_url = $journal->license_url ?? '';
        $this->authors_retain_copyright = $journal->authors_retain_copyright;
        $this->allows_commercial_reuse = $journal->allows_commercial_reuse;
        $this->copyright_policy = array_merge($emptyLocales, $journal->getTranslations('copyright_policy') ?? []);
        $this->licenses_visible_in_articles = $journal->licenses_visible_in_articles;

        // Step 4
        $this->publishing_institution = array_merge($emptyLocales, $journal->getTranslations('publishing_institution') ?? []);
        $this->country_code = $journal->country_code ?? '';
        $this->editor_name = array_merge($emptyLocales, $journal->getTranslations('editor_name') ?? []);
        $this->institutional_email = $journal->institutional_email ?? '';
        $this->editorial_board_visible = $journal->editorial_board_visible;
        $this->editorial_board_url = $journal->editorial_board_url ?? '';
        $this->peer_review_type = $journal->peer_review_type ?? '';
        $this->publication_frequency = $journal->publication_frequency ?? '';

        // Step 5
        $this->charges_apc = $journal->charges_apc;
        $this->apc_amount = $journal->apc_amount;
        $this->apc_currency = $journal->apc_currency ?? 'USD';
        $this->has_apc_waivers = $journal->has_apc_waivers;
        $this->funding_sources = $journal->funding_sources ?? [];
        $this->has_advertising = $journal->has_advertising;
        $this->business_model_transparent = $journal->business_model_transparent;

        // Step 6
        $this->has_ethics_policy = $journal->has_ethics_policy;
        $this->adheres_to_cope = $journal->adheres_to_cope;
        $this->has_antiplagiarism_policy = $journal->has_antiplagiarism_policy;
        $this->antiplagiarism_tool = $journal->antiplagiarism_tool ?? '';
        $this->has_conflict_of_interest_policy = $journal->has_conflict_of_interest_policy;
        $this->declares_ai_use = $journal->declares_ai_use;
        $this->assigns_doi = $journal->assigns_doi;

        // Step 7
        $this->issn_print = $journal->issn_print ?? '';
        $this->issn_online = $journal->issn_online ?? '';
        $this->publisher = $journal->publisher ?? '';

        // OAI
        $this->oai_base_url = $journal->oai_base_url ?? '';
        $this->oai_set_spec = $journal->oai_set_spec ?? '';
        $this->oai_metadata_prefix = $journal->oai_metadata_prefix ?? 'oai_dc';
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
        $primary = $this->primary_locale;
        $rules = match($this->currentStep) {
            1 => [
                "title.{$primary}" => 'required|string|min:3|max:255',
                "description.{$primary}" => 'required|string|min:50|max:2000',
                'subject_areas' => 'required|array|min:1',
                'publication_languages' => 'required|array|min:1',
                'url' => 'required|url',
            ],
            2 => [
                'is_open_access' => 'required|boolean',
                'access_type' => 'required|string',
                'articles_accessible_without_registration' => 'required|boolean',
            ],
            3 => [
                'license_type' => 'required|string',
                'authors_retain_copyright' => 'required|boolean',
            ],
            4 => [
                "publishing_institution.{$primary}" => 'required|string|max:255',
                'country_code' => 'required|string|size:2',
                "editor_name.{$primary}" => 'required|string|max:255',
                'institutional_email' => 'required|email',
                'editorial_board_visible' => 'required|boolean',
                'peer_review_type' => 'required|string',
                'publication_frequency' => 'required|string',
            ],
            5 => [
                'charges_apc' => 'required|boolean',
            ],
            6 => [
                'has_ethics_policy' => 'required|boolean',
                'has_antiplagiarism_policy' => 'required|boolean',
                'oai_base_url' => 'nullable|url',
                'oai_set_spec' => 'nullable|string',
                'oai_metadata_prefix' => 'nullable|string',
            ],
            default => [],
        };

        $this->validate($rules);
    }

    public function saveDraft()
    {
        // Filtra entradas vacías de los arrays de traducciones
        $cleanTranslations = fn(array $arr) => array_filter($arr, fn($v) => filled($v));

        $data = [
            'primary_locale' => $this->primary_locale,
            // Step 1
            'is_open_access' => $this->is_open_access,
            'access_type' => $this->access_type ?: null,
            'articles_accessible_without_registration' => $this->articles_accessible_without_registration,
            'allows_self_archiving' => $this->allows_self_archiving,
            'open_access_policy_url' => $this->open_access_policy_url ?: null,
            'has_embargo' => $this->has_embargo,
            'embargo_months' => $this->has_embargo ? $this->embargo_months : null,

            // Step 2
            'title' => $cleanTranslations($this->title),
            'slug' => Str::slug($this->title[$this->primary_locale] ?? '') . '-' . Str::random(6),
            'abbreviated_name' => $cleanTranslations($this->abbreviated_name),
            'description' => $cleanTranslations($this->description),
            'subject_areas' => $this->subject_areas,
            'target_audience' => $this->target_audience,
            'publication_languages' => $this->publication_languages,
            'start_year' => $this->start_year,
            'url' => $this->url ?: null,
            'logo' => $this->existing_logo,

            // Step 3
            'license_type' => $this->license_type ?: null,
            'license_url' => $this->license_url ?: null,
            'authors_retain_copyright' => $this->authors_retain_copyright,
            'allows_commercial_reuse' => $this->allows_commercial_reuse,
            'copyright_policy' => $cleanTranslations($this->copyright_policy),
            'licenses_visible_in_articles' => $this->licenses_visible_in_articles,

            // Step 4
            'publishing_institution' => $cleanTranslations($this->publishing_institution),
            'country_code' => $this->country_code ?: null,
            'editor_name' => $cleanTranslations($this->editor_name),
            'institutional_email' => $this->institutional_email ?: null,
            'editorial_board_visible' => $this->editorial_board_visible,
            'editorial_board_url' => $this->editorial_board_url ?: null,
            'peer_review_type' => $this->peer_review_type ?: null,
            'publication_frequency' => $this->publication_frequency ?: null,

            // Step 5
            'charges_apc' => $this->charges_apc,
            'apc_amount' => $this->charges_apc ? $this->apc_amount : null,
            'apc_currency' => $this->charges_apc ? $this->apc_currency : null,
            'has_apc_waivers' => $this->has_apc_waivers,
            'funding_sources' => $this->funding_sources,
            'has_advertising' => $this->has_advertising,
            'business_model_transparent' => $this->business_model_transparent,

            // Step 6
            'has_ethics_policy' => $this->has_ethics_policy,
            'adheres_to_cope' => $this->adheres_to_cope,
            'has_antiplagiarism_policy' => $this->has_antiplagiarism_policy,
            'antiplagiarism_tool' => $this->antiplagiarism_tool ?: null,
            'has_conflict_of_interest_policy' => $this->has_conflict_of_interest_policy,
            'declares_ai_use' => $this->declares_ai_use,
            'assigns_doi' => $this->assigns_doi,

            // Step 7
            'issn_print' => $this->issn_print ?: null,
            'issn_online' => $this->issn_online ?: null,
            'publisher' => $this->publisher ?: null,
            // OAI
            'oai_base_url' => $this->oai_base_url ?: null,
            'oai_set_spec' => $this->oai_set_spec ?: null,
            'oai_metadata_prefix' => $this->oai_metadata_prefix ?: 'oai_dc',
            'status' => 'draft',
        ];

        // Handle logo upload
        if ($this->logo && !is_string($this->logo)) {
            $data['logo'] = $this->logo->store('journal-logos', 'public');
            $this->existing_logo = $data['logo'];
        }

        if ($this->journal) {
            unset($data['slug']);
            $this->journal->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $this->journal = Journal::create($data);
        }
    }

    public function submit()
    {
        $this->saveDraft();
        return redirect()->route('app.checkout', $this->journal);
    }

    public function saveAsDraft()
    {
        $this->saveDraft();
        session()->flash('message', __('Draft saved successfully.'));
        return redirect()->route('app.dashboard');
    }

    public function listJournal()
    {
        $this->saveDraft();
        $this->journal->update(['status' => 'pending_listing']);

        // Notify user that listing request was received
        auth()->user()->notify(new ListingRequested($this->journal));

        session()->flash('message', __('Your request to list the journal has been submitted.'));
        return redirect()->route('app.dashboard');
    }

    public function render()
    {
        return view('livewire.submission-wizard', [
            'countries' => Countries::forSelect(),
        ])->layout('components.layouts.app', [
            'title' => __('Register Journal') . ' - Editorial Standards Platform',
        ]);
    }
}
