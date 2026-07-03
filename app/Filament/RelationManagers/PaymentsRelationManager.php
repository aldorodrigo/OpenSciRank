<?php

namespace App\Filament\RelationManagers;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static string|BackedEnum|null $icon = 'heroicon-o-credit-card';

    /**
     * Roadmap #35 — los pagos (montos, transacciones) son información comercial
     * exclusiva del super_admin. Defensa en profundidad: aunque el evaluator ya
     * no alcanza EditJournal (único page que monta este relation manager), lo
     * blindamos igual por si otra página lo reusa.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.payments_rel.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.payments_rel.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.payments_rel.plural');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.payments_rel.product'))
                    ->formatStateUsing(fn (Model $record): string => $record->product?->getTranslationWithFallback('name') ?? '—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label(__('admin.payments_rel.amount'))
                    ->money(fn (Model $record) => $record->currency ?? 'USD')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label(__('admin.payments_rel.total_paid'))
                            ->query(fn (QueryBuilder $query) => $query->where('status', 'completed'))
                            ->money('USD')
                    ),

                TextColumn::make('status')
                    ->label(__('admin.payments_rel.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('admin.payment_status.pending'),
                        'completed' => __('admin.payment_status.completed'),
                        'failed' => __('admin.payment_status.failed'),
                        'refunded' => __('admin.payment_status.refunded'),
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label(__('admin.payments_rel.date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Model $record): string => $record->created_at->locale(app()->getLocale())->diffForHumans()),

                TextColumn::make('transaction_id')
                    ->label(__('admin.payments_rel.transaction_id'))
                    ->copyable()
                    ->limit(24)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.payments_rel.status'))
                    ->options([
                        'pending' => __('admin.payment_status.pending'),
                        'completed' => __('admin.payment_status.completed'),
                        'failed' => __('admin.payment_status.failed'),
                        'refunded' => __('admin.payment_status.refunded'),
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Action::make('ver_en_stripe')
                    ->label(__('admin.payments_rel.view_on_stripe'))
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
