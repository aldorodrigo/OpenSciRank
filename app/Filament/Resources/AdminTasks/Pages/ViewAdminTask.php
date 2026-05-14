<?php

namespace App\Filament\Resources\AdminTasks\Pages;

use App\Filament\Resources\AdminTasks\AdminTaskResource;
use App\Models\AdminTask;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewAdminTask extends ViewRecord
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Asignar a…
            Action::make('assign')
                ->label(__('Asignar a…'))
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->visible(fn (): bool => $this->record->isOpen())
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
                ->visible(fn (): bool => $this->record->status === AdminTask::STATUS_PENDING)
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

            // Marcar agendada (solo consulting)
            Action::make('schedule')
                ->label(__('Marcar agendada'))
                ->icon('heroicon-o-calendar-days')
                ->color('indigo')
                ->visible(fn (): bool => $this->record->type === AdminTask::TYPE_CONSULTING
                    && in_array($this->record->status, [AdminTask::STATUS_PENDING, AdminTask::STATUS_IN_PROGRESS], true)
                )
                ->form([
                    DateTimePicker::make('scheduled_for')
                        ->label(__('Fecha y hora de la sesión'))
                        ->required()
                        ->native(false)
                        ->minDate(now()),
                ])
                ->action(function (array $data): void {
                    $this->record->markScheduled(Carbon::parse($data['scheduled_for']));

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(['scheduled_for' => $data['scheduled_for']])
                        ->log(__('Consultoría agendada para :date', ['date' => $data['scheduled_for']]));

                    Notification::make()
                        ->title(__('Consultoría agendada'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'scheduled_for']);
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
            Action::make('complete')
                ->label(__('Marcar completada'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->isOpen() && in_array(
                    $this->record->type,
                    [AdminTask::TYPE_CONSULTING, AdminTask::TYPE_ORPHAN_PAYMENT],
                    true
                ))
                ->requiresConfirmation()
                ->modalHeading(__('Completar tarea'))
                ->modalDescription(__('Esta acción cerrará la tarea. Podés agregar una nota opcional.'))
                ->form([
                    Textarea::make('note')
                        ->label(__('Nota (opcional)'))
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->action(function (array $data): void {
                    $note = filled($data['note'] ?? null) ? trim($data['note']) : null;
                    $this->record->complete($note);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties(array_filter(['note' => $note]))
                        ->log(__('Tarea completada'));

                    Notification::make()
                        ->title(__('Tarea marcada como completada'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'completed_at', 'notes']);
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
}
