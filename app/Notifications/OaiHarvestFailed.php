<?php

namespace App\Notifications;

use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Aviso al super_admin cuando una cosecha OAI-PMH agota sus reintentos y queda
 * en estado `failed`. Se dispara desde `HarvestJournalArticles::failed()` para
 * que el admin actúe (revisar endpoint, corregir config, reintentar) en vez de
 * que la cosecha fallida pase inadvertida.
 *
 * Multiidioma vía HasLocalePreference del destinatario.
 */
class OaiHarvestFailed extends QueuedNotification
{
    public function __construct(
        public Journal $journal,
        public ?string $error = null,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $error = $this->error ?: $this->journal->oai_last_harvest_error ?: '—';

        return (new MailMessage)
            ->subject(__('oai_harvest_failed.subject', ['journal' => $this->journal->title]).' — '.config('app.name'))
            ->greeting(__('oai_harvest_failed.greeting', ['name' => $notifiable->name]))
            ->line(__('oai_harvest_failed.intro', ['journal' => $this->journal->title]))
            ->line(__('oai_harvest_failed.endpoint', ['url' => $this->journal->oai_base_url ?: '—']))
            ->line(__('oai_harvest_failed.error', ['error' => $error]))
            ->line(__('oai_harvest_failed.instruction'))
            ->action(
                __('oai_harvest_failed.cta'),
                url('/admin/journals/'.$this->journal->id.'/edit')
            );
    }
}
