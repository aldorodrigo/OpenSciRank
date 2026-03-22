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
    public string $level = '';
    public string $country = '';
    public string $type = 'all'; // all, journals, books
    public string $sortBy = 'score'; // score, title, recent

    protected $queryString = [
        'search' => ['except' => ''],
        'level' => ['except' => ''],
        'country' => ['except' => ''],
        'type' => ['except' => 'all'],
        'sortBy' => ['except' => 'score'],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingLevel() { $this->resetPage(); }
    public function updatingCountry() { $this->resetPage(); }
    public function updatingType() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'level', 'country', 'type', 'sortBy']);
        $this->resetPage();
    }

    public function render()
    {
        $journals = collect();
        $books = collect();

        // Get available countries for filter
        $countries = Journal::whereIn('status', ['listed', 'evaluated', 'certified'])
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
                ->when($this->level, fn($q) => $q->where('current_level', $this->level))
                ->when($this->country, fn($q) => $q->where('country_code', $this->country));

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
                ->when($this->level, fn($q) => $q->where('current_level', $this->level));

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

            $page = request()->get('page', 1);
            $perPage = 12;
            $total = $merged->count();
            $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);

            return view('livewire.search-journals', [
                'results' => $paginator,
                'totalCount' => $total,
                'countries' => $countries,
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
            'countries' => $countries,
            'isAllType' => false,
        ]);
    }
}
