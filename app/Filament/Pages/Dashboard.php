<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Contracts\QueueMonitorWidget;
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

    /**
     * #59 — los widgets del panel de colas se auto-descubren (para registrarse
     * como componentes Livewire) pero pertenecen a QueueMonitor, no al dashboard.
     */
    public function getWidgets(): array
    {
        return array_values(array_filter(
            parent::getWidgets(),
            fn (string $widget): bool => ! is_subclass_of($widget, QueueMonitorWidget::class),
        ));
    }
}
