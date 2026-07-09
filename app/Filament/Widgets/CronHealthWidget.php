<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Contracts\QueueMonitorWidget;
use App\Models\ScheduledTaskRun;
use Filament\Widgets\Widget;

/**
 * #59 — salud de los cron. Muestra los comandos programados en routes/console.php
 * con su última corrida, runtime, estado y un flag "atrasado" cuando superan la
 * antigüedad esperada según su cadencia. Los que nunca corrieron se marcan como
 * tal. Poblado por los listeners de ScheduledTaskFinished/Failed.
 */
class CronHealthWidget extends Widget implements QueueMonitorWidget
{
    protected string $view = 'filament.widgets.cron-health';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * Comando base => antigüedad máxima esperada (horas) antes de marcar "atrasado".
     * Cadencias de routes/console.php: daily→26h, hourly→2h, weekly→192h, monthly→768h.
     */
    public const COMMANDS = [
        'seal:check-expiration' => 26,
        'books:check-featured' => 26,
        'tasks:check-overdue' => 26,
        'consulting:send-reminders' => 26,
        'consulting:expire-proposals' => 26,
        'sitemap:generate' => 26,
        'email-logs:prune' => 26,
        'messages:daily-digest' => 2,
        'oai:harvest' => 192,
        'metrics:refresh-journals' => 768,
    ];

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        $runs = ScheduledTaskRun::all()->keyBy(fn (ScheduledTaskRun $r): string => $r->baseCommand());

        $rows = [];
        foreach (self::COMMANDS as $command => $maxHours) {
            /** @var ScheduledTaskRun|null $run */
            $run = $runs->get($command);
            $lastRan = $run?->last_ran_at;
            $never = $lastRan === null;
            $overdue = $never || $lastRan->lt(now()->subHours($maxHours));

            $rows[] = [
                'command' => $command,
                'description' => __('admin.queue_monitor.cron_desc.'.$command),
                'last_ran_at' => $lastRan?->format('d/m/Y H:i'),
                'ago' => $lastRan?->diffForHumans(),
                'runtime' => $run?->runtime_ms !== null ? $this->formatRuntime($run->runtime_ms) : null,
                'status' => $run?->status,
                'error' => $run?->error,
                'never' => $never,
                'overdue' => $overdue,
            ];
        }

        return $rows;
    }

    private function formatRuntime(int $ms): string
    {
        return $ms >= 1000 ? round($ms / 1000, 1).'s' : $ms.'ms';
    }
}
