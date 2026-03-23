<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SealExpiringSoon extends Notification
{
    public function __construct(public Journal $journal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = now()->diffInDays($this->journal->seal_expires_at);

        return (new MailMessage)
            ->subject('Tu Editorial Standards Seal vence pronto - ' . config('app.name'))
            ->greeting("Hola {$notifiable->name},")
            ->line("El Editorial Standards Seal de tu revista **\"{$this->journal->title}\"** vence en **{$daysLeft} días** (el {$this->journal->seal_expires_at->format('d/m/Y')}).")
            ->line('Una vez vencido, el sello dejará de ser válido y no podrá mostrarse en tu sitio web.')
            ->line('Renueva ahora por 2 años y mantén tu certificación vigente.')
            ->action('Renovar Sello', url("/app/renew/{$this->journal->id}"))
            ->line('Gracias por confiar en ' . config('app.name') . '.');
    }
}
