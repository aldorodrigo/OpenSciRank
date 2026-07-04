<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica a los participantes del hilo (excepto al autor) cuando llega
 * un nuevo mensaje en una conversación.
 *
 * La URL de destino cambia según el rol del destinatario:
 *   - super_admin → /admin/conversations/{id}
 *   - otros       → /app/messages/{id}
 *
 * Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class NewConversationMessage extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation  = $this->message->conversation;
        $author        = $this->message->user;
        $preview       = mb_strimwidth(strip_tags($this->message->body ?? ''), 0, 200, '…');
        $hasAttachments = $this->message->attachments()->exists();

        $isSuperAdmin = $notifiable->hasRole('super_admin');
        $url = $isSuperAdmin
            ? url('/admin/conversations/'.$conversation->id)
            : url('/app/messages/'.$conversation->id);

        $mail = (new MailMessage)
            ->subject(__('notifications.new_conversation_message.subject', [
                'subject' => $conversation->subject,
            ]))
            ->greeting(__('notifications.new_conversation_message.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.new_conversation_message.intro', ['author' => $author?->name ?? '—']))
            ->line(__('notifications.new_conversation_message.preview', ['text' => $preview]));

        if ($hasAttachments) {
            $mail->line(__('notifications.new_conversation_message.has_attachments'));
        }

        $mail
            ->action(__('notifications.new_conversation_message.cta'), $url)
            ->line(__('notifications.new_conversation_message.outro'));

        return $mail;
    }
}
