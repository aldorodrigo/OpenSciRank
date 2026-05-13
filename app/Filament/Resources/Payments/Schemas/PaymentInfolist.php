<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Comprador')
                    ->placeholder('-'),
                TextEntry::make('product.name')
                    ->label('Producto')
                    ->formatStateUsing(fn ($record): string => $record->product?->getTranslationWithFallback('name') ?? '-')
                    ->placeholder('-'),
                TextEntry::make('coupon_id')
                    ->label('Cupón')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('provider')
                    ->label('Proveedor'),
                TextEntry::make('transaction_id')
                    ->label('ID Transacción')
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->currency ?? 'USD'),
                TextEntry::make('currency')
                    ->label('Moneda'),
                TextEntry::make('status')
                    ->label('Estado'),
                TextEntry::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('payable_id')
                    ->label('Revista / Libro')
                    ->formatStateUsing(function ($record): string {
                        if ($record->payable === null) {
                            return 'Pago huérfano (registro eliminado)';
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
                    ->placeholder('Sin asociar'),
            ]);
    }
}
