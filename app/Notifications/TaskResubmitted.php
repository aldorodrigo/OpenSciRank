<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación enviada cuando el editor resubmite un journal/book tras
 * un pedido de cambios. La admin_task asociada ya estaba in_progress;
 * acá avisamos al asignado (o super_admin) que las correcciones llegaron
 * y puede retomar el trabajo (Sprint 3.6 #37).
 */
class TaskResubmitted extends QueuedNotification
{
    public function __construct(public AdminTask $task)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->task->renderedTitle();
        $url = url('/admin/admin-tasks/'.$this->task->id);

        return (new MailMessage)
            ->subject(__('task_resubmitted.subject', ['title' => $title]))
            ->greeting(__('task_resubmitted.greeting', ['name' => $notifiable->name]))
            ->line(__('task_resubmitted.intro'))
            ->line(__('task_resubmitted.task', ['title' => $title]))
            ->line(__('task_resubmitted.next_step'))
            ->action(__('task_resubmitted.cta'), $url);
    }
}
