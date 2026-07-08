<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * #59 — modelo de solo lectura sobre la tabla `failed_jobs` de Laravel, para
 * poder listarla en una tabla Filament con acciones por fila (reintentar/borrar/
 * inspeccionar). No se crea/edita vía Eloquent: la escribe el runtime de colas y
 * la gestión es vía `queue:retry {id}` / `queue:forget {uuid}` / `queue:flush`.
 */
class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'failed_at' => 'datetime',
    ];

    /**
     * Nombre legible del job, extraído del payload serializado.
     */
    public function jobName(): string
    {
        $payload = json_decode($this->payload ?? '', true);

        return $payload['displayName'] ?? ($payload['job'] ?? '—');
    }

    /**
     * Primer renglón de la excepción (para la columna de la tabla).
     */
    public function exceptionFirstLine(): string
    {
        if (empty($this->exception)) {
            return '—';
        }

        return trim(explode("\n", (string) $this->exception)[0] ?? '—');
    }
}
