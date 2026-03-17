<?php

namespace App\Console\Commands;

use App\Services\OaiPmhService;
use Illuminate\Console\Command;

class TestOaiCommand extends Command
{
    protected $signature = 'oai:test
        {url : OAI-PMH base URL to test}
        {--set= : Optional set specification}
        {--prefix=oai_dc : Metadata prefix}';

    protected $description = 'Test an OAI-PMH endpoint by fetching repository info and sample records';

    public function handle(OaiPmhService $service): int
    {
        $url = $this->argument('url');

        // Step 1: Identify
        $this->info('🔍 Testing Identify...');
        try {
            $info = $service->identify($url);
            $this->table(
                ['Field', 'Value'],
                collect($info)->map(fn ($v, $k) => [$k, $v])->values()->toArray()
            );
        } catch (\Exception $e) {
            $this->error("❌ Identify failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->newLine();

        // Step 2: Preview records
        $this->info('📄 Fetching sample records...');
        try {
            $records = $service->previewRecords(
                $url,
                $this->option('set'),
                $this->option('prefix'),
                5
            );

            if (empty($records)) {
                $this->warn('No records found.');
                return self::SUCCESS;
            }

            foreach ($records as $i => $record) {
                $num = $i + 1;
                $this->info("--- Record #{$num} ---");
                $this->line("  Title:   {$record['title']}");
                $this->line("  Authors: " . ($record['authors'] ?? '—'));
                $this->line("  Date:    " . ($record['date'] ?? '—'));
                $this->line("  URL:     " . ($record['url'] ?? '—'));
                $this->line("  PDF:     " . ($record['pdf_url'] ?? '—'));
                $this->line("  Lang:    " . ($record['language'] ?? '—'));
                if ($record['description']) {
                    $desc = strlen($record['description']) > 150
                        ? substr($record['description'], 0, 150) . '...'
                        : $record['description'];
                    $this->line("  Abstract: {$desc}");
                }
                $this->newLine();
            }

            $count = count($records);
            $this->info("✅ Found {$count} record(s). Endpoint is working correctly.");
        } catch (\Exception $e) {
            $this->error("❌ ListRecords failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
