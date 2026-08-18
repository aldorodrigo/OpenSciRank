<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use App\Filament\Actions\JournalOaiActions;
use App\Models\Journal;
use App\Models\JournalMetricSnapshot;
use Filament\Actions;
use Filament\Forms;
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
            // Refresco de métricas y alta de snapshot Scholar comparten la factory
            // JournalOaiActions (misma lógica que las header actions de las páginas).
            ->headerActions([
                JournalOaiActions::refreshMetrics(fn (): Journal => $this->getOwnerRecord()),
                JournalOaiActions::registerScholar(fn (): Journal => $this->getOwnerRecord()),
            ])
            ->actions([])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
