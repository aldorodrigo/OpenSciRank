<?php

namespace App\Filament\Widgets;

use App\Models\AdminTask;
use App\Models\Conversation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Roadmap #35 — resumen del escritorio del evaluador. Espejo scopeado de
 * AdminTasksOverview: cuenta SOLO las tasks asignadas al evaluador logueado
 * (assigned_to), nunca el global. Solo lo ve el evaluator.
 */
class EvaluatorTasksOverview extends BaseWidget
{
    protected static ?int $sort = -3;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin'));
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $pending = AdminTask::query()
            ->where('assigned_to', $userId)
            ->where('status', AdminTask::STATUS_PENDING)
            ->count();

        $inProgress = AdminTask::query()
            ->where('assigned_to', $userId)
            ->where('status', AdminTask::STATUS_IN_PROGRESS)
            ->count();

        $overdue = AdminTask::query()
            ->where('assigned_to', $userId)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $completedLastWeek = AdminTask::query()
            ->where('assigned_to', $userId)
            ->where('status', AdminTask::STATUS_COMPLETED)
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();

        // Roadmap #35 (Fase 4) — mensajes sin leer en los hilos del evaluador.
        $unreadMessages = Conversation::forUser(auth()->user())
            ->where('status', Conversation::STATUS_OPEN)
            ->get()
            ->sum(fn (Conversation $c) => $c->unreadCountFor(auth()->user()));

        // Roadmap #35 — cada stat enlaza a su lista filtrada en /admin/admin-tasks
        // (ya scopeada al evaluador por getEloquentQuery).
        $tasksUrl = route('filament.admin.resources.admin-tasks.index');

        return [
            Stat::make(__('evaluator_desk.stats.pending'), $pending)
                ->description(__('evaluator_desk.stats.pending_desc'))
                ->descriptionIcon('heroicon-o-inbox')
                ->color($pending > 0 ? 'warning' : 'gray')
                ->url($tasksUrl.'?tableFilters[status][values][0]='.AdminTask::STATUS_PENDING),

            Stat::make(__('evaluator_desk.stats.in_progress'), $inProgress)
                ->description(__('evaluator_desk.stats.in_progress_desc'))
                ->descriptionIcon('heroicon-o-play')
                ->color($inProgress > 0 ? 'info' : 'gray')
                ->url($tasksUrl.'?tableFilters[status][values][0]='.AdminTask::STATUS_IN_PROGRESS),

            Stat::make(__('evaluator_desk.stats.overdue'), $overdue)
                ->description(__('evaluator_desk.stats.overdue_desc'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url($tasksUrl.'?tableFilters[overdue][isActive]=true'),

            Stat::make(__('evaluator_desk.stats.completed_week'), $completedLastWeek)
                ->description(__('evaluator_desk.stats.completed_week_desc'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->url($tasksUrl.'?activeTab=completed'),

            Stat::make(__('evaluator_desk.stats.unread'), $unreadMessages)
                ->description(__('evaluator_desk.stats.unread_desc'))
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color($unreadMessages > 0 ? 'warning' : 'gray'),
        ];
    }
}
