<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Visibilidad de las páginas públicas (Roadmap #62).
 *
 * Combina el registro declarativo de `config/site_pages.php` con el estado que
 * el admin guarda desde `Sistema → Menús`. El estado vive en un único setting
 * JSON (`site_pages`, grupo `pages`) — una sola entrada de cache para todas las
 * páginas, en vez de una query por link del header.
 *
 * Dos ejes independientes por página:
 *   - `enabled`  → si es false la ruta devuelve 404 (bloqueo real, middleware
 *                  `EnsurePageEnabled`). Las páginas `locked` nunca se apagan.
 *   - `in_menu`  → si es false el link desaparece de header/mobile/footer, pero
 *                  la página sigue accesible por URL directa.
 *
 * Una página deshabilitada nunca aparece en el menú, sin importar `in_menu`.
 */
class PageVisibility
{
    public const SETTING_KEY = 'site_pages';

    public const SETTING_GROUP = 'pages';

    /**
     * Estado crudo de todas las páginas registradas: defaults del config con
     * los overrides del admin aplicados encima. Es lo que pinta el formulario.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $stored = Setting::get(self::SETTING_KEY, []);
        $stored = is_array($stored) ? $stored : [];

        $pages = [];

        foreach (config('site_pages', []) as $key => $page) {
            $override = is_array($stored[$key] ?? null) ? $stored[$key] : [];
            $locked = (bool) ($page['locked'] ?? false);

            $pages[$key] = [
                'key' => $key,
                'path' => $page['path'] ?? '/',
                'route' => $page['route'] ?? null,
                'locked' => $locked,
                'has_menu_link' => (bool) ($page['has_menu_link'] ?? true),
                'sitemap' => (bool) ($page['sitemap'] ?? true),
                // Una página locked está siempre accesible: ignoramos el override.
                'enabled' => $locked
                    ? true
                    : (bool) ($override['enabled'] ?? $page['enabled'] ?? true),
                'in_menu' => (bool) ($override['in_menu'] ?? $page['in_menu'] ?? true),
            ];
        }

        return $pages;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * ¿La página es accesible? Una clave no registrada se considera accesible
     * para no romper rutas que todavía no están en el registro.
     */
    public static function enabled(string $key): bool
    {
        return self::get($key)['enabled'] ?? true;
    }

    /**
     * ¿Corresponde pintar el link en los menús? Requiere que además esté
     * accesible — un link que lleva a un 404 no se muestra nunca.
     */
    public static function inMenu(string $key): bool
    {
        $page = self::get($key);

        if (! $page) {
            return false;
        }

        return $page['enabled'] && $page['in_menu'];
    }

    /**
     * Persiste el estado. Solo se guardan claves registradas y valores
     * booleanos; las páginas `locked` se fuerzan a accesibles.
     *
     * @param  array<string, array<string, mixed>>  $state
     */
    public static function save(array $state): void
    {
        $clean = [];

        foreach (config('site_pages', []) as $key => $page) {
            $input = is_array($state[$key] ?? null) ? $state[$key] : [];
            $locked = (bool) ($page['locked'] ?? false);

            $clean[$key] = [
                'enabled' => $locked
                    ? true
                    : (bool) ($input['enabled'] ?? $page['enabled'] ?? true),
                'in_menu' => (bool) ($input['in_menu'] ?? $page['in_menu'] ?? true),
            ];
        }

        Setting::set(self::SETTING_KEY, $clean, 'json', self::SETTING_GROUP);
    }
}
