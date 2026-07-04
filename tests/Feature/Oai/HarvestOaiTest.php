<?php

declare(strict_types=1);

namespace Tests\Feature\Oai;

use App\Jobs\HarvestJournalArticles;
use App\Models\HarvestedArticle;
use App\Models\Journal;
use App\Models\User;
use App\Services\OaiPmhService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HarvestOaiTest extends TestCase
{
    use RefreshDatabase;

    private function journal(string $status = 'listed', ?string $oaiUrl = 'https://revista.test/oai'): Journal
    {
        $user = User::factory()->create();

        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista de prueba',
            'slug' => 'revista-prueba-'.$user->id,
            'primary_locale' => 'es',
            'status' => $status,
            'oai_base_url' => $oaiUrl,
            'oai_metadata_prefix' => 'oai_dc', // evita la request extra a ListMetadataFormats
        ]);
    }

    private function oaiPage(string $identifier, ?string $resumptionToken = null): string
    {
        $token = $resumptionToken !== null
            ? "<resumptionToken>{$resumptionToken}</resumptionToken>"
            : '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
         xmlns:dc="http://purl.org/dc/elements/1.1/">
  <ListRecords>
    <record>
      <header><identifier>{$identifier}</identifier></header>
      <metadata>
        <oai_dc:dc>
          <dc:title xml:lang="es">Título de {$identifier}</dc:title>
          <dc:creator>Autor Uno</dc:creator>
          <dc:date>2024-01-15</dc:date>
          <dc:identifier>https://revista.test/article/{$identifier}</dc:identifier>
        </oai_dc:dc>
      </metadata>
    </record>
    {$token}
  </ListRecords>
</OAI-PMH>
XML;
    }

    public function test_harvest_page_parses_record_and_returns_next_token(): void
    {
        Http::fake([
            'revista.test/oai*' => Http::response($this->oaiPage('oai:test:1', 'TOKEN123')),
        ]);

        $journal = $this->journal();
        $result = app(OaiPmhService::class)->harvestPage($journal);

        $this->assertSame(1, $result['count']);
        $this->assertSame('TOKEN123', $result['nextToken']);
        $this->assertSame('oai_dc', $result['metadataPrefix']);
        $this->assertDatabaseHas('harvested_articles', [
            'identifier' => 'oai:test:1',
            'journal_id' => $journal->id,
        ]);
    }

    public function test_list_records_follows_resumption_token_across_pages(): void
    {
        Http::fake([
            'revista.test/oai*' => Http::sequence()
                ->push($this->oaiPage('oai:test:1', 'TOKEN123'))
                ->push($this->oaiPage('oai:test:2')), // sin token → última página
        ]);

        $journal = $this->journal();
        $total = app(OaiPmhService::class)->listRecords($journal);

        $this->assertSame(2, $total);
        $this->assertSame(2, HarvestedArticle::where('journal_id', $journal->id)->count());
        $this->assertNotNull($journal->fresh()->oai_last_harvested_at);
    }

    public function test_command_queue_only_dispatches_jobs_for_listed_and_certified(): void
    {
        Queue::fake();

        $listed = $this->journal('listed');
        $certified = $this->journal('certified');
        $draft = $this->journal('draft'); // no debe cosecharse
        $noOai = $this->journal('listed', null); // sin OAI: excluida

        $this->artisan('oai:harvest --all --queue')->assertSuccessful();

        Queue::assertPushed(HarvestJournalArticles::class, 2);
        $this->assertSame('queued', $listed->fresh()->oai_harvest_status);
        $this->assertSame('queued', $certified->fresh()->oai_harvest_status);
        $this->assertNull($draft->fresh()->oai_harvest_status);
        $this->assertNull($noOai->fresh()->oai_harvest_status);
    }

    public function test_command_sync_runs_inline_without_queue(): void
    {
        Queue::fake();
        Http::fake([
            'revista.test/oai*' => Http::response($this->oaiPage('oai:test:9')),
        ]);

        $journal = $this->journal();

        $this->artisan("oai:harvest --journal={$journal->id}")->assertSuccessful();

        Queue::assertNothingPushed();
        $this->assertDatabaseHas('harvested_articles', ['identifier' => 'oai:test:9']);
    }

    public function test_job_finishes_and_updates_status_when_no_more_pages(): void
    {
        Http::fake([
            'revista.test/oai*' => Http::response($this->oaiPage('oai:test:done')),
        ]);

        $journal = $this->journal();
        (new HarvestJournalArticles($journal))->handle(app(OaiPmhService::class));

        $journal->refresh();
        $this->assertSame('idle', $journal->oai_harvest_status);
        $this->assertSame(1, $journal->oai_last_harvest_count);
        $this->assertNotNull($journal->oai_last_harvested_at);
    }

    public function test_job_redispatches_continuation_when_pages_remain(): void
    {
        Queue::fake();
        // Cada página devuelve token → el job agota su budget y encola la continuación.
        Http::fake([
            'revista.test/oai*' => Http::response($this->oaiPage('oai:test:loop', 'NEXT')),
        ]);

        $journal = $this->journal();
        (new HarvestJournalArticles($journal))->handle(app(OaiPmhService::class));

        Queue::assertPushed(HarvestJournalArticles::class, function (HarvestJournalArticles $job) use ($journal) {
            return $job->journal->id === $journal->id
                && $job->resumptionToken === 'NEXT'
                && $job->accumulatedCount === HarvestJournalArticles::PAGE_BUDGET;
        });

        // No debe finalizar todavía: sigue "running".
        $this->assertSame('running', $journal->fresh()->oai_harvest_status);
    }
}
