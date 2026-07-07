<?php

namespace App\Jobs;

use App\Models\Journal;
use App\Models\User;
use App\Notifications\OaiHarvestFailed;
use App\Services\OaiPmhService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Cosecha OAI-PMH de una revista en segundo plano, reanudable.
 *
 * Patrón "continuation": procesa un budget de páginas por corrida y, si aún
 * quedan páginas (resumption token), se re-encola a sí mismo con el token para
 * continuar. Así cada job es corto (no supera el retry_after de la cola) y una
 * cosecha de miles de artículos no bloquea el panel ni se pierde ante un corte.
 *
 * El botón de Filament y el cron `oai:harvest --queue` despachan este job.
 */
class HarvestJournalArticles implements ShouldQueue
{
    use Queueable;

    /**
     * Cuántas páginas OAI procesar por corrida antes de re-encolar la continuación.
     */
    public const PAGE_BUDGET = 10;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public int $timeout = 300;

    public function __construct(
        public Journal $journal,
        public ?string $resumptionToken = null,
        public ?string $metadataPrefix = null,
        public ?int $causedByUserId = null,
        public int $accumulatedCount = 0,
    ) {
        $this->onQueue('harvest');
    }

    /**
     * Evita cosechas concurrentes de la misma revista (y la duplicación por
     * retry_after). La cadena de continuación es secuencial, así que no se
     * bloquea a sí misma: cada job libera el lock al terminar antes de que el
     * siguiente lo tome.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('oai-harvest-'.$this->journal->id))
                ->dontRelease()
                ->expireAfter(360),
        ];
    }

    public function handle(OaiPmhService $service): void
    {
        // Marca el inicio solo en la primera corrida de la cadena.
        if ($this->resumptionToken === null && $this->accumulatedCount === 0) {
            $this->journal->update([
                'oai_harvest_status' => 'running',
                'oai_last_harvest_error' => null,
            ]);
        }

        $token = $this->resumptionToken;
        $prefix = $this->metadataPrefix;
        $count = $this->accumulatedCount;
        $pages = 0;

        do {
            $result = $service->harvestPage($this->journal, $token, $prefix);
            $count += $result['count'];
            $prefix = $result['metadataPrefix'];
            $token = $result['nextToken'];
            $pages++;
        } while ($token !== null && $pages < self::PAGE_BUDGET);

        // Aún quedan páginas → continuar en un job nuevo (checkpoint).
        if ($token !== null) {
            self::dispatch($this->journal, $token, $prefix, $this->causedByUserId, $count);

            return;
        }

        // Cosecha completa.
        $this->journal->update([
            'oai_last_harvested_at' => now(),
            'oai_harvest_status' => 'idle',
            'oai_last_harvest_count' => $count,
            'oai_last_harvest_error' => null,
        ]);

        activity()
            ->performedOn($this->journal)
            ->causedBy($this->causedByUserId ? User::find($this->causedByUserId) : null)
            ->withProperties([
                'articles_count' => $count,
                'oai_base_url' => $this->journal->oai_base_url,
            ])
            ->log("Cosecha OAI-PMH ejecutada: {$count} artículo(s)");
    }

    /**
     * Se ejecuta tras agotar los reintentos: deja el estado en `failed` con el
     * mensaje visible en el panel y registra la actividad.
     */
    public function failed(Throwable $exception): void
    {
        $this->journal->update([
            'oai_harvest_status' => 'failed',
            'oai_last_harvest_error' => mb_substr($exception->getMessage(), 0, 1000),
        ]);

        activity()
            ->performedOn($this->journal)
            ->causedBy($this->causedByUserId ? User::find($this->causedByUserId) : null)
            ->withProperties(['error' => $exception->getMessage()])
            ->log('Cosecha OAI-PMH fallida');

        // #57 — avisar a los super_admins para que actúen (endpoint/config/reintento).
        $admins = User::role('super_admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new OaiHarvestFailed($this->journal, $exception->getMessage()),
            );
        }
    }
}
