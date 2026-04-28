<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingRejected extends Notification
{

    public function __construct(public Model $record, public ?string $notes = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? __('journal') : __('book');
        $title = $this->record->getTranslationWithFallback('title');

        $mail = (new MailMessage)
            ->subject(__('Listing request rejected') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We regret to inform you that the listing request for your :type **":title"** has not been approved.', ['type' => $type, 'title' => $title]));

        if ($this->notes) {
            $mail->line(__('**Observations:** :notes', ['notes' => $this->notes]));
        }

        return $mail
            ->line(__('If you have questions, you can contact us from our contact page.'))
            ->action(__('Contact'), url('/contact'));
    }
}
