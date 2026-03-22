<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangesRequested extends Notification
{

    public function __construct(
        public Model $record,
        public string $context,
        public ?string $notes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->record instanceof \App\Models\Journal ? 'revista' : 'libro';
        $title = $this->record->title;
        $contextLabel = $this->context === 'evaluation' ? 'evaluación' : 'listado';

        $mail = (new MailMessage)
            ->subject("Correcciones solicitadas ({$contextLabel}) - " . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("Se han solicitado correcciones en tu {$type} **\"{$title}\"** como parte del proceso de {$contextLabel}.");

        if ($this->notes) {
            $mail->line("**Observaciones del revisor:**")
                ->line($this->notes);
        }

        return $mail
            ->action('Revisar y Corregir', url('/app'))
            ->line('Por favor, realiza las correcciones indicadas y reenvía tu solicitud.');
    }
}
