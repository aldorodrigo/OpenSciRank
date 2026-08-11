<?php

/*
|--------------------------------------------------------------------------
| Páginas públicas administrables (Roadmap #62)
|--------------------------------------------------------------------------
|
| Registro declarativo de las páginas públicas del sitio. El admin decide en
| `Sistema → Menús` (/admin/menu-settings) si cada una está accesible y si
| aparece en los menús; el estado se persiste en `settings.site_pages` (JSON)
| y se lee vía `App\Support\PageVisibility`.
|
| Este archivo define únicamente los DEFAULTS y los metadatos fijos. Agregar
| una página nueva acá basta para que aparezca en el panel — no hace falta
| tocar la página de Filament.
|
| Claves de cada entrada:
|   path           Ruta (sin prefijo de idioma). Solo informativo/UI y sitemap.
|   route          Nombre de ruta base, para referencia.
|   locked         true = no se puede deshabilitar el acceso (la home).
|   has_menu_link  false = no tiene link gestionable en header/footer.
|   enabled        Default de "accesible" si el admin nunca la tocó.
|   in_menu        Default de "visible en el menú".
|   sitemap        true = se publica en sitemap.xml cuando está habilitada.
|
| El label visible se traduce con `admin.menus.pages.{key}` en es/en/pt.
|
*/

return [

    'home' => [
        'path' => '/',
        'route' => 'home',
        'locked' => true,
        'has_menu_link' => false,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'search' => [
        'path' => '/search',
        'route' => 'search',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'ranking' => [
        'path' => '/ranking',
        'route' => 'ranking',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'pricing' => [
        'path' => '/pricing',
        'route' => 'pricing',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'seal_renewal' => [
        'path' => '/seal-renewal',
        'route' => 'seal.renewal.info',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'methodology' => [
        'path' => '/methodology',
        'route' => 'methodology',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'blog' => [
        'path' => '/blog',
        'route' => 'blog.index',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'about' => [
        'path' => '/about',
        'route' => 'about',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'contact' => [
        'path' => '/contact',
        'route' => 'contact',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'terms' => [
        'path' => '/terms',
        'route' => 'terms',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

    'privacy' => [
        'path' => '/privacy',
        'route' => 'privacy',
        'locked' => false,
        'has_menu_link' => true,
        'enabled' => true,
        'in_menu' => true,
        'sitemap' => true,
    ],

];
