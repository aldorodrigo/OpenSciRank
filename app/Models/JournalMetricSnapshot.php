<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalMetricSnapshot extends Model
{
    public const SOURCE_OPENALEX = 'openalex';
    public const SOURCE_CROSSREF = 'crossref';
    public const SOURCE_SCHOLAR_MANUAL = 'scholar_manual';
    public const SOURCE_ORCID = 'orcid';

    protected $fillable = [
        'journal_id',
        'source',
        'h_index',
        'total_citations',
        'mean_citedness_2y',
        'raw_payload',
        'captured_by',
        'captured_at',
        'notes',
        'failed',
        'error_message',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'captured_at' => 'datetime',
        'mean_citedness_2y' => 'decimal:3',
        'failed' => 'boolean',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function capturedBy()
    {
        return $this->belongsTo(User::class, 'captured_by');
    }
}
