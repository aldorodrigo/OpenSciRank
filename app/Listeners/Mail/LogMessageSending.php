<?php

namespace App\Listeners\Mail;

use App\Models\EmailLog;
use App\Support\MailLogContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Crea la fila de email_logs (status `sending`) justo antes de que el
 * transporte envíe. Inyecta un header `X-EmailLog-Uuid` en el mensaje para
 * que LogMessageSent pueda correlacionar y marcarlo `sent` con el message-id
 * de SES. Best-effort: nunca debe abortar el envío real.
 */
class LogMessageSending
{
    public function handle(MessageSending $event): void
    {
        try {
            $message = $event->message;

            $uuid = (string) Str::uuid();
            $message->getHeaders()->addTextHeader('X-EmailLog-Uuid', $uuid);

            $to = $message->getTo();
            $first = $to[0] ?? null;

            $context = MailLogContext::pull();
            $notifiable = $context['notifiable'] ?? null;
            $notificationClass = $context['class']
                ?? ($event->data['__laravel_notification'] ?? null);

            EmailLog::create([
                'correlation_uuid' => $uuid,
                'notifiable_type' => $notifiable instanceof Model ? $notifiable->getMorphClass() : null,
                'notifiable_id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
                'notification_class' => $notificationClass,
                'mailer' => $event->data['mailer'] ?? config('mail.default'),
                'recipient_email' => $first?->getAddress() ?? 'unknown',
                'recipient_name' => $first?->getName() ?: null,
                'subject' => $message->getSubject(),
                'status' => EmailLog::STATUS_SENDING,
                'html_body' => config('mail_logging.store_html', false) ? $message->getHtmlBody() : null,
                'sending_at' => Date::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('LogMessageSending failed', ['error' => $e->getMessage()]);
        }
    }
}
