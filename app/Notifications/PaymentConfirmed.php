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
        $item = $this->payment->product?->name ?? 'Servicio';

        return (new MailMessage)
            ->subject('Pago confirmado - ' . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Tu pago de **\${$this->payment->amount} {$this->payment->currency}** ha sido procesado exitosamente.")
            ->line("**Concepto:** {$item}")
            ->line("**ID de transacción:** {$this->payment->transaction_id}")
            ->line('Tu envío ha sido recibido y será revisado por nuestro equipo.')
            ->action('Ver Mi Panel', url('/app'))
            ->line('Gracias por confiar en nosotros.');
    }
}
