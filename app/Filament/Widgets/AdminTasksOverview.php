<?php

namespace App\Filament\Widgets;

use App\Models\AdminTask;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Sprint 3.6 #32: widget de overview de tasks pendientes para el admin.
 * Muestra el conteo de open, vencidas, sin asignar y completadas en los
 * últimos 7 días — los 4 números que el admin quiere ver al entrar.
 */
class AdminTasksOverview extends BaseWidget
{
    /** Ordenado antes que SealStatusOverview (sort -3 < -2). */
    protected static ?int $sort = -3;

    /**
     * Roadmap #35 — widget global de admin (conteos de TODAS las tasks).
     * El evaluator tiene su propio EvaluatorTasksOverview scopeado a lo suyo;
     * este widget solo lo ve el super_admin.
     */
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected function getStats(): array
    {
        $open = AdminTask::open()->count();
        $overdue = AdminTask::overdue()->count();
        $unassigned = AdminTask::open()->unassigned()->count();
        $completedLastWeek = AdminTask::where('status', AdminTask::STATUS_COMPLETED)
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make(__('admin_tasks.widget.open'), $open)
                ->description(__('admin_tasks.widget.open_desc'))
                ->descriptionIcon('heroicon-o-queue-list')
                ->color($open > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.admin-tasks.index')),

            Stat::make(__('admin_tasks.widget.overdue'), $overdue)
                ->description(__('admin_tasks.widget.overdue_desc'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.admin-tasks.index').'?tableFilters[overdue][isActive]=true'),

            Stat::make(__('admin_tasks.widget.unassigned'), $unassigned)
                ->description(__('admin_tasks.widget.unassigned_desc'))
                ->descriptionIcon('heroicon-o-user-minus')
                ->color($unassigned > 0 ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.admin-tasks.index').'?tableFilters[unassigned][isActive]=true'),

            Stat::make(__('admin_tasks.widget.completed_week'), $completedLastWeek)
                ->description(__('admin_tasks.widget.completed_week_desc'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
