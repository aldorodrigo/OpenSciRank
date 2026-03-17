<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Producto')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\RichEditor::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                \Filament\Forms\Components\Select::make('currency')
                    ->label('Moneda')
                    ->options([
                        'USD' => 'USD - Dólar Estadounidense',
                        'ARS' => 'ARS - Peso Argentino',
                        'EUR' => 'EUR - Euro',
                        'MXN' => 'MXN - Peso Mexicano',
                        'COP' => 'COP - Peso Colombiano',
                        'CLP' => 'CLP - Peso Chileno',
                        'PEN' => 'PEN - Sol Peruano',
                    ])
                    ->required()
                    ->default('USD'),
                Toggle::make('is_active')
                    ->label('Activo (Disponible para compra)')
                    ->default(true)
                    ->required(),
            ]);
    }
}
