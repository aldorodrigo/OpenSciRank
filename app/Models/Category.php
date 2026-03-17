<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'integer',
    ];

    public function criteriaItems()
    {
        return $this->hasMany(CriteriaItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
