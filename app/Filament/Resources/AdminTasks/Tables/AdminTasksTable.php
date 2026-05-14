<?php

namespace App\Filament\Resources\AdminTasks\Tables;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use App\Models\AdminTask;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AdminTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn (Builder $query) => $query
                ->orderByRaw("FIELD(priority, 'high', 'normal', 'low')")
                ->orderBy('due_at', 'asc')
            )
            ->columns([
                // 1. Tipo
                TextColumn::make('type')
                    ->label(__('Tipo'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::TYPE_EVALUATE_JOURNAL,
                        AdminTask::TYPE_REEVALUATE_JOURNAL => 'indigo',
                        AdminTask::TYPE_RENEWAL_EVALUATION => 'purple',
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                        AdminTask::TYPE_REVIEW_LISTING_BOOK => 'info',
                        AdminTask::TYPE_CONSULTING => 'success',
                        AdminTask::TYPE_ORPHAN_PAYMENT => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación'),
                        AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación'),
                        AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación'),
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado Revista'),
                        AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado Libro'),
                        AdminTask::TYPE_CONSULTING => __('Consultoría'),
                        AdminTask::TYPE_ORPHAN_PAYMENT => __('Pago Huérfano'),
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                // 2. Tarea (título renderizado)
                TextColumn::make('title_key')
                    ->label(__('Tarea'))
                    ->formatStateUsing(fn (AdminTask $record): string => $record->renderedTitle())
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where(function (Builder $q) use ($search): void {
                            $q->where('type', 'like', "%{$search}%")
                                ->orWhere('payment_id', 'like', "%{$search}%")
                                ->orWhere('related_id', 'like', "%{$search}%");
                        });
                    })
                    ->wrap()
                    ->toggleable(),

                // 3. Relacionado a (Journal o Book link polimórfico)
                TextColumn::make('related_id')
                    ->label(__('Relacionado a'))
                    ->formatStateUsing(function (AdminTask $record): string {
                        if (! $record->related) {
                            return '—';
                        }

                        return $record->related->getTranslationWithFallback('title');
                    })
                    ->url(fn (AdminTask $record): ?string => match (true) {
                        $record->related === null => null,
                        $record->related_type === 'App\\Models\\Journal' => JournalResource::getUrl('edit', ['record' => $record->related_id]),
                        $record->related_type === 'App\\Models\\Book' => BookResource::getUrl('edit', ['record' => $record->related_id]),
                        default => null,
                    })
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->toggleable(),

                // 4. Prioridad
                TextColumn::make('priority')
                    ->label(__('Prioridad'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::PRIORITY_HIGH => 'danger',
                        AdminTask::PRIORITY_NORMAL => 'gray',
                        AdminTask::PRIORITY_LOW => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::PRIORITY_HIGH => __('Alta'),
                        AdminTask::PRIORITY_NORMAL => __('Normal'),
                        AdminTask::PRIORITY_LOW => __('Baja'),
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                // 5. Express (badge ámbar visible si el pago incluyó uplift Express +$50)
                TextColumn::make('payment.metadata.is_express')
                    ->label(__('Express'))
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-bolt')
                    ->getStateUsing(fn (AdminTask $record): ?string => ($record->payment?->metadata['is_express'] ?? false) ? __('Express') : null)
                    ->placeholder('—')
                    ->tooltip(__('Pago con servicio Express. Resultado en plazo reducido.'))
                    ->toggleable(),

                // 6. Asignado a
                TextColumn::make('assignee.name')
                    ->label(__('Asignado'))
                    ->placeholder(__('Sin asignar'))
                    ->color(fn (AdminTask $record): ?string => $record->assignee ? null : 'gray')
                    ->searchable()
                    ->toggleable(),

                // Monto de la tarea (sólo super_admin). Cada task muestra el
                // monto que le corresponde, no el total del payment. Ejemplo:
                // task evaluate_journal con Express muestra $149 ($99+$50),
                // mientras que la task consulting del MISMO pago muestra $215.
                TextColumn::make('task_amount')
                    ->label(__('Monto'))
                    ->getStateUsing(fn (AdminTask $record): ?string => $record->taskAmount() !== null
                        ? '$'.number_format($record->taskAmount(), 0).' '.($record->payment?->currency ?? 'USD')
                        : null
                    )
                    ->placeholder('—')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                    ->toggleable(),

                // 7. Pagado el
                TextColumn::make('payment.created_at')
                    ->label(__('Pagado el'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 7. Vence
                TextColumn::make('due_at')
                    ->label(__('Vence'))
                    ->placeholder('—')
                    ->sortable()
                    ->formatStateUsing(function (AdminTask $record): string {
                        if (! $record->due_at) {
                            return '—';
                        }

                        $days = $record->daysUntilDue();
                        $formatted = $record->due_at->format('d/m/Y');

                        if ($record->isOverdue()) {
                            $absDays = abs($days);

                            return "{$formatted} · ".__('Expired :days days ago', ['days' => $absDays]);
                        }

                        return "{$formatted} · ".__('Expires in :days days', ['days' => $days]);
                    })
                    ->color(function (AdminTask $record): string {
                        if (! $record->due_at) {
                            return 'gray';
                        }

                        if ($record->isOverdue()) {
                            return 'danger';
                        }

                        $days = $record->daysUntilDue();

                        if ($days < 3) {
                            return 'danger';
                        }

                        if ($days < 7) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->toggleable(),

                // 8. Estado
                TextColumn::make('status')
                    ->label(__('Estado'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::STATUS_PENDING => 'gray',
                        AdminTask::STATUS_IN_PROGRESS => 'info',
                        AdminTask::STATUS_SCHEDULED => 'indigo',
                        AdminTask::STATUS_IN_SESSION => 'warning',
                        AdminTask::STATUS_COMPLETED => 'success',
                        AdminTask::STATUS_CANCELLED => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::STATUS_PENDING => __('Pendiente'),
                        AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                        AdminTask::STATUS_SCHEDULED => __('Agendada'),
                        AdminTask::STATUS_IN_SESSION => __('En sesión'),
                        AdminTask::STATUS_COMPLETED => __('Completada'),
                        AdminTask::STATUS_CANCELLED => __('Cancelada'),
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
            ])

            // ── Filtros ───────────────────────────────────────────────────────
            ->filters([
                // Tipo (multi-select)
                SelectFilter::make('type')
                    ->label(__('Tipo'))
                    ->multiple()
                    ->options([
                        AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación'),
                        AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación'),
                        AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación'),
                        AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado Revista'),
                        AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado Libro'),
                        AdminTask::TYPE_CONSULTING => __('Consultoría'),
                        AdminTask::TYPE_ORPHAN_PAYMENT => __('Pago Huérfano'),
                    ]),

                // Estado (multi-select)
                SelectFilter::make('status')
                    ->label(__('Estado'))
                    ->multiple()
                    ->options([
                        AdminTask::STATUS_PENDING => __('Pendiente'),
                        AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                        AdminTask::STATUS_SCHEDULED => __('Agendada'),
                        AdminTask::STATUS_IN_SESSION => __('En sesión'),
                        AdminTask::STATUS_COMPLETED => __('Completada'),
                        AdminTask::STATUS_CANCELLED => __('Cancelada'),
                    ]),

                // Asignado a mí
                TernaryFilter::make('assigned_to_me')
                    ->label(__('Asignado a mí'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('assigned_to', auth()->id()),
                        false: fn (Builder $query) => $query->where('assigned_to', '!=', auth()->id()),
                        blank: fn (Builder $query) => $query,
                    ),

                // Sin asignar
                Filter::make('unassigned')
                    ->label(__('Sin asignar'))
                    ->query(fn (Builder $query) => $query->whereNull('assigned_to'))
                    ->toggle(),

                // Vencidas
                Filter::make('overdue')
                    ->label(__('Vencidas'))
                    ->query(fn (Builder $query) => $query
                        ->whereIn('status', AdminTask::STATUSES_OPEN)
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now())
                    )
                    ->toggle(),

                // Solo Express (metadata.is_express)
                Filter::make('express')
                    ->label(__('Solo Express'))
                    ->query(fn (Builder $query) => $query->whereHas('payment', function (Builder $q): void {
                        $q->whereJsonContains('metadata->is_express', true);
                    }))
                    ->toggle(),
            ])

            // ── Acciones de fila ──────────────────────────────────────────────
            ->recordActions([
                ViewAction::make()
                    ->label(__('Ver')),

                \Filament\Actions\ActionGroup::make([
                    // Asignar a…
                    Action::make('assign')
                        ->label(__('Asignar a…'))
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->visible(fn (AdminTask $record): bool => $record->isOpen())
                        ->form([
                            Select::make('user_id')
                                ->label(__('Usuario'))
                                ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (AdminTask $record, array $data): void {
                            $user = User::findOrFail($data['user_id']);
                            $record->assignToUser($user);

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'assignee_id' => $user->id,
                                    'assignee_name' => $user->name,
                                ])
                                ->log(__('Tarea asignada a :name', ['name' => $user->name]));

                            // Notificar al asignado (Sprint 3.6 #32 sub-tarea 7)
                            $user->notify(new \App\Notifications\TaskAssigned($record->fresh()));

                            Notification::make()
                                ->title(__('Tarea asignada a :name', ['name' => $user->name]))
                                ->success()
                                ->send();
                        }),

                    // Iniciar (Fase 1 UX): auto-asigna al admin actual si
                    // estaba sin asignar y redirige a la pagina donde se hace
                    // el trabajo (workUrl segun el tipo de task).
                    Action::make('start')
                        ->label(fn (AdminTask $record): string => match ($record->type) {
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
                        ->visible(fn (AdminTask $record): bool => $record->status === AdminTask::STATUS_PENDING)
                        ->action(function (AdminTask $record) {
                            // Auto-asignar al admin actual si la task estaba sin asignar
                            $autoAssigned = false;
                            if (! $record->assigned_to) {
                                $record->update(['assigned_to' => auth()->id()]);
                                $autoAssigned = true;
                            }

                            $record->start();

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties(['auto_assigned' => $autoAssigned])
                                ->log(__('Tarea iniciada'));

                            Notification::make()
                                ->title(__('Tarea iniciada'))
                                ->body($autoAssigned ? __('Asignada a ti automáticamente. Redirigiendo al trabajo…') : __('Redirigiendo al trabajo…'))
                                ->success()
                                ->send();

                            return redirect()->to($record->workUrl());
                        }),

                    // Continuar (Fase 1 UX): para tasks ya in_progress que
                    // pertenecen al admin actual — atajo para volver al
                    // trabajo sin pasar por el detalle de la task.
                    Action::make('continue_work')
                        ->label(fn (AdminTask $record): string => match ($record->type) {
                            AdminTask::TYPE_EVALUATE_JOURNAL,
                            AdminTask::TYPE_REEVALUATE_JOURNAL,
                            AdminTask::TYPE_RENEWAL_EVALUATION => __('Continuar evaluación'),
                            AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                            AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Continuar revisión'),
                            AdminTask::TYPE_ORPHAN_PAYMENT => __('Seguir investigando'),
                            default => __('Continuar'),
                        })
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color(fn (AdminTask $record): string => $record->isOverdue() ? 'danger' : 'info')
                        ->visible(fn (AdminTask $record): bool => $record->status === AdminTask::STATUS_IN_PROGRESS)
                        ->url(fn (AdminTask $record): string => $record->workUrl()),

                    // Marcar agendada (solo consulting)
                    Action::make('schedule')
                        ->label(__('Marcar agendada'))
                        ->icon('heroicon-o-calendar-days')
                        ->color('indigo')
                        ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING
                            && in_array($record->status, [AdminTask::STATUS_PENDING, AdminTask::STATUS_IN_PROGRESS], true)
                        )
                        ->form([
                            DateTimePicker::make('scheduled_for')
                                ->label(__('Fecha y hora de la sesión'))
                                ->required()
                                ->native(false)
                                ->minDate(now()),
                        ])
                        ->action(function (AdminTask $record, array $data): void {
                            $record->markScheduled(Carbon::parse($data['scheduled_for']));

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties(['scheduled_for' => $data['scheduled_for']])
                                ->log(__('Consultoría agendada para :date', ['date' => $data['scheduled_for']]));

                            Notification::make()
                                ->title(__('Consultoría agendada'))
                                ->success()
                                ->send();
                        }),

                    // Iniciar sesión (solo consulting scheduled)
                    Action::make('in_session')
                        ->label(__('Iniciar sesión'))
                        ->icon('heroicon-o-video-camera')
                        ->color('success')
                        ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING
                            && $record->status === AdminTask::STATUS_SCHEDULED
                        )
                        ->requiresConfirmation()
                        ->modalHeading(__('Iniciar sesión de consultoría'))
                        ->modalDescription(__('¿Confirmar que la sesión ha comenzado?'))
                        ->action(function (AdminTask $record): void {
                            $record->markInSession();

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log(__('Sesión de consultoría iniciada'));

                            Notification::make()
                                ->title(__('Sesión iniciada'))
                                ->success()
                                ->send();
                        }),

                    // Marcar completada — sólo visible en tipos que NO auto-completan
                    // por hook (consulting y orphan_payment). Las evaluaciones y
                    // revisiones de listado se cierran solas al guardar el trabajo
                    // (EvaluateJournal::save / ReviewListing::save / BookObserver).
                    // Si el evaluador marcara completada antes de guardar, dejaría
                    // un estado inconsistente: task=completed pero status=submitted.
                    Action::make('complete')
                        ->label(__('Marcar completada'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (AdminTask $record): bool => $record->isOpen() && in_array(
                            $record->type,
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
                        ->action(function (AdminTask $record, array $data): void {
                            $note = filled($data['note'] ?? null) ? trim($data['note']) : null;
                            $record->complete($note);

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties(array_filter(['note' => $note]))
                                ->log(__('Tarea completada'));

                            Notification::make()
                                ->title(__('Tarea marcada como completada'))
                                ->success()
                                ->send();
                        }),

                    // Cancelar (solo super_admin)
                    Action::make('cancel')
                        ->label(__('Cancelar'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (AdminTask $record): bool => $record->isOpen()
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
                        ->action(function (AdminTask $record, array $data): void {
                            $record->cancel(trim($data['reason']));

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties(['cancelled_reason' => $data['reason']])
                                ->log(__('Tarea cancelada: :reason', ['reason' => $data['reason']]));

                            Notification::make()
                                ->title(__('Tarea cancelada'))
                                ->warning()
                                ->send();
                        }),

                    // Reactivar (solo super_admin, solo tasks cancelled)
                    Action::make('reactivate')
                        ->label(__('Reactivar'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (AdminTask $record): bool => $record->status === AdminTask::STATUS_CANCELLED
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
                        ->action(function (AdminTask $record, array $data): void {
                            $previousReason = $record->cancelled_reason;
                            $record->reactivate(filled($data['reason'] ?? null) ? trim($data['reason']) : null);

                            activity()
                                ->performedOn($record)
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
                        }),
                ])->tooltip(__('Acciones'))->icon('heroicon-m-ellipsis-vertical'),
            ])

            // ── Acciones bulk ─────────────────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    // Asignar a evaluador (bulk)
                    BulkAction::make('bulk_assign')
                        ->label(__('Asignar a…'))
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->form([
                            Select::make('user_id')
                                ->label(__('Usuario'))
                                ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $user = User::findOrFail($data['user_id']);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->isOpen()) {
                                    $record->assignToUser($user);
                                    $user->notify(new \App\Notifications\TaskAssigned($record->fresh()));
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title(__(':count tarea(s) asignadas a :name', ['count' => $count, 'name' => $user->name]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Cambiar prioridad (bulk)
                    BulkAction::make('bulk_priority')
                        ->label(__('Cambiar prioridad'))
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('warning')
                        ->form([
                            Select::make('priority')
                                ->label(__('Prioridad'))
                                ->options([
                                    AdminTask::PRIORITY_HIGH => __('Alta'),
                                    AdminTask::PRIORITY_NORMAL => __('Normal'),
                                    AdminTask::PRIORITY_LOW => __('Baja'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $count = 0;

                            foreach ($records as $record) {
                                $record->update(['priority' => $data['priority']]);
                                $count++;
                            }

                            $label = match ($data['priority']) {
                                AdminTask::PRIORITY_HIGH => __('Alta'),
                                AdminTask::PRIORITY_NORMAL => __('Normal'),
                                AdminTask::PRIORITY_LOW => __('Baja'),
                                default => $data['priority'],
                            };

                            Notification::make()
                                ->title(__(':count tarea(s) con prioridad :priority', ['count' => $count, 'priority' => $label]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Marcar completadas (bulk)
                    BulkAction::make('bulk_complete')
                        ->label(__('Marcar completadas'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('Completar tareas seleccionadas'))
                        ->modalDescription(__('¿Marcar todas las tareas abiertas seleccionadas como completadas?'))
                        ->action(function (Collection $records): void {
                            $count = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if (! $record->isOpen()) {
                                    continue;
                                }

                                // Solo tipos que NO auto-completan vía hook
                                if (! in_array($record->type, [AdminTask::TYPE_CONSULTING, AdminTask::TYPE_ORPHAN_PAYMENT], true)) {
                                    $skipped++;
                                    continue;
                                }

                                $record->complete();

                                activity()
                                    ->performedOn($record)
                                    ->causedBy(auth()->user())
                                    ->log(__('Tarea completada (bulk)'));

                                $count++;
                            }

                            $title = $skipped > 0
                                ? __(':count tarea(s) marcadas, :skipped omitida(s) (auto-completan al guardar)', ['count' => $count, 'skipped' => $skipped])
                                : __(':count tarea(s) marcadas como completadas', ['count' => $count]);

                            Notification::make()
                                ->title($title)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
