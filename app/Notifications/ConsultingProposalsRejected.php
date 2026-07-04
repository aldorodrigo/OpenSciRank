<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifica al EVALUADOR (y super_admin) cuando el editor rechaza todas las
 * propuestas de horario y pide nuevas fechas.
 */
class ConsultingProposalsRejected extends QueuedNotification
{
    public function __construct(
        public AdminTask $task,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->task->renderedTitle();
        $editorName = $this->task->editor()?->name ?? '—';

        $mail = (new MailMessage)
            ->subject(__('notifications.consulting_proposals_rejected.subject', ['title' => $title]))
            ->greeting(__('notifications.consulting_proposals_rejected.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_proposals_rejected.intro', ['editor' => $editorName]));

        if ($this->reason) {
            $mail->line(__('notifications.consulting_proposals_rejected.reason', ['reason' => $this->reason]));
        }

        $mail
            ->line(__('notifications.consulting_proposals_rejected.next_step'))
            ->action(__('notifications.consulting_proposals_rejected.cta'), url('/admin/admin-tasks/'.$this->task->id))
            ->line(__('notifications.consulting_proposals_rejected.outro'));

        return $mail;
    }
}
