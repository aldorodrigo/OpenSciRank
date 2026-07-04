<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

class SealExpiringUrgent extends QueuedNotification
{
    public function __construct(public Journal $journal, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.seal_expiring_urgent.subject', ['days' => $this->daysLeft]))
            ->greeting(__('notifications.seal_expiring_urgent.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_expiring_urgent.line1', [
                'title' => $this->journal->getTranslationWithFallback('title'),
                'days' => $this->daysLeft,
                'date' => $this->journal->seal_expires_at->format('d/m/Y'),
            ]))
            ->line(__('notifications.seal_expiring_urgent.line2'))
            ->action(__('notifications.seal_expiring_urgent.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_expiring_urgent.footer'));
    }
}
