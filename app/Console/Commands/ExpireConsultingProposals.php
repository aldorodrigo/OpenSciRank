<?php

namespace App\Console\Commands;

use App\Models\AdminTask;
use App\Models\ConsultingProposal;
use App\Models\User;
use App\Notifications\ConsultingProposalsExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Sprint 3.7 #39 — expirar propuestas de consultoría vencidas.
 *
 * Recorre las ConsultingProposal con status=active y expires_at en el pasado:
 *  1. Las marca como 'expired'.
 *  2. Si la task asociada queda sin propuestas activas, la devuelve a 'pending'.
 *  3. Notifica al editor + evaluador + super_admin con ConsultingProposalsExpired.
 *
 * Idempotente: si no hay propuestas vencidas, no hace nada.
 */
class ExpireConsultingProposals extends Command
{
    protected $signature = 'consulting:expire-proposals';

    protected $description = 'Expira propuestas de consultoría no respondidas y devuelve la task a pending.';

    public function handle(): int
    {
        $expiredProposals = ConsultingProposal::query()
            ->where('status', ConsultingProposal::STATUS_ACTIVE)
            ->where('expires_at', '<', now())
            ->with(['adminTask.related', 'adminTask.assignee'])
            ->get();

        if ($expiredProposals->isEmpty()) {
            $this->info('No expired proposals found.');
            return self::SUCCESS;
        }

        $superAdmin = User::role('super_admin')->first();
        $tasksAffected = collect();

        foreach ($expiredProposals as $proposal) {
            $proposal->markExpired();
            $tasksAffected->push($proposal->admin_task_id);
        }

        // Por cada task afectada, si ya no tiene propuestas activas, revertir a pending
        $tasksAffected = $tasksAffected->unique();
        $notified = 0;

        foreach ($tasksAffected as $taskId) {
            $task = AdminTask::with(['related', 'assignee'])->find($taskId);
            if (! $task) continue;

            $stillActive = $task->activeProposals()->count();

            if ($stillActive === 0 && $task->status === AdminTask::STATUS_PROPOSAL_SENT) {
                $task->update(['status' => AdminTask::STATUS_PENDING]);

                // Notificar editor + evaluador + super_admin
                $recipients = collect($task->consultingRecipients());
                if ($superAdmin && ! $recipients->contains('id', $superAdmin->id)) {
                    $recipients->push($superAdmin);
                }

                if ($recipients->isNotEmpty()) {
                    NotificationFacade::send($recipients->all(), new ConsultingProposalsExpired($task));
                    $notified++;
                }

                activity()
                    ->performedOn($task)
                    ->withProperties([
                        'expired_proposals_count' => $expiredProposals->where('admin_task_id', $taskId)->count(),
                    ])
                    ->log('All consulting proposals expired without response');
            }
        }

        $this->info("Expired {$expiredProposals->count()} proposal(s). Reverted {$notified} task(s) to pending.");

        return self::SUCCESS;
    }
}
