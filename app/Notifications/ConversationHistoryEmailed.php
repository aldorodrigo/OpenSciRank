<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Support\TimezoneHelper;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Sprint 3.7 #45 — historial completo de la conversación al cerrarla.
 *
 * Cuando admin/super_admin cierra una conversación con el toggle "Enviar
 * historial por email" marcado, ambas partes (editor + admin + evaluador
 * involucrados) reciben este email con el registro cronológico de todos
 * los mensajes. Sirve como respaldo fuera de la plataforma.
 *
 * Sin queue. Multiidioma vía HasLocalePreference. Fechas en TZ del destinatario.
 */
class ConversationHistoryEmailed extends Notification
{
    public function __construct(
        public Conversation $conversation,
        public ?string $closingNote = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $messages = $this->conversation->messages()
            ->with(['user', 'attachments'])
            ->orderBy('created_at')
            ->get();

        $msg = (new MailMessage)
            ->subject(__('conversation_history_emailed.subject', [
                'subject' => $this->conversation->subject,
            ]))
            ->greeting(__('conversation_history_emailed.greeting', ['name' => $notifiable->name]))
            ->line(__('conversation_history_emailed.intro', [
                'subject' => $this->conversation->subject,
                'count' => $messages->count(),
            ]));

        if ($this->closingNote) {
            $msg->line(__('conversation_history_emailed.closing_note_label'))
                ->line('> '.$this->closingNote);
        }

        $msg->line('---');

        foreach ($messages as $m) {
            $author = $m->user?->name ?? '—';
            $time = TimezoneHelper::format($m->created_at, $notifiable, 'd/m/Y H:i');

            $msg->line("**{$author}** · _{$time}_")
                ->line($m->body);

            if ($m->attachments->isNotEmpty()) {
                $links = $m->attachments->map(function ($att) {
                    $url = URL::temporarySignedRoute(
                        'messages.attachment',
                        now()->addDays(30),
                        ['attachment' => $att->id]
                    );
                    return "📎 [{$att->original_name}]({$url})";
                })->implode("\n");

                $msg->line($links);
            }

            $msg->line('---');
        }

        return $msg->line(__('conversation_history_emailed.outro'));
    }
}
