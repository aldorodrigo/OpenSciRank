<?php

namespace App\Notifications;

use App\Support\TimezoneHelper;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

/**
 * Sprint 3.7 #44 — digest diario de conversaciones con mensajes sin leer.
 *
 * Reemplaza el batching de 5 min por un único email diario a las 9:00 de la
 * hora local del destinatario. Lista cada hilo con mensajes pendientes, el
 * último mensaje + autor, y un link directo al hilo.
 *
 * Sin queue. Multiidioma vía HasLocalePreference.
 */
class ConversationsDailyDigest extends QueuedNotification
{
    /**
     * @param  Collection<int, array{conversation: \App\Models\Conversation, unread_count: int, last_message: \App\Models\Message}>  $items
     */
    public function __construct(public Collection $items) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $totalMessages = $this->items->sum('unread_count');
        $totalConversations = $this->items->count();

        $msg = (new MailMessage)
            ->subject(__('conversations_daily_digest.subject', [
                'count' => $totalMessages,
            ]))
            ->greeting(__('conversations_daily_digest.greeting', ['name' => $notifiable->name]))
            ->line(__('conversations_daily_digest.intro', [
                'total' => $totalMessages,
                'conversations' => $totalConversations,
            ]));

        // Por cada conversation con mensajes sin leer
        foreach ($this->items as $item) {
            $conv = $item['conversation'];
            $unreadCount = $item['unread_count'];
            $lastMessage = $item['last_message'];
            $author = $lastMessage->user?->name ?? '—';
            $preview = mb_substr(trim($lastMessage->body), 0, 140);
            if (mb_strlen($lastMessage->body) > 140) {
                $preview .= '…';
            }

            $msg->line('---')
                ->line(__('conversations_daily_digest.thread_header', [
                    'subject' => $conv->subject,
                    'count' => $unreadCount,
                ]))
                ->line(__('conversations_daily_digest.thread_preview', [
                    'author' => $author,
                    'time' => TimezoneHelper::format($lastMessage->created_at, $notifiable, 'd/m H:i'),
                    'preview' => $preview,
                ]));
        }

        $threadUrl = $notifiable->hasRole('super_admin')
            ? url('/admin/conversations')
            : url('/app/messages');

        return $msg
            ->action(__('conversations_daily_digest.cta'), $threadUrl)
            ->line(__('conversations_daily_digest.outro'));
    }
}
