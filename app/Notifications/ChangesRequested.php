<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangesRequested extends Notification
{

    public function __construct(
        public Model $record,
        public string $context,
        public ?string $notes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? __('journal') : __('book');
        $title = $this->record->getTranslationWithFallback('title');
        $contextLabel = $this->context === 'evaluation' ? __('evaluation') : __('listing');

        $mail = (new MailMessage)
            ->subject(__('Corrections requested (:context)', ['context' => $contextLabel]) . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Corrections have been requested for your :type **":title"** as part of the :context process.', ['type' => $type, 'title' => $title, 'context' => $contextLabel]));

        if ($this->notes) {
            $mail->line(__("**Reviewer's observations:**"))
                ->line($this->notes);
        }

        return $mail
            ->action(__('Review and Correct'), url('/app'))
            ->line(__('Please make the indicated corrections and resubmit your request.'));
    }
}
