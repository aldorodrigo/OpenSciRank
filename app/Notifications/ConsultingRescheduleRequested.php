<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al EVALUADOR cuando el editor solicita reagendar la sesión.
 * El editor ya sabe que pidió reagendar, así que no se le notifica a él.
 *
 * Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class ConsultingRescheduleRequested extends Notification
{
    use Queueable;

    public function __construct(
        public AdminTask $task,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title      = $this->task->renderedTitle();
        $editorName = $this->task->editor()?->name ?? '—';

        return (new MailMessage)
            ->subject(__('notifications.consulting_reschedule_requested.subject', ['title' => $title]))
            ->greeting(__('notifications.consulting_reschedule_requested.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_reschedule_requested.intro', ['editor' => $editorName]))
            ->line(__('notifications.consulting_reschedule_requested.reason_label', ['reason' => $this->reason]))
            ->line(__('notifications.consulting_reschedule_requested.next_step'))
            ->action(__('notifications.consulting_reschedule_requested.cta'), url('/admin/admin-tasks/'.$this->task->id))
            ->line(__('notifications.consulting_reschedule_requested.outro'));
    }
}
