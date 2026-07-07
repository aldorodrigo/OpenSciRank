<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use App\Models\Journal;
use App\Models\JournalMetricSnapshot;
use App\Services\Metrics\JournalMetricsService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MetricSnapshotsRelationManager extends RelationManager
{
    protected static string $relationship = 'metricSnapshots';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.metrics.snapshots_title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.metrics.snapshot_label');
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('h_index')
                ->label(__('admin.metrics.h_index'))
                ->numeric()
                ->minValue(0),
            Forms\Components\TextInput::make('total_citations')
                ->label(__('admin.metrics.total_citations'))
                ->numeric()
                ->minValue(0),
            Forms\Components\Textarea::make('notes')
                ->label(__('admin.metrics.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('captured_at')
                    ->label(__('admin.metrics.captured_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label(__('admin.metrics.source'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('admin.journal.metrics_source_'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        JournalMetricSnapshot::SOURCE_OPENALEX => 'success',
                        JournalMetricSnapshot::SOURCE_CROSSREF => 'info',
                        JournalMetricSnapshot::SOURCE_SCHOLAR_MANUAL => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('h_index')
                    ->label(__('admin.metrics.h_index'))
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_citations')
                    ->label(__('admin.metrics.total_citations'))
                    ->numeric()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('mean_citedness_2y')
                    ->label(__('admin.metrics.mean_citedness'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('capturedBy.name')
                    ->label(__('admin.metrics.captured_by'))
                    ->placeholder('—'),
            ])
            // El historial solo muestra capturas con datos; las fuentes que fallan no se registran.
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('failed', false))
            ->defaultSort('captured_at', 'desc')
            ->headerActions([
                Actions\Action::make('refresh_metrics')
                    ->label(__('admin.metrics.action_refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.metrics.confirm_refresh_heading'))
                    ->modalDescription(__('admin.metrics.confirm_refresh_body'))
                    ->action(function (): void {
                        /** @var Journal $journal */
                        $journal = $this->getOwnerRecord();
                        $result = app(JournalMetricsService::class)->refresh($journal);

                        // Ninguna fuente devolvió datos: avisar el motivo, no un falso éxito.
                        if (! $result->hasAnyData()) {
                            Notification::make()
                                ->title(__('admin.metrics.notif_refresh_failed'))
                                ->body($result->errorSummary() ?: __('admin.metrics.notif_refresh_failed_body'))
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $notification = Notification::make()
                            ->title(__('admin.metrics.notif_refreshed'))
                            ->success();

                        // Éxito parcial: alguna fuente falló, mostrar cuál y por qué.
                        if ($result->hasErrors()) {
                            $notification
                                ->body(__('admin.metrics.notif_refresh_partial', ['detail' => $result->errorSummary()]))
                                ->warning();
                        }

                        $notification->send();
                    }),
                Actions\Action::make('register_scholar')
                    ->label(__('admin.metrics.action_register_scholar'))
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('h_index')
                            ->label(__('admin.metrics.h_index'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('total_citations')
                            ->label(__('admin.metrics.total_citations'))
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('profile_url')
                            ->label(__('admin.metrics.scholar_profile_url'))
                            ->url()
                            ->placeholder('https://scholar.google.com/citations?user=...'),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.metrics.notes'))
                            ->rows(2),
                    ])
                    ->action(function (array $data): void {
                        /** @var Journal $journal */
                        $journal = $this->getOwnerRecord();
                        app(JournalMetricsService::class)->registerScholarSnapshot(
                            journal: $journal,
                            hIndex: $data['h_index'] !== null ? (int) $data['h_index'] : null,
                            totalCitations: isset($data['total_citations']) && $data['total_citations'] !== null
                                ? (int) $data['total_citations']
                                : null,
                            capturedByUserId: auth()->id(),
                            profileUrl: $data['profile_url'] ?? null,
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()
                            ->title(__('admin.metrics.notif_scholar_registered'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
