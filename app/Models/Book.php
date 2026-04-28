<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Book extends Model
{
    use SoftDeletes, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'abstract',
        'collection_series',
        'sponsor_entity',
        'table_of_contents',
        'rights_holder',
        'available_metrics',
    ];

    protected $guarded = [];

    protected $casts = [
        // JSON fields
        'keywords' => 'array',
        'knowledge_areas' => 'array',
        'funded_by' => 'array',
        'indexes' => 'array',
        'chapter_files' => 'array',
        'supplementary_files' => 'array',

        // Boolean fields
        'is_open_access' => 'boolean',
        'allows_reuse' => 'boolean',
        'allows_commercial_use' => 'boolean',
        'has_peer_review' => 'boolean',
        'has_editorial_committee' => 'boolean',
        'has_editorial_standards' => 'boolean',
        'has_antiplagiarism' => 'boolean',
        'has_ethics_code' => 'boolean',
        'is_indexed' => 'boolean',

        // Decimal fields
        'access_cost' => 'decimal:2',
        'author_apc' => 'decimal:2',
        'current_score' => 'decimal:2',

        // Date fields
        'publication_date' => 'date',
        'exact_publication_date' => 'date',
        'submission_date' => 'date',
        'approval_date' => 'date',
    ];

    public function getTranslationWithFallback(string $field): string
    {
        $value = $this->getTranslation($field, app()->getLocale(), false);
        if (!empty($value)) {
            return $value;
        }
        return $this->getTranslation($field, $this->primary_locale ?? 'es', false) ?? '';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authors()
    {
        return $this->hasMany(BookAuthor::class)->orderBy('order');
    }

    public function responsibleEditor()
    {
        return $this->belongsTo(User::class, 'responsible_editor_id');
    }

    // Scoped queries for authors by role
    public function bookAuthors()
    {
        return $this->authors()->where('role', 'author');
    }

    public function editors()
    {
        return $this->authors()->where('role', 'editor');
    }

    public function translators()
    {
        return $this->authors()->where('role', 'translator');
    }

    public function coordinators()
    {
        return $this->authors()->where('role', 'coordinator');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
