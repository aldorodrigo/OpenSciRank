<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CmsPost extends Model
{
    protected $fillable = [
        'user_id',
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

    public const CATEGORIES = [
        'guias' => [
            'label' => 'Guías',
            'color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400',
        ],
        'ciencia-abierta' => [
            'label' => 'Ciencia Abierta',
            'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        ],
        'indexacion' => [
            'label' => 'Indexación',
            'color' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400',
        ],
        'criterios' => [
            'label' => 'Criterios',
            'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        ],
        'casos-de-exito' => [
            'label' => 'Casos de Éxito',
            'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400',
        ],
        'novedades' => [
            'label' => 'Novedades',
            'color' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-400',
        ],
    ];

    protected static function booted(): void
    {
        static::creating(function (CmsPost $post) {
            if (empty($post->slug)) {
                $slug = Str::slug($post->title);
                $original = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $original . '-' . ++$count;
                }
                $post->slug = $slug;
            }
        });
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

    public function getCatLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category]['label'] ?? $this->category ?? '';
    }

    public function getCatColorAttribute(): string
    {
        return self::CATEGORIES[$this->category]['color'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}
