<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use App\Models\AdminTask;
use App\Models\CriteriaItem;
use App\Models\Journal;
use App\Models\JournalEvaluationScore;
use App\Notifications\ChangesRequested;
use App\Notifications\EvaluationCompleted;
use App\Notifications\SealRenewalApproved;
use App\Notifications\SealRenewalRejected;
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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();

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

    public function getTitle(): string | Htmlable
    {
        return 'Evaluar: ' . $this->record->getTranslationWithFallback('title');
    }

    public function getCriteriaByCategory(): Collection
    {
        return CriteriaItem::active()
            ->with('category')
            ->orderBy('order')
            ->get()
            ->groupBy(fn ($item) => $item->category?->name ?? 'Sin categoría');
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
        if ($this->getTotalCount() === 0) return 0;
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
            if (!$item) continue;

            $totalWeight += $item->weight;

            if ($isMet) {
                $earnedWeight += $item->weight;
            } elseif ($item->is_core) {
                $coresFailed = true;
            }
        }

        if ($totalWeight === 0) return 0;

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
        
        if ($score >= 80) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 40) return 'C';
        
        return '';
    }

    /**
     * Check if journal qualifies for the Editorial Standards Seal
     * Condition: Score >= 75 AND ALL critical indicators met
     */
    public function qualifiesForSeal(): bool
    {
        $score = $this->calculateScore();
        if ($score < 75) return false;

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
            if ($item && !$isMet) {
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
            $completed = $items->filter(fn ($item) => !empty($this->scores[$item->id]))->count();
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
            ->filter(fn ($item) => ($item->category?->name ?? 'Sin categoría') === $categoryName);

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
            if (!in_array($this->assigned_status, ['requires_changes_evaluation', 'rejected'])) {
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

        $coresFailed = $this->getCoresFailedCount();
        $body = "Nota final: {$score}%";
        if ($coresFailed > 0) {
            $body .= " — ⚠️ {$coresFailed} criterio(s) excluyente(s) no cumplido(s), nota limitada al 49%";
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
            ->log(($isRenewalFlow ? 'Renovación evaluada' : 'Evaluación completada') . ": {$score}% — estado: {$this->assigned_status}");

        Notification::make()
            ->title('Evaluación completada')
            ->body($body)
            ->success()
            ->send();

        $this->redirect(JournalResource::getUrl('index'));
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

        Notification::make()
            ->title('Borrador guardado')
            ->body('Progreso: ' . $this->getCompletedCount() . '/' . $this->getTotalCount() . ' criterios marcados')
            ->success()
            ->send();
    }
}
