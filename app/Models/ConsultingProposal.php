<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Sprint 3.7 #39 — propuesta de horario de consultoría.
 *
 * Una task de tipo consulting puede tener varias propuestas activas a la vez.
 * Cuando el editor acepta una, las demás del mismo task pasan a `superseded`
 * automáticamente vía `AdminTask::acceptProposal()`.
 */
class ConsultingProposal extends Model
{
    use LogsActivity;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUSES_CLOSED = [
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
        self::STATUS_SUPERSEDED,
    ];

    protected $fillable = [
        'admin_task_id',
        'proposed_by_user_id',
        'proposed_slot',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_by_user_id',
        'notes',
    ];

    protected $casts = [
        'proposed_slot' => 'datetime',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'proposed_slot', 'expires_at', 'accepted_at', 'accepted_by_user_id'])
            ->logOnlyDirty();
    }

    // ── Relations ────────────────────────────────────────────────────

    public function adminTask(): BelongsTo
    {
        return $this->belongsTo(AdminTask::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE)
            ->where('expires_at', '<', now());
    }

    // ── Logic helpers ────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->isActive() && $this->expires_at?->isPast());
    }

    public function markRejected(): self
    {
        $this->update(['status' => self::STATUS_REJECTED]);
        return $this;
    }

    public function markSuperseded(): self
    {
        $this->update(['status' => self::STATUS_SUPERSEDED]);
        return $this;
    }

    public function markExpired(): self
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
        return $this;
    }
}
