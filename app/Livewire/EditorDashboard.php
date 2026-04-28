<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Journal;
use App\Models\Book;

class EditorDashboard extends Component
{
    use WithPagination;

    public bool $showObservationsModal = false;
    public string $observationsNotes = '';
    public string $observationsTitle = '';
    public ?int $observationsJournalId = null;
    public ?int $observationsBookId = null;

    // Ordenamiento
    public string $journalSortField = 'title';
    public string $journalSortDir = 'asc';
    public string $bookSortField = 'title';
    public string $bookSortDir = 'asc';

    // Filtros de revistas
    public string $journalSearch = '';
    public string $journalStatusFilter = '';
    public string $journalScoreFilter = '';
    public string $journalSealFilter = '';

    // Filtros de libros
    public string $bookSearch = '';
    public string $bookStatusFilter = '';
    public string $bookScoreFilter = '';

    // Paginación separada por tabla
    public int $journalPage = 1;
    public int $bookPage = 1;
    public int $perPage = 5;

    public function updatedJournalSearch(): void { $this->journalPage = 1; }
    public function updatedJournalStatusFilter(): void { $this->journalPage = 1; }
    public function updatedJournalScoreFilter(): void { $this->journalPage = 1; }
    public function updatedJournalSealFilter(): void { $this->journalPage = 1; }
    public function updatedBookSearch(): void { $this->bookPage = 1; }
    public function updatedBookStatusFilter(): void { $this->bookPage = 1; }
    public function updatedBookScoreFilter(): void { $this->bookPage = 1; }

    public function changePerPage(int $value): void
    {
        $this->perPage = $value;
        $this->journalPage = 1;
        $this->bookPage = 1;
    }

    public function applyQuickFilter(string $type): void
    {
        $this->journalSearch = '';
        $this->journalStatusFilter = '';
        $this->journalScoreFilter = '';
        $this->journalSealFilter = '';
        $this->bookSearch = '';
        $this->bookStatusFilter = '';
        $this->bookScoreFilter = '';
        $this->journalPage = 1;
        $this->bookPage = 1;

        if ($type === 'certified') {
            $this->journalStatusFilter = 'certified';
        } elseif ($type === 'action_needed') {
            $this->journalStatusFilter = 'action_needed';
        } elseif ($type === 'submitted') {
            $this->journalStatusFilter = 'submitted';
            $this->bookStatusFilter = 'submitted';
        }

        $this->dispatch('scroll-to-tables');
    }

    public function sortJournals(string $field): void
    {
        if ($this->journalSortField === $field) {
            $this->journalSortDir = $this->journalSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->journalSortField = $field;
            $this->journalSortDir = 'asc';
        }
        $this->journalPage = 1;
    }

    public function sortBooks(string $field): void
    {
        if ($this->bookSortField === $field) {
            $this->bookSortDir = $this->bookSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->bookSortField = $field;
            $this->bookSortDir = 'asc';
        }
        $this->bookPage = 1;
    }

    public function showObservations($id, string $type = 'journal')
    {
        if ($type === 'journal') {
            $record = Journal::where('user_id', auth()->id())->findOrFail($id);
            $this->observationsJournalId = $id;
            $this->observationsBookId = null;
            $this->observationsTitle = $record->getTranslationWithFallback('title');
        } else {
            $record = Book::where('user_id', auth()->id())->findOrFail($id);
            $this->observationsBookId = $id;
            $this->observationsJournalId = null;
            $this->observationsTitle = $record->getTranslationWithFallback('title');
        }

        $this->observationsNotes = $record->evaluation_notes ?? __('No observations recorded.');
        $this->showObservationsModal = true;
    }

    public function confirmResubmitForListing()
    {
        if ($this->observationsJournalId) {
            $this->resubmitForListing($this->observationsJournalId);
        } elseif ($this->observationsBookId) {
            $this->resubmitBookForListing($this->observationsBookId);
        }
        $this->showObservationsModal = false;
        $this->observationsJournalId = null;
        $this->observationsBookId = null;
    }

    public function closeObservationsModal()
    {
        $this->showObservationsModal = false;
    }

    public function deleteJournal($journalId)
    {
        $journal = Journal::where('user_id', auth()->id())->findOrFail($journalId);
        $journal->delete();
        session()->flash('message', __('Journal deleted successfully.'));
    }

    public function resubmitForListing($journalId)
    {
        $journal = Journal::where('user_id', auth()->id())
            ->where('status', 'requires_changes_listing')
            ->findOrFail($journalId);

        $journal->update(['status' => 'pending_listing']);
        session()->flash('message', __('Your journal has been resubmitted for listing review.'));
    }

    public function resubmitBookForListing($bookId)
    {
        $book = Book::where('user_id', auth()->id())
            ->where('status', 'requires_changes_listing')
            ->findOrFail($bookId);

        $book->update(['status' => 'pending_listing']);
        session()->flash('message', __('Your book has been resubmitted for listing review.'));
    }

    public function deleteBook($bookId)
    {
        $book = Book::where('user_id', auth()->id())->findOrFail($bookId);
        $book->delete();
        session()->flash('message', __('Book deleted successfully.'));
    }

    public function harvestOai($journalId)
    {
        $journal = Journal::where('user_id', auth()->id())
            ->where('status', 'indexed')
            ->whereNotNull('oai_base_url')
            ->findOrFail($journalId);

        try {
            $service = app(\App\Services\OaiPmhService::class);
            $count = $service->listRecords($journal);
            session()->flash('message', '✅ ' . __('Harvest completed: :count article(s) retrieved.', ['count' => $count]));
        } catch (\Exception $e) {
            session()->flash('error', '❌ ' . __('Harvest error: :message', ['message' => $e->getMessage()]));
        }
    }

