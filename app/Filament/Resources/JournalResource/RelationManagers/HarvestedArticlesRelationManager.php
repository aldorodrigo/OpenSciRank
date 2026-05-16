<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
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
