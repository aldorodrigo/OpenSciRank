<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Pages\EvaluatorDesk;
use App\Filament\Resources\JournalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJournals extends ListRecords
{
    protected static string $resource = JournalResource::class;

    /**
     * Roadmap #35 — el recurso Journals está oculto del nav para el evaluator,
     * pero la URL /admin/journals seguiría siendo alcanzable (tiene ViewAny:Journal
     * por requisito de EvaluateJournal). Blindamos la lista por URL: el evaluator
     * es redirigido a su cola de Tareas.
     */
    public function mount(): void
    {
        $user = auth()->user();

        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            $this->redirect(EvaluatorDesk::getUrl());

            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