    private function computeBannerType($journals): string
    {
        $hasExpiredSeal = $journals->contains(fn ($j) => $j->seal_expires_at?->isPast());
        $hasExpiringSeal = $journals->contains(fn ($j) => $j->seal_expires_at && ! $j->seal_expires_at->isPast() && now()->diffInDays($j->seal_expires_at) <= 60);

        if ($hasExpiredSeal) {
            return 'seal_expired';
        }
        if ($hasExpiringSeal) {
            return 'seal_expiring';
        }
        if ($journals->isEmpty()) {
            return 'welcome';
        }
        if ($journals->every(fn ($j) => $j->status === 'draft')) {
            return 'drafts_only';
        }
        if ($journals->contains('status', 'evaluated') && ! $journals->contains('status', 'certified')) {
            return 'evaluated_not_certified';
        }
        if ($journals->contains('status', 'listed') && ! $journals->contains(fn ($j) => in_array($j->status, ['submitted', 'certified']))) {
            return 'listed_no_evaluation';
        }

        return 'none';
    }

    public function render()
    {
        $allJournals = Journal::where('user_id', auth()->id())->get();
        $allBooks = Book::where('user_id', auth()->id())->get();

        // Filtrado de revistas
        $filteredJournals = $allJournals
            ->when($this->journalSearch, fn ($c) => $c->filter(fn ($j) => str_contains(mb_strtolower($j->title), mb_strtolower($this->journalSearch))))
            ->when($this->journalStatusFilter, function ($c) {
                if ($this->journalStatusFilter === 'action_needed') {
                    return $c->whereIn('status', ['draft', 'listed', 'evaluated', 'requires_changes_listing', 'requires_changes_evaluation']);
                }
                return $c->where('status', $this->journalStatusFilter);
            })
            ->when($this->journalScoreFilter, fn ($c) => $c->filter(function ($j) {
                $score = $j->score;
                return match ($this->journalScoreFilter) {
                    'high' => $score >= 75,
                    'medium' => $score >= 50 && $score < 75,
                    'low' => $score > 0 && $score < 50,
                    'none' => $score === null || $score === 0,
                    default => true,
                };
            }))
            ->when($this->journalSealFilter, fn ($c) => $c->filter(function ($j) {
                return match ($this->journalSealFilter) {
                    'active' => $j->seal_status === 'active' && $j->seal_expires_at && ! $j->seal_expires_at->isPast(),
                    'expiring' => $j->seal_expires_at && ! $j->seal_expires_at->isPast() && now()->diffInDays($j->seal_expires_at) <= 60,
                    'expired' => $j->seal_expires_at?->isPast(),
                    'none' => ! $j->seal_status || $j->seal_status !== 'active',
                    default => true,
                };
            }));

        // Ordenamiento y paginación de revistas
        $sortedJournals = $filteredJournals->sortBy(
            $this->journalSortField,
            SORT_REGULAR,
            $this->journalSortDir === 'desc'
        );
        $journalTotal = $sortedJournals->count();
        $journals = $sortedJournals->slice(($this->journalPage - 1) * $this->perPage, $this->perPage)->values();

        // Filtrado de libros
        $filteredBooks = $allBooks
            ->when($this->bookSearch, fn ($c) => $c->filter(fn ($b) => str_contains(mb_strtolower($b->title), mb_strtolower($this->bookSearch))))
            ->when($this->bookStatusFilter, fn ($c) => $c->where('status', $this->bookStatusFilter))
            ->when($this->bookScoreFilter, fn ($c) => $c->filter(function ($b) {
                $score = $b->score;
                return match ($this->bookScoreFilter) {
                    'high' => $score >= 75,
                    'medium' => $score >= 50 && $score < 75,
                    'low' => $score > 0 && $score < 50,
                    'none' => $score === null || $score === 0,
                    default => true,
                };
            }));

        // Ordenamiento y paginación de libros
        $sortedBooks = $filteredBooks->sortBy(
            $this->bookSortField,
            SORT_REGULAR,
            $this->bookSortDir === 'desc'
        );
        $bookTotal = $sortedBooks->count();
        $books = $sortedBooks->slice(($this->bookPage - 1) * $this->perPage, $this->perPage)->values();

        $certifiedCount = $allJournals->where('status', 'certified')->count();
        $actionNeededCount = $allJournals->whereIn('status', ['draft', 'listed', 'evaluated', 'requires_changes_listing', 'requires_changes_evaluation'])->count();
        $bannerType = $this->computeBannerType($allJournals);

        $evaluatedJournal = $bannerType === 'evaluated_not_certified'
            ? $allJournals->firstWhere('status', 'evaluated')
            : null;

        $listedJournal = $bannerType === 'listed_no_evaluation'
            ? $allJournals->firstWhere('status', 'listed')
            : null;

        $sealJournal = in_array($bannerType, ['seal_expired', 'seal_expiring'])
            ? $allJournals->first(fn ($j) => $j->seal_expires_at && ($j->seal_expires_at->isPast() || now()->diffInDays($j->seal_expires_at) <= 60))
            : null;

        return view('livewire.editor-dashboard', [
            'journals' => $journals,
            'journalTotal' => $journalTotal,
            'books' => $books,
            'bookTotal' => $bookTotal,
            'allJournals' => $allJournals,
            'allBooks' => $allBooks,
            'certifiedCount' => $certifiedCount,
            'actionNeededCount' => $actionNeededCount,
            'bannerType' => $bannerType,
            'evaluatedJournal' => $evaluatedJournal,
            'listedJournal' => $listedJournal,
            'sealJournal' => $sealJournal,
        ])->layout('components.layouts.app', [
            'title' => __('My Dashboard') . ' - Editorial Standards Platform',
        ]);
    }
}
