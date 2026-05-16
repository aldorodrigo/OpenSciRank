<?php

namespace App\Notifications;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/**
 * Notifica al destinatario apropiado cuando se abre un nuevo hilo de
 * conversación:
 *   - Editor abre hilo → super_admin recibe esta notificación.
 *   - Admin abre hilo  → editor (destinatario inicial) recibe esta notificación.
 *
 * El subject line del email refleja el tipo de recurso vinculado al hilo.
 *
 * Sin ShouldQueue — QUEUE_CONNECTION=sync.
 */
class NewConversationOpened extends Notification
{
    use Queueable;

    public function __construct(public Conversation $conversation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->preferred_locale ?? 'es');

        $isSuperAdmin = $notifiable->hasRole('super_admin');
        $prefix       = $isSuperAdmin
            ? 'notifications.new_conversation_opened.admin'
            : 'notifications.new_conversation_opened.editor';

        $subjectLine  = $this->resolveSubjectLine($isSuperAdmin);
        $starter      = $this->conversation->startedBy;

        $url = $isSuperAdmin
            ? url('/admin/conversations/'.$this->conversation->id)
            : url('/app/messages/'.$this->conversation->id);

        return (new MailMessage)
            ->subject($subjectLine)
            ->greeting(__("{$prefix}.greeting", ['name' => $notifiable->name]))
            ->line(__("{$prefix}.intro", ['starter' => $starter?->name ?? '—']))
            ->line(__("{$prefix}.context", ['subject' => $subjectLine]))
            ->action(__("{$prefix}.cta"), $url)
            ->line(__("{$prefix}.outro"));
    }

    /**
     * Construye el subject line (usado tanto en el subject del email
     * como en la línea de contexto del cuerpo).
     */
    private function resolveSubjectLine(bool $isSuperAdmin): string
    {
        $related = $this->conversation->subjectModel;

        if ($related instanceof Journal) {
            $name = $related->getTranslationWithFallback('title');
            $key = $isSuperAdmin
                ? 'notifications.new_conversation_opened.admin.subject_journal'
                : 'notifications.new_conversation_opened.editor.subject_journal';
            return __($key, ['name' => $name]);
        }

        if ($related instanceof Book) {
            $name = $related->getTranslationWithFallback('title');
            $key = $isSuperAdmin
                ? 'notifications.new_conversation_opened.admin.subject_book'
                : 'notifications.new_conversation_opened.editor.subject_book';
            return __($key, ['name' => $name]);
        }

        if ($related instanceof AdminTask) {
            $title = $related->renderedTitle();
            $key = $isSuperAdmin
                ? 'notifications.new_conversation_opened.admin.subject_task'
                : 'notifications.new_conversation_opened.editor.subject_task';
            return __($key, ['title' => $title]);
        }

        // Sin recurso vinculado: consulta general
        $key = $isSuperAdmin
            ? 'notifications.new_conversation_opened.admin.subject_general'
            : 'notifications.new_conversation_opened.editor.subject_general';
        return __($key);
    }
}
