<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/**
 * Reemplaza PaymentConfirmed para los SKUs de consultoría:
 * - action-plan-consulting ($215, payable=Journal)
 * - new-journal-consulting ($1500, payable=User)
 *
 * Solo se envía al editor (cliente). Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class ConsultingPaymentConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->preferred_locale ?? 'es');

        $slug     = $this->payment->product?->slug ?? '';
        $isNewJournal = $slug === 'new-journal-consulting';

        $mail = (new MailMessage)
            ->subject(__('notifications.consulting_payment_confirmed.editor.subject'))
            ->greeting(__('notifications.consulting_payment_confirmed.editor.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_payment_confirmed.editor.intro', [
                'amount'   => number_format((float) $this->payment->amount, 2),
                'currency' => strtoupper($this->payment->currency ?? 'USD'),
            ]));

        if ($isNewJournal) {
            $mail
                ->line(__('notifications.consulting_payment_confirmed.editor.new_journal.line1'))
                ->line(__('notifications.consulting_payment_confirmed.editor.new_journal.line2'))
                ->line(__('notifications.consulting_payment_confirmed.editor.new_journal.line3'));
        } else {
            $mail
                ->line(__('notifications.consulting_payment_confirmed.editor.action_plan.line1'))
                ->line(__('notifications.consulting_payment_confirmed.editor.action_plan.line2'));
        }

        $mail
            ->action(__('notifications.consulting_payment_confirmed.editor.cta'), url('/app/consulting'))
            ->line(__('notifications.consulting_payment_confirmed.editor.outro'));

        return $mail;
    }
}
