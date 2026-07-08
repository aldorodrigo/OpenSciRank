<?php

namespace App\Listeners;

use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * #59 — heartbeat del worker de colas. El evento `Looping` se dispara en cada
 * poll del worker (incluso ocioso), así que un `last_seen` reciente = worker vivo.
 * El panel QueueMonitor lee esta clave para mostrar "worker activo/caído" — el
 * síntoma que faltaba en el incidente de #58 (worker muerto e invisible).
 *
 * Se escribe con throttle de 30s para no martillar el store de cache (database).
 */
class RecordWorkerHeartbeat
{
    public const CACHE_KEY = 'queue:worker:last_seen';

    private const THROTTLE_SECONDS = 30;

    public function handle(Looping $event): void
    {
        $last = Cache::get(self::CACHE_KEY);

        if (is_string($last)) {
            $lastSeen = Carbon::parse($last);
            if ($lastSeen->diffInSeconds(now()) < self::THROTTLE_SECONDS) {
                return;
            }
        }

        // TTL amplio: si el worker muere, la clave persiste con su timestamp viejo
        // y el panel la detecta como "caído" por antigüedad (no por expiración).
        Cache::put(self::CACHE_KEY, now()->toIso8601String(), now()->addDay());
    }
}
