<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación enviada al admin cuando un editor paga una renovación del sello.
 * El journal vuelve al flujo de evaluación y queda esperando una nueva revisión
 * editorial antes de extender el sello (política Opción B, 2026-05-10).
 */
class NewRenewalEvaluation extends Notification
{
    // Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync

    public function __construct(
        public Journal $journal,
        public int $years,
        public float $amount,
        public string $currency,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->journal->getTranslationWithFallback('title');
        $editor = $this->journal->user?->name ?? '—';
        $editorEmail = $this->journal->user?->email ?? '—';
        $amount = number_format($this->amount, 2);

        return (new MailMessage)
            ->subject(__('renewal_evaluation.subject') . ' — ' . config('app.name'))
            ->greeting(__('renewal_evaluation.greeting', ['name' => $notifiable->name]))
            ->line(__('renewal_evaluation.intro'))
            ->line(__('renewal_evaluation.journal', ['title' => $title]))
            ->line(__('renewal_evaluation.editor', ['name' => $editor, 'email' => $editorEmail]))
            ->line(__('renewal_evaluation.years', ['years' => $this->years]))
            ->line(__('renewal_evaluation.amount', ['amount' => $amount, 'currency' => $this->currency]))
            ->line(__('renewal_evaluation.instruction'))
            ->action(__('renewal_evaluation.cta'), url('/admin/journals/' . $this->journal->id . '/evaluate'));
    }
}
