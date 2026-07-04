<?php

namespace App\Notifications;

use App\Models\AdminTask;
use App\Support\TimezoneHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al EDITOR cuando el evaluador envía propuestas de horario.
 * Muestra cada slot en la TZ del editor con un link de aceptación por slot.
 *
 * Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class ConsultingProposalSent extends Notification
{
    use Queueable;

    public function __construct(public AdminTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title     = $this->task->renderedTitle();
        $proposals = $this->task->activeProposals()->get();

        $mail = (new MailMessage)
            ->subject(__('notifications.consulting_proposal_sent.subject', ['title' => $title]))
            ->greeting(__('notifications.consulting_proposal_sent.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.consulting_proposal_sent.intro'))
            ->line(__('notifications.consulting_proposal_sent.slots_header'));

        foreach ($proposals as $proposal) {
            $dateFormatted = TimezoneHelper::formatWithIndicator($proposal->proposed_slot, $notifiable, 'd/m/Y H:i');
            $acceptUrl = url('/app/consulting/'.$this->task->id.'/accept/'.$proposal->id);
            $mail->line(__('notifications.consulting_proposal_sent.slot_line', [
                'date' => $dateFormatted,
                'url'  => $acceptUrl,
            ]));
        }

        $mail
            ->line(__('notifications.consulting_proposal_sent.expiry_note', [
                'date' => TimezoneHelper::format($proposals->first()?->expires_at, $notifiable, 'd/m/Y'),
            ]))
            ->action(__('notifications.consulting_proposal_sent.cta'), url('/app/consulting'))
            ->line(__('notifications.consulting_proposal_sent.outro'));

        return $mail;
    }
}
