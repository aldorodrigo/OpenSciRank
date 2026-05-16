<?php

namespace App\Livewire;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Product;
use App\Models\User;
use App\Support\AdminTaskFactory;
use App\Support\PaymentPayableResolver;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Sprint 3.7 #40 — hilo de mensajes reutilizable.
 *
 * Usado en /app/messages/{id}, /admin/conversations/{id}, y embebido
 * en ViewAdminTask (sección "Conversación").
 */
class MessageThread extends Component
{
    use WithFileUploads;

    /** Hilo activo (mount param). */
    public Conversation $conversation;

    /** 'editor' | 'admin' — define UI y permisos. */
    public string $role = 'editor';

    /** Texto del nuevo mensaje. */
    public string $newBody = '';

    /** Archivos temporales subidos. */
    public array $pendingFiles = [];

    // ── Sprint 3.7 #44: modal "Crear tarea desde mensaje" ────────────────
    /** ID del mensaje desde el cual se abre el modal. Null = cerrado. */
    public ?int $creatingTaskFromMessageId = null;

    /** Estado del form del modal (wire:model). */
    public array $taskForm = [
        'type' => 'support',
        'title' => '',
        'priority' => 'normal',
        'assigned_to' => null,
        'due_at' => null,
        'related_type' => null,
        'related_id' => null,
        'manual_reason' => '',
        'notes' => '',
        // Link de pago opcional
        'attach_payment_link' => false,
        'product_id' => null,
        // Sprint 3.7 #44: payable explícito al que aplicar el pago.
        // Si product es Book → debe ser un Book del editor; si es Journal → Journal; etc.
        'payment_payable_id' => null,
        'send_auto_message' => true,
    ];

    // ── Lifecycle ────────────────────────────────────────────────────────

    public function mount(Conversation $conversation, string $role = 'editor'): void
    {
        $this->conversation = $conversation;
        $this->role = $role;
        $this->conversation->markReadBy(auth()->user());
    }

