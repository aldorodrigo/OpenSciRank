<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingRejected extends Notification
{

    public function __construct(public Model $record, public ?string $notes = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? 'revista' : 'libro';
        $title = $this->record->title;

        $mail = (new MailMessage)
            ->subject("Solicitud de listado rechazada - " . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Lamentamos informarte que la solicitud de listado de tu {$type} **\"{$title}\"** no ha sido aprobada.");

        if ($this->notes) {
            $mail->line("**Observaciones:** {$this->notes}");
        }

        return $mail
            ->line('Si tienes dudas, puedes contactarnos desde nuestra página de contacto.')
            ->action('Contactar', url('/contact'));
    }
}
