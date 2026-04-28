<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SealExpiringSoon extends Notification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = now()->diffInDays($this->journal->seal_expires_at);

        return (new MailMessage)
            ->subject(__('Your Editorial Standards Seal is expiring soon') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('The Editorial Standards Seal of your journal **":title"** expires in **:days days** (on :date).', ['title' => $this->journal->getTranslationWithFallback('title'), 'days' => $daysLeft, 'date' => $this->journal->seal_expires_at->format('d/m/Y')]))
            ->line(__('Once expired, the seal will no longer be valid and cannot be displayed on your website.'))
            ->line(__('Renew now for 2 years and keep your certification active.'))
            ->action(__('Renew Seal'), url("/app/renew/{$this->journal->id}"))
            ->line(__('Thank you for trusting :app.', ['app' => config('app.name')]));
    }
}
