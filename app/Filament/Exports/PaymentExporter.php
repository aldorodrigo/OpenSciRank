<?php

namespace App\Filament\Exports;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label(__('admin.export.payment.id')),
            ExportColumn::make('created_at')->label(__('admin.export.payment.date')),
            ExportColumn::make('user.name')->label(__('admin.export.payment.buyer')),
            ExportColumn::make('user.email')->label(__('admin.export.payment.buyer_email')),
            ExportColumn::make('product.name')
                ->label(__('admin.export.payment.product'))
                ->formatStateUsing(fn (Payment $record): string => $record->product?->getTranslationWithFallback('name') ?? ''),
            ExportColumn::make('payable_type')
                ->label(__('admin.export.payment.type'))
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    Journal::class => 'Revista',
                    Book::class => 'Libro',
                    default => $state ?? '',
                }),
            ExportColumn::make('payable_id')->label(__('admin.export.payment.entity_id')),
            ExportColumn::make('amount')->label(__('admin.export.payment.amount')),
            ExportColumn::make('currency')->label(__('admin.export.payment.currency')),
            ExportColumn::make('status')->label(__('admin.export.payment.status')),
            ExportColumn::make('provider')->label(__('admin.export.payment.provider')),
            ExportColumn::make('transaction_id')->label(__('admin.export.payment.transaction_id')),
            ExportColumn::make('stripe_session_id')->label(__('admin.export.payment.stripe_session')),
            ExportColumn::make('coupon.code')->label(__('admin.export.payment.coupon')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de pagos se ha completado con ' . number_format($export->successful_rows) . ' ' . str('fila')->plural($export->successful_rows) . ' exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron al exportarse.';
        }

        return $body;
    }
}
