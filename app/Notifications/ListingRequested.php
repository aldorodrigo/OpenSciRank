<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingRequested extends Notification
{

    public function __construct(public Model $record) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? 'revista' : 'libro';
        $title = $this->record->title;

        return (new MailMessage)
            ->subject("Solicitud de listado recibida - " . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Hemos recibido tu solicitud para listar tu {$type} **\"{$title}\"** en nuestra plataforma.")
            ->line("Nuestro equipo revisará la información proporcionada y te notificaremos cuando se complete la revisión.")
            ->line("El proceso de revisión toma habitualmente entre 3 y 5 días hábiles.")
            ->action('Ver Mi Panel', url('/app'))
            ->line('Gracias por confiar en nosotros.');
    }
}
