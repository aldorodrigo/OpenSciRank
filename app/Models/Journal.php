<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'abbreviated_name',
        'description',
        'subject_areas',
        'target_audience',
        'publication_languages',
        'is_open_access',
        'access_type',
        'articles_accessible_without_registration',
        'allows_self_archiving',
        'open_access_policy_url',
        'has_embargo',
        'embargo_months',
        'license_type',
        'license_url',
        'authors_retain_copyright',
        'allows_commercial_reuse',
        'copyright_policy',
        'licenses_visible_in_articles',
        'publishing_institution',
        'editor_name',
        'institutional_email',
        'editorial_board_visible',
        'editorial_board_url',
        'peer_review_type',
        'publication_frequency',
        'charges_apc',
        'apc_amount',
        'apc_currency',
        'has_apc_waivers',
        'funding_sources',
        'has_advertising',
        'business_model_transparent',
        'has_ethics_policy',
        'adheres_to_cope',
        'has_antiplagiarism_policy',
        'antiplagiarism_tool',
        'has_conflict_of_interest_policy',
        'declares_ai_use',
        'assigns_doi',
        'logo',
        'status',
        'assigned_evaluator_id',
        'current_score',
        'current_level',
        'evaluated_at',
        'evaluation_notes',
        'issn_print',
        'issn_online',
        'publisher',
        'url',
        'country_code',
        'start_year',
        // OAI Fields
        'oai_base_url',
        'oai_set_spec',
        'oai_metadata_prefix',
        'oai_last_harvested_at',
    ];

    protected $casts = [
        'subject_areas' => 'array',
        'target_audience' => 'array',
        'publication_languages' => 'array',
        'funding_sources' => 'array',
        'is_open_access' => 'boolean',
        'articles_accessible_without_registration' => 'boolean',
        'allows_self_archiving' => 'boolean',
        'has_embargo' => 'boolean',
        'authors_retain_copyright' => 'boolean',
        'allows_commercial_reuse' => 'boolean',
        'licenses_visible_in_articles' => 'boolean',
        'editorial_board_visible' => 'boolean',
        'charges_apc' => 'boolean',
        'has_apc_waivers' => 'boolean',
        'has_advertising' => 'boolean',
        'business_model_transparent' => 'boolean',
        'has_ethics_policy' => 'boolean',
        'adheres_to_cope' => 'boolean',
        'has_antiplagiarism_policy' => 'boolean',
        'has_conflict_of_interest_policy' => 'boolean',
        'declares_ai_use' => 'boolean',
        'assigns_doi' => 'boolean',
        'apc_amount' => 'decimal:2',
        'current_score' => 'decimal:2',
        'evaluated_at' => 'datetime',
        'oai_last_harvested_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedEvaluator()
    {
        return $this->belongsTo(User::class, 'assigned_evaluator_id');
    }

    public function evaluationScores()
    {
        return $this->hasMany(JournalEvaluationScore::class);
    }

    public function harvestedArticles()
    {
        return $this->hasMany(HarvestedArticle::class);
    }

    // Helper methods
    public function isAssignedToEvaluator(): bool
    {
        return !is_null($this->assigned_evaluator_id);
    }

    public function isEvaluated(): bool
    {
        return !is_null($this->evaluated_at);
    }

    public function calculateScore(): float
    {
        $scores = $this->evaluationScores()->with('criteriaItem')->get();
        
        if ($scores->isEmpty()) {
            return 0;
        }

        $totalWeight = 0;
        $earnedWeight = 0;
        $coresFailed = false;

        foreach ($scores as $score) {
            $item = $score->criteriaItem;
            if (!$item || !$item->is_active) continue;

            $totalWeight += $item->weight;

            if ($score->is_met) {
                $earnedWeight += $item->weight;
            } elseif ($item->is_core) {
                $coresFailed = true;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        $percentage = ($earnedWeight / $totalWeight) * 100;

        // Si algún indicador core falla, penalizar la nota
        if ($coresFailed) {
            $percentage = min($percentage, 49); // Máximo 49 si falla core
        }

        return round($percentage, 2);
    }

    public function getCompletionPercentage(): float
    {
        $totalCriteria = CriteriaItem::active()->count();
        $completedScores = $this->evaluationScores()->count();

        if ($totalCriteria === 0) {
            return 0;
        }

        return round(($completedScores / $totalCriteria) * 100, 2);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
