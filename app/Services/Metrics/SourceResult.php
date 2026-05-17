<?php

namespace App\Services\Metrics;

/**
 * Normalized response from a metrics source adapter.
 */
class SourceResult
{
    public function __construct(
        public readonly string $source,
        public readonly bool $found,
        public readonly ?int $hIndex = null,
        public readonly ?int $totalCitations = null,
        public readonly ?float $meanCitedness2y = null,
        public readonly ?string $externalId = null,
        public readonly array $rawPayload = [],
        public readonly ?string $errorMessage = null,
    ) {}

    public static function notFound(string $source, ?string $error = null): self
    {
        return new self(source: $source, found: false, errorMessage: $error);
    }

    public static function error(string $source, string $error): self
    {
        return new self(source: $source, found: false, errorMessage: $error);
    }
}
