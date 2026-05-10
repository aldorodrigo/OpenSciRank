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
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('created_at')->label('Fecha'),
            ExportColumn::make('user.name')->label('Comprador'),
            ExportColumn::make('user.email')->label('Email Comprador'),
            ExportColumn::make('product.name')
                ->label('Producto')
                ->formatStateUsing(fn (Payment $record): string => $record->product?->getTranslationWithFallback('name') ?? ''),
            ExportColumn::make('payable_type')
                ->label('Tipo')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    Journal::class => 'Revista',
                    Book::class => 'Libro',
                    default => $state ?? '',
                }),
            ExportColumn::make('payable_id')->label('ID Entidad'),
            ExportColumn::make('amount')->label('Monto'),
            ExportColumn::make('currency')->label('Moneda'),
            ExportColumn::make('status')->label('Estado'),
            ExportColumn::make('provider')->label('Proveedor'),
            ExportColumn::make('transaction_id')->label('ID Transacción'),
            ExportColumn::make('stripe_session_id')->label('ID Sesión Stripe'),
            ExportColumn::make('coupon.code')->label('Cupón'),
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
