<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

class EvaluatorAssigned extends QueuedNotification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New journal assigned for evaluation').' - '.config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('The journal **":title"** has been assigned to you for evaluation.', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('Please review the journal data and complete the evaluation as soon as possible.'))
            // Roadmap #35 — deep-link directo a la página de evaluación del journal,
            // no al panel genérico (el evaluador no navega el resto del admin).
            ->action(__('Go to evaluation'), url('/admin/journals/'.$this->journal->id.'/evaluate'))
            ->line(__('Thank you for your collaboration.'));
    }
}
