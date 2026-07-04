<?php

namespace App\Notifications;

use App\Models\Journal;
use App\Notifications\Concerns\ReminderNotification;
use Illuminate\Notifications\Messages\MailMessage;

class SealExpiringEarlyReminder extends QueuedNotification
{
    use ReminderNotification;

    public function __construct(public Journal $journal, public int $daysLeft) {}

    public function toMail(object $notifiable): MailMessage
    {
        // Fecha límite de la promoción: 30 días antes del vencimiento del sello.
        // Coincide con el cierre de la ventana D-60..D-30 controlada en
        // PaymentCheckout::getIsInEarlyRenewalWindowProperty().
        $promoDeadline = $this->journal->seal_expires_at->copy()->subDays(30)->format('d/m/Y');

        return (new MailMessage)
            ->withSymfonyMessage($this->unsubscribeHeaders($notifiable))
            ->subject(__('notifications.seal_early_reminder.subject', ['title' => $this->journal->getTranslationWithFallback('title')]))
            ->greeting(__('notifications.seal_early_reminder.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.seal_early_reminder.line1', [
                'title' => $this->journal->getTranslationWithFallback('title'),
                'days' => $this->daysLeft,
                'date' => $this->journal->seal_expires_at->format('d/m/Y'),
            ]))
            ->line(__('notifications.seal_early_reminder.line2'))
            ->line(__('notifications.seal_early_reminder.line3', [
                'pct' => \App\Livewire\PaymentCheckout::EARLY_RENEWAL_DISCOUNT_PCT,
                'deadline' => $promoDeadline,
            ]))
            ->action(__('notifications.seal_early_reminder.cta'), route('app.renew', $this->journal, true))
            ->line(__('notifications.seal_early_reminder.footer'));
    }
}
