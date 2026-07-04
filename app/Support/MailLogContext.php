<?php

namespace App\Support;

/**
 * Puente entre el evento NotificationSending (que conoce el notifiable y la
 * clase de la notification) y MessageSending (que conoce el mensaje real pero
 * no el notifiable). Los eventos de mail son síncronos y secuenciales dentro
 * del proceso, así que recordar el último contexto y consumirlo en el
 * siguiente MessageSending es 1:1 y fiable (un notify = un NotificationSending
 * de canal `mail` seguido de un MessageSending).
 *
 * Best-effort: si algo no cuadra, el log queda con notifiable null.
 */
class MailLogContext
{
    protected static ?array $pending = null;

    public static function remember(?object $notifiable, ?string $notificationClass): void
    {
        static::$pending = [
            'notifiable' => $notifiable,
            'class' => $notificationClass,
        ];
    }

    public static function pull(): ?array
    {
        $pending = static::$pending;
        static::$pending = null;

        return $pending;
    }
}
