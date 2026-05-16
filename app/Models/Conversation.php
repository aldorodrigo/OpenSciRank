<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Sprint 3.7 #40 — hilo de conversación polimórfico.
 *
 * Puede estar atado a: Journal | Book | AdminTask | null (consulta general).
 * Cuando el subject se soft-deletea (ej. journal borrado), el hilo pasa a
 * status `archived` automáticamente — handled en Conversation::archiveOrphans()
 * o vía observer en el modelo del subject.
 */
class Conversation extends Model
{
    use LogsActivity;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ARCHIVED = 'archived';

    public const ROLE_EDITOR = 'editor';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EVALUATOR = 'evaluator';
    public const ROLE_OBSERVER = 'observer';

    protected $fillable = [
        'subject',
        'subject_type',
        'subject_id',
        'started_by_user_id',
        'status',
        'last_message_at',
        'closed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'subject'])
            ->logOnlyDirty();
    }

    // ── Relations ────────────────────────────────────────────────────

    /**
     * Relación polimórfica al recurso del que trata el hilo (Journal/Book/AdminTask).
     *
     * **Nota:** la tabla tiene una columna `subject` (string del título) que
     * Eloquent prefiere sobre un método llamado `subject()`. Por eso la
     * relación se llama `subjectModel` y NO `subject`. Para acceder al modelo
     * usá `$conv->subjectModel`; para el título string usá `$conv->subject`.
     *
     * Internamente apunta a las columnas subject_type/subject_id vía morphTo
     * con nombre explícito 'subject'.
     */
    public function subjectModel(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_OPEN);
    }

    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->whereHas('participants', fn ($p) => $p->where('user_id', $user->id));
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Agregar (o reactivar) un participante.
     * Si ya existía, idempotente. Si no, lo crea con el rol indicado.
     */
    public function addParticipant(User $user, string $role = self::ROLE_ADMIN): ConversationParticipant
    {
        return $this->participants()->firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'joined_at' => now()]
        );
    }

    /**
     * Marcar como leído por el usuario indicado.
     */
    public function markReadBy(User $user): void
    {
        $this->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Cantidad de mensajes no leídos por el usuario en este hilo.
     */
    public function unreadCountFor(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        if (! $participant) return 0;

        $cutoff = $participant->last_read_at;
        $query = $this->messages()->where('user_id', '!=', $user->id);

        if ($cutoff) {
            $query->where('created_at', '>', $cutoff);
        }

        return $query->count();
    }

    /**
     * Destinatarios para emails de notificación: participantes no muteados
     * que no son el autor del mensaje.
     */
    public function recipientsForNotification(?User $exceptUser = null): \Illuminate\Support\Collection
    {
        $query = $this->participants()
            ->where('email_muted', false)
            ->with('user');

        if ($exceptUser) {
            $query->where('user_id', '!=', $exceptUser->id);
        }

        return $query->get()->pluck('user')->filter();
    }

    /**
     * Cierra hilos cuyo subject ha sido soft-deleted (Sprint 3.7 mejora #5.7).
     * Llamado desde observers del subject.
     */
    public static function archiveOrphansFor(Model $subject): int
    {
        return static::where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('status', self::STATUS_OPEN)
            ->update(['status' => self::STATUS_ARCHIVED]);
    }
}
