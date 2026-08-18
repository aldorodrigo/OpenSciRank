<?php

namespace App\Support;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Journal;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Factory de admin_tasks. Centraliza la lógica de "qué task generar y
 * con qué prioridad/SLA" según el escenario (pago aceptado, listing
 * iniciado, pago huérfano). Sprint 3.6 #32.
 *
 * Acá NO se decide si la task se envía por email — eso lo hace el
 * lifecycle del modelo via assignToUser() o el cron tasks:check-overdue.
 */
class AdminTaskFactory
{
    /**
     * Genera la(s) task(s) derivada(s) de un Payment recién aceptado.
     * Llamado desde StripePaymentService::createPaymentFromSession().
     *
     * Reglas:
     *  - journal-evaluation               → TYPE_EVALUATE_JOURNAL
     *  - journal-reevaluation             → TYPE_REEVALUATE_JOURNAL
     *  - seal-renewal-{1,2,3}y            → TYPE_RENEWAL_EVALUATION
     *  - book-listing                     → TYPE_REVIEW_LISTING_BOOK
     *  - book-listing-featured-1y solo    → no genera task (automático)
     *  - addon action-plan-consulting     → TYPE_CONSULTING (task adicional)
     *
     * @return array<int, AdminTask>
     */
    public static function fromPayment(Payment $payment, ?Model $payable, array $metadata = []): array
    {
        $tasks = [];

        $mainSlug = $payment->product?->slug;
        $isExpress = (bool) ($payment->metadata['is_express'] ?? false);

        // Resolver tipo de la task principal según producto + payable
        $primaryType = self::resolvePrimaryType($mainSlug, $payable, $metadata);

        if ($primaryType !== null) {
            $tasks[] = self::createTask(
                type: $primaryType,
                payment: $payment,
                related: $payable,
                isExpress: $isExpress && in_array($primaryType, [
                    AdminTask::TYPE_EVALUATE_JOURNAL,
                    AdminTask::TYPE_REEVALUATE_JOURNAL,
                ], true),
            );
        }

        // Addon: action-plan-consulting (si fue comprado junto con el plan
        // principal viene en metadata.addon_slugs). Genera task aparte.
        $addonSlugs = self::extractAddonSlugs($metadata);
        if (in_array('action-plan-consulting', $addonSlugs, true)) {
            $tasks[] = self::createTask(
                type: AdminTask::TYPE_CONSULTING,
                payment: $payment,
                related: $payable,
                isExpress: false,
            );
        }

        return $tasks;
    }

