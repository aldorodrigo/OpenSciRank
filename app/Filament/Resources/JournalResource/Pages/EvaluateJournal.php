<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Actions\JournalOaiActions;
use App\Filament\Resources\JournalResource;
use App\Jobs\HarvestJournalArticles;
use App\Jobs\RefreshJournalMetricsJob;
use App\Models\AdminTask;
use App\Models\Conversation;
use App\Models\CriteriaItem;
use App\Models\Journal;
use App\Models\JournalEvaluationScore;
use App\Notifications\ChangesRequested;
use App\Notifications\EvaluationCompleted;
use App\Notifications\NewConversationOpened;
use App\Notifications\SealRenewalApproved;
use App\Notifications\SealRenewalRejected;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class EvaluateJournal extends Page
{
    use InteractsWithRecord;

    protected static string $resource = JournalResource::class;

    protected string $view = 'filament.resources.journal-resource.pages.evaluate-journal';

    public array $scores = [];

    public string $evaluation_notes = '';

    public bool $showConfirmModal = false;

    public string $assigned_level = '';

    public string $assigned_status = 'evaluated';

    /**
     * Sprint 3.6 #32 Fase 2 UX: la task abierta de evaluación asociada
     * a este journal (si existe). Se muestra como banner contextual en
     * la vista para que el admin sepa que está "trabajando en una task"
     * y pueda volver al listado o ver el detalle.
     */
    public function getCurrentTaskProperty(): ?AdminTask
    {
        return AdminTask::query()
            ->where('related_type', Journal::class)
            ->where('related_id', $this->record->id)
            ->whereIn('type', [
                AdminTask::TYPE_EVALUATE_JOURNAL,
                AdminTask::TYPE_REEVALUATE_JOURNAL,
                AdminTask::TYPE_RENEWAL_EVALUATION,
            ])
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * True si la evaluación en curso viene de un pago Express (+$50).
     * El evaluador necesita saberlo para priorizar — entrega en plazo
     * reducido (sla_evaluation_express_business_days, default 5d).
     */
    public function getIsExpressEvaluationProperty(): bool
    {
        $payment = $this->currentTask?->payment;

        return (bool) ($payment?->metadata['is_express'] ?? false);
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();

        // Roadmap #35 — defensa en profundidad: aunque el listado ya filtra
        // por assigned_evaluator_id, bloqueamos acceso directo por URL a
        // journals no asignados al evaluador logueado. Con la asignación
        // unificada (Journal::assignEvaluator) ambos campos coinciden, pero
        // autorizamos también por la task abierta para no depender de un solo
        // campo si alguno quedara desincronizado.
        $user = auth()->user();
        if ($user->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            // Roadmap #35 — conflicto de interés: un evaluador NUNCA puede
            // evaluar una revista de la que es dueño/editor, aunque por error
            // haya quedado asignado. Red de seguridad final.
            abort_if($this->record->user_id === $user->id, 403);

            $assignedByJournal = $this->record->assigned_evaluator_id === $user->id;
            $assignedByTask = $this->currentTask?->assigned_to === $user->id;

            abort_unless($assignedByJournal || $assignedByTask, 403);
        }

        // Load existing scores
        $existingScores = $this->record->evaluationScores()
            ->pluck('is_met', 'criteria_item_id')
            ->toArray();

        // Initialize scores array
        $criteria = CriteriaItem::active()->orderBy('order')->get();
        foreach ($criteria as $item) {
            $this->scores[$item->id] = $existingScores[$item->id] ?? false;
        }

        $this->evaluation_notes = $this->record->evaluation_notes ?? '';
        $this->assigned_level = $this->record->current_level ?? '';
        $this->assigned_status = in_array($this->record->status, ['submitted', 'requires_changes_evaluation'])
            ? 'evaluated'
            : ($this->record->status ?? 'evaluated');
    }

    public function getTitle(): string|Htmlable
    {
        $title = $this->record->getTranslationWithFallback('title');

        return __('admin.eval_page.title', ['name' => $title]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_certificate')
                ->label(__('Download Certificate (PDF)'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('app.journal.certificate.pdf', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->status === 'certified'
                    && $this->record->seal_status === 'active'
                    && $this->record->seal_expires_at
                    && $this->record->seal_expires_at->isFuture()
                ),

            Action::make('download_report')
                ->label(__('Download Evaluation Report (PDF)'))
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('app.journal.report.pdf', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->evaluated_at !== null),

            // #57 — evidencia OAI/métricas dentro de la tarea de evaluación.
            // El mount() ya autoriza al evaluador asignado (o super_admin), por eso
            // no re-chequeamos rol; testConnection/harvest ya se ocultan sin oai_base_url.
            JournalOaiActions::testConnection(fn (): Journal => $this->getRecord()),
            JournalOaiActions::harvest(fn (): Journal => $this->getRecord()),
            JournalOaiActions::refreshMetrics(fn (): Journal => $this->getRecord()),
        ];
    }

    public function getCriteriaByCategory(): Collection
    {
        return CriteriaItem::active()
            ->with('category')
            ->orderBy('order')
            ->get()
            ->groupBy(fn ($item) => $item->category?->name ?? __('admin.eval_page.no_category'));
    }

    public function getCompletedCount(): int
    {
        return collect($this->scores)->filter(fn ($v) => $v)->count();
    }

    public function getTotalCount(): int
    {
        return count($this->scores);
    }

    public function getCompletionPercentage(): float
    {
        if ($this->getTotalCount() === 0) {
            return 0;
        }

        return round(($this->getCompletedCount() / $this->getTotalCount()) * 100, 1);
    }

    public function calculateScore(): float
    {
        $criteria = CriteriaItem::active()->get()->keyBy('id');

        $totalWeight = 0;
        $earnedWeight = 0;
        $coresFailed = false;

        foreach ($this->scores as $criteriaId => $isMet) {
            $item = $criteria->get($criteriaId);
            if (! $item) {
                continue;
            }

            $totalWeight += $item->weight;

            if ($isMet) {
                $earnedWeight += $item->weight;
            } elseif ($item->is_core) {
                $coresFailed = true;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        $percentage = ($earnedWeight / $totalWeight) * 100;

        // Regla del Documento Maestro: Si hay cores que fallan, máximo 49%
        if ($coresFailed) {
            $percentage = min($percentage, 49);
        }

        return round($percentage, 2);
    }

    /**
     * Determine suggested level based on score
     * A: 80-100, B: 60-79, C: 40-59
     */
    public function getSuggestedLevel(): string
    {
        $score = $this->calculateScore();

        if ($score >= 80) {
            return 'A';
        }
        if ($score >= 60) {
            return 'B';
        }
        if ($score >= 40) {
            return 'C';
        }

        return '';
    }

    /**
     * Check if journal qualifies for the Editorial Standards Seal
     * Condition: Score >= 75 AND ALL critical indicators met
     */
    public function qualifiesForSeal(): bool
    {
        $score = $this->calculateScore();
        if ($score < 75) {
            return false;
        }

        // Critical Indicators (Criteria codes defined in methodology)
        $criticalCodes = ['1.1', '2.1', '3.1', '4.2', '5.1'];
        $criticalItems = CriteriaItem::whereIn('code', $criticalCodes)->get()->keyBy('id');

        foreach ($criticalItems as $itemId => $item) {
            if (empty($this->scores[$itemId])) {
                return false;
            }
        }

        return true;
    }

    public function getCoresFailedCount(): int
    {
        $criteria = CriteriaItem::active()->where('is_core', true)->get()->keyBy('id');
        $count = 0;

        foreach ($this->scores as $criteriaId => $isMet) {
            $item = $criteria->get($criteriaId);
            if ($item && ! $isMet) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get progress per category: ['Category Name' => ['completed' => X, 'total' => Y]]
     */
    public function getCategoryProgress(): array
    {
        $progress = [];
        foreach ($this->getCriteriaByCategory() as $categoryName => $items) {
            $total = $items->count();
            $completed = $items->filter(fn ($item) => ! empty($this->scores[$item->id]))->count();
            $progress[$categoryName] = [
                'completed' => $completed,
                'total' => $total,
            ];
        }

        return $progress;
    }

    /**
     * Toggle all criteria in a given category
     */
    public function toggleAllInCategory(string $categoryName, bool $value): void
    {
        $items = CriteriaItem::active()
            ->with('category')
            ->orderBy('order')
            ->get()
            ->filter(fn ($item) => ($item->category?->name ?? __('admin.eval_page.no_category')) === $categoryName);

        foreach ($items as $item) {
            $this->scores[$item->id] = $value;
        }
    }

    /**
     * Open confirmation modal before saving
     */
    public function confirmSave(): void
    {
        $this->assigned_level = $this->getSuggestedLevel();

        // Sugerir estado basado en si califica para el sello
        if ($this->qualifiesForSeal()) {
            $this->assigned_status = 'certified';
        } else {
            // Si ya estaba en requires_changes_evaluation o rejected, mantenerlo o sugerir evaluated
            if (! in_array($this->assigned_status, ['requires_changes_evaluation', 'rejected'])) {
                $this->assigned_status = 'evaluated';
            }
        }

        $this->showConfirmModal = true;
    }

    /**
     * Close confirmation modal
     */
    public function cancelSave(): void
    {
        $this->showConfirmModal = false;
    }

    public function save(): void
    {
        $this->showConfirmModal = false;

        // Save each score
        foreach ($this->scores as $criteriaId => $isMet) {
            JournalEvaluationScore::updateOrCreate(
                [
                    'journal_id' => $this->record->id,
                    'criteria_item_id' => $criteriaId,
                ],
                [
                    'is_met' => $isMet,
                    'evaluator_id' => auth()->id(),
                ]
            );
        }

        $score = $this->calculateScore();

        // Capturar antes del update: si el admin pide cambios, conservamos
        // pending_renewal_years; en los demás paths lo limpiamos.
        $pendingYears = $this->record->pending_renewal_years;
        $isRenewalFlow = $pendingYears !== null;
        $qualifies = $this->qualifiesForSeal();

        if ($isRenewalFlow) {
            // Política Opción B: si la re-evaluación aprueba, extender sello;
            // si no, sin reembolso y queda evaluated.
            // Excepción: si el admin eligió requires_changes_evaluation o
            // rejected, respetar su decisión — pedir cambios mantiene el
            // contexto de renovación para cuando el editor resuba; rechazar
            // la cancela definitivamente.
            if (! in_array($this->assigned_status, ['requires_changes_evaluation', 'rejected'])) {
                $this->assigned_status = $qualifies ? 'certified' : 'evaluated';
            }
        }

        // Preservar pending_renewal_years sólo cuando el admin pide cambios:
        // el editor resubirá y la renovación seguirá su curso.
        $clearPendingRenewal = ! $isRenewalFlow
            || $this->assigned_status !== 'requires_changes_evaluation';

        $updateData = [
            'current_score' => $score,
            'current_level' => $this->assigned_level ?: null,
            'evaluation_notes' => $this->evaluation_notes,
            'evaluated_at' => now(),
            'status' => $this->assigned_status,
        ];

        if ($clearPendingRenewal) {
            $updateData['pending_renewal_years'] = null;
        }

        $this->record->update($updateData);

        if ($isRenewalFlow && $this->assigned_status === 'certified') {
            // Extender el sello por los años pagados (suma sobre el seal_expires_at vigente).
            $this->record->renewSeal($pendingYears);
        } elseif (! $isRenewalFlow && $this->assigned_status === 'certified') {
            // Flujo de evaluación normal certificada — sello de 1 año.
            $this->record->awardSeal(1);
        }

        // #57 — al certificar, encolar cosecha OAI + refresco de métricas para tener
        // datos frescos desde el día 1 (async, no bloquea el guardado de la evaluación).
        if ($this->assigned_status === 'certified') {
            if (! empty($this->record->oai_base_url)) {
                $this->record->update(['oai_harvest_status' => 'queued']);
                HarvestJournalArticles::dispatch($this->record, causedByUserId: auth()->id());
            }

            RefreshJournalMetricsJob::dispatch($this->record);
        }

        $coresFailed = $this->getCoresFailedCount();
        $body = __('admin.eval_page.final_score', ['score' => $score]);
        if ($coresFailed > 0) {
            $body .= __('admin.eval_page.cores_failed', ['count' => $coresFailed]);
        }

        // Notify journal owner via email
        $owner = $this->record->user;
        if ($owner) {
            if ($isRenewalFlow && $this->assigned_status === 'certified') {
                $owner->notify(new SealRenewalApproved($this->record->fresh(), $pendingYears));
            } elseif ($isRenewalFlow && $this->assigned_status === 'requires_changes_evaluation') {
                // Renovación con cambios pedidos: mantenemos el contexto.
                // El editor resube y la renovación sigue su curso.
                $owner->notify(new ChangesRequested($this->record, 'evaluation', $this->evaluation_notes));
            } elseif ($isRenewalFlow) {
                // evaluated (no alcanzó 75%) o rejected (admin canceló) → sin sello, sin reembolso.
                $owner->notify(new SealRenewalRejected($this->record->fresh()));
            } elseif ($this->assigned_status === 'requires_changes_evaluation') {
                $owner->notify(new ChangesRequested($this->record, 'evaluation', $this->evaluation_notes));
            } else {
                $owner->notify(new EvaluationCompleted($this->record->fresh()));
            }
        }

        // Sprint 3.6 #32: auto-cerrar admin_tasks asociadas cuando llegamos
        // a status terminal (certified/evaluated/rejected). Si es
        // requires_changes_evaluation, la task queda abierta — el admin sigue
        // trabajando, solo pausa para que el editor corrija.
        $terminalStatuses = ['certified', 'evaluated', 'rejected'];
        if (in_array($this->assigned_status, $terminalStatuses, true)) {
            $taskTypes = $isRenewalFlow
                ? [AdminTask::TYPE_RENEWAL_EVALUATION]
                : [AdminTask::TYPE_EVALUATE_JOURNAL, AdminTask::TYPE_REEVALUATE_JOURNAL];

            $closed = AdminTask::query()
                ->where('related_type', Journal::class)
                ->where('related_id', $this->record->id)
                ->whereIn('type', $taskTypes)
                ->whereIn('status', AdminTask::STATUSES_OPEN)
                ->get();

            foreach ($closed as $task) {
                $task->complete("Cerrada automáticamente: evaluación finalizada con status {$this->assigned_status}, score {$score}%");
            }
        }

        // SLA: días entre submisión y evaluación, si tenemos submitted_at.
        $slaDays = $this->record->submitted_at
            ? (int) $this->record->submitted_at->startOfDay()->diffInDays(now()->startOfDay(), false)
            : null;

        activity()
            ->performedOn($this->record)
            ->causedBy(auth()->user())
            ->withProperties([
                'score' => $score,
                'cores_failed' => $coresFailed,
                'final_status' => $this->assigned_status,
                'level' => $this->assigned_level ?: null,
                'seal_awarded' => $this->assigned_status === 'certified',
                'renewal_flow' => $isRenewalFlow,
                'renewal_years' => $isRenewalFlow ? $pendingYears : null,
                'renewal_context_preserved' => $isRenewalFlow && ! $clearPendingRenewal,
                'sla_days' => $slaDays,
            ])
            ->log(($isRenewalFlow ? 'Renovación evaluada' : 'Evaluación completada').": {$score}% — estado: {$this->assigned_status}");

        Notification::make()
            ->title(__('admin.eval_page.completed'))
            ->body($body)
            ->success()
            ->send();

        // Roadmap #35 — el evaluador vuelve a su escritorio; el super_admin al
        // listado de revistas.
        $user = auth()->user();
        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            $this->redirect(\App\Filament\Pages\EvaluatorDesk::getUrl());
        } else {
            $this->redirect(JournalResource::getUrl('index'));
        }
    }

    public function saveDraft(): void
    {
        // Save scores without finalizing
        foreach ($this->scores as $criteriaId => $isMet) {
            JournalEvaluationScore::updateOrCreate(
                [
                    'journal_id' => $this->record->id,
                    'criteria_item_id' => $criteriaId,
                ],
                [
                    'is_met' => $isMet,
                    'evaluator_id' => auth()->id(),
                ]
            );
        }

        $this->record->update([
            'evaluation_notes' => $this->evaluation_notes,
        ]);

        $marked = $this->getCompletedCount();

        Notification::make()
            ->title(__('admin.eval_page.draft_saved'))
            ->body(__('admin.eval_page.draft_progress', ['marked' => $marked]))
            ->success()
            ->send();
    }

    // ── Roadmap #35: mensajería evaluator↔editor anclada al Journal ──────

    /**
     * Rol de mensajería del usuario que mira esta página: 'evaluator' para el
     * evaluador asignado, 'admin' para el super_admin. Define UI y permisos del
     * hilo embebido.
     */
    public function getMessagingRoleProperty(): string
    {
        $user = auth()->user();

        return ($user->hasRole('evaluator') && ! $user->hasRole('super_admin'))
            ? 'evaluator'
            : 'admin';
    }

    /**
     * Hilo OPEN anclado a este Journal, si existe. Es el canal del evaluador
     * para consultar al editor sin exponer la bandeja global de conversaciones.
     */
    public function getEditorConversationProperty(): ?Conversation
    {
        return Conversation::query()
            ->where('subject_type', Journal::class)
            ->where('subject_id', $this->record->id)
            ->where('status', Conversation::STATUS_OPEN)
            ->latest('last_message_at')
            ->first();
    }

    /**
     * Abre (o reusa) el hilo con el editor de esta revista. Solo el evaluador
     * asignado o un super_admin. Idempotente: si ya hay un hilo OPEN, no crea
     * otro. Ancla el hilo al Journal para que el editor lo vea en su bandeja.
     */
    public function startEditorConversation(): void
    {
        $user = auth()->user();

        abort_unless(
            $user->hasRole('super_admin')
            || ($user->hasRole('evaluator') && $this->record->assigned_evaluator_id === $user->id),
            403
        );

        // Idempotente: si ya hay hilo abierto, no hacemos nada (la vista ya lo embebe).
        if ($this->editorConversation) {
            return;
        }

        $editor = $this->record->user;
        if (! $editor) {
            Notification::make()
                ->title(__('evaluator_msg.no_editor'))
                ->danger()
                ->send();

            return;
        }

        $conversation = Conversation::create([
            'subject' => __('evaluator_msg.subject', ['title' => $this->record->getTranslationWithFallback('title')]),
            'subject_type' => Journal::class,
            'subject_id' => $this->record->id,
            'started_by_user_id' => $user->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $conversation->addParticipant(
            $user,
            $this->messagingRole === 'evaluator' ? Conversation::ROLE_EVALUATOR : Conversation::ROLE_ADMIN
        );
        $conversation->addParticipant($editor, Conversation::ROLE_EDITOR);

        try {
            $editor->notify(new NewConversationOpened($conversation));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NewConversationOpened not sent (evaluator): '.$e->getMessage(), [
                'journal_id' => $this->record->id,
                'conversation_id' => $conversation->id,
            ]);
        }

        Notification::make()
            ->title(__('evaluator_msg.started'))
            ->success()
            ->send();
    }
}
