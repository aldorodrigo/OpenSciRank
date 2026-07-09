<?php

namespace App\Filament\Resources\AdminTasks\Pages;

use App\Filament\Resources\AdminTasks\AdminTaskResource;
use App\Models\AdminTask;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class ViewAdminTask extends ViewRecord
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Ver pago (modal, solo super_admin con pago asociado)
            // Sprint 3.7 #46: usar effective_payment para soportar tanto el
            // flujo viejo (payment_id directo) como el nuevo #44 (solicited_by_admin_task_id).
            Action::make('view_payment')
                ->label(__('Ver pago'))
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->visible(fn (): bool => $this->record->effective_payment !== null
                    && (auth()->user()?->hasRole('super_admin') ?? false)
                )
                ->modalHeading(__('Información del pago'))
                ->modalDescription(__('Detalle completo de la transacción y su desglose. Filas resaltadas pertenecen a esta tarea.'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('Cerrar'))
                ->modalWidth('5xl')
                ->modalContent(fn (): View => view(
                    'filament.admin-tasks.payment-modal',
                    ['task' => $this->record]
                )),

            // Asignar a…
            Action::make('assign')
                ->label(__('Asignar a…'))
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->visible(fn (): bool => $this->record->isOpen()
                    && (auth()->user()?->hasRole('super_admin') ?? false)
                )
                ->form([
                    Select::make('user_id')
                        ->label(__('Usuario'))
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $user = User::findOrFail($data['user_id']);
                    $this->record->assignToUser($user);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'assignee_id' => $user->id,
                            'assignee_name' => $user->name,
                        ])
                        ->log(__('Tarea asignada a :name', ['name' => $user->name]));

                    // Notificar al asignado (Sprint 3.6 #32 sub-tarea 7)
                    $user->notify(new \App\Notifications\TaskAssigned($this->record->fresh()));

                    Notification::make()
                        ->title(__('Tarea asignada a :name', ['name' => $user->name]))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'assigned_to']);
                }),

            // Iniciar (Fase 1 UX): auto-asigna y redirige al workUrl
            Action::make('start')
                ->label(fn (): string => match ($this->record->type) {
                    AdminTask::TYPE_EVALUATE_JOURNAL => __('Iniciar evaluación'),
                    AdminTask::TYPE_REEVALUATE_JOURNAL => __('Iniciar re-evaluación'),
                    AdminTask::TYPE_RENEWAL_EVALUATION => __('Iniciar eval. renovación'),
                    AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                    AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Revisar listado'),
                    AdminTask::TYPE_CONSULTING => __('Iniciar consultoría'),
                    AdminTask::TYPE_ORPHAN_PAYMENT => __('Investigar pago'),
                    default => __('Iniciar'),
                })
                ->icon('heroicon-o-play')
                ->color('warning')
                // Sprint 4 #61: también accionable en "reenviada" (editor corrigió).
                ->visible(fn (): bool => in_array($this->record->status, [AdminTask::STATUS_PENDING, AdminTask::STATUS_RESUBMITTED], true))
                ->action(function () {
                    $autoAssigned = false;
                    if (! $this->record->assigned_to) {
                        $this->record->update(['assigned_to' => auth()->id()]);
                        $autoAssigned = true;
                    }

                    $this->record->start();

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(['auto_assigned' => $autoAssigned])
                        ->log(__('Tarea iniciada'));

                    Notification::make()
                        ->title(__('Tarea iniciada'))
                        ->body($autoAssigned ? __('Asignada a ti automáticamente. Redirigiendo al trabajo…') : __('Redirigiendo al trabajo…'))
                        ->success()
                        ->send();

                    return redirect()->to($this->record->workUrl());
                }),

            // Continuar (Fase 1 UX): atajo si la task ya esta in_progress
            Action::make('continue_work')
                ->label(fn (): string => match ($this->record->type) {
                    AdminTask::TYPE_EVALUATE_JOURNAL,
                    AdminTask::TYPE_REEVALUATE_JOURNAL,
                    AdminTask::TYPE_RENEWAL_EVALUATION => __('Continuar evaluación'),
                    AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                    AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Continuar revisión'),
                    AdminTask::TYPE_ORPHAN_PAYMENT => __('Seguir investigando'),
                    default => __('Continuar'),
                })
                ->icon('heroicon-o-arrow-right-circle')
                ->color(fn (): string => $this->record->isOverdue() ? 'danger' : 'info')
                ->visible(fn (): bool => $this->record->status === AdminTask::STATUS_IN_PROGRESS)
                ->url(fn (): string => $this->record->workUrl()),

            // ── Sprint 3.7 #39: Proponer fechas ──────────────────────────────
            // Visible cuando type=consulting && status IN (pending, in_progress, proposal_sent)
            Action::make('propose_dates')
                ->label(__('Proponer fechas'))
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && in_array($this->record->status, [
                        AdminTask::STATUS_PENDING,
                        AdminTask::STATUS_IN_PROGRESS,
                    ], true)
                )
                ->form([
                    Repeater::make('slots')
                        ->label(__('Fechas propuestas'))
                        ->schema([
                            DateTimePicker::make('slot')
                                ->label(__('Fecha y hora'))
                                ->required()
                                ->native(false)
                                ->minDate(now()->addHours(2)),
                            TextInput::make('notes')
                                ->label(__('Notas para el editor (opcional)'))
                                ->maxLength(200),
                        ])
                        ->minItems(1)
                        ->maxItems(3)
                        ->reorderable(false)
                        ->addActionLabel(__('Agregar fecha')),
                ])
                ->action(function (array $data): void {
                    $slots = collect($data['slots'])->map(fn (array $entry) => [
                        'slot' => Carbon::parse($entry['slot']),
                        'notes' => filled($entry['notes'] ?? null) ? $entry['notes'] : null,
                    ])->all();

                    $this->record->sendProposals($slots, auth()->user());

                    $count = count($slots);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(['proposal_count' => $count])
                        ->log(__('Propuestas de fecha enviadas: :count slot(s)', ['count' => $count]));

                    Notification::make()
                        ->title(__('Propuestas enviadas — el editor recibió un email con :count fechas.', ['count' => $count]))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'proposal_count_sent']);
                }),

            // ── Sprint 3.7 #39: Agendar directo (bypass de propuesta) ─────────
            // Solo super_admin, solo consulting pending
            Action::make('schedule_direct')
                ->label(__('Agendar directo (saltar propuesta)'))
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->tooltip(__('Solo para casos donde ya acordaste fecha con el editor por fuera.'))
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && $this->record->status === AdminTask::STATUS_PENDING
                    && (auth()->user()?->hasRole('super_admin') ?? false)
                )
                ->form([
                    DateTimePicker::make('scheduled_for')
                        ->label(__('Fecha y hora de la sesión'))
                        ->required()
                        ->native(false)
                        ->minDate(now()),
                    Textarea::make('reason')
                        ->label(__('Motivo del bypass (requerido)'))
                        ->required()
                        ->minLength(5)
                        ->maxLength(500)
                        ->rows(2)
                        ->helperText(__('Explicá por qué se salta el flujo de propuesta.')),
                ])
                ->action(function (array $data): void {
                    $this->record->markScheduled(Carbon::parse($data['scheduled_for']));

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'scheduled_for' => $data['scheduled_for'],
                            'bypass_reason' => $data['reason'],
                        ])
                        ->log(__('Consultoría agendada directamente (bypass propuesta): :reason', ['reason' => $data['reason']]));

                    Notification::make()
                        ->title(__('Consultoría agendada'))
                        ->body(__('Se notificó al editor y al evaluador con la fecha de la sesión.'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'scheduled_for']);
                }),

            // ── Sprint 3.7 #39: Reagendar vía propuesta ───────────────────────
            // Vuelve la task a proposal_sent con 1 slot nuevo
            Action::make('reschedule_propose')
                ->label(__('Reagendar (proponer nueva fecha)'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && $this->record->status === AdminTask::STATUS_SCHEDULED
                )
                ->form([
                    Repeater::make('slots')
                        ->label(__('Nueva(s) fecha(s) propuesta(s)'))
                        ->schema([
                            DateTimePicker::make('slot')
                                ->label(__('Fecha y hora'))
                                ->required()
                                ->native(false)
                                ->minDate(now()->addHours(2)),
                            TextInput::make('notes')
                                ->label(__('Notas para el editor (opcional)'))
                                ->maxLength(200),
                        ])
                        ->minItems(1)
                        ->maxItems(3)
                        ->reorderable(false)
                        ->addActionLabel(__('Agregar fecha')),
                ])
                ->action(function (array $data): void {
                    $previous = $this->record->scheduled_for?->toIso8601String();

                    $slots = collect($data['slots'])->map(fn (array $entry) => [
                        'slot' => Carbon::parse($entry['slot']),
                        'notes' => filled($entry['notes'] ?? null) ? $entry['notes'] : null,
                    ])->all();

                    $this->record->sendProposals($slots, auth()->user());

                    $count = count($slots);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'previous_scheduled_for' => $previous,
                            'proposal_count' => $count,
                        ])
                        ->log(__('Reagendamiento: :count propuesta(s) enviadas al editor', ['count' => $count]));

                    Notification::make()
                        ->title(__('Propuestas de reagendamiento enviadas'))
                        ->body(__('El editor recibió :count nueva(s) fecha(s) para elegir.', ['count' => $count]))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'scheduled_for', 'proposal_count_sent']);
                }),

            // ── Sprint 3.7 #39: Reagendar directo (solo super_admin) ──────────
            Action::make('reschedule_direct')
                ->label(__('Reagendar directo'))
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->tooltip(__('Solo super_admin: reagenda sin pasar por propuesta.'))
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && $this->record->status === AdminTask::STATUS_SCHEDULED
                    && (auth()->user()?->hasRole('super_admin') ?? false)
                )
                ->form([
                    DateTimePicker::make('scheduled_for')
                        ->label(__('Nueva fecha y hora de la sesión'))
                        ->required()
                        ->native(false)
                        ->minDate(now())
                        ->default(fn () => $this->record->scheduled_for),
                ])
                ->action(function (array $data): void {
                    $previous = $this->record->scheduled_for?->toIso8601String();

                    $this->record->markScheduled(
                        Carbon::parse($data['scheduled_for']),
                        notify: true,
                        isRescheduling: true,
                    );

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'previous_scheduled_for' => $previous,
                            'new_scheduled_for' => $data['scheduled_for'],
                        ])
                        ->log(__('Consultoría reagendada directamente para :date', ['date' => $data['scheduled_for']]));

                    Notification::make()
                        ->title(__('Consultoría reagendada'))
                        ->body(__('Se notificó al editor y al evaluador con la nueva fecha.'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['scheduled_for', 'consulting_reminder_sent_at']);
                }),

            // ── Sprint 3.7 #39: URL de la reunión ────────────────────────────
            // Editable cuando type=consulting && status=scheduled
            Action::make('set_meet_url')
                ->label(__('URL de la reunión'))
                ->icon('heroicon-o-video-camera')
                ->color('info')
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && $this->record->status === AdminTask::STATUS_SCHEDULED
                )
                ->form([
                    TextInput::make('consulting_meet_url')
                        ->label(__('Enlace de videollamada'))
                        ->url()
                        ->placeholder('https://meet.google.com/...')
                        ->default(fn () => $this->record->consulting_meet_url)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $url = filled($data['consulting_meet_url'] ?? null) ? trim($data['consulting_meet_url']) : null;
                    $this->record->update(['consulting_meet_url' => $url]);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(['consulting_meet_url' => $url])
                        ->log(__('URL de reunión actualizada'));

                    Notification::make()
                        ->title(__('URL de la reunión guardada'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['consulting_meet_url']);
                }),

            // Iniciar sesión (solo consulting scheduled)
            Action::make('in_session')
                ->label(__('Iniciar sesión'))
                ->icon('heroicon-o-video-camera')
                ->color('success')
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && $this->record->status === AdminTask::STATUS_SCHEDULED
                )
                ->requiresConfirmation()
                ->modalHeading(__('Iniciar sesión de consultoría'))
                ->modalDescription(__('¿Confirmar que la sesión ha comenzado?'))
                ->action(function (): void {
                    $this->record->markInSession();

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->log(__('Sesión de consultoría iniciada'));

                    Notification::make()
                        ->title(__('Sesión iniciada'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            // Marcar completada
            // Marcar completada — sólo para tipos que NO auto-completan vía
            // hook (consulting, orphan_payment). Las evaluaciones y revisiones
            // de listado se cierran al guardar el trabajo en EvaluateJournal,
            // ReviewListing o vía BookObserver.
            // Sprint 3.7 #44 Fase 1+2: marcar completada disponible para CUALQUIER
            // task abierta. Para tipos con auto-completion (eval, listing, renewal)
            // mostramos warning + motivo obligatorio. Para tipos sin auto-completion
            // (support, consulting, orphan_payment) el motivo es opcional.
            Action::make('complete')
                ->label(__('Marcar completada'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                // Sprint 3.7 #44: NO permitir completar si la task está
                // awaiting_payment — primero el editor tiene que pagar.
                // Roadmap #35: el evaluator NO cierra tasks a mano — sus
                // evaluaciones se auto-completan al guardar en EvaluateJournal.
                // Un cierre manual dejaría task=completed con journal=submitted.
                ->visible(fn (): bool => $this->record->isOpen()
                    && $this->record->status !== AdminTask::STATUS_AWAITING_PAYMENT
                    && ! ((auth()->user()?->hasRole('evaluator') ?? false)
                        && ! (auth()->user()?->hasRole('super_admin') ?? false))
                )
                ->requiresConfirmation()
                ->modalHeading(__('Completar tarea'))
                ->modalDescription(function (): string {
                    $autoCompletionTypes = [
                        AdminTask::TYPE_EVALUATE_JOURNAL,
                        AdminTask::TYPE_REEVALUATE_JOURNAL,
                        AdminTask::TYPE_RENEWAL_EVALUATION,
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                        AdminTask::TYPE_REVIEW_LISTING_BOOK,
                    ];

                    if (in_array($this->record->type, $autoCompletionTypes, true)) {
                        return __('⚠️ Esta tarea normalmente se cierra al guardar la evaluación/revisión desde el flujo de trabajo. Si la cerrás manualmente acá, el recurso queda en su estado actual y vos sos responsable de actualizarlo. Solo hacelo para cortesías o si el trabajo se hizo por fuera de la plataforma.');
                    }

                    return __('Esta acción cerrará la tarea. Podés agregar una nota opcional.');
                })
                ->form(function (): array {
                    $autoCompletionTypes = [
                        AdminTask::TYPE_EVALUATE_JOURNAL,
                        AdminTask::TYPE_REEVALUATE_JOURNAL,
                        AdminTask::TYPE_RENEWAL_EVALUATION,
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                        AdminTask::TYPE_REVIEW_LISTING_BOOK,
                    ];
                    $requiresReason = in_array($this->record->type, $autoCompletionTypes, true);

                    $form = [];

                    if ($requiresReason) {
                        $form[] = Textarea::make('manual_reason')
                            ->label(__('Motivo de cierre manual'))
                            ->helperText(__('Requerido para tipos que normalmente se auto-cierran. Queda en activity log.'))
                            ->required()
                            ->minLength(10)
                            ->maxLength(500)
                            ->rows(2);
                    }

                    $form[] = Textarea::make('note')
                        ->label(__('Nota interna (opcional)'))
                        ->rows(3)
                        ->maxLength(1000);

                    if ($this->record->type === AdminTask::TYPE_CONSULTING) {
                        $form[] = Textarea::make('client_visible_notes')
                            ->label(__('Resumen visible al cliente (opcional)'))
                            ->helperText(__('Si lo completás, el editor recibe un email con este resumen como ConsultingSessionSummary.'))
                            ->rows(4)
                            ->maxLength(2000);
                    }

                    // Aviso explícito cuando hay link de pago pendiente
                    if ($this->record->solicitedPayment && $this->record->solicitedPayment->status !== 'completed') {
                        $form[] = \Filament\Forms\Components\Placeholder::make('payment_warning')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">'
                                .'⚠️ '.__('Esta tarea tiene un link de pago pendiente. Si la cerrás, el editor recibirá un email avisando que ya no necesita pagar.')
                                .'</div>'
                            ));
                    }

                    return $form;
                })
                ->action(function (array $data): void {
                    $autoCompletionTypes = [
                        AdminTask::TYPE_EVALUATE_JOURNAL,
                        AdminTask::TYPE_REEVALUATE_JOURNAL,
                        AdminTask::TYPE_RENEWAL_EVALUATION,
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                        AdminTask::TYPE_REVIEW_LISTING_BOOK,
                    ];
                    $isManualOverride = in_array($this->record->type, $autoCompletionTypes, true);

                    $manualReason = filled($data['manual_reason'] ?? null) ? trim($data['manual_reason']) : null;
                    $note = filled($data['note'] ?? null) ? trim($data['note']) : null;

                    // Si hay motivo de cierre manual, lo concatenamos a la nota
                    // estándar para que quede registrado dentro de notes también.
                    $combinedNote = $manualReason
                        ? '**'.__('Cierre manual').':** '.$manualReason.($note ? "\n\n".$note : '')
                        : $note;

                    // complete() ahora dispara side effects automáticamente
                    // (propuestas superseded para consulting, email a editor si
                    // hay link de pago pendiente, etc.).
                    $this->record->complete($combinedNote);

                    $clientNotes = filled($data['client_visible_notes'] ?? null)
                        ? trim($data['client_visible_notes'])
                        : null;

                    if ($clientNotes) {
                        $this->record->update(['client_visible_notes' => $clientNotes]);

                        if ($this->record->type === AdminTask::TYPE_CONSULTING) {
                            $editor = $this->record->editor();
                            if ($editor) {
                                try {
                                    $editor->notify(new \App\Notifications\ConsultingSessionSummary($this->record->fresh()));
                                } catch (\Throwable $e) {
                                    \Illuminate\Support\Facades\Log::warning(
                                        'ConsultingSessionSummary not sent: '.$e->getMessage(),
                                        ['admin_task_id' => $this->record->id]
                                    );
                                }
                            }
                        }
                    }

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(array_filter([
                            'note' => $note,
                            'manual_reason' => $manualReason,
                            'manual_override' => $isManualOverride,
                            'client_visible_notes' => $clientNotes,
                        ]))
                        ->log($isManualOverride
                            ? __('Tarea cerrada manualmente (override de auto-completion)')
                            : __('Tarea completada')
                        );

                    Notification::make()
                        ->title(__('Tarea marcada como completada'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'completed_at', 'notes', 'client_visible_notes']);
                }),

            // Cancelar (solo super_admin)
            Action::make('cancel')
                ->label(__('Cancelar'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->isOpen()
                    && auth()->user()?->hasRole('super_admin')
                )
                ->requiresConfirmation()
                ->modalHeading(__('Cancelar tarea'))
                ->modalDescription(__('La tarea quedará en estado cancelada. Un super_admin podrá reactivarla más adelante si hace falta.'))
                ->form([
                    Textarea::make('reason')
                        ->label(__('Razón de cancelación'))
                        ->required()
                        ->minLength(5)
                        ->maxLength(500)
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->cancel(trim($data['reason']));

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(['cancelled_reason' => $data['reason']])
                        ->log(__('Tarea cancelada: :reason', ['reason' => $data['reason']]));

                    Notification::make()
                        ->title(__('Tarea cancelada'))
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'cancelled_reason', 'completed_at']);
                }),

            // Reactivar (solo super_admin, solo tasks cancelled)
            Action::make('reactivate')
                ->label(__('Reactivar'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === AdminTask::STATUS_CANCELLED
                    && (auth()->user()?->hasRole('super_admin') ?? false)
                )
                ->requiresConfirmation()
                ->modalHeading(__('Reactivar tarea'))
                ->modalDescription(__('La tarea volverá a estado pendiente y aparecerá en el queue. La razón de cancelación previa se guarda en las notas.'))
                ->form([
                    Textarea::make('reason')
                        ->label(__('Motivo de reactivación (opcional)'))
                        ->maxLength(500)
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $previousReason = $this->record->cancelled_reason;
                    $this->record->reactivate(filled($data['reason'] ?? null) ? trim($data['reason']) : null);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'reactivation_reason' => $data['reason'] ?? null,
                            'previous_cancelled_reason' => $previousReason,
                        ])
                        ->log(__('Tarea reactivada'));

                    Notification::make()
                        ->title(__('Tarea reactivada'))
                        ->body(__('Volvió al queue como pendiente.'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'cancelled_reason', 'completed_at', 'started_at', 'notes']);
                }),
        ];
    }

    /**
     * Sprint 3.7 #40 — sección de conversación al pie de la vista de tarea.
     *
     * Roadmap #35 — solo super_admin: el partial embebe message-thread con
     * role='admin' y su botón "abrir conversación" pega contra
     * AdminTaskConversationController (super_admin-only). El evaluador tiene su
     * propia mensajería anclada al Journal dentro de EvaluateJournal.
     */
    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        if (! (auth()->user()?->hasRole('super_admin') ?? false)) {
            return null;
        }

        return view('filament.admin-tasks.conversation-section', [
            'task' => $this->record,
        ]);
    }
}
