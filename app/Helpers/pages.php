<?php

use App\Support\PageVisibility;

/*
 * Helpers de visibilidad de páginas públicas (Roadmap #62).
 *
 * Pensados para usarse desde Blade sin importar la clase:
 *   @if (page_in_menu('pricing')) … @endif
 */

if (! function_exists('page_enabled')) {
    /**
     * ¿La página pública está accesible? (false ⇒ la ruta devuelve 404).
     */
    function page_enabled(string $key): bool
    {
        return PageVisibility::enabled($key);
    }
}

if (! function_exists('page_in_menu')) {
    /**
     * ¿Se muestra el link de la página en header/mobile/footer?
     */
    function page_in_menu(string $key): bool
    {
        return PageVisibility::inMenu($key);
    }
}
