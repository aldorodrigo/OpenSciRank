<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use App\Models\AdminTask;
use App\Models\Journal;
use App\Notifications\ChangesRequested;
use App\Notifications\ListingApproved;
use App\Notifications\ListingRejected;
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

    /**
     * Sprint 3.6 #32 Fase 2 UX: la task abierta de listing asociada
     * a este journal (si existe) — banner contextual.
     */
    public function getCurrentTaskProperty(): ?AdminTask
    {
        return AdminTask::query()
            ->where('related_type', Journal::class)
            ->where('related_id', $this->record->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_JOURNAL)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->orderByDesc('created_at')
            ->first();
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();

        // Roadmap #35 — el flujo de listing review (gratuito) queda fuera
        // del alcance del rol evaluator: no existe campo de asignación para
        // este flujo, así que se bloquea del todo en vez de heredar acceso
        // implícito vía los permisos de Journal.
        $user = auth()->user();
        abort_if($user->hasRole('evaluator') && ! $user->hasRole('super_admin'), 403);

        $this->evaluation_notes = $this->record->evaluation_notes ?? '';

        // If it's already listed/rejected/requires_changes_listing, keep that status
        if (in_array($this->record->status, ['listed', 'rejected', 'requires_changes_listing'])) {
            $this->assigned_status = $this->record->status;
        } else {
            $this->assigned_status = 'listed';
        }
    }

    public function getTitle(): string | Htmlable
    {
        $title = $this->record->getTranslationWithFallback('title');

        return __('admin.review_page.title', ['name' => $title]);
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

        $updateData = [
            'evaluation_notes' => $this->evaluation_notes,
            'status' => $this->assigned_status,
        ];

        if ($this->assigned_status === 'listed' && !$this->record->listed_at) {
            $updateData['listed_at'] = now();
        }

        $this->record->update($updateData);

        $statusText = match ($this->assigned_status) {
            'listed' => __('admin.review_page.status_listed'),
            'rejected' => __('admin.review_page.status_rejected'),
            'requires_changes_listing' => __('admin.review_page.status_changes'),
            default => __('admin.review_page.status_updated'),
        };

        // Notify journal owner via email
        $owner = $this->record->user;
        if ($owner) {
            match ($this->assigned_status) {
                'listed' => $owner->notify(new ListingApproved($this->record)),
                'rejected' => $owner->notify(new ListingRejected($this->record, $this->evaluation_notes)),
                'requires_changes_listing' => $owner->notify(new ChangesRequested($this->record, 'listing', $this->evaluation_notes)),
                default => null,
            };
        }

        // Sprint 3.6 #32: cerrar admin_task asociada cuando el listing
        // termina (listed/rejected). requires_changes_listing deja la
        // task abierta porque el admin sigue trabajando.
        if (in_array($this->assigned_status, ['listed', 'rejected'], true)) {
            AdminTask::query()
                ->where('related_type', Journal::class)
                ->where('related_id', $this->record->id)
                ->where('type', AdminTask::TYPE_REVIEW_LISTING_JOURNAL)
                ->whereIn('status', AdminTask::STATUSES_OPEN)
                ->get()
                ->each(fn (AdminTask $task) => $task->complete(
                    "Cerrada automáticamente: listing revisado con status {$this->assigned_status}"
                ));
        }

        activity()
            ->performedOn($this->record)
            ->causedBy(auth()->user())
            ->withProperties([
                'final_status' => $this->assigned_status,
                'has_notes' => filled($this->evaluation_notes),
            ])
            ->log("Revisión de listado: {$statusText}");

        Notification::make()
            ->title(__('admin.review_page.completed'))
            ->body(__('admin.review_page.body', ['status' => $statusText]))
            ->success()
            ->send();

        $this->redirect(JournalResource::getUrl('index'));
    }
}
