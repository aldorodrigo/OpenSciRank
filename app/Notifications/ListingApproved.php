<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingApproved extends Notification
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
            ->subject("Tu {$type} ha sido listada - " . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Nos complace informarte que tu {$type} **\"{$title}\"** ha sido aprobada y listada en nuestra plataforma.")
            ->action('Ver Mi Panel', url('/app'))
            ->line('Gracias por ser parte de nuestra comunidad.');
    }
}
