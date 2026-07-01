<?php

namespace App\Http\Middleware;

use App\Filament\Pages\EvaluatorDesk;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Roadmap #35 — el evaluador aterriza en su escritorio, no en el Dashboard
 * genérico de admin. Filament redirige al Dashboard ('/') tras el login; este
 * middleware intercepta esa landing (y cualquier visita directa al Dashboard)
 * y manda al evaluator a /admin/evaluator-desk. super_admin no se ve afectado.
 */
class RedirectEvaluatorToDesk
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->hasRole('evaluator')
            && ! $user->hasRole('super_admin')
            && $request->routeIs('filament.admin.pages.dashboard')
        ) {
            return redirect(EvaluatorDesk::getUrl());
        }

        return $next($request);
    }
}
