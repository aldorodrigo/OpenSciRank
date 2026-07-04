<?php

namespace App\Notifications;

use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use App\Notifications\Concerns\ReminderNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Roadmap #35 — recordatorio al editor de que tiene un mensaje pendiente en un
 * hilo. Lo dispara manualmente el evaluador/admin cuando el editor no contesta,
 * con reglas anti-spam (cooldown + respeta muteo) en MessageThread::remindEditor.
 *
 * (User implementa HasLocalePreference).
 */
class PendingMessageReminder extends QueuedNotification
{
    use ReminderNotification;

    public function __construct(public Conversation $conversation) {}

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->conversation->subjectModel;
        $resource = match (true) {
            $subject instanceof Journal, $subject instanceof Book => $subject->getTranslationWithFallback('title'),
            default => $this->conversation->subject,
        };

        $url = url('/app/messages/'.$this->conversation->id);

        return (new MailMessage)
            ->withSymfonyMessage($this->unsubscribeHeaders($notifiable))
            ->subject(__('pending_message_reminder.subject'))
            ->greeting(__('pending_message_reminder.greeting', ['name' => $notifiable->name]))
            ->line(__('pending_message_reminder.intro', ['resource' => $resource]))
            ->action(__('pending_message_reminder.cta'), $url)
            ->line(__('pending_message_reminder.outro'));
    }
}
