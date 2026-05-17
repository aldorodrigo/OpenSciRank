<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Services\Metrics\JournalMetricsService;
use Illuminate\Console\Command;

class RefreshJournalMetrics extends Command
{
    protected $signature = 'metrics:refresh-journals
        {--journal= : ID específico de journal (omitir para procesar todos los certified vigentes)}
        {--sleep=1 : Segundos de pausa entre journals para respetar polite pool de las APIs}
        {--dry-run : Ejecuta sin persistir cambios}';

    protected $description = 'Refresca h-index y total de citas de journals certified con sello vigente vía OpenAlex y Crossref.';

    public function handle(JournalMetricsService $service): int
    {
        $query = Journal::query()
            ->where('status', 'certified')
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '>', now());

        if ($id = $this->option('journal')) {
            $query->where('id', $id);
        }

        $journals = $query->get();

        if ($journals->isEmpty()) {
            $this->info('No hay journals certified vigentes para procesar.');

            return self::SUCCESS;
        }

        $this->info("Procesando {$journals->count()} journals.");

        $processed = 0;
        $updated = 0;
        $errors = 0;
        $sleepSeconds = max(0, (int) $this->option('sleep'));
        $dryRun = (bool) $this->option('dry-run');

        foreach ($journals as $journal) {
            $processed++;
            $issn = $journal->issn_online ?: $journal->issn_print;
            $this->line(sprintf('  [%d/%d] %s (ISSN %s)', $processed, $journals->count(), $journal->title, $issn ?: '—'));

            if ($dryRun) {
                continue;
            }

            try {
                $snapshots = $service->refresh($journal);
                $hadAtLeastOneOk = collect($snapshots)->contains(fn ($s) => ! $s->failed);

                if ($hadAtLeastOneOk) {
                    $updated++;
                    $journal->refresh();
                    $this->line(sprintf('       h-index=%s · citas=%s · source=%s',
                        $journal->h_index ?? '—',
                        $journal->total_citations ?? '—',
                        $journal->metrics_source ?? '—',
                    ));
                } else {
                    $errors++;
                    $this->warn('       todas las fuentes fallaron');
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("       error: {$e->getMessage()}");
                report($e);
            }

            if ($sleepSeconds > 0 && $processed < $journals->count()) {
                sleep($sleepSeconds);
            }
        }

        $this->info(sprintf('Listo: %d procesados, %d actualizados, %d errores.', $processed, $updated, $errors));

        return $errors > 0 && $updated === 0 ? self::FAILURE : self::SUCCESS;
    }
}
