<?php

namespace App\Livewire;

use App\Models\AdminTask;
use App\Models\ConsultingProposal;
use App\Models\Journal;
use App\Models\User;
use App\Support\TimezoneHelper;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Sprint 3.7 #39 — panel de consultorías del editor (cliente).
 *
 * Ruta: GET /app/consulting
 *
 * Carga todas las AdminTask de tipo `consulting` donde el usuario autenticado
 * es el editor (owner del recurso relacionado), las segmenta en tres secciones:
 *   - Acción requerida (status=proposal_sent)
 *   - Activas (pending, in_progress, scheduled, in_session)
 *   - Historial (completed, cancelled)
 *
 * @property-read Collection $tasks           Todas las tasks del usuario
 * @property-read Collection $actionTasks     Tasks con propuesta pendiente de aceptar
 * @property-read Collection $activeTasks     Tasks abiertas sin propuesta
 * @property-read Collection $historyTasks    Tasks terminales
 * @property-read int        $actionCount     Cantidad de tasks con acción requerida
 */
class EditorConsultingPanel extends Component
{
    // ── Estado modales ────────────────────────────────────────────────

    /** Muestra el modal "Contratar nueva consultoría". */
    public bool $showHireModal = false;

    /** Task cuyo modal de rechazo de propuestas está abierto. */
    public ?int $rejectTaskId = null;

    /** Motivo opcional para el rechazo de propuestas. */
    public string $rejectReason = '';

    // ── Computed: cargar tasks ────────────────────────────────────────

    /**
     * Todas las tasks de tipo consulting visibles para el usuario actual.
     * Unión de dos queries polimórficas:
     *   1. related_type=Journal, related→user_id = auth->id
     *   2. related_type=User, related_id = auth->id
     */
    public function getTasksProperty(): Collection
    {
        $userId = auth()->id();

        // Journals propiedad del editor
        $journalIds = Journal::where('user_id', $userId)
            ->withTrashed()
            ->pluck('id');

        $journalTasks = AdminTask::where('type', AdminTask::TYPE_CONSULTING)
            ->where('related_type', Journal::class)
            ->whereIn('related_id', $journalIds)
            ->with(['assignee', 'activeProposals', 'payment.product'])
            ->get();

        // Consulting directo al usuario (new-journal-consulting)
        $userTasks = AdminTask::where('type', AdminTask::TYPE_CONSULTING)
            ->where('related_type', User::class)
            ->where('related_id', $userId)
            ->with(['assignee', 'activeProposals', 'payment.product'])
            ->get();

        return $journalTasks->merge($userTasks)->sortByDesc('created_at')->values();
    }

    /** Tasks con propuesta esperando decisión del editor. */
    public function getActionTasksProperty(): Collection
    {
        return $this->tasks->where('status', AdminTask::STATUS_PROPOSAL_SENT)->values();
    }

    /** Tasks abiertas que no están en estado proposal_sent. */
    public function getActiveTasksProperty(): Collection
    {
        $openStatuses = collect(AdminTask::STATUSES_OPEN)
            ->reject(fn ($s) => $s === AdminTask::STATUS_PROPOSAL_SENT)
            ->all();

        return $this->tasks->whereIn('status', $openStatuses)->values();
    }

    /** Tasks terminales: completed / cancelled. */
    public function getHistoryTasksProperty(): Collection
    {
        return $this->tasks->whereIn('status', AdminTask::STATUSES_TERMINAL)->values();
    }

    /** Número de tasks con acción requerida (para badge). */
    public function getActionCountProperty(): int
    {
        return $this->actionTasks->count();
    }

    // ── Helpers de formato ────────────────────────────────────────────

