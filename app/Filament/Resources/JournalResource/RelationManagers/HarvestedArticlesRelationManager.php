<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use App\Filament\Actions\JournalOaiActions;
use App\Models\Journal;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HarvestedArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestedArticles';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.harvested.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.harvested.model');
    }

    /**
     * Badge en la tab con el estado de cosecha, solo cuando hay algo que mirar
     * (encolada / corriendo / falló). En idle o sin cosechar no muestra badge.
     */
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Journal $ownerRecord */
        $status = $ownerRecord->oai_harvest_status;

        if (! in_array($status, ['queued', 'running', 'failed'], true)) {
            return null;
        }

        return __('admin.journal.oai_harvest_status_'.$status);
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Journal $ownerRecord */
        return match ($ownerRecord->oai_harvest_status) {
            'running' => 'info',
            'failed' => 'danger',
            'queued' => 'gray',
            default => null,
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.harvested.title_col'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('authors')
                    ->label(__('admin.harvested.authors'))
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.harvested.date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('language')
                    ->label(__('admin.harvested.language'))
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('admin.harvested.page'))
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->placeholder('—')
                    ->limit(1)
                    ->formatStateUsing(fn () => 'Ver'),
                Tables\Columns\TextColumn::make('pdf_url')
                    ->label(__('admin.harvested.pdf'))
                    ->url(fn ($record) => $record->pdf_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-document-arrow-down')
                    ->placeholder('—')
                    ->limit(1)
                    ->formatStateUsing(fn () => 'PDF'),
            ])
            ->defaultSort('date', 'desc')
            // Refresco automático mientras una cosecha está en curso (estado + tabla).
            ->poll('10s')
            ->headerActions([
                JournalOaiActions::testConnection(fn (): Journal => $this->getOwnerRecord()),
                JournalOaiActions::harvest(fn (): Journal => $this->getOwnerRecord()),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
