<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación al editor cuando su re-evaluación de renovación fue aprobada
 * (score >= 75% y todos los indicadores críticos cumplidos). El sello se
 * extendió por la cantidad de años pagados.
 */
class SealRenewalApproved extends Notification
{
    // Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync

    public function __construct(public Journal $journal, public int $years) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->journal->getTranslationWithFallback('title');
        $score = number_format((float) $this->journal->current_score, 1);
        $expires = $this->journal->seal_expires_at?->format('d/m/Y') ?? '—';

        return (new MailMessage)
            ->subject(__('renewal_approved.subject') . ' — ' . config('app.name'))
            ->greeting(__('renewal_approved.greeting', ['name' => $notifiable->name]))
            ->line(__('renewal_approved.intro', ['title' => $title]))
            ->line(__('renewal_approved.score', ['score' => $score]))
            ->line(__('renewal_approved.years', ['years' => $this->years]))
            ->line(__('renewal_approved.expires', ['date' => $expires]))
            ->action(__('renewal_approved.cta'), url('/app/badge/' . $this->journal->id))
            ->line(__('renewal_approved.thanks'));
    }
}
