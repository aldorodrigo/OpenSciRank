<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class CmsPost extends Model
{
    use HasTranslations;

    public array $translatable = [
        'title',
        'excerpt',
        'content',
    ];

    protected $fillable = [
        'user_id',
        'primary_locale',
        'title',
        'slug',
        'content',
        'excerpt',
        'type',
        'category',
        'emoji',
        'is_featured',
        'read_time',
        'published_at',
        'image_path',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CmsPost $post) {
            if (empty($post->slug)) {
                $primary = $post->primary_locale ?: 'es';
                $title = $post->getTranslation('title', $primary, false)
                    ?: $post->getTranslation('title', app()->getLocale(), false)
                    ?: '';
                $slug = Str::slug($title);
                if (empty($slug)) {
                    $slug = 'post';
                }
                $original = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $original . '-' . ++$count;
                }
                $post->slug = $slug;
            }
        });
    }

    public function getTranslationWithFallback(string $field): string
    {
        $value = $this->getTranslation($field, app()->getLocale(), false);
        if (!empty($value)) {
            return $value;
        }
        return $this->getTranslation($field, $this->primary_locale ?? 'es', false) ?? '';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function categoryRel(): BelongsTo
    {
        return $this->belongsTo(CmsCategory::class, 'category', 'slug');
    }

    public function getCatLabelAttribute(): string
    {
        return $this->categoryRel?->getTranslationWithFallback('name') ?? '';
    }

    public function getCatColorAttribute(): string
    {
        return $this->categoryRel?->color ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}
