<?php

namespace App\Listeners\Mail;

use App\Support\MailLogContext;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Log;

/**
 * Captura el notifiable + clase de la notification para que LogMessageSending
 * pueda enlazar la fila de email_logs al destinatario. Solo canal `mail`.
 */
class LogNotificationSending
{
    public function handle(NotificationSending $event): void
    {
        try {
            if ($event->channel !== 'mail') {
                return;
            }

            MailLogContext::remember($event->notifiable, get_class($event->notification));
        } catch (\Throwable $e) {
            Log::warning('LogNotificationSending failed', ['error' => $e->getMessage()]);
        }
    }
}
