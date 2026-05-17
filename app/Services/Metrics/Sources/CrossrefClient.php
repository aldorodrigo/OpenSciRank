<?php

namespace App\Services\Metrics\Sources;

use App\Services\Metrics\SourceResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resuelve datos de un journal en Crossref por ISSN.
 *
 * https://api.crossref.org/journals/{issn}
 * No expone h-index directamente — solo total de DOIs registrados,
 * que se guarda en snapshot como contraste, no para alimentar la columna oficial.
 */
class CrossrefClient
{
    private const BASE_URL = 'https://api.crossref.org/journals/';

    public function fetchByIssn(?string $issn): SourceResult
    {
        $issn = $this->normalizeIssn($issn);

        if (! $issn) {
            return SourceResult::notFound('crossref', 'ISSN ausente o inválido');
        }

        try {
            $response = Http::timeout(15)
                ->withUserAgent($this->userAgent())
                ->get(self::BASE_URL.$issn);
        } catch (\Throwable $e) {
            Log::warning('Crossref request failed', ['issn' => $issn, 'error' => $e->getMessage()]);

            return SourceResult::error('crossref', $e->getMessage());
        }

        if ($response->status() === 404) {
            return SourceResult::notFound('crossref', 'ISSN no indexado en Crossref');
        }

        if (! $response->successful()) {
            return SourceResult::error('crossref', "HTTP {$response->status()}");
        }

        $message = $response->json('message') ?? [];
        $counts = $message['counts'] ?? [];

        return new SourceResult(
            source: 'crossref',
            found: true,
            hIndex: null, // Crossref no expone h-index
            totalCitations: $this->intOrNull($counts['total-dois'] ?? null),
            meanCitedness2y: null,
            externalId: $message['ISSN'][0] ?? $issn,
            rawPayload: $message,
        );
    }

    private function normalizeIssn(?string $issn): ?string
    {
        if (! $issn) {
            return null;
        }
        $issn = trim($issn);

        return preg_match('/^\d{4}-\d{3}[\dX]$/i', $issn) ? strtoupper($issn) : null;
    }

    private function intOrNull($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function userAgent(): string
    {
        $mailto = config('mail.from.address', 'admin@editorialstandards.org');

        return sprintf('EditorialStandardsPlatform/1.0 (mailto:%s)', $mailto);
    }
}
