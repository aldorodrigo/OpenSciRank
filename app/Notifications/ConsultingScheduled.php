<?php

namespace App\Notifications;

use App\Models\AdminTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/**
 * Notificación enviada al editor y al evaluador asignado cuando el admin
 * agenda (o reagenda) una sesión de consultoría (Sprint 3.6 #32).
 *
 * Sin ShouldQueue — el proyecto usa QUEUE_CONNECTION=sync.
 */
class ConsultingScheduled extends Notification
{
    use Queueable;

    public function __construct(
        public AdminTask $task,
        public bool $isRescheduling = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->preferred_locale ?? 'es');

        $isEditor = $this->task->related
            && $this->task->related->user
            && $this->task->related->user->is($notifiable);

        $prefix   = $isEditor ? 'consulting_scheduled.editor' : 'consulting_scheduled.evaluator';
        $title    = $this->task->renderedTitle();
        $date     = $this->task->scheduled_for->format('d/m/Y H:i');
        $resource = $this->task->related?->name ?? $title;

        $subjectKey = $this->isRescheduling
            ? "{$prefix}.subject_rescheduled"
            : "{$prefix}.subject";

        $introKey = $this->isRescheduling
            ? "{$prefix}.intro_rescheduled"
            : "{$prefix}.intro_scheduled";

        $url = $isEditor
            ? url('/app')
            : url('/admin/admin-tasks/'.$this->task->id);

        $mail = (new MailMessage)
            ->subject(__($subjectKey, ['title' => $title]))
            ->greeting(__("{$prefix}.greeting", ['name' => $notifiable->name]))
            ->line(__($introKey))
            ->line(__("{$prefix}.session_at", ['date' => $date]))
            ->line(__("{$prefix}.resource", ['name' => $resource]));

        if ($this->task->payment) {
            $mail->line(__("{$prefix}.amount_paid", [
                'amount'   => number_format($this->task->payment->amount, 2),
                'currency' => strtoupper($this->task->payment->currency ?? 'USD'),
            ]));
        }

        if ($this->task->assignee) {
            $mail->line(__("{$prefix}.evaluator", ['name' => $this->task->assignee->name]));
        } else {
            $mail->line(__("{$prefix}.no_evaluator"));
        }

        $mail->action(__("{$prefix}.cta"), $url)
            ->line(__("{$prefix}.outro"));

        return $mail;
    }
}
