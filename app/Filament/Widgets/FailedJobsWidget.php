<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Contracts\QueueMonitorWidget;
use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Artisan;

/**
 * #59 — gestión granular de jobs fallidos: reintentar, borrar o inspeccionar la
 * excepción completa de UN job (antes solo había reintentar-todos/vaciar-todos en
 * la página). Sobre el modelo read-only FailedJob (tabla `failed_jobs`).
 */
class FailedJobsWidget extends BaseWidget implements QueueMonitorWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.queue_monitor.recent_failures'))
            ->query(FailedJob::query())
            ->defaultSort('failed_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('queue')
                    ->label(__('admin.queue_monitor.queue_column'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('job_name')
                    ->label(__('admin.queue_monitor.job'))
                    ->getStateUsing(fn (FailedJob $record): string => $record->jobName())
                    ->wrap(),

                TextColumn::make('failed_at')
                    ->label(__('admin.queue_monitor.failed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('exception')
                    ->label(__('admin.queue_monitor.exception'))
                    ->getStateUsing(fn (FailedJob $record): string => $record->exceptionFirstLine())
                    ->color('danger')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('inspect')
                    ->label(__('admin.queue_monitor.inspect'))
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->modalHeading(fn (FailedJob $record): string => $record->jobName())
                    ->modalContent(fn (FailedJob $record) => view('filament.widgets.failed-job-detail', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false),

                Action::make('retry')
                    ->label(__('admin.queue_monitor.retry_one'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()
                            ->title(__('admin.queue_monitor.retry_one_success'))
                            ->success()
                            ->send();
                    }),

                Action::make('forget')
                    ->label(__('admin.queue_monitor.forget_one'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:forget', ['id' => $record->uuid]);

                        Notification::make()
                            ->title(__('admin.queue_monitor.forget_one_success'))
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading(__('admin.queue_monitor.no_failures'))
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([10, 25, 50]);
    }
}
