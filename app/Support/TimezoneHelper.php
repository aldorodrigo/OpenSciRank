<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;
use DateTimeZone;

/**
 * Sprint 3.7 #41 — utilidades para renderizar fechas en TZ del usuario.
 *
 * Uso típico desde notification toMail() y desde views:
 *   TimezoneHelper::format($task->scheduled_for, $notifiable, 'd/m/Y H:i')
 *
 * Fallback: si el user no tiene timezone seteado, usa config('app.timezone').
 */
class TimezoneHelper
{
    /**
     * Lista corta de zonas horarias agrupadas para el select del registro.
     *
     * @return array<string, array<string, string>>
     */
    public static function commonZones(): array
    {
        return [
            'Latinoamérica' => [
                'America/Argentina/Buenos_Aires' => 'Argentina (UTC-3)',
                'America/Sao_Paulo' => 'Brasil — São Paulo (UTC-3)',
                'America/Bogota' => 'Colombia (UTC-5)',
                'America/Santiago' => 'Chile (UTC-3/UTC-4)',
                'America/Mexico_City' => 'México (UTC-6)',
                'America/Lima' => 'Perú (UTC-5)',
                'America/Caracas' => 'Venezuela (UTC-4)',
                'America/Montevideo' => 'Uruguay (UTC-3)',
                'America/Asuncion' => 'Paraguay (UTC-3/UTC-4)',
                'America/La_Paz' => 'Bolivia (UTC-4)',
                'America/Guayaquil' => 'Ecuador (UTC-5)',
                'America/Costa_Rica' => 'Costa Rica (UTC-6)',
                'America/Guatemala' => 'Guatemala (UTC-6)',
                'America/Panama' => 'Panamá (UTC-5)',
                'America/Havana' => 'Cuba (UTC-5)',
            ],
            'América del Norte' => [
                'America/New_York' => 'EEUU — Este (UTC-5)',
                'America/Chicago' => 'EEUU — Central (UTC-6)',
                'America/Denver' => 'EEUU — Mountain (UTC-7)',
                'America/Los_Angeles' => 'EEUU — Pacífico (UTC-8)',
                'America/Toronto' => 'Canadá — Toronto (UTC-5)',
                'America/Vancouver' => 'Canadá — Vancouver (UTC-8)',
            ],
            'Europa' => [
                'Europe/Madrid' => 'España (UTC+1)',
                'Europe/Lisbon' => 'Portugal (UTC+0)',
                'Europe/London' => 'Reino Unido (UTC+0)',
                'Europe/Paris' => 'Francia (UTC+1)',
                'Europe/Berlin' => 'Alemania (UTC+1)',
                'Europe/Rome' => 'Italia (UTC+1)',
            ],
            'Otros' => [
                'UTC' => 'UTC',
                'Asia/Tokyo' => 'Japón (UTC+9)',
                'Asia/Shanghai' => 'China (UTC+8)',
                'Australia/Sydney' => 'Australia — Sydney (UTC+10)',
            ],
        ];
    }

    /**
     * Lista plana de todas las zonas válidas, para validación.
     *
     * @return array<int, string>
     */
    public static function allValidZones(): array
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Resuelve la TZ del usuario con fallback a la app.
     */
    public static function userTimezone(?User $user): string
    {
        return $user?->timezone ?: config('app.timezone', 'UTC');
    }

    /**
     * Formatea una fecha en la TZ del usuario.
     */
    public static function format(?CarbonInterface $datetime, ?User $user, string $format = 'd/m/Y H:i'): string
    {
        if (! $datetime) return '—';
        $tz = self::userTimezone($user);
        return $datetime->copy()->setTimezone($tz)->format($format);
    }

    /**
     * Devuelve la fecha + indicador de TZ entre paréntesis.
     * Ej: "15/05/2026 14:00 (UTC-3 — Argentina)"
     */
    public static function formatWithIndicator(?CarbonInterface $datetime, ?User $user, string $format = 'd/m/Y H:i'): string
    {
        if (! $datetime) return '—';
        $tz = self::userTimezone($user);
        $formatted = $datetime->copy()->setTimezone($tz)->format($format);
        $offsetName = $datetime->copy()->setTimezone($tz)->format('T');
        return "{$formatted} ({$offsetName})";
    }
}
