<?php

namespace App\Console\Commands;

use App\Models\AdminTask;
use App\Models\User;
use App\Notifications\TaskOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Sprint 3.6 #32: detecta admin_tasks vencidas y envía notificación
 * `TaskOverdue` al usuario asignado (o al super_admin si no hay asignado).
 *
 * Cooldown: para no spammear con emails todos los días sobre la misma
 * task vencida, usamos un cache por task_id con TTL 24h.
 */
class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';

    protected $description = 'Find overdue admin tasks and notify their assignees (or super_admin if unassigned).';

    /** TTL del aviso por task: 1 día. */
    protected const NOTIFICATION_COOLDOWN_SECONDS = 86400;

    public function handle(): int
    {
        $overdue = AdminTask::overdue()->with('assignee')->get();

        if ($overdue->isEmpty()) {
            $this->info('No overdue tasks found.');

            return self::SUCCESS;
        }

        $superAdmin = User::where('email', config('app.admin_email', 'admin@editorialstandards.com'))->first();
        $sent = 0;
        $skipped = 0;

        foreach ($overdue as $task) {
            $cacheKey = "task-overdue-notified:{$task->id}";

            if (Cache::has($cacheKey)) {
                $skipped++;
                continue;
            }

            $recipient = $task->assignee ?? $superAdmin;
            if (! $recipient) {
                $this->warn("Task #{$task->id} overdue but no recipient resolvable.");
                continue;
            }

            $recipient->notify(new TaskOverdue($task));
            Cache::put($cacheKey, true, self::NOTIFICATION_COOLDOWN_SECONDS);

            $sent++;
        }

        $this->info("Overdue tasks: {$overdue->count()} found, {$sent} notified, {$skipped} skipped by cooldown.");

        return self::SUCCESS;
    }
}
