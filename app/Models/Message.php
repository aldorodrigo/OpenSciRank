<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sprint 3.7 #40 — mensaje dentro de un hilo.
 *
 * notification_sent_at: null = pendiente de email (batch cron cada 5 min);
 * not null = email ya enviado por messages:notify-pending.
 */
class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'edited_at',
        'notification_sent_at',
        'payment_link_for_task_id',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    /**
     * Sprint 3.7 #44 — task asociada al botón de pago embebido en este mensaje.
     * Si está seteada y la task tiene paymentLinkUrl() vigente, la view
     * renderiza un botón "Pagar X" en lugar del body en texto plano.
     */
    public function paymentLinkTask(): BelongsTo
    {
        return $this->belongsTo(AdminTask::class, 'payment_link_for_task_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Sprint 3.7 #44 — admin_tasks derivadas de este mensaje (action
     * "Crear tarea" del MessageThread). Un mensaje puede generar varias
     * tasks (admin lo decide).
     */
    public function derivedTasks(): HasMany
    {
        return $this->hasMany(AdminTask::class, 'source_message_id');
    }

    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }
}
