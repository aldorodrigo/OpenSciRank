<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\JournalOaiActions;
use App\Filament\Widgets\Contracts\QueueMonitorWidget;
use App\Jobs\HarvestJournalArticles;
use App\Models\Journal;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;

/**
 * #59 — administración operativa de la cola de cosechas OAI, por revista.
 * Estado, artículos, error y acciones (cosechar / probar conexión / destrabar)
 * reusando el factory JournalOaiActions, más re-cosecha masiva.
 */
class OaiHarvestQueueWidget extends BaseWidget implements QueueMonitorWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.queue_monitor.harvests_heading'))
            ->query(
                Journal::query()
                    ->whereNotNull('oai_base_url')
                    ->withCount('harvestedArticles')
            )
            ->defaultSort('oai_last_harvested_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.queue_monitor.harvest_journal'))
                    ->getStateUsing(fn (Journal $record): string => $record->title)
                    ->wrap()
                    ->limit(60),

                TextColumn::make('oai_harvest_status')
                    ->label(__('admin.queue_monitor.harvest_status'))
                    ->badge()
                    ->placeholder(__('admin.queue_monitor.harvest_never'))
                    ->color(fn (?string $state): string => match ($state) {
                        'running' => 'info',
                        'idle' => 'success',
                        'failed' => 'danger',
                        'queued' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? __('admin.journal.oai_harvest_status_'.$state)
                        : __('admin.queue_monitor.harvest_never')),

                TextColumn::make('harvested_articles_count')
                    ->label(__('admin.queue_monitor.harvest_articles'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('oai_last_harvested_at')
                    ->label(__('admin.queue_monitor.harvest_last'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('oai_last_harvest_error')
                    ->label(__('admin.queue_monitor.harvest_error'))
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('oai_harvest_status')
                    ->label(__('admin.queue_monitor.harvest_status'))
                    ->options([
                        'queued' => __('admin.journal.oai_harvest_status_queued'),
                        'running' => __('admin.journal.oai_harvest_status_running'),
                        'idle' => __('admin.journal.oai_harvest_status_idle'),
                        'failed' => __('admin.journal.oai_harvest_status_failed'),
                    ]),
            ])
            ->recordActions([
                JournalOaiActions::harvest(),
                JournalOaiActions::testConnection(),
                JournalOaiActions::reset(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('harvest_selected')
                        ->label(__('admin.queue_monitor.harvest_bulk'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $queued = 0;
                            foreach ($records as $journal) {
                                if (empty($journal->oai_base_url)) {
                                    continue;
                                }
                                $journal->update(['oai_harvest_status' => 'queued']);
                                HarvestJournalArticles::dispatch($journal, causedByUserId: auth()->id());
                                $queued++;
                            }

                            Notification::make()
                                ->title(__('admin.queue_monitor.harvest_bulk_done', ['count' => $queued]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }
}
