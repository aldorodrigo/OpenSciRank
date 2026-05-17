<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Journal;

/**
 * Agrupa los scores de evaluación de una revista por categoría
 * para mostrar el desglose en el informe PDF. Reusa la misma fuente
 * de verdad que Journal::calculateScore() pero la devuelve estructurada.
 */
class JournalEvaluationBreakdown
{
    /**
     * @return array{
     *   total_score: float,
     *   core_failed: bool,
     *   categories: array<int, array{
     *     id: int,
     *     name: string,
     *     score: float,
     *     items: array<int, array{
     *       name: string,
     *       description: ?string,
     *       weight: int,
     *       is_core: bool,
     *       is_met: bool,
     *       notes: ?string,
     *     }>
     *   }>
     * }
     */
    public function forJournal(Journal $journal): array
    {
        $scores = $journal->evaluationScores()
            ->with(['criteriaItem.category'])
            ->get()
            ->filter(fn ($s) => $s->criteriaItem && $s->criteriaItem->is_active);

        $categories = Category::active()->orderBy('weight', 'desc')->get();

        $coreFailed = false;
        $breakdown = [];

        foreach ($categories as $category) {
            $items = $scores
                ->filter(fn ($s) => (int) $s->criteriaItem->category_id === (int) $category->id)
                ->sortBy(fn ($s) => $s->criteriaItem->order ?? 0)
                ->values();

            $catWeight = 0;
            $catEarned = 0;
            $itemRows = [];

            foreach ($items as $score) {
                $item = $score->criteriaItem;
                $catWeight += (int) $item->weight;

                if ($score->is_met) {
                    $catEarned += (int) $item->weight;
                } elseif ($item->is_core) {
                    $coreFailed = true;
                }

                $itemRows[] = [
                    'name' => (string) $item->name,
                    'description' => $item->description,
                    'weight' => (int) $item->weight,
                    'is_core' => (bool) $item->is_core,
                    'is_met' => (bool) $score->is_met,
                    'notes' => $score->notes,
                ];
            }

            $breakdown[] = [
                'id' => (int) $category->id,
                'name' => (string) $category->name,
                'score' => $catWeight > 0 ? round(($catEarned / $catWeight) * 100, 1) : 0.0,
                'items' => $itemRows,
            ];
        }

        return [
            'total_score' => (float) ($journal->current_score ?? $journal->calculateScore()),
            'core_failed' => $coreFailed,
            'categories' => $breakdown,
        ];
    }
}
