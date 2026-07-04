<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Envía al EDITOR el resumen de la sesión de consultoría una vez marcada
 * como completed, pero solo si client_visible_notes no está vacío.
 *
 * El caller (EvaluateJournal o AdminTask::complete) debe verificar
 * client_visible_notes antes de llamar notify(), o puede instanciar
 * la notificación directamente — la guarda es interna en shouldSend().
 */
class ConsultingSessionSummary extends QueuedNotification
{
    public function __construct(public AdminTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Omitir el envío si no hay notas visibles al cliente.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return ! empty(trim($this->task->client_visible_notes ?? ''));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->task->renderedTitle();

        return (new MailMessage)
            ->subject(__('notifications.consulting_session_summary.subject', ['title' => $title]))
            ->greeting(__('notifications.consulting_session_summary.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_session_summary.intro'))
            ->line($this->task->client_visible_notes)
            ->action(__('notifications.consulting_session_summary.cta'), url('/app/consulting'))
            ->line(__('notifications.consulting_session_summary.outro'));
    }
}
