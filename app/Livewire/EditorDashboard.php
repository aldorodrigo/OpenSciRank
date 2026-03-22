<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Journal;
use App\Models\Book;

class EditorDashboard extends Component
{
    public bool $showObservationsModal = false;
    public string $observationsNotes = '';
    public string $observationsTitle = '';
    public ?int $observationsJournalId = null;
    public ?int $observationsBookId = null;

    public function showObservations($id, string $type = 'journal')
    {
        if ($type === 'journal') {
            $record = Journal::where('user_id', auth()->id())->findOrFail($id);
            $this->observationsJournalId = $id;
            $this->observationsBookId = null;
            $this->observationsTitle = $record->title;
        } else {
            $record = Book::where('user_id', auth()->id())->findOrFail($id);
            $this->observationsBookId = $id;
            $this->observationsJournalId = null;
            $this->observationsTitle = $record->title;
        }

        $this->observationsNotes = $record->evaluation_notes ?? 'Sin observaciones registradas.';
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
        session()->flash('message', 'Revista eliminada exitosamente.');
    }

    public function resubmitForListing($journalId)
    {
        $journal = Journal::where('user_id', auth()->id())
            ->where('status', 'requires_changes_listing')
            ->findOrFail($journalId);

        $journal->update(['status' => 'pending_listing']);
        session()->flash('message', 'Tu revista ha sido reenviada para revision de listado.');
    }

    public function resubmitBookForListing($bookId)
    {
        $book = Book::where('user_id', auth()->id())
            ->where('status', 'requires_changes_listing')
            ->findOrFail($bookId);

        $book->update(['status' => 'pending_listing']);
        session()->flash('message', 'Tu libro ha sido reenviado para revision de listado.');
    }

    public function deleteBook($bookId)
    {
        $book = Book::where('user_id', auth()->id())->findOrFail($bookId);
        $book->delete();
        session()->flash('message', 'Libro eliminado exitosamente.');
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
            session()->flash('message', "✅ Cosecha completada: {$count} artículo(s) obtenidos.");
        } catch (\Exception $e) {
            session()->flash('error', "❌ Error en la cosecha: {$e->getMessage()}");
        }
    }

    public function render()
    {
        $journals = Journal::where('user_id', auth()->id())->get();
        $books = Book::where('user_id', auth()->id())->get();

        return view('livewire.editor-dashboard', [
            'journals' => $journals,
            'books' => $books,
        ])->layout('components.layouts.app', [
            'title' => 'Mi Panel - Editorial Standards Platform',
        ]);
    }
}
