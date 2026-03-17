<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Journal;
use App\Models\Book;

class EditorDashboard extends Component
{
    public function deleteJournal($journalId)
    {
        $journal = Journal::where('user_id', auth()->id())->findOrFail($journalId);
        $journal->delete();
        session()->flash('message', 'Revista eliminada exitosamente.');
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
            'title' => 'Mi Panel - OpenSciRank',
        ]);
    }
}
