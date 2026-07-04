<?php

namespace App\Listeners\Mail;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

/**
 * Registra una fila `failed` cuando Laravel emite NotificationFailed (canal
 * mail). Es best-effort y complementario: el grueso de los fallos de entrega
 * en cola aparece en `failed_jobs` (panel QueueMonitor) tras agotar reintentos.
 */
class LogNotificationFailed
{
    public function handle(NotificationFailed $event): void
    {
        try {
            if (($event->channel ?? 'mail') !== 'mail') {
                return;
            }

            $notifiable = $event->notifiable;
            $exception = $event->data['exception'] ?? null;

            EmailLog::create([
                'notifiable_type' => $notifiable instanceof Model ? $notifiable->getMorphClass() : null,
                'notifiable_id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
                'notification_class' => get_class($event->notification),
                'mailer' => config('mail.default'),
                'recipient_email' => $notifiable->email ?? 'unknown',
                'status' => EmailLog::STATUS_FAILED,
                'error_message' => $exception instanceof \Throwable ? $exception->getMessage() : null,
                'failed_at' => Date::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('LogNotificationFailed failed', ['error' => $e->getMessage()]);
        }
    }
}
