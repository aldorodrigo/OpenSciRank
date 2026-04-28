<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SealExpired extends Notification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your Editorial Standards Seal has expired') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('The Editorial Standards Seal of your journal **":title"** has expired.', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('This means that:'))
            ->line(__('- The seal is no longer valid and should not be displayed on your website.'))
            ->line(__('- Your journal retains its score and evaluation level.'))
            ->line(__('- You can renew the seal at any time.'))
            ->line(__('Renew for 2 years and recover your editorial certification.'))
            ->action(__('Renew Seal'), url("/app/renew/{$this->journal->id}"))
            ->line(__('Thank you for trusting :app.', ['app' => config('app.name')]));
    }
}
