<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluatorAssigned extends Notification
{

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New journal assigned for evaluation') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('The journal **":title"** has been assigned to you for evaluation.', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('Please review the journal data and complete the evaluation as soon as possible.'))
            ->action(__('Go to Admin Panel'), url('/admin'))
            ->line(__('Thank you for your collaboration.'));
    }
}
