<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SealExpired extends Notification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu Editorial Standards Seal ha vencido - ' . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("El Editorial Standards Seal de tu revista **\"{$this->journal->title}\"** ha vencido.")
            ->line('Esto significa que:')
            ->line('- El sello ya no es válido y no debe mostrarse en tu sitio web.')
            ->line('- Tu revista mantiene su puntuación y nivel de evaluación.')
            ->line('- Puedes renovar el sello en cualquier momento.')
            ->line('Renueva por 2 años y recupera tu certificación editorial.')
            ->action('Renovar Sello', url("/app/renew/{$this->journal->id}"))
            ->line('Gracias por confiar en ' . config('app.name') . '.');
    }
}
