<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.product.name'))
                    ->formatStateUsing(fn (Product $record): string => $record->getTranslationWithFallback('name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin.product.slug'))
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('admin.product.base_price'))
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.product.active'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('admin.product.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
