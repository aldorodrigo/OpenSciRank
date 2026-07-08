<?php

namespace App\Listeners;

use App\Models\ScheduledTaskRun;
use Illuminate\Console\Events\ScheduledTaskFailed;

/**
 * #59 — registra la corrida fallida de una tarea programada (cron health), con
 * el mensaje de error para el panel. Best-effort.
 */
class RecordScheduledTaskFailed
{
    public function handle(ScheduledTaskFailed $event): void
    {
        $command = $this->resolveCommand($event);
        if ($command === null) {
            return;
        }

        ScheduledTaskRun::updateOrCreate(
            ['command' => $command],
            [
                'last_ran_at' => now(),
                'status' => ScheduledTaskRun::STATUS_FAILED,
                'error' => mb_substr($event->exception?->getMessage() ?? 'error', 0, 1000),
            ],
        );
    }

    private function resolveCommand(ScheduledTaskFailed $event): ?string
    {
        $raw = $event->task->command ?: $event->task->getSummaryForDisplay();

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        return ScheduledTaskRun::normalizeCommand($raw);
    }
}
