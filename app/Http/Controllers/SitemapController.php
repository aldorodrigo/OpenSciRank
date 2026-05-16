<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Sitemap.xml dinámico con cache en archivo estático.
 *
 * Estrategia:
 *  1. Apache/Nginx sirve `public/sitemap.xml` directamente si existe (más rápido,
 *     sin pasar por PHP). El cron `sitemap:generate` lo regenera diario a las 03:30.
 *  2. Si por algún motivo el archivo no existe (cron no corrió todavía, nueva
 *     instalación), esta ruta es el fallback: ejecuta el comando on-demand,
 *     genera el archivo y lo sirve.
 *
 * Refactor 2026-05-15: migrado de Blade view a `spatie/laravel-sitemap`. Permite
 * URLs con priority, lastmod y changefreq estándar — mejor SEO.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $path = public_path('sitemap.xml');

        // Fallback: si el cron no generó el archivo (primer deploy, server down
        // durante el cron, etc.), lo regeneramos ahora mismo. Idempotente.
        if (! file_exists($path)) {
            Artisan::call('sitemap:generate');
        }

        $content = file_exists($path) ? file_get_contents($path) : '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>';

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600'); // 1h browser cache
    }
}
