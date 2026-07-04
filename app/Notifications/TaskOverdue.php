<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación enviada cuando una admin_task pasa su due_at (Sprint 3.6 #32).
 * El cron `tasks:check-overdue` la dispara contra el assignee (o contra el
 * super_admin si la task quedó sin asignar).
 */
class TaskOverdue extends QueuedNotification
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
        $daysOverdue = abs((int) $this->task->daysUntilDue());
        $dueAt = $this->task->due_at ? $this->task->due_at->format('d/m/Y') : '—';
        $url = url('/admin/admin-tasks/'.$this->task->id);

        return (new MailMessage)
            ->subject(__('task_overdue.subject', ['title' => $title]))
            ->greeting(__('task_overdue.greeting', ['name' => $notifiable->name]))
            ->line(__('task_overdue.intro', ['days' => $daysOverdue]))
            ->line(__('task_overdue.task', ['title' => $title]))
            ->line(__('task_overdue.due_at', ['date' => $dueAt]))
            ->line(__('task_overdue.action_required'))
            ->action(__('task_overdue.cta'), $url)
            ->error();
    }
}
