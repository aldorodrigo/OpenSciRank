<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifica a editor + evaluador + super_admin cuando las propuestas de horario
 * vencen sin respuesta del editor. La task vuelve a pending.
 */
class ConsultingProposalsExpired extends QueuedNotification
{
    public function __construct(public AdminTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->task->renderedTitle();
        $isEditor = $notifiable->is($this->task->editor());

        $prefix = $isEditor
            ? 'notifications.consulting_proposals_expired.editor'
            : 'notifications.consulting_proposals_expired.admin';

        $mail = (new MailMessage)
            ->subject(__('notifications.consulting_proposals_expired.subject', ['title' => $title]))
            ->greeting(__("{$prefix}.greeting", ['name' => $notifiable->name]))
            ->line(__("{$prefix}.intro", ['title' => $title]))
            ->line(__("{$prefix}.consequence"));

        $url = $isEditor
            ? url('/app/consulting')
            : url('/admin/admin-tasks/'.$this->task->id);

        $mail
            ->action(__('notifications.consulting_proposals_expired.cta'), $url)
            ->line(__('notifications.consulting_proposals_expired.outro'));

        return $mail;
    }
}
