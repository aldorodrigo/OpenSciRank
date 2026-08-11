<?php

namespace App\Http\Middleware;

use App\Support\PageVisibility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso a una página pública deshabilitada desde
 * `Sistema → Menús` (Roadmap #62).
 *
 * Se aplica por ruta con el alias `page`:
 *   Route::get('/pricing', …)->middleware('page:pricing');
 *
 * Devuelve 404 (no 403) a propósito: una página apagada no debe delatar que
 * existe. Ocultar el link del menú no alcanzaba — la URL directa seguía viva.
 */
class EnsurePageEnabled
{
    public function handle(Request $request, Closure $next, string $page): Response
    {
        abort_unless(PageVisibility::enabled($page), 404);

        return $next($request);
    }
}
