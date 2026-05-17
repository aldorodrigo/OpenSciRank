<?php

namespace App\Services\Metrics\Sources;

use App\Services\Metrics\SourceResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resuelve métricas de un journal en OpenAlex por ISSN.
 *
 * https://docs.openalex.org/api-entities/sources
 * Polite pool: agregar mailto en query y User-Agent.
 */
class OpenAlexClient
{
    private const BASE_URL = 'https://api.openalex.org/sources';

    public function fetchByIssn(?string $issn): SourceResult
    {
        $issn = $this->normalizeIssn($issn);

        if (! $issn) {
            return SourceResult::notFound('openalex', 'ISSN ausente o inválido');
        }

        try {
            $response = Http::timeout(15)
                ->withUserAgent($this->userAgent())
                ->get(self::BASE_URL, [
                    'filter' => "issn:{$issn}",
                    'per-page' => 1,
                    'mailto' => $this->mailto(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('OpenAlex request failed', ['issn' => $issn, 'error' => $e->getMessage()]);

            return SourceResult::error('openalex', $e->getMessage());
        }

        if (! $response->successful()) {
            return SourceResult::error('openalex', "HTTP {$response->status()}");
        }

        $data = $response->json();
        $results = $data['results'] ?? [];

        if (empty($results)) {
            return SourceResult::notFound('openalex', 'ISSN no indexado en OpenAlex');
        }

        $source = $results[0];
        $stats = $source['summary_stats'] ?? [];

        return new SourceResult(
            source: 'openalex',
            found: true,
            hIndex: $this->intOrNull($stats['h_index'] ?? $source['h_index'] ?? null),
            totalCitations: $this->intOrNull($source['cited_by_count'] ?? null),
            meanCitedness2y: $this->floatOrNull($stats['2yr_mean_citedness'] ?? null),
            externalId: $this->extractVenueId($source['id'] ?? null),
            rawPayload: $source,
        );
    }

    private function normalizeIssn(?string $issn): ?string
    {
        if (! $issn) {
            return null;
        }
        $issn = trim($issn);
        // Format: 1234-5678 or 1234-567X
        return preg_match('/^\d{4}-\d{3}[\dX]$/i', $issn) ? strtoupper($issn) : null;
    }

    private function extractVenueId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $parts = explode('/', rtrim($url, '/'));

        return end($parts) ?: null;
    }

    private function intOrNull($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function floatOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function userAgent(): string
    {
        return sprintf('EditorialStandardsPlatform/1.0 (mailto:%s)', $this->mailto());
    }

    private function mailto(): string
    {
        return config('mail.from.address', 'admin@editorialstandards.org');
    }
}