    /** Formatea una fecha en la TZ del editor autenticado. */
    public function formatDate(\Carbon\CarbonInterface $dt, string $format = 'd/m/Y H:i'): string
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return TimezoneHelper::formatWithIndicator($dt, $user, $format);
    }

    /**
     * Indica si el botón "Reagendar" debe estar habilitado.
     * Regla:
     *   - >48h  → siempre habilitado
     *   - 24-48h → habilitado solo si reschedule_count < 1
     *   - <24h  → deshabilitado
     */
    public function canReschedule(AdminTask $task): bool
    {
        if (! $task->scheduled_for) {
            return false;
        }

        $hoursUntil = now()->diffInHours($task->scheduled_for, false);

        if ($hoursUntil < 24) {
            return false;
        }

        if ($hoursUntil <= 48) {
            return ($task->reschedule_count ?? 0) < 1;
        }

        return true;
    }

    /** Tooltip para el botón Reagendar cuando está deshabilitado. */
    public function rescheduleTooltip(AdminTask $task): string
    {
        if (! $task->scheduled_for) {
            return '';
        }

        $hoursUntil = now()->diffInHours($task->scheduled_for, false);

        if ($hoursUntil < 24) {
            return 'No se puede reagendar con menos de 24 horas de anticipación.';
        }

        if ($hoursUntil <= 48 && ($task->reschedule_count ?? 0) >= 1) {
            return 'Solo se permite un reagendamiento en la ventana de 24-48 h.';
        }

        return '';
    }

    // ── Acciones ──────────────────────────────────────────────────────

    /** El editor acepta una propuesta de horario. */
    public function acceptProposal(int $taskId, int $proposalId): void
    {
        $task = $this->findOwnTask($taskId);

        $proposal = ConsultingProposal::find($proposalId);

        if (! $proposal || $proposal->admin_task_id !== $task->id) {
            session()->flash('toast', ['type' => 'error', 'message' => 'Propuesta no encontrada.']);

            return;
        }

        if (! $proposal->isActive()) {
            session()->flash('toast', ['type' => 'error', 'message' => 'Esta propuesta ya no está disponible.']);

            return;
        }

        $task->acceptProposal($proposal, auth()->user());

        $formatted = TimezoneHelper::formatWithIndicator($proposal->proposed_slot, auth()->user());
        session()->flash('toast', ['type' => 'success', 'message' => "Sesión confirmada para {$formatted}."]);
    }

    /** Abre el modal de rechazo para la task indicada. */
    public function openRejectModal(int $taskId): void
    {
        $this->findOwnTask($taskId); // autorización
        $this->rejectTaskId = $taskId;
        $this->rejectReason = '';
    }

    /** Cierra el modal de rechazo. */
    public function closeRejectModal(): void
    {
        $this->rejectTaskId = null;
        $this->rejectReason = '';
    }

    /** El editor rechaza todas las propuestas y pide otras fechas. */
    public function rejectAllProposals(): void
    {
        if (! $this->rejectTaskId) {
            return;
        }

        $task = $this->findOwnTask($this->rejectTaskId);
        $task->rejectAllProposals(auth()->user(), $this->rejectReason ?: null);

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Solicitud enviada. El evaluador recibirá tu pedido de nuevas fechas.',
        ]);

        $this->closeRejectModal();
    }

    // ── Autorización ──────────────────────────────────────────────────

    /** Busca y autoriza que la task pertenezca al editor autenticado. */
    private function findOwnTask(int $taskId): AdminTask
    {
        $task = $this->tasks->firstWhere('id', $taskId);

        if (! $task) {
            abort(403, 'No tenés acceso a esta tarea.');
        }

        return $task;
    }

    // ── Render ────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.editor-consulting-panel', [
            'tasks'        => $this->tasks,
            'actionTasks'  => $this->actionTasks,
            'activeTasks'  => $this->activeTasks,
            'historyTasks' => $this->historyTasks,
            'actionCount'  => $this->actionCount,
            'user'         => auth()->user(),
        ])->layout('components.layouts.app', [
            'title' => 'Mis Consultorías - Editorial Standards Platform',
        ]);
    }
}
