<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Journal;
use App\Models\Book;

class SearchJournals extends Component
{
    use WithPagination;

    public string $search = '';
    public string $country = '';
    public string $type = 'all'; // all, journals, books
    public string $sortBy = 'score'; // score, title, recent
    public string $subjectArea = '';
    public string $frequency = '';
    public string $accessType = '';
    public string $apcRange = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'country' => ['except' => ''],
        'type' => ['except' => 'all'],
        'sortBy' => ['except' => 'score'],
        'subjectArea' => ['except' => ''],
        'frequency' => ['except' => ''],
        'accessType' => ['except' => ''],
        'apcRange' => ['except' => ''],
    ];

    public const SUBJECT_AREAS = [
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

    public const FREQUENCIES = [
        'continuous' => 'Publicación continua',
        'monthly' => 'Mensual',
        'bimonthly' => 'Bimestral',
        'quarterly' => 'Trimestral',
        'biannual' => 'Semestral',
        'annual' => 'Anual',
    ];

    public const ACCESS_TYPES = [
        'full_oa' => 'Acceso Abierto completo',
        'hybrid' => 'Híbrido',
        'restricted' => 'Restringido',
    ];

    public const APC_RANGES = [
        'no_apc' => 'No cobra APC',
        '0-500' => 'Hasta $500 USD',
        '501-1500' => '$501 – $1,500 USD',
        '1501+' => 'Más de $1,500 USD',
    ];

    public const COUNTRIES = [
        'AR' => 'Argentina',
        'AU' => 'Australia',
        'BR' => 'Brasil',
        'CA' => 'Canadá',
        'CH' => 'Suiza',
        'CL' => 'Chile',
        'CN' => 'China',
        'CO' => 'Colombia',
        'CR' => 'Costa Rica',
        'CU' => 'Cuba',
        'DE' => 'Alemania',
        'EC' => 'Ecuador',
        'EG' => 'Egipto',
        'ES' => 'España',
        'FR' => 'Francia',
        'GB' => 'Reino Unido',
        'IN' => 'India',
        'IR' => 'Irán',
        'IT' => 'Italia',
        'JP' => 'Japón',
        'KE' => 'Kenia',
        'KR' => 'Corea del Sur',
        'MX' => 'México',
        'NG' => 'Nigeria',
        'NL' => 'Países Bajos',
        'NZ' => 'Nueva Zelanda',
        'PE' => 'Perú',
        'PK' => 'Pakistán',
        'PT' => 'Portugal',
        'TR' => 'Turquía',
        'US' => 'Estados Unidos',
        'UY' => 'Uruguay',
        'VE' => 'Venezuela',
        'ZA' => 'Sudáfrica',
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingCountry() { $this->resetPage(); }
    public function updatingType() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }
    public function updatingSubjectArea() { $this->resetPage(); }
    public function updatingFrequency() { $this->resetPage(); }
    public function updatingAccessType() { $this->resetPage(); }
    public function updatingApcRange() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'country', 'type', 'sortBy', 'subjectArea', 'frequency', 'accessType', 'apcRange']);
        $this->resetPage();
    }

    public static function countryName(string $code): string
    {
        return self::COUNTRIES[$code] ?? $code;
    }

    public static function countryFlag(string $code): string
    {
        $code = strtoupper($code);
        if (strlen($code) !== 2) return '';
        $flag = mb_chr(0x1F1E6 + ord($code[0]) - ord('A'))
              . mb_chr(0x1F1E6 + ord($code[1]) - ord('A'));
        return $flag;
    }

    public function render()
    {
        $journals = collect();
        $books = collect();

        // Get available countries for filter
        $countryCodes = Journal::whereIn('status', ['listed', 'evaluated', 'certified'])
            ->where(fn($q) => $q
                ->where('status', '!=', 'certified')
                ->orWhereNull('seal_expires_at')
                ->orWhere('seal_expires_at', '>', now())
            )
            ->whereNotNull('country_code')
            ->distinct()
            ->pluck('country_code')
            ->sort()
            ->values();

        if ($this->type !== 'books') {
            $journalQuery = Journal::query()
                ->whereIn('status', ['listed', 'evaluated', 'certified'])
                ->where(fn($q) => $q
                    ->where('status', '!=', 'certified')
                    ->orWhereNull('seal_expires_at')
                    ->orWhere('seal_expires_at', '>', now())
                )
                ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->country, fn($q) => $q->where('country_code', $this->country))
                ->when($this->subjectArea, fn($q) => $q->whereJsonContains('subject_areas', $this->subjectArea))
                ->when($this->frequency, fn($q) => $q->where('publication_frequency', $this->frequency))
                ->when($this->accessType, fn($q) => $q->where('access_type', $this->accessType))
                ->when($this->apcRange, fn($q) => match ($this->apcRange) {
                    'no_apc' => $q->where(fn($sub) => $sub->where('charges_apc', false)->orWhereNull('charges_apc')),
                    '0-500' => $q->where('charges_apc', true)->whereBetween('apc_amount', [0, 500]),
                    '501-1500' => $q->where('charges_apc', true)->whereBetween('apc_amount', [501, 1500]),
                    '1501+' => $q->where('charges_apc', true)->where('apc_amount', '>', 1500),
                    default => $q,
                });

            match ($this->sortBy) {
                'score' => $journalQuery->orderByDesc('current_score'),
                'title' => $journalQuery->orderBy('title'),
                'recent' => $journalQuery->orderByDesc('created_at'),
                default => $journalQuery->orderByDesc('current_score'),
            };

            if ($this->type === 'journals') {
                $journals = $journalQuery->paginate(12);
            } else {
                $journals = $journalQuery->get();
            }
        }

        if ($this->type !== 'journals') {
            $bookQuery = Book::query()
                ->where('status', 'listed')
                ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->subjectArea, fn($q) => $q->whereJsonContains('knowledge_areas', $this->subjectArea))
                ->when($this->accessType, fn($q) => $q->where('access_type', $this->accessType));

            match ($this->sortBy) {
                'score' => $bookQuery->orderByDesc('current_score'),
                'title' => $bookQuery->orderBy('title'),
                'recent' => $bookQuery->orderByDesc('created_at'),
                default => $bookQuery->orderByDesc('current_score'),
            };

            if ($this->type === 'books') {
                $books = $bookQuery->paginate(12);
            } else {
                $books = $bookQuery->get();
            }
        }

        // Counts by type
        $journalTotal = $this->type === 'books' ? 0 : ($this->type === 'journals' ? $journals->total() : $journals->count());
        $bookTotal = $this->type === 'journals' ? 0 : ($this->type === 'books' ? $books->total() : $books->count());

        // For "all" type, we merge and paginate manually
        if ($this->type === 'all') {
            $merged = $journals->map(fn($j) => ['item' => $j, 'type' => 'journal'])
                ->merge($books->map(fn($b) => ['item' => $b, 'type' => 'book']));

            // Sort merged
            $merged = match ($this->sortBy) {
                'score' => $merged->sortByDesc(fn($r) => $r['item']->current_score ?? 0),
                'title' => $merged->sortBy(fn($r) => $r['item']->title),
                'recent' => $merged->sortByDesc(fn($r) => $r['item']->created_at),
                default => $merged->sortByDesc(fn($r) => $r['item']->current_score ?? 0),
            };

            $page = $this->getPage();
            $perPage = 12;
            $total = $merged->count();
            $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            ]);

            return view('livewire.search-journals', [
                'results' => $paginator,
                'totalCount' => $total,
                'journalTotal' => $journalTotal,
                'bookTotal' => $bookTotal,
                'countryCodes' => $countryCodes,
                'isAllType' => true,
            ]);
        }

        // For single-type queries
        $paginatedResults = $this->type === 'books' ? $books : $journals;
        $wrappedResults = $paginatedResults->getCollection()->map(fn($item) => [
            'item' => $item,
            'type' => $this->type === 'books' ? 'book' : 'journal',
        ]);
        $paginatedResults->setCollection($wrappedResults);

        return view('livewire.search-journals', [
            'results' => $paginatedResults,
            'totalCount' => $paginatedResults->total(),
            'journalTotal' => $journalTotal,
            'bookTotal' => $bookTotal,
            'countryCodes' => $countryCodes,
            'isAllType' => false,
        ]);
    }
}
