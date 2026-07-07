<?php

declare(strict_types=1);

namespace Tests\Feature\Metrics;

use App\Models\Journal;
use App\Models\JournalMetricSnapshot;
use App\Models\User;
use App\Services\Metrics\JournalMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JournalMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function certifiedJournalWithIssn(string $issn = '1932-6203'): Journal
    {
        $user = User::factory()->create();

        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Test Journal',
            'slug' => 'test-journal-'.$user->id,
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'active',
            'seal_awarded_at' => now()->subDays(7),
            'seal_expires_at' => now()->addYear(),
            'evaluated_at' => now()->subDays(7),
            'current_score' => 82.5,
            'issn_online' => $issn,
        ]);
    }

    public function test_refresh_persists_snapshot_and_updates_journal_from_openalex(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S100200300',
                    'h_index' => 80,
                    'cited_by_count' => 50000,
                    'summary_stats' => ['2yr_mean_citedness' => 3.5, 'h_index' => 80],
                ]],
            ]),
            'api.crossref.org/journals/*' => Http::response([
                'message' => ['counts' => ['total-dois' => 1234]],
            ]),
        ]);

        $journal = $this->certifiedJournalWithIssn();
        $service = app(JournalMetricsService::class);

        $result = $service->refresh($journal);

        $this->assertCount(2, $result->snapshots);
        $this->assertFalse($result->hasErrors());
        $journal->refresh();

        $this->assertSame(80, $journal->h_index);
        $this->assertSame(50000, $journal->total_citations);
        $this->assertSame('openalex', $journal->metrics_source);
        $this->assertSame('S100200300', $journal->openalex_venue_id);
        $this->assertNotNull($journal->metrics_updated_at);
    }

    public function test_manual_scholar_snapshot_takes_precedence_over_openalex(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S1',
                    'h_index' => 50,
                    'cited_by_count' => 10000,
                    'summary_stats' => ['2yr_mean_citedness' => 1.2],
                ]],
            ]),
            'api.crossref.org/journals/*' => Http::response(['message' => ['counts' => []]]),
        ]);

        $journal = $this->certifiedJournalWithIssn();
        $service = app(JournalMetricsService::class);

        $service->registerScholarSnapshot(
            journal: $journal,
            hIndex: 120,
            totalCitations: 200000,
            capturedByUserId: null,
        );

        $journal->refresh();
        $this->assertSame(120, $journal->h_index);
        $this->assertSame('scholar_manual', $journal->metrics_source);

        // Even after a refresh from APIs, the recent Scholar snapshot wins
        $service->refresh($journal);

        $journal->refresh();
        $this->assertSame(120, $journal->h_index, 'Scholar manual <90 days must prevail over OpenAlex');
        $this->assertSame('scholar_manual', $journal->metrics_source);
    }

    public function test_journal_with_unindexed_issn_gets_null_h_index(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response(['results' => []]),
            'api.crossref.org/journals/*' => Http::response('', 404),
        ]);

        $journal = $this->certifiedJournalWithIssn('9999-999X');
        $result = app(JournalMetricsService::class)->refresh($journal);

        // Ninguna fuente devolvió datos: no se persiste ningún snapshot en el historial.
        $this->assertFalse($result->hasAnyData());
        $this->assertTrue($result->hasErrors());
        $this->assertSame(0, $journal->metricSnapshots()->count());

        $journal->refresh();
        $this->assertNull($journal->h_index);
        $this->assertNull($journal->metrics_source);
    }

    public function test_old_scholar_snapshot_does_not_prevent_openalex_update(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S2',
                    'h_index' => 70,
                    'cited_by_count' => 20000,
                    'summary_stats' => ['2yr_mean_citedness' => 2.0],
                ]],
            ]),
            'api.crossref.org/journals/*' => Http::response(['message' => ['counts' => []]]),
        ]);

        $journal = $this->certifiedJournalWithIssn();

        // Old Scholar snapshot (older than 90 days)
        JournalMetricSnapshot::create([
            'journal_id' => $journal->id,
            'source' => JournalMetricSnapshot::SOURCE_SCHOLAR_MANUAL,
            'h_index' => 200,
            'total_citations' => 500000,
            'captured_at' => now()->subDays(100),
            'failed' => false,
        ]);

        app(JournalMetricsService::class)->refresh($journal);
        $journal->refresh();

        $this->assertSame(70, $journal->h_index, 'Scholar snapshot >90 days must be ignored');
        $this->assertSame('openalex', $journal->metrics_source);
    }
}
