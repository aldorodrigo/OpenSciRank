<?php

namespace App\Console\Commands;

use App\Jobs\HarvestJournalArticles;
use App\Models\Journal;
use App\Services\OaiPmhService;
use Illuminate\Console\Command;

class HarvestOaiCommand extends Command
{
    protected $signature = 'oai:harvest
        {--journal= : Cosechar una revista específica por ID}
        {--all : Cosechar todas las revistas listed/certified con OAI configurado}
        {--queue : Encolar la cosecha en background (Job) en vez de correr inline}';

    protected $description = 'Cosecha artículos desde endpoints OAI-PMH 2.0 configurados en las revistas';

    public function handle(OaiPmhService $service): int
    {
        $journals = $this->getJournals();

        if ($journals->isEmpty()) {
            $this->warn('No journals found with OAI endpoints configured.');

            return self::FAILURE;
        }

        $queue = (bool) $this->option('queue');

        $this->info("Found {$journals->count()} journal(s) to harvest.");
        $this->newLine();

        // Modo encolado: despacha un job por revista y sale (para el cron).
        if ($queue) {
            foreach ($journals as $journal) {
                $journal->update(['oai_harvest_status' => 'queued']);
                HarvestJournalArticles::dispatch($journal);
                $this->line("   📥 Encolada: {$journal->title}");
            }

            $this->newLine();
            $this->info("🎉 {$journals->count()} cosecha(s) encolada(s).");

            return self::SUCCESS;
        }

        // Modo inline (debug): comportamiento síncrono con progreso en terminal.
        $totalArticles = 0;

        foreach ($journals as $journal) {
            $this->info("📡 Harvesting: {$journal->title}");
            $this->comment("   URL: {$journal->oai_base_url}");

            try {
                $count = $service->listRecords($journal, function ($num, $title) {
                    $this->line("   [{$num}] {$title}");
                });

                $totalArticles += $count;
                $this->info("   ✅ {$count} article(s) harvested.");
            } catch (\Exception $e) {
                $this->error("   ❌ Error: {$e->getMessage()}");
            }

            $this->newLine();
        }

        $this->info("🎉 Total: {$totalArticles} article(s) harvested from {$journals->count()} journal(s).");

        return self::SUCCESS;
    }

    private function getJournals()
    {
        if ($id = $this->option('journal')) {
            return Journal::where('id', $id)->whereNotNull('oai_base_url')->get();
        }

        if ($this->option('all')) {
            // Solo revistas publicadas en el directorio: listed o certified.
            return Journal::whereNotNull('oai_base_url')
                ->whereIn('status', ['listed', 'certified'])
                ->get();
        }

        $this->error('Please specify --journal=ID or --all');

        return collect();
    }
}
