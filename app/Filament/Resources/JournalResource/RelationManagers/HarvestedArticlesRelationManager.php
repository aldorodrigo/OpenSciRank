<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class HarvestedArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestedArticles';
    protected static ?string $title = 'Artículos Cosechados (OAI)';
    protected static ?string $modelLabel = 'Artículo';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('authors')
                    ->label('Autores')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('url')
                    ->label('Página')
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->placeholder('—')
                    ->limit(1)
                    ->formatStateUsing(fn () => 'Ver'),
                Tables\Columns\TextColumn::make('pdf_url')
                    ->label('PDF')
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
