<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class CmsCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'slug',
        'name',
        'color',
        'primary_locale',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getTranslationWithFallback(string $field): string
    {
        $value = $this->getTranslation($field, app()->getLocale(), false);
        if (!empty($value)) {
            return $value;
        }
        return $this->getTranslation($field, $this->primary_locale ?? 'es', false) ?? '';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CmsPost::class, 'category', 'slug');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