    /**
     * Sprint 3.6 #37: cuando el editor resubmite un journal tras un pedido
     * de cambios (vía SubmissionWizard::submit bypass), buscamos la task
     * abierta asociada, le agregamos una nota con la fecha de resubmisión
     * y notificamos al asignado (o al super_admin si está sin asignar).
     *
     * No crea task nueva — la existente (in_progress) sigue siendo la misma
     * unidad de trabajo, solo necesita una señal para el admin.
     *
     * @return int  cantidad de tasks afectadas (0 si no había ninguna abierta).
     */
    public static function notifyJournalResubmission(Journal $journal): int
    {
        // Sprint 4 #61: el listado se maneja explícito en requestJournalListing()
        // (EditorDashboard::resubmitForListing / SubmissionWizard::listJournal),
        // por eso NO incluimos TYPE_REVIEW_LISTING_JOURNAL acá.
        $evalTypes = [
            AdminTask::TYPE_EVALUATE_JOURNAL,
            AdminTask::TYPE_REEVALUATE_JOURNAL,
            AdminTask::TYPE_RENEWAL_EVALUATION,
        ];

        // Caso A: hay tasks ABIERTAS del journal → marcamos reenviada + notificamos.
        $openTasks = AdminTask::query()
            ->where('related_type', Journal::class)
            ->where('related_id', $journal->id)
            ->whereIn('type', $evalTypes)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->get();

        $superAdmin = self::resolveSuperAdmin();

        if ($openTasks->isNotEmpty()) {
            foreach ($openTasks as $task) {
                // Sprint 4 #61: sub-estado "reenviada" para que el revisor
                // distinga esta tarea de "en progreso" / "cambios solicitados".
                $task->markResubmitted();
                self::appendResubmissionNote($task);
                self::notifyResubmission($task->fresh(), $superAdmin);
            }
            return $openTasks->count();
        }

        // Caso B (Sprint 4 #37 fix): no hay tasks abiertas, pero el journal
        // pasa a submitted (resubmisión post-requires_changes_evaluation O
        // post-pending_renewal_years). La task original quedó COMPLETED por
        // EvaluateJournal::save(). La REABRIMOS como in_progress para que
        // el admin no se pierda este trabajo.
        $lastCompleted = AdminTask::query()
            ->where('related_type', Journal::class)
            ->where('related_id', $journal->id)
            ->whereIn('type', $evalTypes)
            ->where('status', AdminTask::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->first();

        if ($lastCompleted) {
            // Reabrir manteniendo assignment, payment_id, etc. Sprint 4 #61:
            // el sub-estado "reenviada" marca que el editor corrigió y reenvió.
            $lastCompleted->markResubmitted();
            self::appendResubmissionNote($lastCompleted, reopened: true);

            activity()
                ->performedOn($lastCompleted)
                ->withProperties([
                    'reopened_at' => now()->toIso8601String(),
                    'previous_status' => 'completed',
                ])
                ->log(__('Task reopened due to journal resubmission'));

            self::notifyResubmission($lastCompleted->fresh(), $superAdmin);
            return 1;
        }

        // Caso C (edge case): no hay task abierta NI completada previa para este
        // journal. Crear una nueva de tipo evaluate_journal y notificar.
        // Esto no debería pasar en flujos normales — solo si una resubmisión
        // se dispara sin pago previo (ej. tras un seed manual).
        $newTask = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->getTranslationWithFallback('title')],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
            'due_at' => AdminTask::calculateDueAt(AdminTask::TYPE_EVALUATE_JOURNAL),
            'notes' => '⚠️ '.__('Task auto-created on resubmission — no prior task found for this journal.'),
        ]);

        self::notifyResubmission($newTask, $superAdmin);
        return 1;
    }

    /**
     * Issue #75 — el editor corrigió y reenvió un LIBRO tras un pedido de
     * cambios en la revisión de listado.
     *
     * Sin esto la tarea quedaba en `changes_requested` para siempre y nadie le
     * avisaba al revisor que la pelota había vuelto a su lado — el equivalente
     * para revistas ya existía (`notifyJournalResubmission`).
     *
     * No crea tareas: un libro sin tarea de listado nunca pagó ni recibió
     * cortesía, así que no hay revisión que reanudar.
     *
     * @return int cantidad de tareas afectadas (0 si no había ninguna).
     */
    public static function notifyBookResubmission(Book $book): int
    {
        $superAdmin = self::resolveSuperAdmin();

        $openTasks = AdminTask::query()
            ->where('related_type', Book::class)
            ->where('related_id', $book->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_BOOK)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->get();

        if ($openTasks->isNotEmpty()) {
            foreach ($openTasks as $task) {
                $task->markResubmitted();
                self::appendResubmissionNote($task);
                self::notifyResubmission($task->fresh(), $superAdmin);
            }

            return $openTasks->count();
        }

        // La tarea pudo haberse cerrado (ej. el admin rechazó y después el
        // editor volvió a mandar). La reabrimos para no perder el trabajo.
        $lastCompleted = AdminTask::query()
            ->where('related_type', Book::class)
            ->where('related_id', $book->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_BOOK)
            ->where('status', AdminTask::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->first();

        if (! $lastCompleted) {
            return 0;
        }

        $lastCompleted->markResubmitted();
        self::appendResubmissionNote($lastCompleted, reopened: true);

        activity()
            ->performedOn($lastCompleted)
            ->withProperties([
                'reopened_at' => now()->toIso8601String(),
                'previous_status' => 'completed',
            ])
            ->log(__('Task reopened due to book resubmission'));

        self::notifyResubmission($lastCompleted->fresh(), $superAdmin);

        return 1;
    }

    /**
     * Helper: agrega nota "Editor resubmitted on X" a las notas de la task.
     */
    protected static function appendResubmissionNote(AdminTask $task, bool $reopened = false): void
    {
        $marker = $reopened ? '🔄 ' : '↻ ';
        $message = $reopened
            ? __('Task reopened — editor resubmitted on :date', ['date' => now()->format('d/m/Y H:i')])
            : __('Editor resubmitted on :date', ['date' => now()->format('d/m/Y H:i')]);

        $task->update([
            'notes' => trim(
                ($task->notes ? $task->notes."\n\n" : '').
                $marker.$message
            ),
        ]);
    }

    /**
     * Helper: notifica TaskResubmitted al assignee (o super_admin si está sin asignar).
     */
    protected static function notifyResubmission(AdminTask $task, ?\App\Models\User $superAdmin): void
    {
        $recipient = $task->assignee ?? $superAdmin;
        if ($recipient) {
            try {
                $recipient->notify(new \App\Notifications\TaskResubmitted($task));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('TaskResubmitted notification failed', [
                    'task_id' => $task->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Helper: resuelve el super_admin destinatario por defecto de notificaciones
     * de tareas sin asignar (por email de configuración).
     */
    protected static function resolveSuperAdmin(): ?\App\Models\User
    {
        return \App\Models\User::where('email', config('app.admin_email', 'admin@editorialstandards.com'))->first();
    }

    /**
     * Task generada cuando un editor solicita listing gratuito de revista.
     * Llamado desde SubmissionWizard::listJournal() (sin pago de por medio).
     */
    public static function forJournalListing(Journal $journal): AdminTask
    {
        return self::createTask(
            type: AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
            payment: null,
            related: $journal,
            isExpress: false,
        );
    }

    /**
     * Sprint 4 #61 — el revisor pidió cambios: pasar las tareas ABIERTAS del
     * recurso (de los tipos dados) al sub-estado changes_requested, para que
     * en la lista del evaluador se distinga de "pendiente"/"en progreso".
     * Devuelve cuántas tareas se tocaron.
     *
     * Issue #75: acepta cualquier modelo relacionado, no sólo Journal — la
     * revisión de listado de libros usa el mismo sub-estado.
     */
    public static function markChangesRequested(Model $record, array $types): int
    {
        $tasks = AdminTask::query()
            ->where('related_type', $record::class)
            ->where('related_id', $record->id)
            ->whereIn('type', $types)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->get();

        $tasks->each(fn (AdminTask $task) => $task->markChangesRequested());

        return $tasks->count();
    }

    /**
     * Sprint 4 #61 — el editor (re)solicita el listado de una revista.
     *
     * - Primer listado (no hay tarea abierta) → se crea la tarea normal.
     * - Reenvío tras requires_changes_listing (ya hay una tarea abierta, típicamente
     *   en changes_requested) → se REUTILIZA esa tarea marcándola "reenviada",
     *   en vez de crear una segunda tarea pending (arregla el bug de duplicados).
     *
     * Llamado desde SubmissionWizard::listJournal() y EditorDashboard::resubmitForListing().
     */
    public static function requestJournalListing(Journal $journal): AdminTask
    {
        $task = AdminTask::query()
            ->where('related_type', Journal::class)
            ->where('related_id', $journal->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_JOURNAL)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->orderByDesc('id')
            ->first();

        if (! $task) {
            // Primer listado: crear la tarea de revisión de listado.
            return self::forJournalListing($journal);
        }

        // Reenvío: reutilizar la tarea abierta y marcarla como reenviada.
        $task->markResubmitted();
        self::appendResubmissionNote($task);
        self::notifyResubmission($task->fresh(), self::resolveSuperAdmin());

        return $task->fresh();
    }

    /**
     * Sprint 3.7 #44 — task creada manualmente por un admin desde un mensaje
     * del MessageThread. El admin elige tipo, asignación, prioridad, etc.
     *
     * Soporta todos los `TYPES_MANUALLY_CREATABLE`:
     *  - support: genérica, sin side effects
     *  - evaluate_journal / reevaluate_journal: pasa el journal a submitted
     *  - renewal_evaluation: pasa journal a submitted + setea pending_renewal_years
     *  - review_listing_journal: pasa journal a pending_listing
     *  - review_listing_book: pasa book a pending_listing
     *  - consulting: queda pending, entra al flujo normal de propuestas
     *
     * El parámetro `$config` espera (al menos):
     *  - type (string, requerido)
     *  - title (string, opcional — se autogenera si null)
     *  - priority (string, default 'normal')
     *  - assigned_to (int, default = creator)
     *  - due_at (Carbon, default = calculateDueAt según type)
     *  - related (Model|null — Journal/Book/User según type)
     *  - notes (string, opcional — pre-rellena con mensaje + link al hilo)
     *  - manual_reason (string, requerido si type != support — motivo de la creación manual)
     *  - creator_id (int, requerido — quien crea la task, para activity log)
     */
    public static function fromMessage(Message $message, array $config): AdminTask
    {
        $type = $config['type'] ?? AdminTask::TYPE_SUPPORT;

        if (! in_array($type, AdminTask::TYPES_MANUALLY_CREATABLE, true)) {
            throw new \InvalidArgumentException("Type {$type} cannot be created manually from a message.");
        }

        $related = $config['related'] ?? null;
        $creatorId = $config['creator_id'] ?? auth()->id();

        // Autogenerar título si no vino
        $title = $config['title'] ?? self::autoTitle($type, $message, $related);

        // title_params incluye el preview/name según tipo + opcionalmente el
        // product_id cuando el admin adjuntó link de pago (Sprint 3.7 #44).
        $titleParams = self::titleParamsForManual($type, $message, $related, $title);
        if (! empty($config['product_id'])) {
            $titleParams['product_id'] = (int) $config['product_id'];
        }

        // Sprint 3.7 #44: status_override permite arrancar en awaiting_payment
        // cuando hay link de pago adjunto. Sin override, default=pending.
        $initialStatus = $config['status_override'] ?? AdminTask::STATUS_PENDING;

        $task = AdminTask::create([
            'type' => $type,
            'title_key' => self::titleKeyForManualType($type),
            'title_params' => $titleParams,
            'payment_id' => null,
            'related_type' => $related ? $related::class : null,
            'related_id' => $related?->id,
            'source_message_id' => $message->id,
            'assigned_to' => $config['assigned_to'] ?? $creatorId,
            'status' => $initialStatus,
            'priority' => $config['priority'] ?? AdminTask::PRIORITY_NORMAL,
            'due_at' => $config['due_at'] ?? AdminTask::calculateDueAt($type),
            'notes' => self::buildNotes($message, $config),
        ]);

        // Side effects según tipo. **SOLO si arrancó pending** (cortesía sin pago).
        // Si es awaiting_payment, los side effects (ej. journal→submitted) se
        // ejecutan cuando el webhook confirma el pago. Si no, el journal quedaría
        // en submitted aunque el editor nunca pague.
        if ($initialStatus !== AdminTask::STATUS_AWAITING_PAYMENT) {
            self::applyTypeSideEffects($task, $type, $related, $config);
        }

        // Activity log
        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties([
                'source_message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'manual_reason' => $config['manual_reason'] ?? null,
            ])
            ->log('Task created manually from message');

        return $task;
    }

    /**
     * Title key adecuado según tipo (manual). Para support usamos
     * `tasks.support` o `tasks.support_with_context` según el contexto.
     * Para los tipos heredados del pago, reusamos los keys existentes.
     */
    protected static function titleKeyForManualType(string $type): string
    {
        return match ($type) {
            AdminTask::TYPE_SUPPORT => 'tasks.support', // se ajusta abajo si hay contexto
            AdminTask::TYPE_EVALUATE_JOURNAL => 'tasks.evaluate_journal',
            AdminTask::TYPE_REEVALUATE_JOURNAL => 'tasks.reevaluate_journal',
            AdminTask::TYPE_RENEWAL_EVALUATION => 'tasks.renewal_evaluation',
            AdminTask::TYPE_REVIEW_LISTING_JOURNAL => 'tasks.review_listing_journal',
            AdminTask::TYPE_REVIEW_LISTING_BOOK => 'tasks.review_listing_book',
            AdminTask::TYPE_CONSULTING => 'tasks.consulting',
            default => 'tasks.unknown',
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected static function titleParamsForManual(string $type, Message $message, ?Model $related, ?string $customTitle): array
    {
        // Para support: usamos preview del mensaje
        if ($type === AdminTask::TYPE_SUPPORT) {
            $preview = mb_substr(trim($message->body), 0, 60);
            if (mb_strlen($message->body) > 60) {
                $preview .= '…';
            }
            return ['preview' => $preview];
        }

        // Para los heredados, usamos resourceName del related
        return ['name' => self::resourceName($related)];
    }

    /**
     * Construye las notas internas con contexto del mensaje + motivo manual + URL del hilo.
     */
    protected static function buildNotes(Message $message, array $config): string
    {
        $parts = [];

        if (! empty($config['notes'])) {
            $parts[] = trim($config['notes']);
        }

        // Motivo manual (requerido para tipos != support)
        if (! empty($config['manual_reason'])) {
            $parts[] = '**'.__('Motivo de creación manual').':** '.trim($config['manual_reason']);
        }

        // Bloque del mensaje origen
        $parts[] = '---';
        $parts[] = '**'.__('Mensaje origen').':**';
        $parts[] = '> '.str_replace("\n", "\n> ", trim($message->body));
        $parts[] = '— '.($message->user->name ?? 'Anónimo').' · '.$message->created_at->format('d/m/Y H:i');

        // Link al hilo
        $threadUrl = url('/admin/conversations/'.$message->conversation_id);
        $parts[] = '['.__('Ver hilo completo').']('.$threadUrl.')';

        return implode("\n\n", $parts);
    }

    /**
     * Autogenera el título según tipo. Override-able vía $config['title'].
     */
    protected static function autoTitle(string $type, Message $message, ?Model $related): string
    {
        $preview = mb_substr(trim($message->body), 0, 60);
        if (mb_strlen($message->body) > 60) {
            $preview .= '…';
        }

        return match ($type) {
            AdminTask::TYPE_SUPPORT => __('Soporte: «:preview»', ['preview' => $preview]),
            AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación manual: :name', ['name' => self::resourceName($related)]),
            AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación manual: :name', ['name' => self::resourceName($related)]),
            AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación manual: :name', ['name' => self::resourceName($related)]),
            AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado manual: :name', ['name' => self::resourceName($related)]),
            AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado libro manual: :name', ['name' => self::resourceName($related)]),
            AdminTask::TYPE_CONSULTING => __('Consultoría manual: :name', ['name' => self::resourceName($related)]),
            default => '—',
        };
    }

    /**
     * Side effects según tipo creado manualmente. Replica lo que hace el flujo
     * de pago automático para mantener consistencia del status del recurso.
     */
    /**
     * Visibilidad pública: el webhook necesita re-disparar los side effects
     * cuando una task awaiting_payment se confirma con pago (transición a
     * pending). En ese punto los config originales se reconstruyen con valores
     * sensatos por default.
     */
    public static function applyTypeSideEffects(AdminTask $task, string $type, ?Model $related, array $config = []): void
    {
        if ($related === null) return;

        match ($type) {
            AdminTask::TYPE_EVALUATE_JOURNAL,
            AdminTask::TYPE_REEVALUATE_JOURNAL => $related instanceof Journal && $related->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]),

            AdminTask::TYPE_RENEWAL_EVALUATION => $related instanceof Journal && $related->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'pending_renewal_years' => (int) ($config['renewal_years'] ?? 1),
            ]),

            AdminTask::TYPE_REVIEW_LISTING_JOURNAL => $related instanceof Journal && $related->update([
                'status' => 'pending_listing',
            ]),

            AdminTask::TYPE_REVIEW_LISTING_BOOK => $related instanceof Book && $related->update([
                'status' => 'pending_listing',
            ]),

            // consulting + support: no side effects en el recurso
            default => null,
        };
    }

    /**
     * Task de pago huérfano (payable no encontrado al procesar webhook).
     * Prioridad high siempre. Sprint 1 #27 + Sprint 3.6 #32.
     */
    public static function forOrphanPayment(Payment $payment, string $payableType, int $payableId): AdminTask
    {
        return AdminTask::create([
            'type' => AdminTask::TYPE_ORPHAN_PAYMENT,
            'title_key' => 'tasks.orphan_payment',
            'title_params' => [
                'payable_type' => class_basename($payableType),
                'payable_id' => $payableId,
                'transaction' => $payment->transaction_id ?? '',
            ],
            'payment_id' => $payment->id,
            // related_type/related_id quedan null — el recurso fue soft-deleted
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_HIGH,
            'due_at' => AdminTask::calculateDueAt(AdminTask::TYPE_ORPHAN_PAYMENT),
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────

    protected static function resolvePrimaryType(?string $slug, ?Model $payable, array $metadata): ?string
    {
        if ($slug === null || $payable === null) {
            return null;
        }

        $isRenewal = ($metadata['is_renewal'] ?? '0') === '1';

        // Renovación (Opción B vigente): siempre genera task de re-evaluación
        if ($isRenewal && str_starts_with($slug, 'seal-renewal-')) {
            return AdminTask::TYPE_RENEWAL_EVALUATION;
        }

        return match (true) {
            $slug === 'journal-evaluation' => AdminTask::TYPE_EVALUATE_JOURNAL,
            $slug === 'journal-reevaluation' => AdminTask::TYPE_REEVALUATE_JOURNAL,
            $slug === 'book-listing' => AdminTask::TYPE_REVIEW_LISTING_BOOK,
            // book-listing-featured-1y standalone (libro ya listed): sin task
            $slug === 'book-listing-featured-1y' => null,
            // action-plan-consulting NUNCA es producto principal en flujo real,
            // pero por defensa devolvemos consulting si llega así
            $slug === 'action-plan-consulting' => AdminTask::TYPE_CONSULTING,
            // Sprint 3.7 #38 — Pack Lanzamiento Editorial: producto standalone
            // para crear nueva revista. payable_type=User (no journal aún).
            $slug === 'new-journal-consulting' => AdminTask::TYPE_CONSULTING,
            // Sprint 3.7 #46 — Crédito de Soporte: caso edge donde el pago
            // llega sin task pre-existente (el flujo normal es task creada
            // en awaiting_payment desde MessageThread, y el webhook sólo
            // la activa). Si por algún motivo no hay task previa, crear
            // una de tipo support con el User como related.
            $slug === 'support-credit' => AdminTask::TYPE_SUPPORT,
            default => null,
        };
    }

    protected static function createTask(
        string $type,
        ?Payment $payment,
        ?Model $related,
        bool $isExpress = false,
    ): AdminTask {
        return AdminTask::create([
            'type' => $type,
            'title_key' => self::titleKeyFor($type, $payment),
            'title_params' => self::titleParamsFor($type, $related, $payment),
            'payment_id' => $payment?->id,
            'related_type' => $related ? $related::class : null,
            'related_id' => $related?->id,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => $isExpress ? AdminTask::PRIORITY_HIGH : AdminTask::PRIORITY_NORMAL,
            'due_at' => AdminTask::calculateDueAt($type, $isExpress),
        ]);
    }

    protected static function titleKeyFor(string $type, ?Payment $payment = null): string
    {
        // Sprint 3.7 #38: las dos consultorías comparten type=CONSULTING pero
        // tienen títulos distintos en UI según el slug del producto.
        if ($type === AdminTask::TYPE_CONSULTING && $payment?->product?->slug === 'new-journal-consulting') {
            return 'tasks.consulting_new_journal';
        }

        return match ($type) {
            AdminTask::TYPE_EVALUATE_JOURNAL => 'tasks.evaluate_journal',
            AdminTask::TYPE_REEVALUATE_JOURNAL => 'tasks.reevaluate_journal',
            AdminTask::TYPE_RENEWAL_EVALUATION => 'tasks.renewal_evaluation',
            AdminTask::TYPE_REVIEW_LISTING_JOURNAL => 'tasks.review_listing_journal',
            AdminTask::TYPE_REVIEW_LISTING_BOOK => 'tasks.review_listing_book',
            AdminTask::TYPE_CONSULTING => 'tasks.consulting',
            AdminTask::TYPE_ORPHAN_PAYMENT => 'tasks.orphan_payment',
            default => 'tasks.unknown',
        };
    }

    protected static function titleParamsFor(string $type, ?Model $related, ?Payment $payment): array
    {
        $name = self::resourceName($related);

        $params = ['name' => $name];

        if ($type === AdminTask::TYPE_RENEWAL_EVALUATION && $related instanceof Journal) {
            $params['years'] = $related->pending_renewal_years ?? 1;
        }

        if ($type === AdminTask::TYPE_CONSULTING && $payment) {
            $params['amount'] = (float) $payment->amount;
            $params['currency'] = $payment->currency;
        }

        return $params;
    }

    protected static function resourceName(?Model $related): string
    {
        if ($related === null) {
            return '—';
        }

        // Sprint 3.7 #38: tasks de new-journal-consulting tienen related=User.
        // El "nombre" del recurso es el del editor que va a crear la revista.
        if ($related instanceof \App\Models\User) {
            return $related->name;
        }

        if (method_exists($related, 'getTranslationWithFallback')) {
            // Journal/Book con HasTranslations: title está traducido
            return $related->getTranslationWithFallback('title');
        }

        return $related->name ?? (string) $related->id;
    }

    /**
     * @return array<string>
     */
    protected static function extractAddonSlugs(array $metadata): array
    {
        $raw = $metadata['addon_slugs'] ?? '';

        if (is_array($raw)) {
            return array_filter(array_map('trim', $raw));
        }

        if (is_string($raw) && $raw !== '') {
            return array_filter(array_map('trim', explode(',', $raw)));
        }

        return [];
    }
}
