<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación enviada al admin cuando se registra un pago huérfano,
 * es decir, cuando el payable (Journal o Book) no se encontró al procesar
 * el webhook de Stripe (posible soft-delete entre checkout y webhook).
 */
class PaymentOrphan extends QueuedNotification
{
    public function __construct(public Payment $payment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payableType = class_basename($this->payment->payable_type ?? 'Unknown');
        $payableId = $this->payment->payable_id ?? '—';
        $sessionId = $this->payment->metadata['stripe_session_id'] ?? '—';
        $amount = number_format((float) $this->payment->amount, 2);
        $currency = $this->payment->currency ?? 'USD';
        $transactionId = $this->payment->transaction_id ?? '—';

        return (new MailMessage)
            ->subject(__('payment_orphan.subject', ['id' => $transactionId]).' — '.config('app.name'))
            ->greeting(__('payment_orphan.greeting', ['name' => $notifiable->name]))
            ->line(__('payment_orphan.intro'))
            ->line(__('payment_orphan.session_id', ['session_id' => $sessionId]))
            ->line(__('payment_orphan.transaction_id', ['transaction_id' => $transactionId]))
            ->line(__('payment_orphan.amount', ['amount' => $amount, 'currency' => $currency]))
            ->line(__('payment_orphan.payable', ['type' => $payableType, 'id' => $payableId]))
            ->line(__('payment_orphan.payment_id', ['id' => $this->payment->id]))
            ->line(__('payment_orphan.instruction'))
            ->action(__('payment_orphan.cta'), url('/admin/payments/'.$this->payment->id));
    }
}
