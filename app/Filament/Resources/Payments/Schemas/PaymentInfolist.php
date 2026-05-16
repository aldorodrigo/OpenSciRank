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
                    ->label(__('admin.payment.buyer'))
                    ->placeholder('-'),
                TextEntry::make('product.name')
                    ->label(__('admin.payment.product'))
                    ->formatStateUsing(fn ($record): string => $record->product?->getTranslationWithFallback('name') ?? '-')
                    ->placeholder('-'),
                TextEntry::make('coupon_id')
                    ->label(__('admin.payment.coupon'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('provider')
                    ->label(__('admin.payment.provider')),
                TextEntry::make('transaction_id')
                    ->label(__('admin.payment.transaction_id_short'))
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->label(__('admin.payment.amount'))
                    ->money(fn ($record) => $record->currency ?? 'USD'),
                TextEntry::make('currency')
                    ->label(__('admin.payment.currency')),
                // Servicio Express uplift (Sprint 3.6: indicador visual)
                TextEntry::make('express_indicator')
                    ->label(__('admin.payment.express'))
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-bolt')
                    ->getStateUsing(fn ($record): string => ($record->metadata['is_express'] ?? false) ? 'Sí' : 'No'),
                // Desglose del pago: items + total. Util para finanzas.
                TextEntry::make('breakdown')
                    ->label(__('admin.payment.breakdown'))
                    ->getStateUsing(function ($record): string {
                        $b = $record->breakdown();
                        $lines = [];
                        $lines[] = $b['main']['name'].': $'.number_format($b['main']['price'], 0);
                        if ($b['express']) {
                            $lines[] = 'Express: $'.number_format($b['express'], 0);
                        }
                        foreach ($b['addons'] as $addon) {
                            $lines[] = $addon['name'].': $'.number_format($addon['price'], 0);
                        }
                        if ($b['discount'] > 0) {
                            $lines[] = 'Descuento: −$'.number_format($b['discount'], 0);
                        }
                        $lines[] = '**Total: $'.number_format($b['amount'], 0).' '.$b['currency'].'**';

                        return implode("\n", $lines);
                    })
                    ->markdown()
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->label(__('admin.payment.status')),
                TextEntry::make('created_at')
                    ->label(__('admin.payment.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('admin.payment.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('payable_id')
                    ->label(__('admin.payment.related_resource'))
                    ->formatStateUsing(function ($record): string {
                        if ($record->payable === null) {
                            return __('admin.payment.orphan_label');
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
                    ->placeholder(__('admin.common.not_associated')),
            ]);
    }
}
