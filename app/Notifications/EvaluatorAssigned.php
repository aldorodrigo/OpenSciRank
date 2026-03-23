<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluatorAssigned extends Notification
{

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva revista asignada para evaluación - ' . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Se te ha asignado la revista **\"{$this->journal->title}\"** para evaluación.")
            ->line("Por favor, revisa los datos de la revista y completa la evaluación lo antes posible.")
            ->action('Ir al Panel de Administración', url('/admin'))
            ->line('Gracias por tu colaboración.');
    }
}
