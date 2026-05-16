<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Notifications\ConversationsDailyDigest;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Sprint 3.7 #44 — digest diario de mensajes sin leer.
 *
 * REEMPLAZA `messages:notify-pending` (cron de 5 min). Estrategia:
 *
 *   1. Corremos cada hora.
 *   2. Identificamos usuarios cuya zona horaria está actualmente en las 9:00.
 *   3. Para cada uno, buscamos hilos donde el user es participant y tiene
 *      mensajes sin leer (created_at > last_read_at, autor != user).
 *   4. Si tiene al menos 1 conversation con unread > 0, mandamos digest.
 *   5. Si no tiene nada → skip (no enviamos email vacío).
 *
 * Excluye mensajes con `payment_link_for_task_id` (esos se notifican al
 * instante por su propia lógica en MessageThread::createTaskFromMessage).
 *
 * Cooldown global: NO enviamos digest si el último digest para el user
 * fue hace menos de 20h (defensa por si el cron corre dos veces).
 */
class SendConversationsDailyDigest extends Command
{
    protected $signature = 'messages:daily-digest';

    protected $description = 'Envía digest diario de mensajes sin leer a usuarios cuya hora local sea 9:00.';

    /** Hora local del destinatario en la que se envía el digest. */
    protected const SEND_HOUR = 9;

    /** Cooldown en horas para evitar dobles envíos. */
    protected const COOLDOWN_HOURS = 20;

    public function handle(): int
    {
        $users = User::query()
            ->whereNotNull('timezone')
            ->get()
            ->filter(function (User $user) {
                $localHour = Carbon::now($user->timezone)->hour;
                return $localHour === self::SEND_HOUR;
            });

        if ($users->isEmpty()) {
            $this->info('No hay usuarios en ventana 9:00 local.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $items = $this->collectUnreadItemsFor($user);

            if ($items->isEmpty()) {
                $skipped++;
                continue;
            }

            // Cooldown: skip si ya recibió digest hace menos de COOLDOWN_HOURS
            $cooldownKey = "daily-digest-sent:{$user->id}";
            if (cache()->has($cooldownKey)) {
                $skipped++;
                continue;
            }

            try {
                $user->notify(new ConversationsDailyDigest($items));
                cache()->put($cooldownKey, true, now()->addHours(self::COOLDOWN_HOURS));
                $sent++;

                activity()
                    ->causedBy($user)
                    ->withProperties([
                        'unread_conversations' => $items->count(),
                        'unread_messages_total' => $items->sum('unread_count'),
                    ])
                    ->log('Daily digest sent');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Daily digest failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Digest enviados: {$sent}. Saltados: {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Para un user, devuelve sus conversaciones con mensajes sin leer (>0).
     *
     * @return \Illuminate\Support\Collection<int, array{conversation: Conversation, unread_count: int, last_message: Message}>
     */
    protected function collectUnreadItemsFor(User $user): \Illuminate\Support\Collection
    {
        // Participants del user no muteados, hilos abiertos
        $participants = ConversationParticipant::query()
            ->where('user_id', $user->id)
            ->where('email_muted', false)
            ->with(['conversation' => fn ($q) => $q->where('status', Conversation::STATUS_OPEN)])
            ->get();

        $items = collect();

        foreach ($participants as $participant) {
            $conv = $participant->conversation;
            if (! $conv) continue;

            $cutoff = $participant->last_read_at;
            $unreadQuery = Message::where('conversation_id', $conv->id)
                ->where('user_id', '!=', $user->id)
                // Excluir mensajes con link de pago (notificados inmediato)
                ->whereNull('payment_link_for_task_id');

            if ($cutoff) {
                $unreadQuery->where('created_at', '>', $cutoff);
            }

            $unreadCount = $unreadQuery->count();
            if ($unreadCount === 0) continue;

            $lastMessage = (clone $unreadQuery)->with('user')->latest()->first();

            $items->push([
                'conversation' => $conv,
                'unread_count' => $unreadCount,
                'last_message' => $lastMessage,
            ]);
        }

        return $items;
    }
}
