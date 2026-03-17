<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Group::make([
                    \Filament\Forms\Components\Section::make('Detalles del Pago')
                        ->schema([
                            TextInput::make('transaction_id')
                                ->label('ID de Transacción (Stripe/Provider)'),
                            TextInput::make('provider')
                                ->label('Proveedor'),
                            TextInput::make('amount')
                                ->label('Monto')
                                ->prefix('$'),
                            TextInput::make('currency')
                                ->label('Moneda'),
                            TextInput::make('status')
                                ->label('Estado'),
                        ])->columns(2),

                    \Filament\Forms\Components\Section::make('Entidades Relacionadas')
                        ->schema([
                            \Filament\Forms\Components\Select::make('user_id')
                                ->label('Usuario (Comprador)')
                                ->relationship('user', 'name'),
                            \Filament\Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->relationship('product', 'name'),
                            TextInput::make('payable_type')
                                ->label('Entidad a la que aplica el pago'),
                            TextInput::make('payable_id')
                                ->label('ID Entidad'),
                        ])->columns(2),

                    \Filament\Forms\Components\Section::make('Metadata Técnica')
                        ->schema([
                            \Filament\Forms\Components\KeyValue::make('metadata')
                                ->label('Metadatos Internos'),
                        ]),
                ])->disabled(),
            ]);
    }
}
