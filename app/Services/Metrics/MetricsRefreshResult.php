<?php

namespace App\Services\Metrics;

use App\Models\JournalMetricSnapshot;

/**
 * Resultado de una corrida de refresco de métricas.
 *
 * Solo las fuentes que devolvieron datos generan snapshot; las que fallan
 * NO se persisten (no ensucian el historial) pero su motivo queda en $errors
 * para poder mostrarlo al admin.
 */
class MetricsRefreshResult
{
    /**
     * @param  JournalMetricSnapshot[]  $snapshots  Snapshots efectivamente persistidos.
     * @param  array<string, string>  $errors  Motivo de falla por fuente (source => mensaje).
     */
    public function __construct(
        public array $snapshots = [],
        public array $errors = [],
    ) {}

    public function hasAnyData(): bool
    {
        return $this->snapshots !== [];
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Resumen legible de los errores por fuente.
     * Ej: "OpenAlex: ISSN no indexado — Crossref: HTTP 500".
     */
    public function errorSummary(): string
    {
        $parts = [];
        foreach ($this->errors as $source => $message) {
            $parts[] = ucfirst($source).': '.$message;
        }

        return implode(' — ', $parts);
    }
}
