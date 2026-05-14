<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación enviada al usuario asignado cuando se le asigna una
 * admin_task (Sprint 3.6 #32).
 *
 * Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync.
 */
class TaskAssigned extends Notification
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
        $priority = match ($this->task->priority) {
            AdminTask::PRIORITY_HIGH => __('admin_tasks.priority.high'),
            AdminTask::PRIORITY_LOW => __('admin_tasks.priority.low'),
            default => __('admin_tasks.priority.normal'),
        };
        $dueAt = $this->task->due_at ? $this->task->due_at->format('d/m/Y H:i') : '—';
        $url = url('/admin/admin-tasks/'.$this->task->id);

        return (new MailMessage)
            ->subject(__('task_assigned.subject', ['title' => $title]))
            ->greeting(__('task_assigned.greeting', ['name' => $notifiable->name]))
            ->line(__('task_assigned.intro'))
            ->line(__('task_assigned.task', ['title' => $title]))
            ->line(__('task_assigned.priority', ['priority' => $priority]))
            ->line(__('task_assigned.due_at', ['date' => $dueAt]))
            ->action(__('task_assigned.cta'), $url);
    }
}
