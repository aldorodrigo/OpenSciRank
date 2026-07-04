<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

class SealExpired extends QueuedNotification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.seal_expired.subject', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->greeting(__('notifications.seal_expired.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_expired.line1', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('notifications.seal_expired.line2'))
            ->line(__('notifications.seal_expired.line3'))
            ->line(__('notifications.seal_expired.line4'))
            ->action(__('notifications.seal_expired.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_expired.footer'));
    }
}
