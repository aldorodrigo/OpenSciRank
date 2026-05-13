<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación al editor cuando su re-evaluación de renovación NO alcanzó
 * el umbral del 75%. No hay reembolso (pagó por la evaluación, no por
 * garantía de sello). Se invita a comprar una re-evaluación si quiere
 * reintentar tras corregir indicadores.
 */
class SealRenewalRejected extends Notification
{
    // Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->journal->getTranslationWithFallback('title');
        $score = number_format((float) $this->journal->current_score, 1);

        $mail = (new MailMessage)
            ->subject(__('renewal_rejected.subject') . ' — ' . config('app.name'))
            ->greeting(__('renewal_rejected.greeting', ['name' => $notifiable->name]))
            ->line(__('renewal_rejected.intro', ['title' => $title]))
            ->line(__('renewal_rejected.score', ['score' => $score]))
            ->line(__('renewal_rejected.completed'));

        if ($this->journal->evaluation_notes) {
            $mail->line(__('renewal_rejected.notes_label'))
                ->line($this->journal->evaluation_notes);
        }

        return $mail
            ->line(__('renewal_rejected.retry_offer'))
            ->action(__('renewal_rejected.cta'), url('/app/checkout/' . $this->journal->id))
            ->line(__('renewal_rejected.support'));
    }
}
