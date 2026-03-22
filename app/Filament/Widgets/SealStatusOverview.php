<?php

namespace App\Filament\Widgets;

use App\Models\Journal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SealStatusOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $activeSeals = Journal::where('seal_status', 'active')
            ->where('status', 'certified')
            ->count();

        $expiringSoon = Journal::where('status', 'certified')
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '>', now())
            ->where('seal_expires_at', '<=', now()->addDays(30))
            ->count();

        $expired = Journal::whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '<', now())
            ->whereIn('seal_status', ['active', 'expiring_soon', 'expired'])
            ->count();

        $pendingNotification = Journal::whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '<=', now()->addDays(30))
            ->whereNull('seal_notified_at')
            ->count();

        return [
            Stat::make('Sellos Activos', $activeSeals)
                ->description('Certificaciones vigentes')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Próximos a Vencer', $expiringSoon)
                ->description('Vencen en menos de 30 días')
                ->descriptionIcon('heroicon-o-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'gray'),

            Stat::make('Sellos Vencidos', $expired)
                ->description('Requieren renovación')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($expired > 0 ? 'danger' : 'gray'),

            Stat::make('Sin Notificar', $pendingNotification)
                ->description('No se les ha enviado recordatorio')
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color($pendingNotification > 0 ? 'danger' : 'gray'),
        ];
    }
}
