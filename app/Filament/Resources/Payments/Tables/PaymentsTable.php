<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Exports\PaymentExporter;
use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use Filament\Actions\BulkActionGroup;
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
                TextColumn::make('payable_id')
                    ->label('Revista / Libro')
                    ->formatStateUsing(function ($record): string {
                        if ($record->payable === null) {
                            return 'Pago huérfano';
                        }

                        $tipo = match ($record->payable_type) {
                            'App\\Models\\Journal' => 'Revista',
                            'App\\Models\\Book' => 'Libro',
                            default => $record->payable_type,
                        };

                        $nombre = $record->payable->getTranslationWithFallback('title');

                        return "[{$tipo}] {$nombre}";
                    })
                    ->color(fn ($record): string => $record->payable === null ? 'danger' : 'primary')
                    ->url(fn ($record): ?string => match (true) {
                        $record->payable === null => null,
                        $record->payable_type === 'App\\Models\\Journal' => JournalResource::getUrl('edit', ['record' => $record->payable_id]),
                        $record->payable_type === 'App\\Models\\Book' => BookResource::getUrl('edit', ['record' => $record->payable_id]),
                        default => null,
                    })
                    ->openUrlInNewTab()
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHasMorph(
                            'payable',
                            [\App\Models\Journal::class, \App\Models\Book::class],
                            fn ($q) => $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.*')) LIKE ?", ["%{$search}%"])
                        );
                    }),
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
                \Filament\Tables\Filters\TernaryFilter::make('orphan')
                    ->label('Pagos huérfanos')
                    ->nullable()
                    ->attribute('error_note')
                    ->trueLabel('Solo huérfanos')
                    ->falseLabel('Sin errores')
                    ->placeholder('Todos'),
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
