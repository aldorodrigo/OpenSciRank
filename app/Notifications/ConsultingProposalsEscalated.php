<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al super_admin cuando una task de consultoría supera el cap de
 * rondas de propuestas sin acuerdo (AdminTask::MAX_PROPOSAL_ROUNDS).
 * Requiere intervención humana.
 *
 * Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class ConsultingProposalsEscalated extends Notification
{
    use Queueable;

    public function __construct(public AdminTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title      = $this->task->renderedTitle();
        $editorName = $this->task->editor()?->name ?? '—';
        $rounds     = $this->task->proposal_count_sent;

        return (new MailMessage)
            ->subject(__('notifications.consulting_proposals_escalated.subject', ['title' => $title]))
            ->greeting(__('notifications.consulting_proposals_escalated.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_proposals_escalated.intro', [
                'title'  => $title,
                'rounds' => $rounds,
                'max'    => AdminTask::MAX_PROPOSAL_ROUNDS,
            ]))
            ->line(__('notifications.consulting_proposals_escalated.editor_info', ['editor' => $editorName]))
            ->line(__('notifications.consulting_proposals_escalated.action_required'))
            ->action(__('notifications.consulting_proposals_escalated.cta'), url('/admin/admin-tasks/'.$this->task->id))
            ->line(__('notifications.consulting_proposals_escalated.outro'));
    }
}
