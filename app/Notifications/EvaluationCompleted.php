<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationCompleted extends Notification
{

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $score = number_format($this->journal->current_score, 1);
        $isCertified = $this->journal->status === 'certified';
        $status = $isCertified ? __('Certified') : __('Evaluated');

        $mail = (new MailMessage)
            ->subject(__('Evaluation completed: :status', ['status' => $status]) . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('The evaluation of your journal **":title"** has been completed.', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->line(__('**Result:** :status', ['status' => $status]))
            ->line(__('**Score:** :score%', ['score' => $score]));

        if ($this->journal->evaluation_notes) {
            $mail->line(__('**Observations:**'))
                ->line($this->journal->evaluation_notes);
        }

        if ($isCertified && $this->journal->seal_expires_at) {
            $mail->line('---')
                ->line(__('Your journal has received the **Editorial Standards Seal**, valid until **:date**.', ['date' => $this->journal->seal_expires_at->format('d/m/Y')]))
                ->line(__('You can now get the embeddable editorial seal to display on your website.'))
                ->action(__('Get Editorial Seal'), url("/app/badge/{$this->journal->id}"))
                ->line(__('Thank you for participating in our evaluation process.'));
        } else {
            $mail->action(__('View Details'), url('/app'))
                ->line(__('Thank you for participating in our evaluation process.'));
        }

        return $mail;
    }
}
