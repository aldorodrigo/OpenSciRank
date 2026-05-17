<?php

declare(strict_types=1);

namespace Tests\Feature\Metrics;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefreshJournalMetricsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_processes_only_certified_journals_with_active_seal(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S1',
                    'h_index' => 42,
                    'cited_by_count' => 1000,
                    'summary_stats' => ['2yr_mean_citedness' => 1.5],
                ]],
            ]),
            'api.crossref.org/journals/*' => Http::response(['message' => ['counts' => []]]),
        ]);

        $user = User::factory()->create();

        $certified = Journal::create([
            'user_id' => $user->id,
            'title' => 'Active Certified',
            'slug' => 'active-certified',
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'active',
            'seal_expires_at' => now()->addMonths(6),
            'current_score' => 85,
            'issn_online' => '1234-5678',
        ]);

        // Expired seal — should be excluded
        Journal::create([
            'user_id' => $user->id,
            'title' => 'Expired Seal',
            'slug' => 'expired-seal',
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'expired',
            'seal_expires_at' => now()->subDays(10),
            'current_score' => 78,
            'issn_online' => '2222-2222',
        ]);

        // Listed but not certified — should be excluded
        Journal::create([
            'user_id' => $user->id,
            'title' => 'Listed Only',
            'slug' => 'listed-only',
            'primary_locale' => 'es',
            'status' => 'listed',
            'current_score' => 60,
            'issn_online' => '3333-3333',
        ]);

        $this->artisan('metrics:refresh-journals', ['--sleep' => 0])
            ->assertSuccessful();

        $certified->refresh();
        $this->assertSame(42, $certified->h_index);

        // Verify Http was called exactly twice (1 journal × 2 sources)
        Http::assertSentCount(2);
    }

    public function test_command_with_specific_journal_option(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S9',
                    'h_index' => 17,
                    'cited_by_count' => 500,
                    'summary_stats' => ['2yr_mean_citedness' => 0.8],
                ]],
            ]),
            'api.crossref.org/journals/*' => Http::response(['message' => ['counts' => []]]),
        ]);

        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Single Target',
            'slug' => 'single-target',
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'active',
            'seal_expires_at' => now()->addYear(),
            'current_score' => 90,
            'issn_online' => '4444-4444',
        ]);

        $this->artisan('metrics:refresh-journals', ['--journal' => $journal->id, '--sleep' => 0])
            ->assertSuccessful();

        $journal->refresh();
        $this->assertSame(17, $journal->h_index);
    }
}
