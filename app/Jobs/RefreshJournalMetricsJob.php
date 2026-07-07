<?php

namespace App\Jobs;

use App\Models\Journal;
use App\Services\Metrics\JournalMetricsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Refresca las métricas de impacto (OpenAlex + Crossref) de una revista en
 * segundo plano.
 *
 * `JournalMetricsService::refresh()` hace llamadas HTTP externas; sacarlas del
 * hilo síncrono evita colgar el request que lo dispara (p. ej. la certificación
 * en EvaluateJournal). Corre en la cola `default`.
 */
class RefreshJournalMetricsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public int $timeout = 120;

    public function __construct(public Journal $journal)
    {
        //
    }

    /**
     * Evita refrescos concurrentes de la misma revista.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('metrics-refresh-'.$this->journal->id))
                ->dontRelease()
                ->expireAfter(180),
        ];
    }

    public function handle(JournalMetricsService $service): void
    {
        $service->refresh($this->journal);
    }
}
