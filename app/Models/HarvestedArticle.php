<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HarvestedArticle extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'authors_json' => 'array',
        'date' => 'date',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
