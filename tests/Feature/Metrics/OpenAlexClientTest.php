<?php

declare(strict_types=1);

namespace Tests\Feature\Metrics;

use App\Services\Metrics\Sources\OpenAlexClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAlexClientTest extends TestCase
{
    public function test_returns_metrics_for_valid_issn(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/S202381698',
                    'h_index' => 156,
                    'cited_by_count' => 1234567,
                    'summary_stats' => [
                        '2yr_mean_citedness' => 4.234,
                        'h_index' => 156,
                    ],
                    'issn' => ['1234-5678'],
                ]],
            ]),
        ]);

        $result = (new OpenAlexClient)->fetchByIssn('1234-5678');

        $this->assertTrue($result->found);
        $this->assertSame('openalex', $result->source);
        $this->assertSame(156, $result->hIndex);
        $this->assertSame(1234567, $result->totalCitations);
        $this->assertSame(4.234, $result->meanCitedness2y);
        $this->assertSame('S202381698', $result->externalId);
    }

    public function test_returns_not_found_when_results_empty(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response(['results' => []]),
        ]);

        $result = (new OpenAlexClient)->fetchByIssn('9999-9999');

        $this->assertFalse($result->found);
        $this->assertNotNull($result->errorMessage);
    }

    public function test_returns_error_when_http_fails(): void
    {
        Http::fake([
            'api.openalex.org/sources*' => Http::response('', 500),
        ]);

        $result = (new OpenAlexClient)->fetchByIssn('1234-5678');

        $this->assertFalse($result->found);
        $this->assertStringContainsString('500', $result->errorMessage);
    }

    public function test_rejects_malformed_issn_without_calling_api(): void
    {
        Http::fake();
        $result = (new OpenAlexClient)->fetchByIssn('not-an-issn');

        $this->assertFalse($result->found);
        Http::assertNothingSent();
    }

    public function test_handles_null_issn(): void
    {
        Http::fake();
        $result = (new OpenAlexClient)->fetchByIssn(null);

        $this->assertFalse($result->found);
        Http::assertNothingSent();
    }
}
