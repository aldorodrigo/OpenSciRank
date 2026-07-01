<?php

namespace App\Models;

use App\Support\BusinessDays;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as NotificationFacade;
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

    // Sprint 3.7 #44 — task genérica creada manualmente por admin desde un mensaje.
    public const TYPE_SUPPORT = 'support';

    public const TYPES = [
        self::TYPE_EVALUATE_JOURNAL,
        self::TYPE_REEVALUATE_JOURNAL,
        self::TYPE_RENEWAL_EVALUATION,
        self::TYPE_REVIEW_LISTING_JOURNAL,
        self::TYPE_REVIEW_LISTING_BOOK,
        self::TYPE_CONSULTING,
        self::TYPE_ORPHAN_PAYMENT,
        self::TYPE_SUPPORT,
    ];

    // Tipos que un admin puede elegir manualmente desde el modal "Crear tarea
    // desde mensaje". orphan_payment se omite porque es del sistema.
    public const TYPES_MANUALLY_CREATABLE = [
        self::TYPE_SUPPORT,
        self::TYPE_EVALUATE_JOURNAL,
        self::TYPE_REEVALUATE_JOURNAL,
        self::TYPE_RENEWAL_EVALUATION,
        self::TYPE_REVIEW_LISTING_JOURNAL,
        self::TYPE_REVIEW_LISTING_BOOK,
        self::TYPE_CONSULTING,
    ];

    // ── Estados ───────────────────────────────────────────────────────
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment'; // Sprint 3.7 #44 — task creada con link de pago, esperando que el editor pague

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PROPOSAL_SENT = 'proposal_sent';  // Sprint 3.7 #39 — solo consulting

    public const STATUS_SCHEDULED = 'scheduled';      // solo consulting

    public const STATUS_IN_SESSION = 'in_session';    // solo consulting

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES_OPEN = [
        self::STATUS_AWAITING_PAYMENT,
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PROPOSAL_SENT,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_SESSION,
    ];

    // Estados que aparecen en la queue de trabajo normal "Por hacer".
    // Excluye awaiting_payment (queue separada, todavía no se cobró).
    public const STATUSES_WORK_QUEUE = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PROPOSAL_SENT,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_SESSION,
    ];

    // Cap de rondas de propuesta antes de escalar a super_admin (Sprint 3.7 #39)
    public const MAX_PROPOSAL_ROUNDS = 3;

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
        'source_message_id',
        'assigned_to',
        'status',
        'priority',
        'due_at',
        'scheduled_for',
        'consulting_reminder_sent_at',
        'proposal_count_sent',
        'reschedule_count',
        'consulting_meet_url',
        'started_at',
        'completed_at',
        'cancelled_reason',
        'notes',
        'client_visible_notes',
    ];

    protected $casts = [
        'title_params' => 'array',
        'due_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'consulting_reminder_sent_at' => 'datetime',
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

    public function proposals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConsultingProposal::class)->orderBy('proposed_slot');
    }

    public function activeProposals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConsultingProposal::class)
            ->where('status', ConsultingProposal::STATUS_ACTIVE)
            ->orderBy('proposed_slot');
    }

    /**
     * Hilo de mensajes anclado a esta task (1:1). Si no existe, devuelve null.
     */
    public function conversation(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Conversation::class, 'subject');
    }

    /**
     * Sprint 3.7 #44 — mensaje origen desde el cual el admin creó esta task.
     * Null si la task fue creada por flujo automático (pago, listing, etc.).
     */
    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'source_message_id');
    }

    /**
     * Sprint 3.7 #44 — pago solicitado por esta task (link de pago).
     * Distinto de payment() (pago que originó la task).
     * Una task de support puede tener un solicitedPayment cuando el admin
     * generó un checkout link y el editor lo pagó.
     */
    public function solicitedPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class, 'solicited_by_admin_task_id');
    }

    /**
     * Sprint 3.7 #46 — accessor unificado del Payment asociado a esta task,
     * sin importar si vino del flujo viejo (webhook genera task tras pago →
     * `payment_id` directo) o del flujo nuevo #44 (admin crea task con link →
     * payment llega después y se asocia vía `solicited_by_admin_task_id`).
     *
     * Uso: `$task->effectivePayment` en lugar de `$task->payment` o
     * `$task->solicitedPayment` cuando necesitás cualquiera de los dos.
     */
    public function getEffectivePaymentAttribute(): ?Payment
    {
        return $this->payment ?? $this->solicitedPayment;
    }

    /**
     * Sprint 3.7 — tarea "de cortesía": no tiene ni pago asociado ni link de
     * pago pendiente. Casos típicos:
     *  - Evaluación forzada (#36) sin pago para instituciones / prueba
     *  - Listing gratuito de revista (`forJournalListing`)
     *  - Resubmisiones post-`requires_changes_*` (gratis por design)
     *  - Tareas manuales del admin sin cobro
     *
     * NO es cortesía:
     *  - status=awaiting_payment (esperando que el editor pague el link)
     *  - tiene effective_payment (vía payment_id o solicited_by_admin_task_id)
     *
     * Solo se muestra en el admin panel — el editor no tiene visibilidad.
     */
    public function isComplimentary(): bool
    {
        if ($this->effective_payment !== null) {
            return false;
        }

        if ($this->status === self::STATUS_AWAITING_PAYMENT) {
            return false;
        }

        return true;
    }

    /**
     * Sprint 3.7 #44 — genera el signed checkout URL para esta task si tiene
     * un producto configurado. El admin lo comparte con el editor (manualmente
     * o vía mensaje automático en el hilo). Vence en 30 días.
     *
     * Retorna null si la task no tiene producto asociado.
     */
    public function paymentLinkUrl(): ?string
    {
        $productId = $this->title_params['product_id'] ?? null;
        if (! $productId) {
            return null;
        }

        // Si ya hay un payment completado, el link queda muerto.
        if ($this->solicitedPayment?->status === 'completed') {
            return null;
        }

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'checkout.from-task',
            now()->addDays(30),
            ['task' => $this->id]
        );
    }

    /**
     * Resuelve el editor (cliente) detrás de la task.
     *
     * - Si `related` es User (caso new-journal-consulting standalone) → ese user.
     * - Si `related` es Journal/Book → `related->user` (dueño del recurso).
     * - Si no aplica (huérfanos, tasks manuales) → null.
     */
    public function editor(): ?User
    {
        $related = $this->related;
        if ($related instanceof User) {
            return $related;
        }

        return $related?->user ?? null;
    }

    /**
     * Destinatarios para notificaciones de consultoría: editor + evaluador
     * asignado, ambos deduplicados (puede pasar que el evaluador sea el
     * mismo registro que el editor en pruebas internas).
     *
     * @return array<int, \App\Models\User>
     */
    public function consultingRecipients(): array
    {
        $editor = $this->editor();
        $assignee = $this->assignee;

        $recipients = array_filter([$editor, $assignee]);

        // Deduplicar por id
        $seen = [];
        $unique = [];
        foreach ($recipients as $user) {
            if (! isset($seen[$user->id])) {
                $seen[$user->id] = true;
                $unique[] = $user;
            }
        }

        return $unique;
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
     *
     * Roadmap #35 — fallback robusto: algunas tasks viejas se crearon sin
     * `title_params` (o sin la clave `name`), lo que mostraba el placeholder
     * crudo ":name". Si falta, lo resolvemos desde el recurso relacionado
     * (Journal/Book/User) para que el título siempre sea legible.
     */
    public function renderedTitle(): string
    {
        $params = $this->title_params ?? [];

        if (! array_key_exists('name', $params) && $this->related) {
            $params['name'] = \App\Support\PaymentPayableResolver::payableDisplayName($this->related);
        }

        return __($this->title_key, $params);
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

    /**
     * Monto correspondiente sólo a esta task (no al pago total).
     *
     * Un mismo pago puede generar varias tasks (ej. evaluation + consulting).
     * Acá devolvemos cuánto del pago corresponde específicamente a esta task:
     *  - evaluate/reevaluate/renewal: precio del producto principal + Express si aplica
     *  - consulting: precio del addon action-plan-consulting
     *  - review_listing_book: precio del book-listing
     *  - review_listing_journal: 0 (es flujo gratuito, no hay payment)
     *  - orphan_payment: total del payment (admin investiga sin contexto granular)
     *
     * Devuelve null si no hay payment asociado.
     */
    public function taskAmount(): ?float
    {
        if (! $this->payment) {
            return null;
        }

        return match ($this->type) {
            self::TYPE_EVALUATE_JOURNAL,
            self::TYPE_REEVALUATE_JOURNAL,
            self::TYPE_RENEWAL_EVALUATION => (float) ($this->payment->product?->price ?? 0)
                + (($this->payment->metadata['is_express'] ?? false) ? \App\Livewire\PaymentCheckout::EXPRESS_UPLIFT_AMOUNT : 0),
            self::TYPE_CONSULTING => (float) (\App\Models\Product::where('slug', 'action-plan-consulting')->value('price') ?? 0),
            self::TYPE_REVIEW_LISTING_BOOK => (float) ($this->payment->product?->price ?? 0),
            self::TYPE_ORPHAN_PAYMENT => (float) $this->payment->amount,
            default => null,
        };
    }

    /**
     * URL donde el admin realmente hace el trabajo asociado a esta task.
     * El listado de admin_tasks redirige acá tras "Iniciar" para evitar
     * la fricción de navegar manualmente al recurso.
     */
    public function workUrl(): string
    {
        return match ($this->type) {
            self::TYPE_EVALUATE_JOURNAL,
            self::TYPE_REEVALUATE_JOURNAL,
            self::TYPE_RENEWAL_EVALUATION => $this->related_id
                ? "/admin/journals/{$this->related_id}/evaluate"
                : "/admin/admin-tasks/{$this->id}",
            self::TYPE_REVIEW_LISTING_JOURNAL => $this->related_id
                ? "/admin/journals/{$this->related_id}/review-listing"
                : "/admin/admin-tasks/{$this->id}",
            self::TYPE_REVIEW_LISTING_BOOK => $this->related_id
                ? "/admin/books/{$this->related_id}/edit"
                : "/admin/admin-tasks/{$this->id}",
            self::TYPE_ORPHAN_PAYMENT => $this->payment_id
                ? "/admin/payments/{$this->payment_id}"
                : "/admin/admin-tasks/{$this->id}",
            // Consulting y default: View page de la task (donde están las
            // acciones para programar/iniciar sesión/marcar completada).
            default => "/admin/admin-tasks/{$this->id}",
        };
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
     *
     * Resetea `consulting_reminder_sent_at` para que el cron diario
     * `consulting:send-reminders` vuelva a considerar este registro
     * cuando se reagenda (ej. el editor pide cambio de fecha).
     *
     * Si `$notify` es true (default), envía `ConsultingScheduled` al editor
     * y al evaluador asignado. Pasar `false` desactiva el envío en flujos
     * batch o de seed.
     *
     * Si `$isRescheduling` es true, la notificación usa el subject de
     * reagendamiento ("Tu sesión fue reagendada para …").
     */
    public function markScheduled(CarbonInterface $scheduledFor, bool $notify = true, bool $isRescheduling = false): self
    {
        $this->update([
            'status' => self::STATUS_SCHEDULED,
            'scheduled_for' => $scheduledFor,
            'consulting_reminder_sent_at' => null,
        ]);

        if ($notify) {
            $recipients = $this->consultingRecipients();
            if (! empty($recipients)) {
                NotificationFacade::send(
                    $recipients,
                    new \App\Notifications\ConsultingScheduled($this->fresh(), $isRescheduling)
                );
            }
        }

        return $this;
    }

    /**
     * Sprint 3.7 #39 — enviar propuestas de fecha al editor.
     *
     * Crea N ConsultingProposal records (status=active), pone la task en
     * STATUS_PROPOSAL_SENT e incrementa el contador de rondas. Si excede
     * MAX_PROPOSAL_ROUNDS dispara `ConsultingProposalsEscalated` al super_admin.
     *
     * @param  array<int, array{slot: CarbonInterface, notes?: ?string}>  $slots
     */
    public function sendProposals(array $slots, User $proposedBy, ?int $expiresInDays = null): array
    {
        $expiresInDays ??= 5; // business days default — TODO Sprint 4: usar SlaSettings
        $expiresAt = now()->addDays($expiresInDays);

        $created = [];
        foreach ($slots as $entry) {
            $created[] = $this->proposals()->create([
                'proposed_by_user_id' => $proposedBy->id,
                'proposed_slot' => $entry['slot'],
                'notes' => $entry['notes'] ?? null,
                'status' => ConsultingProposal::STATUS_ACTIVE,
                'expires_at' => $expiresAt,
            ]);
        }

        $this->update([
            'status' => self::STATUS_PROPOSAL_SENT,
            'proposal_count_sent' => $this->proposal_count_sent + 1,
        ]);

        // Notificación al editor con los slots propuestos
        $editor = $this->editor();
        if ($editor) {
            $editor->notify(new \App\Notifications\ConsultingProposalSent($this->fresh()));
        }

        // Escalamiento si excede cap de rondas
        if ($this->fresh()->proposal_count_sent >= self::MAX_PROPOSAL_ROUNDS) {
            $admin = User::role('super_admin')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\ConsultingProposalsEscalated($this->fresh()));
            }
        }

        return $created;
    }

    /**
     * Sprint 3.7 #39 — el editor acepta una propuesta puntual.
     *
     * - Marca la propuesta aceptada como `accepted`.
     * - Marca las demás propuestas activas del mismo task como `superseded`.
     * - Llama a markScheduled() con la fecha aceptada (que envía
     *   ConsultingScheduled a ambos).
     */
    public function acceptProposal(ConsultingProposal $proposal, User $acceptedBy): self
    {
        if ($proposal->admin_task_id !== $this->id) {
            throw new \InvalidArgumentException('Proposal does not belong to this task.');
        }
        if (! $proposal->isActive()) {
            throw new \LogicException('Cannot accept a non-active proposal.');
        }

        // Marcar la propuesta aceptada
        $proposal->update([
            'status' => ConsultingProposal::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'accepted_by_user_id' => $acceptedBy->id,
        ]);

        // Las demás propuestas activas → superseded
        $this->activeProposals()
            ->where('id', '!=', $proposal->id)
            ->each(fn (ConsultingProposal $p) => $p->markSuperseded());

        // Agenda
        $this->markScheduled($proposal->proposed_slot);

        return $this->fresh();
    }

    /**
     * Sprint 3.7 #39 — el editor rechaza todas las propuestas activas.
     * Devuelve la task a PENDING para que el evaluador proponga nuevas.
     */
    public function rejectAllProposals(User $rejectedBy, ?string $reason = null): self
    {
        $this->activeProposals()->each(fn (ConsultingProposal $p) => $p->markRejected());

        $this->update(['status' => self::STATUS_PENDING]);

        // Notificar al evaluador (y al admin) que el editor pidió otras fechas
        $assignee = $this->assignee;
        if ($assignee) {
            $assignee->notify(new \App\Notifications\ConsultingProposalsRejected($this->fresh(), $reason));
        }

        return $this->fresh();
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

        // Sprint 3.7 #44 — bloqueo: tasks awaiting_payment NO se pueden completar
        // manualmente. El admin debe esperar que el editor pague o cancelar
        // explícitamente la task si la necesidad ya no aplica.
        if ($this->status === self::STATUS_AWAITING_PAYMENT) {
            throw new \LogicException(
                'No se puede marcar como completada una tarea con pago pendiente. '
                .'Esperá que el editor pague, o cancelá la tarea si ya no aplica.'
            );
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'notes' => $note ? trim(($this->notes ? $this->notes."\n\n" : '').$note) : $this->notes,
        ]);

        // Sprint 3.7 #44 Fase 2 — side effects al cerrar manualmente.
        $this->handleCompletionSideEffects();

        return $this;
    }

    /**
     * Sprint 3.7 #44 — transición awaiting_payment → pending cuando se confirma
     * el pago vía webhook. Idempotente: si la task ya no está awaiting_payment,
     * no hace nada.
     */
    public function activateAfterPayment(): self
    {
        if ($this->status !== self::STATUS_AWAITING_PAYMENT) {
            return $this;
        }

        $this->update([
            'status' => self::STATUS_PENDING,
        ]);

        return $this;
    }

    /**
     * Side effects al marcar la task como completada manualmente.
     *
     * - Consulting con propuestas activas → marcarlas superseded.
     * - Consulting con solicitedPayment pendiente → avisar al editor que ya
     *   no necesita pagar (caso típico: admin resolvió el caso por otra vía).
     */
    protected function handleCompletionSideEffects(): void
    {
        if ($this->type === self::TYPE_CONSULTING) {
            // Marcar propuestas activas como superseded
            $this->activeProposals()->each(
                fn (ConsultingProposal $p) => $p->markSuperseded()
            );
        }

        // Aplicable a CUALQUIER tipo con link de pago pendiente: si el editor
        // no pagó todavía y la task se cierra, el link queda sin sentido.
        // El admin debería decidir explícitamente si quiere cobrarle igual
        // (entonces no cierra la task) o si quiere liberar al editor (cierra
        // la task y le avisamos).
        $solicited = $this->solicitedPayment;
        if ($solicited && $solicited->status !== 'completed') {
            $editor = $this->editor();
            if ($editor) {
                try {
                    $editor->notify(new \App\Notifications\TaskCompletedPaymentCancelled($this->fresh()));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to notify editor about cancelled payment link', [
                        'task_id' => $this->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
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

    /**
     * Reactivar una task cancelada. Solo aplica a tasks cancelled —
     * las completed no se reactivan (son cierres legítimos).
     *
     * Vuelve a `pending` y limpia los campos terminales para que entre
     * de nuevo al queue. Conserva la razón de cancelación en `notes`
     * para auditoría.
     */
    public function reactivate(?string $reason = null): self
    {
        if ($this->status !== self::STATUS_CANCELLED) {
            return $this;
        }

        $note = trim(sprintf(
            "Reactivada por %s.\nRazón cancelación previa: %s%s",
            auth()->user()?->name ?? 'admin',
            $this->cancelled_reason ?? '—',
            $reason ? "\nMotivo reactivación: ".$reason : ''
        ));

        $this->update([
            'status' => self::STATUS_PENDING,
            'cancelled_reason' => null,
            'completed_at' => null,
            'started_at' => null,
            'notes' => trim(($this->notes ? $this->notes."\n\n" : '').$note),
        ]);

        return $this;
    }

    // ── Factories estáticos por tipo ──────────────────────────────────

    /**
     * Calcula `due_at` según el tipo de task y los SLA configurables.
     * Lee de la tabla `settings` vía Setting::get(), cacheado 1h.
     *
     * Devuelve CarbonInterface (puede ser Carbon o CarbonImmutable según
     * el contexto). El cast `datetime` de Eloquent normaliza al guardarse.
     */
    public static function calculateDueAt(string $type, bool $express = false, ?CarbonInterface $from = null): ?CarbonInterface
    {
        $from ??= now();
        // Forzamos Carbon mutable para que ->copy()->addDays(...) devuelva Carbon.
        $base = Carbon::parse($from);

        return match ($type) {
            self::TYPE_EVALUATE_JOURNAL,
            self::TYPE_REEVALUATE_JOURNAL,
            self::TYPE_RENEWAL_EVALUATION => BusinessDays::addBusinessDays(
                $base,
                $express
                    ? (int) Setting::get('sla_evaluation_express_business_days', 5)
                    : (int) Setting::get('sla_evaluation_business_days', 15)
            ),
            self::TYPE_REVIEW_LISTING_JOURNAL,
            self::TYPE_REVIEW_LISTING_BOOK => $base->copy()->addDays(
                (int) Setting::get('sla_listing_calendar_days', 7)
            ),
            self::TYPE_CONSULTING => $base->copy()->addDays(
                (int) Setting::get('sla_consulting_calendar_days', 7)
            ),
            self::TYPE_ORPHAN_PAYMENT => $base->copy()->addDays(
                (int) Setting::get('sla_orphan_calendar_days', 2)
            ),
            // Sprint 3.7 #44 — task de soporte creada manualmente desde mensaje.
            self::TYPE_SUPPORT => $base->copy()->addDays(
                (int) Setting::get('sla_support_calendar_days', 7)
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
