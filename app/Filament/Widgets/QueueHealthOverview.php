<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Contracts\QueueMonitorWidget;
use App\Listeners\RecordWorkerHeartbeat;
use App\Models\EmailLog;
use App\Models\Journal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * #59 — cabecera de salud del panel de colas: estado del worker + conteos.
 * La stat "Worker" es la respuesta al incidente de #58: si el heartbeat es viejo
 * (o nunca existió), el worker está caído y nada de la cola avanza.
 */
class QueueHealthOverview extends BaseWidget implements QueueMonitorWidget
{
    protected ?string $pollingInterval = '15s';

    /** Umbral para considerar vivo al worker (el heartbeat se refresca cada ~30s). */
    private const WORKER_ALIVE_WITHIN_SECONDS = 120;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected function getStats(): array
    {
        $lastSeen = $this->workerLastSeen();
        $alive = $lastSeen !== null && $lastSeen->gt(now()->subSeconds(self::WORKER_ALIVE_WITHIN_SECONDS));

        $workerStat = Stat::make(
            __('admin.queue_monitor.worker'),
            $alive ? __('admin.queue_monitor.worker_up') : __('admin.queue_monitor.worker_down'),
        )
            ->descriptionIcon($alive ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
            ->color($alive ? 'success' : 'danger')
            ->description(
                $lastSeen
                    ? __('admin.queue_monitor.worker_last_seen', ['ago' => $lastSeen->diffForHumans()])
                    : __('admin.queue_monitor.worker_never')
            );

        $failed = DB::table('failed_jobs')->count();

        return [
            $workerStat,
            Stat::make(__('admin.queue_monitor.harvest_queue'), DB::table('jobs')->where('queue', 'harvest')->count())
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'),
            Stat::make(__('admin.queue_monitor.mail_queue'), DB::table('jobs')->where('queue', 'mail')->count())
                ->descriptionIcon('heroicon-o-envelope')
                ->color('gray'),
            Stat::make(__('admin.queue_monitor.failed_jobs'), $failed)
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($failed > 0 ? 'danger' : 'success'),
            Stat::make(__('admin.queue_monitor.harvest_failed'), Journal::query()->where('oai_harvest_status', 'failed')->count())
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color(fn () => Journal::query()->where('oai_harvest_status', 'failed')->exists() ? 'danger' : 'success'),
            Stat::make(__('admin.queue_monitor.sent_today'), EmailLog::query()
                ->where('status', EmailLog::STATUS_SENT)
                ->whereDate('created_at', today())
                ->count())
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }

    private function workerLastSeen(): ?Carbon
    {
        $value = Cache::get(RecordWorkerHeartbeat::CACHE_KEY);

        return is_string($value) ? Carbon::parse($value) : null;
    }
}
