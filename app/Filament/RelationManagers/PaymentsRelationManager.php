<?php

namespace App\Filament\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pagos';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    protected static string|BackedEnum|null $icon = 'heroicon-o-credit-card';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->formatStateUsing(fn (Model $record): string => $record->product?->getTranslationWithFallback('name') ?? '—')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money(fn (Model $record) => $record->currency ?? 'USD')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total pagado (completados)')
                            ->query(fn (Builder $query) => $query->where('status', 'completed'))
                            ->money('USD')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Model $record): string => $record->created_at->locale('es')->diffForHumans()),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID Transacción')
                    ->copyable()
                    ->limit(24)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('ver_en_stripe')
                    ->label('Ver en Stripe')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Model $record): ?string => filled($record->transaction_id)
                        ? "https://dashboard.stripe.com/payments/{$record->transaction_id}"
                        : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Model $record): bool => filled($record->transaction_id)),
            ])
            ->bulkActions([]);
    }
}
