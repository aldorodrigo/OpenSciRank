<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso a rutas sólo para usuarios con el rol 'super_admin'.
 * Usado en las rutas /admin/conversations (fuera de Filament).
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Acceso restringido a administradores.');
        }

        return $next($request);
    }
}
