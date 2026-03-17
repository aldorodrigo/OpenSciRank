<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Services\OaiPmhService;
use Illuminate\Console\Command;

class HarvestOaiCommand extends Command
{
    protected $signature = 'oai:harvest
        {--journal= : Harvest articles for a specific journal ID}
        {--all : Harvest all journals with OAI configured}';

    protected $description = 'Harvest articles from OAI-PMH 2.0 endpoints configured in journals';

    public function handle(OaiPmhService $service): int
    {
        $journals = $this->getJournals();

        if ($journals->isEmpty()) {
            $this->warn('No journals found with OAI endpoints configured.');
            return self::FAILURE;
        }

        $this->info("Found {$journals->count()} journal(s) to harvest.");
        $this->newLine();

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
            return Journal::whereNotNull('oai_base_url')->get();
        }

        $this->error('Please specify --journal=ID or --all');
        return collect();
    }
}
