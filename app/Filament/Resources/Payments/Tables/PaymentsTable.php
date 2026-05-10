<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Exports\PaymentExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Comprador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->formatStateUsing(fn ($record): string => $record->product?->getTranslationWithFallback('name') ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->label('ID Transacción')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'info',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->exporter(PaymentExporter::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar seleccionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(PaymentExporter::class),
                ]),
            ]);
    }
}
