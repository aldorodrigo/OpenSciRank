<?php

namespace App\Notifications;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifica al destinatario apropiado cuando se abre un nuevo hilo de
 * conversación:
 *   - Editor abre hilo → super_admin recibe esta notificación.
 *   - Admin abre hilo  → editor (destinatario inicial) recibe esta notificación.
 *
 * El subject line del email refleja el tipo de recurso vinculado al hilo.
 */
class NewConversationOpened extends QueuedNotification
{
    public function __construct(public Conversation $conversation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isSuperAdmin = $notifiable->hasRole('super_admin');
        // Roadmap #35 — tercera rama: un evaluador (no super_admin) recibe el
        // CTA hacia /admin, no hacia /app/messages (panel editor, inaccesible
        // para él). Reusa el copy "editor" (genérico "se abrió un hilo").
        $isEvaluator = ! $isSuperAdmin && $notifiable->hasRole('evaluator');

        $prefix = $isSuperAdmin
            ? 'notifications.new_conversation_opened.admin'
            : 'notifications.new_conversation_opened.editor';

        $subjectLine = $this->resolveSubjectLine($isSuperAdmin);
        $starter = $this->conversation->startedBy;

        $url = match (true) {
            $isSuperAdmin => url('/admin/conversations/'.$this->conversation->id),
            $isEvaluator => $this->evaluatorDeepLink(),
            default => url('/app/messages/'.$this->conversation->id),
        };

        return (new MailMessage)
            ->subject($subjectLine)
            ->greeting(__("{$prefix}.greeting", ['name' => $notifiable->name]))
            ->line(__("{$prefix}.intro", ['starter' => $starter?->name ?? '—']))
            ->line(__("{$prefix}.context", ['subject' => $subjectLine]))
            ->action(__("{$prefix}.cta"), $url)
            ->line(__("{$prefix}.outro"));
    }

    /**
     * Roadmap #35 — deep-link del evaluador. Si el hilo está anclado a un
     * Journal, va directo a su página de evaluación (donde vive la mensajería
     * del evaluador); si no, al panel admin genérico.
     */
    private function evaluatorDeepLink(): string
    {
        $related = $this->conversation->subjectModel;

        if ($related instanceof Journal) {
            return url('/admin/journals/'.$related->id.'/evaluate');
        }

        return url('/admin');
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
