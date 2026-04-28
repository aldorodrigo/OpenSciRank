<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function getTranslationWithFallback(string $field): string
    {
        $value = $this->getTranslation($field, app()->getLocale(), false);
        if (! empty($value)) {
            return $value;
        }
        return $this->getTranslation($field, $this->primary_locale ?? 'es', false) ?? '';
    }
}
