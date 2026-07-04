<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Recordatorio enviado 24h antes de una sesión de consultoría al editor
 * y al evaluador asignado (Sprint 3.6 #32).
 *
 * Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync.
 */
class ConsultingReminder extends Notification
{
    use Queueable;

    public function __construct(public AdminTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isEditor = $this->task->related
            && $this->task->related->user
            && $this->task->related->user->is($notifiable);

        $prefix   = $isEditor ? 'consulting_reminder.editor' : 'consulting_reminder.evaluator';
        $title    = $this->task->renderedTitle();
        $date     = $this->task->scheduled_for->format('d/m/Y H:i');
        $resource = $this->task->related?->name ?? $title;

        $url = $isEditor
            ? url('/app')
            : url('/admin/admin-tasks/'.$this->task->id);

        return (new MailMessage)
            ->subject(__("{$prefix}.subject", ['title' => $title]))
            ->greeting(__("{$prefix}.greeting", ['name' => $notifiable->name]))
            ->line(__("{$prefix}.intro"))
            ->line(__("{$prefix}.session_at", ['date' => $date]))
            ->line(__("{$prefix}.resource", ['name' => $resource]))
            ->action(__("{$prefix}.cta"), $url)
            ->line(__("{$prefix}.outro"));
    }
}
