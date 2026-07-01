<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Roadmap #35 — Dashboard genérico de admin. Subclase local para poder
 * ocultarlo del nav del evaluator (que usa su propio EvaluatorDesk como home).
 * Mantiene la ruta '/' y el nombre de ruta filament.admin.pages.dashboard.
 */
class Dashboard extends BaseDashboard
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
