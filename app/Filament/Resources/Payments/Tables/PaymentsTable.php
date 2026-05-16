<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Exports\PaymentExporter;
use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use App\Models\Payment;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.payment.buyer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('admin.payment.product'))
                    ->formatStateUsing(fn ($record): string => $record->product?->getTranslationWithFallback('name') ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payable_id')
                    ->label(__('admin.payment.related_resource'))
                    ->formatStateUsing(function ($record): string {
                        if ($record->payable === null) {
                            return __('admin.payment.orphan_short');
                        }

                        $tipo = match ($record->payable_type) {
                            'App\\Models\\Journal' => 'Revista',
                            'App\\Models\\Book' => 'Libro',
                            'App\\Models\\User' => __('Editor'),
                            default => $record->payable_type,
                        };

                        // Sprint 3.7 #46: User no tiene getTranslationWithFallback;
                        // delegamos al helper centralizado.
                        $nombre = \App\Support\PaymentPayableResolver::payableDisplayName($record->payable);

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
                    ->label(__('admin.payment.amount'))
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                // Express uplift (Sprint 3.6: indicador visual para el admin)
                TextColumn::make('express_indicator')
                    ->label(__('admin.payment.express_short'))
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-bolt')
                    ->getStateUsing(fn ($record): ?string => ($record->metadata['is_express'] ?? false) ? 'Express' : null)
                    ->placeholder('—')
                    ->tooltip(__('admin.payment.express_tooltip'))
                    ->toggleable(),
                TextColumn::make('provider')
                    ->label(__('admin.payment.provider'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->label(__('admin.payment.transaction_id_short'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('admin.payment.status'))
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
                    ->label(__('admin.payment.date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.payment.status'))
                    ->multiple()
                    ->options([
                        'pending' => __('admin.payment_status.pending'),
                        'completed' => __('admin.payment_status.completed'),
                        'failed' => __('admin.payment_status.failed'),
                        'refunded' => __('admin.payment_status.refunded'),
                    ]),

                // Filtro por producto (multi-select): permite ver sólo
                // evaluaciones, sólo renovaciones, sólo consultoría, etc.
                SelectFilter::make('product_id')
                    ->label(__('Producto'))
                    ->multiple()
                    ->searchable()
                    ->options(fn () => Product::query()
                        ->orderBy('slug')
                        ->get()
                        ->mapWithKeys(fn (Product $p) => [
                            $p->id => $p->getTranslationWithFallback('name').' ('.$p->slug.')',
                        ])
                        ->all()
                    ),

                // Tipo de recurso al que aplicó el pago (Revista vs Libro)
                SelectFilter::make('payable_type')
                    ->label(__('Tipo de recurso'))
                    ->options([
                        \App\Models\Journal::class => __('Revista'),
                        \App\Models\Book::class => __('Libro'),
                    ]),

                // Rango de fechas del pago (created_at)
                Filter::make('payment_date')
                    ->label(__('Rango de fechas'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('Desde'))
                            ->native(false),
                        DatePicker::make('to')
                            ->label(__('Hasta'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['from'])) {
                            $indicators[] = __('Desde: :date', ['date' => \Carbon\Carbon::parse($data['from'])->format('d/m/Y')]);
                        }
                        if (! empty($data['to'])) {
                            $indicators[] = __('Hasta: :date', ['date' => \Carbon\Carbon::parse($data['to'])->format('d/m/Y')]);
                        }
                        return $indicators;
                    }),

                // Rango de monto (en la moneda del pago, sin conversión)
                Filter::make('amount_range')
                    ->label(__('Rango de monto'))
                    ->schema([
                        TextInput::make('min')
                            ->label(__('Mínimo'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('max')
                            ->label(__('Máximo'))
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'] ?? null, fn (Builder $q, $v) => $q->where('amount', '>=', $v))
                            ->when($data['max'] ?? null, fn (Builder $q, $v) => $q->where('amount', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['min'])) {
                            $indicators[] = __('Mín: $:amount', ['amount' => $data['min']]);
                        }
                        if (! empty($data['max'])) {
                            $indicators[] = __('Máx: $:amount', ['amount' => $data['max']]);
                        }
                        return $indicators;
                    }),

                // Sólo pagos con uplift Express
                TernaryFilter::make('express')
                    ->label(__('Servicio Express'))
                    ->nullable()
                    ->trueLabel(__('Solo Express'))
                    ->falseLabel(__('Sin Express'))
                    ->placeholder(__('admin.common.all'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereJsonContains('metadata->is_express', true),
                        false: fn (Builder $q) => $q->where(function (Builder $sub) {
                            $sub->whereNull('metadata->is_express')
                                ->orWhereJsonContains('metadata->is_express', false);
                        }),
                        blank: fn (Builder $q) => $q,
                    ),

                // Pagos que tuvieron cupón aplicado
                TernaryFilter::make('has_coupon')
                    ->label(__('Cupón'))
                    ->nullable()
                    ->trueLabel(__('Con cupón'))
                    ->falseLabel(__('Sin cupón'))
                    ->placeholder(__('admin.common.all'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('metadata->coupon_code')
                            ->where('metadata->coupon_code', '!=', ''),
                        false: fn (Builder $q) => $q->where(function (Builder $sub) {
                            $sub->whereNull('metadata->coupon_code')
                                ->orWhere('metadata->coupon_code', '');
                        }),
                        blank: fn (Builder $q) => $q,
                    ),

                // Pagos huérfanos (payable_id apunta a registro borrado)
                TernaryFilter::make('orphan')
                    ->label(__('admin.payment.orphan_filter'))
                    ->nullable()
                    ->attribute('error_note')
                    ->trueLabel(__('admin.payment.orphan_only'))
                    ->falseLabel(__('admin.payment.orphan_clean'))
                    ->placeholder(__('admin.common.all')),
            ])
            ->filtersFormColumns(2)
            ->headerActions([
                ExportAction::make()
                    ->label(__('admin.common.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->exporter(PaymentExporter::class),
            ])
            ->recordActions([
                Action::make('view_detail')
                    ->label(__('Ver'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Payment $record): string => __('Pago').' #'.$record->id)
                    ->modalDescription(__('Detalle del pago, desglose y tareas administrativas relacionadas.'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Cerrar'))
                    ->modalWidth('5xl')
                    ->modalContent(fn (Payment $record): View => view(
                        'filament.payments.payment-detail-modal',
                        ['payment' => $record->load(['user', 'product', 'payable', 'adminTasks.assignee'])]
                    )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('admin.common.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(PaymentExporter::class),
                ]),
            ]);
    }
}
