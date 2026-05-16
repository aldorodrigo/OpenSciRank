<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.user.name'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label(__('admin.user.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label(__('admin.user.roles'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label(__('admin.user.created_at'))
                    ->dateTime('d/m/Y')
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
