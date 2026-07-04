<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

class SealExpiringLastWeek extends QueuedNotification
{
    public function __construct(public Journal $journal, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.seal_expiring_last_week.subject', ['days' => $this->daysLeft]))
            ->greeting(__('notifications.seal_expiring_last_week.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_expiring_last_week.line1', [
                'title' => $this->journal->getTranslationWithFallback('title'),
                'days' => $this->daysLeft,
                'date' => $this->journal->seal_expires_at->format('d/m/Y'),
            ]))
            ->line(__('notifications.seal_expiring_last_week.line2'))
            ->line(__('notifications.seal_expiring_last_week.line3'))
            ->action(__('notifications.seal_expiring_last_week.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_expiring_last_week.footer'));
    }
}
