<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use App\Models\CriteriaItem;
use App\Models\Journal;
use App\Models\JournalEvaluationScore;
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
    public string $assigned_status = 'indexed';

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
        $this->assigned_status = in_array($this->record->status, ['submitted', 'requires_changes'])
            ? 'indexed'
            : ($this->record->status ?? 'indexed');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Evaluar: ' . $this->record->title;
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

        // Si hay cores que fallan, máximo 49%
        if ($coresFailed) {
            $percentage = min($percentage, 49);
        }

        return round($percentage, 2);
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

        // Update journal with score, level, status and notes
        $this->record->update([
            'current_score' => $score,
            'current_level' => $this->assigned_level ?: null,
            'evaluation_notes' => $this->evaluation_notes,
            'evaluated_at' => now(),
            'status' => $this->assigned_status,
        ]);

        $coresFailed = $this->getCoresFailedCount();
        $body = "Nota final: {$score}%";
        if ($coresFailed > 0) {
            $body .= " — ⚠️ {$coresFailed} criterio(s) excluyente(s) no cumplido(s), nota limitada al 49%";
        }

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
