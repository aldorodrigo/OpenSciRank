<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification
{

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item = $this->payment->product?->getTranslationWithFallback('name') ?: __('Service');

        return (new MailMessage)
            ->subject(__('Payment confirmed') . ' - ' . config('app.name'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your payment of **$:amount :currency** has been successfully processed.', ['amount' => $this->payment->amount, 'currency' => $this->payment->currency]))
            ->line(__('**Concept:** :item', ['item' => $item]))
            ->line(__('**Transaction ID:** :id', ['id' => $this->payment->transaction_id]))
            ->line(__('Your submission has been received and will be reviewed by our team.'))
            ->action(__('View My Dashboard'), url('/app'))
            ->line(__('Thank you for trusting us.'));
    }
}
