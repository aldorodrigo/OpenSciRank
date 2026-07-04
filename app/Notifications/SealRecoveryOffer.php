<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SealRecoveryOffer extends Notification
{
    use Queueable;

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.seal_recovery.subject', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->greeting(__('notifications.seal_recovery.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_recovery.line1', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('notifications.seal_recovery.line2'))
            ->line(__('notifications.seal_recovery.line3'))
            ->action(__('notifications.seal_recovery.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_recovery.footer'));
    }
}
