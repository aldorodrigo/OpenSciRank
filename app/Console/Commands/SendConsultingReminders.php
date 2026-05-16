<?php

namespace App\Console\Commands;

use App\Models\AdminTask;
use App\Notifications\ConsultingReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Sprint 3.6 #32 — recordatorios de consultoría 24h antes.
 *
 * Escanea admin_tasks de tipo `consulting` con `scheduled_for` entre
 * ahora y 24h adelante, donde `consulting_reminder_sent_at` está null
 * (lo cual también es el caso después de un reagendamiento, porque
 * `markScheduled()` resetea el flag). Por cada match envía
 * `ConsultingReminder` al editor + evaluador asignado y marca el flag.
 *
 * Idempotente: si el cron corre dos veces en el mismo día, la segunda
 * pasada no encuentra registros porque ya tienen el flag seteado.
 */
class SendConsultingReminders extends Command
{
    protected $signature = 'consulting:send-reminders';

    protected $description = 'Envía recordatorio 24h previo a sesiones de consultoría agendadas.';

    public function handle(): int
    {
        $windowStart = now();
        $windowEnd = now()->addDay();

        $tasks = AdminTask::query()
            ->where('type', AdminTask::TYPE_CONSULTING)
            ->where('status', AdminTask::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_for')
            ->whereBetween('scheduled_for', [$windowStart, $windowEnd])
            ->whereNull('consulting_reminder_sent_at')
            ->with(['assignee', 'related.user', 'payment'])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No consulting sessions scheduled in the next 24h need reminders.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            $recipients = $task->consultingRecipients();

            if (empty($recipients)) {
                $this->warn("Task #{$task->id} has no recipients (no editor, no assignee). Skipping.");
                $skipped++;
                continue;
            }

            NotificationFacade::send($recipients, new ConsultingReminder($task));

            $task->update(['consulting_reminder_sent_at' => now()]);

            activity()
                ->performedOn($task)
                ->withProperties([
                    'recipients' => array_map(fn ($u) => $u->email, $recipients),
                    'scheduled_for' => $task->scheduled_for?->toIso8601String(),
                ])
                ->log('Consulting reminder 24h sent');

            $sent++;
        }

        $this->info("Reminders sent: {$sent}. Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
