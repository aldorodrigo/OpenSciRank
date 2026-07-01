<?php

namespace App\Filament\Widgets;

use App\Models\Journal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SealStatusOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    /**
     * Roadmap #35 — sellos globales son asunto del super_admin. El evaluator
     * no gestiona el ciclo de vida del sello, así que no ve este widget.
     */
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

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
            Stat::make(__('admin.seal_widget.active'), $activeSeals)
                ->description(__('admin.seal_widget.active_desc'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make(__('admin.seal_widget.expiring'), $expiringSoon)
                ->description(__('admin.seal_widget.expiring_desc'))
                ->descriptionIcon('heroicon-o-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'gray'),

            Stat::make(__('admin.seal_widget.expired'), $expired)
                ->description(__('admin.seal_widget.expired_desc'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($expired > 0 ? 'danger' : 'gray'),

            Stat::make(__('admin.seal_widget.not_notified'), $pendingNotification)
                ->description(__('admin.seal_widget.not_notified_desc'))
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color($pendingNotification > 0 ? 'danger' : 'gray'),
        ];
    }
}
