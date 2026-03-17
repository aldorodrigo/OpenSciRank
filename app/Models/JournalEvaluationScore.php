<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEvaluationScore extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_met' => 'boolean',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function criteriaItem()
    {
        return $this->belongsTo(CriteriaItem::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
