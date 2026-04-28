<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingRequested extends Notification
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
            ->subject(__('Listing request received') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We have received your request to list your :type **":title"** on our platform.', ['type' => $type, 'title' => $title]))
            ->line(__('Our team will review the provided information and notify you when the review is complete.'))
            ->line(__('The review process usually takes between 3 and 5 business days.'))
            ->action(__('View My Dashboard'), url('/app'))
            ->line(__('Thank you for trusting us.'));
    }
}
