<?php

namespace App\Models;

use App\Support\BusinessDays;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Cola de trabajo del admin (Sprint 3.6 #32).
 *
 * Una tarea representa una unidad de trabajo derivada de un pago o de
 * una acción del editor (listing gratuito). Se crea desde el webhook
 * de Stripe (`StripePaymentService::createPaymentFromSession`) o desde
 * `SubmissionWizard::listJournal`, y se cierra cuando el trabajo termina
 * (hooks en `EvaluateJournal::save`, `ReviewListing::save`, o cuando el
 * admin la marca completada manualmente — caso consultoría).
 */
class AdminTask extends Model
{
    use LogsActivity;

    // ── Tipos ─────────────────────────────────────────────────────────
    public const TYPE_EVALUATE_JOURNAL = 'evaluate_journal';
    public const TYPE_REEVALUATE_JOURNAL = 'reevaluate_journal';
    public const TYPE_RENEWAL_EVALUATION = 'renewal_evaluation';
    public const TYPE_REVIEW_LISTING_JOURNAL = 'review_listing_journal';
    public const TYPE_REVIEW_LISTING_BOOK = 'review_listing_book';
    public const TYPE_CONSULTING = 'consulting';
    public const TYPE_ORPHAN_PAYMENT = 'orphan_payment';

    public const TYPES = [
        self::TYPE_EVALUATE_JOURNAL,
        self::TYPE_REEVALUATE_JOURNAL,
        self::TYPE_RENEWAL_EVALUATION,
        self::TYPE_REVIEW_LISTING_JOURNAL,
        self::TYPE_REVIEW_LISTING_BOOK,
        self::TYPE_CONSULTING,
        self::TYPE_ORPHAN_PAYMENT,
    ];

    // ── Estados ───────────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SCHEDULED = 'scheduled';      // solo consulting
    public const STATUS_IN_SESSION = 'in_session';    // solo consulting
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES_OPEN = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_SESSION,
    ];

    public const STATUSES_TERMINAL = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // ── Prioridades ───────────────────────────────────────────────────
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'type',
        'title_key',
        'title_params',
        'payment_id',
        'related_type',
        'related_id',
        'assigned_to',
        'status',
        'priority',
        'due_at',
        'scheduled_for',
        'started_at',
        'completed_at',
        'cancelled_reason',
        'notes',
    ];

    protected $casts = [
        'title_params' => 'array',
        'due_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Activity log ──────────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type', 'status', 'assigned_to', 'priority',
                'due_at', 'scheduled_for', 'cancelled_reason',
            ])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }

    // ── Relaciones ────────────────────────────────────────────────────
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUSES_OPEN);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUSES_OPEN)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Render del título usando el key i18n y los parámetros.
     */
    public function renderedTitle(): string
    {
        return __($this->title_key, $this->title_params ?? []);
    }

    /**
     * Días (calendario) restantes hasta `due_at`. Negativo = vencida.
     */
    public function daysUntilDue(): ?int
    {
        if (! $this->due_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->due_at->startOfDay(), false);
    }

    public function isOverdue(): bool
    {
        return $this->due_at
            && in_array($this->status, self::STATUSES_OPEN, true)
            && $this->due_at->isPast();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::STATUSES_OPEN, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::STATUSES_TERMINAL, true);
    }

    // ── Acciones del lifecycle ────────────────────────────────────────

    /**
     * Asignar la task a un usuario. Si ya estaba asignada, reasigna.
     */
    public function assignToUser(User $user): self
    {
        $this->update(['assigned_to' => $user->id]);

        return $this;
    }

    /**
     * Marcar como "iniciada" (admin clicó "Iniciar"). Idempotente.
     */
    public function start(): self
    {
        if ($this->status !== self::STATUS_PENDING) {
            return $this;
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Marcar consultoría como agendada para una fecha.
     */
    public function markScheduled(Carbon $scheduledFor): self
    {
        $this->update([
            'status' => self::STATUS_SCHEDULED,
            'scheduled_for' => $scheduledFor,
        ]);

        return $this;
    }

    /**
     * Marcar consultoría en sesión.
     */
    public function markInSession(): self
    {
        $this->update(['status' => self::STATUS_IN_SESSION]);

        return $this;
    }

    /**
     * Cerrar la task como completada. Idempotente.
     */
    public function complete(?string $note = null): self
    {
        if ($this->isTerminal()) {
            return $this;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'notes' => $note ? trim(($this->notes ? $this->notes."\n\n" : '').$note) : $this->notes,
        ]);

        return $this;
    }

    /**
     * Cancelar la task con razón. Idempotente.
     */
    public function cancel(string $reason): self
    {
        if ($this->isTerminal()) {
            return $this;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_reason' => $reason,
            'completed_at' => now(), // tiempo en que dejó de estar abierta
        ]);

        return $this;
    }

    // ── Factories estáticos por tipo ──────────────────────────────────

    /**
     * Calcula `due_at` según el tipo de task y los SLA configurables.
     * Lee de la tabla `settings` vía Setting::get(), cacheado 1h.
     */
    public static function calculateDueAt(string $type, bool $express = false, ?Carbon $from = null): ?Carbon
    {
        $from ??= now();

        return match ($type) {
            self::TYPE_EVALUATE_JOURNAL,
            self::TYPE_REEVALUATE_JOURNAL,
            self::TYPE_RENEWAL_EVALUATION => BusinessDays::addBusinessDays(
                $from,
                $express
                    ? (int) Setting::get('sla_evaluation_express_business_days', 5)
                    : (int) Setting::get('sla_evaluation_business_days', 15)
            ),
            self::TYPE_REVIEW_LISTING_JOURNAL,
            self::TYPE_REVIEW_LISTING_BOOK => $from->copy()->addDays(
                (int) Setting::get('sla_listing_calendar_days', 7)
            ),
            self::TYPE_CONSULTING => $from->copy()->addDays(
                (int) Setting::get('sla_consulting_calendar_days', 7)
            ),
            self::TYPE_ORPHAN_PAYMENT => $from->copy()->addDays(
                (int) Setting::get('sla_orphan_calendar_days', 2)
            ),
            default => null,
        };
    }

    /**
     * Cancela todas las tasks abiertas asociadas a un pago.
     * Usado por el flujo de reembolso (Sprint 4 #6) — agendado.
     */
    public static function cancelByPayment(int $paymentId, string $reason = 'Pago reembolsado'): int
    {
        $count = 0;
        self::where('payment_id', $paymentId)
            ->whereIn('status', self::STATUSES_OPEN)
            ->each(function (self $task) use ($reason, &$count) {
                $task->cancel($reason);
                $count++;
            });

        return $count;
    }
}
