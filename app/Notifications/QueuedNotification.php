<?php

namespace App\Notifications;

use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Date;

/**
 * Clase base para todas las notificaciones por correo de la plataforma.
 *
 * Encola el envío (ShouldQueue) en la cola `mail` de la conexión por defecto
 * (`database` en producción; `sync` en local/test las ejecuta inline, por lo
 * que Mailpit sigue capturando sin worker). Sacar SES del hilo síncrono evita
 * que un fallo/lentitud de entrega tumbe el request del usuario o el webhook
 * de Stripe, y permite reintentos con backoff.
 *
 * El rate-limit de SES se aplica vía middleware de cola (ver Fase 3 /
 * `middleware()` + `RateLimiter::for('ses')` en AppServiceProvider).
 *
 * Nota de serialización: los hijos reciben modelos Eloquent + escalares
 * (SerializesModels re-hidrata por ID). `ConversationsDailyDigest` pasa una
 * Support\Collection con modelos anidados: serializa vía PHP nativo (payload
 * más pesado pero correcto para un digest de bajo volumen).
 */
abstract class QueuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Reintentos ante fallo de entrega (SES caído/timeout).
     */
    public int $tries = 5;

    /**
     * Espera creciente entre reintentos, en segundos (1min → 5min → 15min).
     */
    public array $backoff = [60, 300, 900];

    /**
     * Ventana máxima de reintentos: tras 6h se deja de intentar y el job
     * pasa a `failed_jobs` (visible en el panel QueueMonitor).
     */
    public function retryUntil(): DateTimeInterface
    {
        return Date::now()->addHours(6);
    }

    /**
     * Despacha el canal `mail` en la cola `mail` (permite priorizarla en el
     * worker: `queue:work --queue=mail,default`). Se usa `viaQueues()` en vez
     * de la propiedad `$queue` porque el trait Queueable ya la declara.
     */
    public function viaQueues(): array
    {
        return ['mail' => config('mail_logging.queue_name', 'mail')];
    }

    /**
     * Middleware del job de envío: throttle por el rate-limiter `ses`
     * (registrado en AppServiceProvider) para no exceder el límite de SES.
     * Al superarlo, el worker re-libera el job con delay en vez de fallar.
     */
    public function middleware(): array
    {
        return [new RateLimited('ses')];
    }
}
