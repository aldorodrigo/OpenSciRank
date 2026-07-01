<?php

namespace App\Filament\Widgets;

use App\Models\AdminTask;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Roadmap #35 — cola de trabajo del evaluador (escritorio).
 *
 * Muestra SOLO las tasks asignadas al evaluador logueado (assigned_to). Incluye
 * la cola activa (work queue) y el historial de completadas para trazabilidad
 * de evaluaciones pasadas, ordenando lo activo arriba. El evaluador nunca ve
 * tasks de otros ni las sin asignar (no hay auto-pickup).
 */
class EvaluatorQueue extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('evaluator_desk.queue.heading'))
            ->description(__('evaluator_desk.queue.description'))
            ->query(
                AdminTask::query()
                    ->where('assigned_to', auth()->id())
                    // Roadmap #35 — solo tareas ACTIVAS (pendiente / en progreso…).
                    // El historial de completadas vive en el tab "Completadas" de
                    // Tareas (/admin/admin-tasks), scopeado al evaluador.
                    ->whereIn('status', AdminTask::STATUSES_WORK_QUEUE)
            )
            // En progreso → pendiente → resto, luego por prioridad y vencimiento.
            ->defaultSort(fn (Builder $query) => $query
                ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'proposal_sent', 'scheduled', 'in_session')")
                ->orderByRaw("FIELD(priority, 'high', 'normal', 'low')")
                ->orderByRaw('due_at IS NULL, due_at ASC')
            )
            ->columns([
                TextColumn::make('type')
                    ->label(__('evaluator_desk.queue.col_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::TYPE_EVALUATE_JOURNAL,
                        AdminTask::TYPE_REEVALUATE_JOURNAL => 'primary',
                        AdminTask::TYPE_RENEWAL_EVALUATION => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación'),
                        AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación'),
                        AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación'),
                        default => $state,
                    }),

                TextColumn::make('title_key')
                    ->label(__('evaluator_desk.queue.col_task'))
                    ->formatStateUsing(fn (AdminTask $record): string => $record->renderedTitle())
                    ->wrap(),

                TextColumn::make('priority')
                    ->label(__('evaluator_desk.queue.col_priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::PRIORITY_HIGH => 'danger',
                        AdminTask::PRIORITY_LOW => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::PRIORITY_HIGH => __('Alta'),
                        AdminTask::PRIORITY_NORMAL => __('Normal'),
                        AdminTask::PRIORITY_LOW => __('Baja'),
                        default => $state,
                    }),

                // Badge Express (uplift +$50): el evaluador prioriza estas.
                TextColumn::make('payment.metadata.is_express')
                    ->label(__('evaluator_desk.queue.col_express'))
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-bolt')
                    ->getStateUsing(fn (AdminTask $record): ?string => ($record->payment?->metadata['is_express'] ?? false) ? __('Express') : null)
                    ->placeholder('—')
                    ->tooltip(__('evaluator_desk.queue.express_tooltip')),

                TextColumn::make('due_at')
                    ->label(__('evaluator_desk.queue.col_due'))
                    ->placeholder('—')
                    ->formatStateUsing(function (AdminTask $record): string {
                        if (! $record->due_at) {
                            return '—';
                        }

                        $days = $record->daysUntilDue();
                        $formatted = $record->due_at->format('d/m/Y');

                        if ($record->isOverdue()) {
                            return "{$formatted} · ".__('Expired :days days ago', ['days' => abs($days)]);
                        }

                        return "{$formatted} · ".__('Expires in :days days', ['days' => $days]);
                    })
                    ->color(function (AdminTask $record): string {
                        if (! $record->due_at || $record->status === AdminTask::STATUS_COMPLETED) {
                            return 'gray';
                        }

                        if ($record->isOverdue()) {
                            return 'danger';
                        }

                        $days = $record->daysUntilDue();

                        return $days < 3 ? 'danger' : ($days < 7 ? 'warning' : 'success');
                    }),

                TextColumn::make('status')
                    ->label(__('evaluator_desk.queue.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AdminTask::STATUS_PENDING => 'gray',
                        AdminTask::STATUS_IN_PROGRESS => 'info',
                        AdminTask::STATUS_COMPLETED => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AdminTask::STATUS_PENDING => __('Pendiente'),
                        AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                        AdminTask::STATUS_PROPOSAL_SENT => __('Propuesta enviada'),
                        AdminTask::STATUS_SCHEDULED => __('Agendada'),
                        AdminTask::STATUS_IN_SESSION => __('En sesión'),
                        AdminTask::STATUS_COMPLETED => __('Completada'),
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('evaluator_desk.queue.col_status'))
                    ->options([
                        AdminTask::STATUS_PENDING => __('Pendiente'),
                        AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                    ]),
            ])
            ->recordActions([
                Action::make('work')
                    ->label(fn (AdminTask $record): string => $record->status === AdminTask::STATUS_PENDING
                        ? __('evaluator_desk.queue.action_start')
                        : __('evaluator_desk.queue.action_continue')
                    )
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color(fn (AdminTask $record): string => $record->isOverdue() ? 'danger' : 'primary')
                    // Solo tasks abiertas se "trabajan"; las completadas son solo lectura.
                    ->visible(fn (AdminTask $record): bool => $record->isOpen())
                    ->action(function (AdminTask $record) {
                        // Auto-iniciar si estaba pendiente (marca started_at) y
                        // llevar a la página de trabajo (evaluación del journal).
                        if ($record->status === AdminTask::STATUS_PENDING) {
                            $record->start();
                        }

                        return redirect()->to($record->workUrl());
                    }),
            ])
            ->emptyStateHeading(__('evaluator_desk.queue.empty_heading'))
            ->emptyStateDescription(__('evaluator_desk.queue.empty_description'))
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->paginated([10, 25, 50]);
    }
}
