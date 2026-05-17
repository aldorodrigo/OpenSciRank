<?php

namespace App\Services\Metrics;

use App\Models\Journal;
use App\Models\JournalMetricSnapshot;
use App\Services\Metrics\Sources\CrossrefClient;
use App\Services\Metrics\Sources\OpenAlexClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Orquesta la captura de métricas de impacto para una revista.
 *
 * Reglas de combinación (resuelven qué número va a journals.h_index):
 *
 * 1. Snapshot Scholar manual con captured_at < 90 días → prevalece.
 * 2. Si no, h_index de OpenAlex.
 * 3. Si OpenAlex no resuelve → h_index = NULL (no aparece en ranking).
 * 4. Crossref nunca alimenta la columna oficial — solo se guarda como contraste.
 */
class JournalMetricsService
{
    public const SCHOLAR_MANUAL_TTL_DAYS = 90;

    public function __construct(
        protected OpenAlexClient $openAlex,
        protected CrossrefClient $crossref,
    ) {}

    /**
     * Refresca las métricas externas (OpenAlex + Crossref) y aplica reglas de combinación.
     *
     * @return JournalMetricSnapshot[]  snapshots creados en esta corrida
     */
    public function refresh(Journal $journal): array
    {
        $snapshots = [];

        $issn = $journal->issn_online ?: $journal->issn_print;

        $openAlexResult = $this->openAlex->fetchByIssn($issn);
        $snapshots[] = $this->persistSnapshot($journal, $openAlexResult);

        $crossrefResult = $this->crossref->fetchByIssn($issn);
        $snapshots[] = $this->persistSnapshot($journal, $crossrefResult);

        $this->applyCombinationRules($journal, $openAlexResult);

        return $snapshots;
    }

    /**
     * Registra una entrada manual de Google Scholar y aplica reglas de combinación.
     */
    public function registerScholarSnapshot(
        Journal $journal,
        ?int $hIndex,
        ?int $totalCitations,
        ?int $capturedByUserId,
        ?string $profileUrl = null,
        ?string $notes = null,
        ?Carbon $capturedAt = null,
    ): JournalMetricSnapshot {
        $snapshot = JournalMetricSnapshot::create([
            'journal_id' => $journal->id,
            'source' => JournalMetricSnapshot::SOURCE_SCHOLAR_MANUAL,
            'h_index' => $hIndex,
            'total_citations' => $totalCitations,
            'mean_citedness_2y' => null,
            'raw_payload' => array_filter([
                'profile_url' => $profileUrl,
                'entered_manually' => true,
            ]),
            'captured_by' => $capturedByUserId,
            'captured_at' => $capturedAt ?? now(),
            'notes' => $notes,
            'failed' => false,
        ]);

        $this->applyCombinationRules($journal->fresh());

        return $snapshot;
    }

    /**
     * Aplica las reglas de combinación al journal (escribe en la columna).
     */
    public function applyCombinationRules(Journal $journal, ?SourceResult $latestOpenAlex = null): void
    {
        $manualScholar = $journal->metricSnapshots()
            ->where('source', JournalMetricSnapshot::SOURCE_SCHOLAR_MANUAL)
            ->where('failed', false)
            ->where('captured_at', '>=', now()->subDays(self::SCHOLAR_MANUAL_TTL_DAYS))
            ->orderByDesc('captured_at')
            ->first();

        if ($manualScholar && $manualScholar->h_index !== null) {
            $journal->update([
                'h_index' => $manualScholar->h_index,
                'total_citations' => $manualScholar->total_citations ?? $journal->total_citations,
                'mean_citedness_2y' => $journal->mean_citedness_2y,
                'metrics_source' => JournalMetricSnapshot::SOURCE_SCHOLAR_MANUAL,
                'metrics_updated_at' => now(),
            ]);

            return;
        }

        // Fallback: último OpenAlex exitoso
        $openAlex = $latestOpenAlex && $latestOpenAlex->found
            ? $latestOpenAlex
            : $this->latestOpenAlexResultFromSnapshots($journal);

        if ($openAlex && $openAlex->found && $openAlex->hIndex !== null) {
            $journal->update([
                'openalex_venue_id' => $openAlex->externalId ?? $journal->openalex_venue_id,
                'h_index' => $openAlex->hIndex,
                'total_citations' => $openAlex->totalCitations,
                'mean_citedness_2y' => $openAlex->meanCitedness2y,
                'metrics_source' => JournalMetricSnapshot::SOURCE_OPENALEX,
                'metrics_updated_at' => now(),
            ]);

            return;
        }

        // Sin manual reciente ni OpenAlex resuelto: limpiamos h_index
        $journal->update([
            'h_index' => null,
            'metrics_source' => null,
            'metrics_updated_at' => now(),
        ]);
    }

    private function latestOpenAlexResultFromSnapshots(Journal $journal): ?SourceResult
    {
        $snapshot = $journal->metricSnapshots()
            ->where('source', JournalMetricSnapshot::SOURCE_OPENALEX)
            ->where('failed', false)
            ->orderByDesc('captured_at')
            ->first();

        if (! $snapshot) {
            return null;
        }

        return new SourceResult(
            source: 'openalex',
            found: true,
            hIndex: $snapshot->h_index,
            totalCitations: $snapshot->total_citations,
            meanCitedness2y: $snapshot->mean_citedness_2y !== null ? (float) $snapshot->mean_citedness_2y : null,
            externalId: $journal->openalex_venue_id,
            rawPayload: $snapshot->raw_payload ?? [],
        );
    }

    private function persistSnapshot(Journal $journal, SourceResult $result): JournalMetricSnapshot
    {
        $payload = [
            'journal_id' => $journal->id,
            'source' => $result->source,
            'h_index' => $result->hIndex,
            'total_citations' => $result->totalCitations,
            'mean_citedness_2y' => $result->meanCitedness2y,
            'raw_payload' => $result->rawPayload ?: null,
            'captured_at' => now(),
            'failed' => ! $result->found,
            'error_message' => $result->errorMessage,
        ];

        if (! $result->found) {
            Log::info('Metrics snapshot empty/failed', [
                'journal_id' => $journal->id,
                'source' => $result->source,
                'error' => $result->errorMessage,
            ]);
        }

        return JournalMetricSnapshot::create($payload);
    }
}
