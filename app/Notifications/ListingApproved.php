<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingApproved extends Notification
{

    public function __construct(public Model $record) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? __('journal') : __('book');
        $title = $this->record->getTranslationWithFallback('title');

        return (new MailMessage)
            ->subject(__('Your :type has been listed', ['type' => $type]) . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We are pleased to inform you that your :type **":title"** has been approved and listed on our platform.', ['type' => $type, 'title' => $title]))
            ->action(__('View My Dashboard'), url('/app'))
            ->line(__('Thank you for being part of our community.'));
    }
}
