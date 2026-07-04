<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Email;

/**
 * Para notificaciones de recordatorio/marketing (ciclo de vida del sello,
 * digests, recordatorios de mensajes). Aporta:
 *
 *  - `via()`: suprime el envío si el destinatario se dio de baja global
 *    (`User::hasOptedOutOfReminders()`) — vale para cualquier disparador
 *    (cron, bulk, etc.), no solo el que la lanzó.
 *  - `unsubscribeHeaders()`: closure para `->withSymfonyMessage(...)` que añade
 *    `List-Unsubscribe` + `List-Unsubscribe-Post` (RFC 8058, one-click) que
 *    Gmail/Yahoo exigen a remitentes de volumen. El enlace es una ruta firmada.
 *
 * Los correos transaccionales (pago, resultado de evaluación) NO usan este
 * trait: no son suprimibles.
 */
trait ReminderNotification
{
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof User && $notifiable->hasOptedOutOfReminders()) {
            return [];
        }

        return ['mail'];
    }

    protected function unsubscribeHeaders(object $notifiable): Closure
    {
        return function (Email $message) use ($notifiable) {
            if (! $notifiable instanceof User) {
                return;
            }

            $url = URL::signedRoute('email.unsubscribe', ['user' => $notifiable->getKey()]);

            $headers = $message->getHeaders();
            $headers->addTextHeader('List-Unsubscribe', '<'.$url.'>');
            $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        };
    }
}
