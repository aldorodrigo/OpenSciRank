<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting clave/valor con cast automático según `type`.
 *
 * Acceso recomendado: `Setting::get('sla_evaluation_business_days', 15)`.
 * El valor se cachea por 1h para evitar query por cada lookup en webhooks
 * y crons. `Setting::set($key, $value)` invalida el cache.
 *
 * Sprint 3.6 #32 — al implementarse SettingsResource completo en
 * Sprint 4 #9 esta tabla queda como base, no se reemplaza.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    protected const CACHE_PREFIX = 'setting:';

    protected const CACHE_TTL = 3600; // 1h

    /**
     * Obtener un setting tipado. Si no existe devuelve $default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'int' => (int) $setting->value,
                'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * Persistir un setting. Si no existe se crea con el tipo inferido.
     */
    public static function set(string $key, mixed $value, ?string $type = null, ?string $group = null): void
    {
        $type ??= match (true) {
            is_int($value) => 'int',
            is_bool($value) => 'bool',
            is_array($value) => 'json',
            default => 'string',
        };

        $stored = match ($type) {
            'json' => json_encode($value),
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group ?? 'general',
            ]
        );

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Trae todos los settings de un grupo como array key=>valor tipado.
     * Util para pintar formularios.
     */
    public static function group(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => self::get($s->key)])
            ->toArray();
    }
}
