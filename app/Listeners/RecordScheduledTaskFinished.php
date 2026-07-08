<?php

namespace App\Listeners;

use App\Models\ScheduledTaskRun;
use Illuminate\Console\Events\ScheduledTaskFinished;

/**
 * #59 — registra la corrida exitosa de una tarea programada (cron health).
 * Upsert por comando: última corrida + runtime. Best-effort: un fallo de logging
 * nunca aborta la ejecución del cron.
 */
class RecordScheduledTaskFinished
{
    public function handle(ScheduledTaskFinished $event): void
    {
        $command = $this->resolveCommand($event);
        if ($command === null) {
            return;
        }

        ScheduledTaskRun::updateOrCreate(
            ['command' => $command],
            [
                'last_ran_at' => now(),
                'runtime_ms' => (int) round(($event->runtime ?? 0) * 1000),
                'status' => ScheduledTaskRun::STATUS_OK,
                'error' => null,
            ],
        );
    }

    private function resolveCommand(ScheduledTaskFinished $event): ?string
    {
        $raw = $event->task->command ?: $event->task->getSummaryForDisplay();

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        return ScheduledTaskRun::normalizeCommand($raw);
    }
}
