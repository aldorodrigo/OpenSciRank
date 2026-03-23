<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationCompleted extends Notification
{

    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $score = number_format($this->journal->current_score, 1);
        $isCertified = $this->journal->status === 'certified';
        $status = $isCertified ? 'Certificada' : 'Evaluada';

        $mail = (new MailMessage)
            ->subject("Evaluación completada: {$status} - " . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("La evaluación de tu revista **\"{$this->journal->title}\"** ha finalizado.")
            ->line("**Resultado:** {$status}")
            ->line("**Puntuación:** {$score}%");

        if ($this->journal->evaluation_notes) {
            $mail->line("**Observaciones:**")
                ->line($this->journal->evaluation_notes);
        }

        if ($isCertified && $this->journal->seal_expires_at) {
            $mail->line('---')
                ->line("Tu revista ha obtenido el **Editorial Standards Seal**, vigente hasta el **{$this->journal->seal_expires_at->format('d/m/Y')}**.")
                ->line('Ahora puedes obtener el sello editorial embebible para mostrar en tu sitio web.')
                ->action('Obtener Sello Editorial', url("/app/badge/{$this->journal->id}"))
                ->line('Gracias por participar en nuestro proceso de evaluación.');
        } else {
            $mail->action('Ver Detalles', url('/app'))
                ->line('Gracias por participar en nuestro proceso de evaluación.');
        }

        return $mail;
    }
}
