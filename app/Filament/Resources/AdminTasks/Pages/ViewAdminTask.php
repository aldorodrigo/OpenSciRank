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

            // Iniciar
            Action::make('start')
                ->label(__('Iniciar'))
                ->icon('heroicon-o-play')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === AdminTask::STATUS_PENDING)
                ->requiresConfirmation()
                ->modalHeading(__('Iniciar tarea'))
                ->modalDescription(__('¿Marcar esta tarea como "En progreso"?'))
                ->action(function (): void {
                    $this->record->start();

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->log(__('Tarea iniciada'));

                    Notification::make()
                        ->title(__('Tarea marcada como En progreso'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'started_at']);
                }),

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
            Action::make('complete')
                ->label(__('Marcar completada'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->isOpen())
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
                ->modalDescription(__('Esta acción es irreversible. Ingresá la razón de cancelación.'))
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
        ];
    }
}
