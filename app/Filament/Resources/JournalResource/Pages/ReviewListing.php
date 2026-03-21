<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use App\Models\Journal;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReviewListing extends Page
{
    use InteractsWithRecord;

    protected static string $resource = JournalResource::class;

    protected string $view = 'filament.resources.journal-resource.pages.review-listing';

    public string $evaluation_notes = '';
    public string $assigned_status = 'listed';
    public bool $showConfirmModal = false;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();

        $this->evaluation_notes = $this->record->evaluation_notes ?? '';
        
        // If it's already listed/rejected/requires_changes, keep that status
        if (in_array($this->record->status, ['listed', 'rejected', 'requires_changes'])) {
            $this->assigned_status = $this->record->status;
        } else {
            $this->assigned_status = 'listed';
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Revisar Solicitud de Listado: ' . $this->record->title;
    }

    public function confirmSave(): void
    {
        $this->showConfirmModal = true;
    }

    public function cancelSave(): void
    {
        $this->showConfirmModal = false;
    }

    public function save(): void
    {
        $this->showConfirmModal = false;

        $this->record->update([
            'evaluation_notes' => $this->evaluation_notes,
            'status' => $this->assigned_status,
        ]);

        $statusText = match ($this->assigned_status) {
            'listed' => 'listada',
            'rejected' => 'rechazada',
            'requires_changes' => 'enviada para correcciones',
            default => 'actualizada',
        };

        Notification::make()
            ->title('Revisión completada')
            ->body("La revista ha sido {$statusText} correctamente.")
            ->success()
            ->send();

        $this->redirect(JournalResource::getUrl('index'));
    }
}