    // ── Validation ───────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'newBody' => 'required|string|max:10000',
            'pendingFiles' => 'array|max:5',
            'pendingFiles.*' => [
                'file',
                'max:'.(MessageAttachment::MAX_BYTES / 1024),
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,gif,svg',
            ],
        ];
    }

    // ── Actions ──────────────────────────────────────────────────────────

    /**
     * Enviar mensaje con adjuntos opcionales.
     * Estrategia de notificación (Sprint 3.7 #44 — refactor):
     *   - Mensajes regulares: NO disparan email individual. El cron
     *     `messages:daily-digest` (9:00 hora local del destinatario) agrupa
     *     todos los hilos con mensajes sin leer en 1 solo email diario.
     *   - Mensajes con `payment_link_for_task_id` (link de pago): email
     *     inmediato vía NewConversationMessage (handled abajo).
     *   - Mensajes vía `NewConversationOpened` (hilo nuevo): email inmediato
     *     desde EditorMessagesInbox/AdminTaskConversationController.
     */
    public function send(): void
    {
        $this->validate();

        if ($this->conversation->status !== Conversation::STATUS_OPEN) {
            session()->flash('thread_error', 'El hilo está cerrado y no acepta nuevos mensajes.');

            return;
        }

        $user = auth()->user();

        // Auto-join admin que abre el hilo por primera vez
        if ($this->role === 'admin') {
            $this->conversation->addParticipant($user, Conversation::ROLE_ADMIN);
        }

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $user->id,
            'body' => $this->newBody,
            // notification_sent_at = null → pending batch notification
        ]);

        // Bump last_message_at
        $this->conversation->update(['last_message_at' => now()]);

        // Guardar adjuntos
        // Capturamos size, mime y original_name ANTES de storeAs(): el método
        // mueve el archivo del temp dir y getSize() falla con
        // UnableToRetrieveMetadata porque el path original ya no existe.
        foreach ($this->pendingFiles as $file) {
            $mime = $file->getMimeType();
            if (! in_array($mime, MessageAttachment::ALLOWED_MIME_TYPES, true)) {
                continue; // skip tipos no permitidos (ya validado client-side)
            }

            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $sizeBytes = $file->getSize();

            $path = $file->storeAs(
                'conversations/'.$this->conversation->id,
                \Str::uuid().'.'.$extension,
                'local'
            );

            MessageAttachment::create([
                'message_id' => $message->id,
                'original_name' => $originalName,
                'stored_path' => $path,
                'mime_type' => $mime,
                'size_bytes' => $sizeBytes,
                'uploaded_by_user_id' => $user->id,
            ]);
        }

        // Marcar leído por el autor inmediatamente
        $this->conversation->markReadBy($user);

        $this->newBody = '';
        $this->pendingFiles = [];
        $this->resetValidation();

        $this->dispatch('message-sent');
    }

    /** Admin puede cerrar la conversación. */
    // ── Sprint 3.7 #45: modal "Cerrar conversación" con opciones ──────

    /** Modal de cierre abierto/cerrado. */
    public bool $showingCloseModal = false;

    /** Form del modal de cierre. */
    public array $closeForm = [
        'send_history' => false,
        'closing_note' => '',
    ];

    /**
     * Abre el modal de cierre. El admin elige si manda historial por email
     * y opcionalmente agrega una nota de cierre.
     */
    public function openCloseModal(): void
    {
        abort_unless($this->role === 'admin', 403);
        $this->showingCloseModal = true;
        $this->closeForm = [
            'send_history' => false,
            'closing_note' => '',
        ];
    }

    public function cancelCloseConversation(): void
    {
        $this->showingCloseModal = false;
    }

    /**
     * Ejecuta el cierre con las opciones del modal.
     * Sprint 3.7 #45: si send_history=true, dispara ConversationHistoryEmailed
     * a todos los participants no muteados con el historial completo + nota.
     */
    public function closeConversation(): void
    {
        abort_unless($this->role === 'admin', 403);

        $this->conversation->update([
            'status' => Conversation::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        // Si se pidió enviar historial, mandar a todos los participantes no muteados
        if (! empty($this->closeForm['send_history'])) {
            $closingNote = filled($this->closeForm['closing_note'] ?? null)
                ? trim($this->closeForm['closing_note'])
                : null;

            $recipients = $this->conversation->participants()
                ->where('email_muted', false)
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $sent = 0;
            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new \App\Notifications\ConversationHistoryEmailed(
                        $this->conversation->fresh(['messages.user', 'messages.attachments']),
                        $closingNote,
                    ));
                    $sent++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('ConversationHistoryEmailed failed', [
                        'conversation_id' => $this->conversation->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            activity()
                ->performedOn($this->conversation)
                ->causedBy(auth()->user())
                ->withProperties([
                    'recipients_count' => $sent,
                    'message_count' => $this->conversation->messages()->count(),
                    'has_closing_note' => $closingNote !== null,
                ])
                ->log('Conversation history emailed on close');
        }

        $this->showingCloseModal = false;
        session()->flash('thread_info', __('Conversación cerrada.'));
    }

    /** Admin puede reabrir la conversación. */
    public function reopenConversation(): void
    {
        abort_unless($this->role === 'admin', 403);

        $this->conversation->update([
            'status' => Conversation::STATUS_OPEN,
            'closed_at' => null,
        ]);

        session()->flash('thread_info', 'Conversación reabierta.');
    }

    /** Eliminar archivo pendiente antes de enviar. */
    public function removePendingFile(int $index): void
    {
        array_splice($this->pendingFiles, $index, 1);
    }

    // ── Polling ──────────────────────────────────────────────────────────

    /**
     * Livewire wire:poll.10s llama a este método para refrescar el hilo
     * y marcar mensajes entrantes como leídos.
     */
    #[On('refresh-thread')]
    public function refreshThread(): void
    {
        $this->conversation->refresh();
        $this->conversation->markReadBy(auth()->user());
    }

    // ── Sprint 3.7 #44: Crear tarea desde mensaje ───────────────────────

    /**
     * Abre el modal "Crear tarea" para un mensaje específico.
     * Solo admins. Pre-rellena el form con defaults sensatos.
     */
    public function openCreateTaskModal(int $messageId): void
    {
        abort_unless($this->role === 'admin', 403);

        $message = $this->conversation->messages()->findOrFail($messageId);

        // Heredar related_type/id del subject del conversation si aplica.
        // Usamos subjectModel (relación) en vez de subject (columna string del título).
        $subject = $this->conversation->subjectModel;
        $inheritedRelatedType = null;
        $inheritedRelatedId = null;
        if ($subject instanceof Journal || $subject instanceof Book || $subject instanceof User) {
            $inheritedRelatedType = $subject::class;
            $inheritedRelatedId = $subject->id;
        }

        // Preview del mensaje (60 chars)
        $preview = mb_substr(trim($message->body), 0, 60);
        if (mb_strlen($message->body) > 60) {
            $preview .= '…';
        }

        $this->creatingTaskFromMessageId = $message->id;
        $this->taskForm = [
            'type' => 'support',
            'title' => __('Soporte: «:preview»', ['preview' => $preview]),
            'priority' => 'normal',
            'assigned_to' => auth()->id(),
            'due_at' => now()->addDays(7)->format('Y-m-d'),
            'related_type' => $inheritedRelatedType,
            'related_id' => $inheritedRelatedId,
            'manual_reason' => '',
            'notes' => '',
            'attach_payment_link' => false,
            'product_id' => null,
            'send_auto_message' => true,
        ];
    }

    /**
     * Cierra el modal sin crear nada.
     */
    public function closeCreateTaskModal(): void
    {
        $this->creatingTaskFromMessageId = null;
        $this->resetValidation();
    }

    /**
     * Sprint 3.7 #44 — al cambiar el producto, auto-resolver el payable si
     * hay solo uno. Si hay varios, dejar al admin elegir. Si no hay ninguno,
     * dejar null (la UI muestra mensaje rojo).
     */
    public function updatedTaskFormProductId(?int $productId): void
    {
        if (! $productId) {
            $this->taskForm['payment_payable_id'] = null;
            return;
        }

        $available = $this->paymentPayables;
        if ($available->count() === 1) {
            $this->taskForm['payment_payable_id'] = $available->first()->id;
        } else {
            // Si hay varios o ninguno, vaciar selección previa para forzar elección manual
            $this->taskForm['payment_payable_id'] = null;
        }
    }

    /**
     * Computed property: payables disponibles para el producto seleccionado,
     * filtrados a los recursos del editor del hilo. Vacío si no hay producto
     * o si el editor no tiene recursos del tipo requerido.
     */
    public function getPaymentPayablesProperty(): \Illuminate\Support\Collection
    {
        if (empty($this->taskForm['product_id'])) return collect();

        $product = Product::find($this->taskForm['product_id']);
        if (! $product) return collect();

        $editor = $this->resolveEditorForCurrentContext();
        if (! $editor) return collect();

        return PaymentPayableResolver::availablePayablesFor($product, $editor);
    }

    /**
     * Computed: tipo de payable esperado (label legible) para el producto seleccionado.
     */
    public function getPaymentPayableLabelProperty(): ?string
    {
        if (empty($this->taskForm['product_id'])) return null;
        $product = Product::find($this->taskForm['product_id']);
        return $product ? PaymentPayableResolver::expectedPayableLabel($product) : null;
    }

    /**
     * Reglas de validación del form, dinámicas según tipo y attach_payment_link.
     */
    protected function taskFormRules(): array
    {
        $rules = [
            'taskForm.type' => 'required|in:'.implode(',', AdminTask::TYPES_MANUALLY_CREATABLE),
            'taskForm.title' => 'required|string|max:200',
            'taskForm.priority' => 'required|in:low,normal,high',
            'taskForm.assigned_to' => 'required|exists:users,id',
            'taskForm.due_at' => 'required|date|after:now',
            'taskForm.notes' => 'nullable|string|max:5000',
        ];

        // manual_reason requerido si tipo != support
        if (($this->taskForm['type'] ?? null) !== 'support') {
            $rules['taskForm.manual_reason'] = 'required|string|min:10|max:500';
        } else {
            $rules['taskForm.manual_reason'] = 'nullable|string|max:500';
        }

        // related_type/id requeridos según tipo
        $type = $this->taskForm['type'] ?? 'support';
        if (in_array($type, ['evaluate_journal','reevaluate_journal','renewal_evaluation','review_listing_journal'], true)) {
            $rules['taskForm.related_type'] = 'required|in:'.Journal::class;
            $rules['taskForm.related_id'] = 'required|exists:journals,id';
        } elseif ($type === 'review_listing_book') {
            $rules['taskForm.related_type'] = 'required|in:'.Book::class;
            $rules['taskForm.related_id'] = 'required|exists:books,id';
        } elseif ($type === 'consulting') {
            $rules['taskForm.related_type'] = 'required|in:'.Journal::class.','.User::class;
            $rules['taskForm.related_id'] = 'required';
        } else {
            // support: libre
            $rules['taskForm.related_type'] = 'nullable|string';
            $rules['taskForm.related_id'] = 'nullable';
        }

        // Link de pago
        if (! empty($this->taskForm['attach_payment_link'])) {
            $rules['taskForm.product_id'] = 'required|exists:products,id';
            // payment_payable_id es opcional en las rules (la lógica de resolución
            // está más abajo); si hay solo 1 payable disponible se auto-asigna.
            $rules['taskForm.payment_payable_id'] = 'nullable|integer';
        }

        return $rules;
    }

    /**
     * Crea la AdminTask desde el mensaje + envía auto-mensaje al hilo si toggle.
     */
    public function createTaskFromMessage(): void
    {
        abort_unless($this->role === 'admin', 403);
        abort_if($this->creatingTaskFromMessageId === null, 400);

        $this->validate($this->taskFormRules());

        $message = $this->conversation->messages()->findOrFail($this->creatingTaskFromMessageId);

        // Resolver related model si vino seteado por el form (Aplicar a)
        $related = null;
        if (! empty($this->taskForm['related_type']) && ! empty($this->taskForm['related_id'])) {
            $relatedClass = $this->taskForm['related_type'];
            if (class_exists($relatedClass)) {
                $related = $relatedClass::find($this->taskForm['related_id']);
            }
        }

        // Sprint 3.7 #44 — si se adjunta link de pago, validar y resolver
        // el payable correcto SEGÚN EL PRODUCTO. Si product=book-listing y
        // el editor no tiene libros → error y abort sin crear la task.
        if (! empty($this->taskForm['attach_payment_link'])) {
            $product = Product::find($this->taskForm['product_id']);
            $editor = $this->resolveEditorForCurrentContext();

            if (! $editor) {
                $this->addError('taskForm.product_id', __('No se pudo identificar al editor de este hilo. No es posible asociar un pago.'));
                return;
            }

            // Si el admin eligió un payable explícito (selector con varios), usarlo
            $paymentPayable = null;
            if (! empty($this->taskForm['payment_payable_id'])) {
                $paymentPayable = PaymentPayableResolver::findPayableForProduct(
                    $product,
                    (int) $this->taskForm['payment_payable_id'],
                    $editor
                );
            } else {
                // Auto-resolver: usa el related heredado si es compatible,
                // sino el primer recurso del editor del tipo correcto.
                $paymentPayable = PaymentPayableResolver::resolveCorrectPayableFor($product, $related, $editor);
            }

            if (! $paymentPayable) {
                $expectedLabel = PaymentPayableResolver::expectedPayableLabel($product);
                $this->addError(
                    'taskForm.product_id',
                    __('El producto :product requiere un :resource del editor, pero no se encontró ninguno disponible. Pedile al editor que registre uno primero o elegí otro producto.', [
                        'product' => $product->getTranslationWithFallback('name'),
                        'resource' => $expectedLabel,
                    ])
                );
                return;
            }

            // Sobre-escribir $related con el payable resuelto (puede no coincidir
            // con el related heredado si el admin eligió un producto incompatible).
            $related = $paymentPayable;
        }

        // Crear task via factory
        $hasPaymentLink = ! empty($this->taskForm['attach_payment_link']);

        $task = AdminTaskFactory::fromMessage($message, [
            'type' => $this->taskForm['type'],
            'title' => $this->taskForm['title'],
            'priority' => $this->taskForm['priority'],
            'assigned_to' => (int) $this->taskForm['assigned_to'],
            'due_at' => \Carbon\Carbon::parse($this->taskForm['due_at']),
            'related' => $related,
            'notes' => $this->taskForm['notes'] ?: null,
            'manual_reason' => $this->taskForm['manual_reason'] ?: null,
            'creator_id' => auth()->id(),
            'product_id' => $hasPaymentLink ? (int) $this->taskForm['product_id'] : null,
            // Sprint 3.7 #44: cuando hay link de pago, la task arranca en
            // status=awaiting_payment (queue separada). Sin link, queda pending
            // como hasta ahora (cortesía, support manual, etc.).
            'status_override' => $hasPaymentLink ? AdminTask::STATUS_AWAITING_PAYMENT : null,
        ]);

        // Si se pidió link de pago + auto-mensaje, enviamos un mensaje en el hilo
        // con payment_link_for_task_id = task->id. La view renderiza un BOTÓN
        // nativo a partir de ese flag, en lugar de un markdown plano que no
        // sería clickeable. El body queda solo con el texto introductorio.
        if (! empty($this->taskForm['attach_payment_link'])
            && ! empty($this->taskForm['send_auto_message'])
            && $task->paymentLinkUrl()
        ) {
            $editor = $task->editor();
            $editorName = $editor->name ?? __('Hola');

            // CRÍTICO: garantizamos que el editor sea participant del hilo
            // antes de crear el mensaje. El cron `messages:notify-pending`
            // solo envía email a participants — si el editor no estaba
            // (caso típico cuando el admin abre un hilo nuevo), nunca recibiría
            // el link de pago.
            if ($editor) {
                $this->conversation->addParticipant($editor, Conversation::ROLE_EDITOR);
            }

            $paymentMessage = Message::create([
                'conversation_id' => $this->conversation->id,
                'user_id' => auth()->id(),
                'body' => __(':editor, te dejo el link de pago para avanzar con esto:', ['editor' => $editorName]),
                'payment_link_for_task_id' => $task->id,
                // Marcamos notification_sent_at = now() para que el cron NO lo
                // re-procese. Disparamos la notificación AHORA (no esperamos
                // los 5 min de cooldown del batching): el link de pago es
                // urgente, debe llegar al instante.
                'notification_sent_at' => now(),
            ]);

            $this->conversation->update(['last_message_at' => now()]);

            // Notificación inmediata a todos los participantes no muteados,
            // excepto el autor (el admin que está creando la task).
            $recipients = $this->conversation->recipientsForNotification(auth()->user());
            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new \App\Notifications\NewConversationMessage($paymentMessage));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send payment link notification', [
                        'recipient_id' => $recipient->id,
                        'message_id' => $paymentMessage->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Notificar al asignado si no soy yo
        if ((int) $this->taskForm['assigned_to'] !== auth()->id()) {
            $assignee = User::find($this->taskForm['assigned_to']);
            if ($assignee) {
                $assignee->notify(new \App\Notifications\TaskAssigned($task));
            }
        }

        $this->closeCreateTaskModal();
        session()->flash('thread_info', __('Tarea #:id creada', ['id' => $task->id]));
        $this->dispatch('task-created');
    }

    /**
     * Lista de usuarios admin para el selector "Asignar a".
     * Cualquier user con role super_admin (en el futuro: + role evaluator).
     */
    public function getAdminUsersProperty(): \Illuminate\Support\Collection
    {
        return User::role('super_admin')->orderBy('name')->get();
    }

    /**
     * Lista de productos activos para el selector cuando attach_payment_link.
     */
    public function getActiveProductsProperty(): \Illuminate\Support\Collection
    {
        return Product::where('is_active', true)
            ->whereNotIn('slug', ['express-evaluation', 'institutional-pack'])
            ->orderBy('price')
            ->get();
    }

    /**
     * Lista de journals del editor (subject del conversation o todos).
     */
    public function getAvailableJournalsProperty(): \Illuminate\Support\Collection
    {
        $editor = $this->resolveEditorForCurrentContext();
        if (! $editor) return collect();

        return Journal::where('user_id', $editor->id)->orderBy('id')->get();
    }

    public function getAvailableBooksProperty(): \Illuminate\Support\Collection
    {
        $editor = $this->resolveEditorForCurrentContext();
        if (! $editor) return collect();

        return Book::where('user_id', $editor->id)->orderBy('id')->get();
    }

    /**
     * Resuelve el editor del contexto: si conversation tiene subject que es
     * Journal/Book/AdminTask, devuelve el user owner. Si subject es User
     * directo, devuelve ese user. Si null (consulta general), devuelve el
     * primer participant con rol 'editor'.
     */
    protected function resolveEditorForCurrentContext(): ?User
    {
        $subject = $this->conversation->subjectModel;

        if ($subject instanceof User) return $subject;
        if ($subject instanceof Journal || $subject instanceof Book) {
            return $subject->user;
        }
        if ($subject instanceof AdminTask) {
            return $subject->editor();
        }

        // null (consulta general): devolver primer participant con role=editor
        $participant = $this->conversation->participants()
            ->where('role', Conversation::ROLE_EDITOR)
            ->with('user')
            ->first();

        return $participant?->user;
    }

    // ── Render ───────────────────────────────────────────────────────────

    public function render()
    {
        $messages = $this->conversation
            ->messages()
            ->with(['user', 'attachments', 'derivedTasks', 'paymentLinkTask.solicitedPayment'])
            ->orderBy('created_at')
            ->get();

        return view('livewire.message-thread', [
            'messages' => $messages,
            'authUser' => auth()->user(),
        ]);
    }
}
