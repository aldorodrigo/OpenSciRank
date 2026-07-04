<?php

namespace App\Listeners\Mail;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

/**
 * Marca `sent` la fila de email_logs creada en LogMessageSending, usando el
 * header de correlación, y guarda el message-id (de SES) para poder cruzar
 * bounces/complaints a futuro. Best-effort.
 */
class LogMessageSent
{
    public function handle(MessageSent $event): void
    {
        try {
            $header = $event->message->getHeaders()->get('X-EmailLog-Uuid');
            $uuid = $header?->getBodyAsString();

            if (! $uuid) {
                return;
            }

            $log = EmailLog::where('correlation_uuid', $uuid)->first();

            if (! $log) {
                return;
            }

            $log->update([
                'status' => EmailLog::STATUS_SENT,
                'ses_message_id' => $event->sent->getMessageId(),
                'sent_at' => Date::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('LogMessageSent failed', ['error' => $e->getMessage()]);
        }
    }
}
