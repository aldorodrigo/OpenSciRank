<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriteriaItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'weight' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCore($query)
    {
        return $query->where('type', 'core');
    }

    public function scopeAdvanced($query)
    {
        return $query->where('type', 'advanced');
    }

    public function scopeExcellence($query)
    {
        return $query->where('type', 'excellence');
    }

    public function scopeExcluyentes($query)
    {
        return $query->where('is_core', true);
    }
}
