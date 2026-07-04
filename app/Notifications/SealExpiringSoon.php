<?php

namespace App\Notifications;

use App\Models\Journal;
use App\Notifications\Concerns\ReminderNotification;
use Illuminate\Notifications\Messages\MailMessage;

class SealExpiringSoon extends QueuedNotification
{
    use ReminderNotification;

    public function __construct(public Journal $journal) {}

    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = (int) now()->diffInDays($this->journal->seal_expires_at);

        return (new MailMessage)
            ->withSymfonyMessage($this->unsubscribeHeaders($notifiable))
            ->subject(__('notifications.seal_expiring_soon.subject', ['days' => $daysLeft]))
            ->greeting(__('notifications.seal_expiring_soon.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_expiring_soon.line1', [
                'title' => $this->journal->getTranslationWithFallback('title'),
                'days' => $daysLeft,
                'date' => $this->journal->seal_expires_at->format('d/m/Y'),
            ]))
            ->line(__('notifications.seal_expiring_soon.line2'))
            ->action(__('notifications.seal_expiring_soon.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_expiring_soon.footer'));
    }
}
