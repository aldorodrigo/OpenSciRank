<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * #59 — última corrida registrada de una tarea programada (cron).
 * Una fila por comando (clave `command`); los listeners hacen upsert.
 */
class ScheduledTaskRun extends Model
{
    public const STATUS_OK = 'ok';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'command',
        'last_ran_at',
        'runtime_ms',
        'status',
        'error',
    ];

    protected $casts = [
        'last_ran_at' => 'datetime',
        'runtime_ms' => 'integer',
    ];

    /**
     * Normaliza el comando crudo del scheduler a la invocación de artisan.
     *
     * El Event del scheduler expone algo como
     * `'/usr/bin/php' 'artisan' oai:harvest --all --queue > '/dev/null' 2>&1`;
     * de ahí extraemos `oai:harvest --all --queue` como clave estable.
     */
    public static function normalizeCommand(string $raw): string
    {
        $raw = trim($raw);

        // Quitar la redirección de salida que agrega el scheduler.
        if (($pos = strpos($raw, ' > ')) !== false) {
            $raw = substr($raw, 0, $pos);
        }

        // Quedarnos con lo que va después de 'artisan'.
        if (($pos = stripos($raw, 'artisan')) !== false) {
            $raw = substr($raw, $pos + strlen('artisan'));
        }

        $raw = trim(str_replace(["'", '"'], '', $raw));

        return $raw !== '' ? $raw : 'unknown';
    }

    /**
     * Token base del comando (antes del primer espacio / argumentos).
     * `oai:harvest --all --queue` → `oai:harvest`.
     */
    public function baseCommand(): string
    {
        return trim(explode(' ', (string) $this->command)[0] ?? '');
    }
}
