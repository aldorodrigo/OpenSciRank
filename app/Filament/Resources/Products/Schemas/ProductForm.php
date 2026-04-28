<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Contenido')
                    ->schema([
                        Tabs::make('name_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    TextInput::make('name.es')
                                        ->label('Nombre del Producto (ES)')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                                Tab::make('EN')->schema([
                                    TextInput::make('name.en')
                                        ->label('Product Name (EN)')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    TextInput::make('name.pt')
                                        ->label('Nome do Produto (PT)')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Tabs::make('description_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    RichEditor::make('description.es')
                                        ->label('Descripción (ES)'),
                                ]),
                                Tab::make('EN')->schema([
                                    RichEditor::make('description.en')
                                        ->label('Description (EN)'),
                                ]),
                                Tab::make('PT')->schema([
                                    RichEditor::make('description.pt')
                                        ->label('Descrição (PT)'),
                                ]),
                            ]),

                        Select::make('primary_locale')
                            ->label('Idioma principal')
                            ->options(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'])
                            ->default('es')
                            ->required(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Identificador interno (ej: journal-evaluation). No traducible.'),
                    ])
                    ->columns(2),

                Section::make('Precio y disponibilidad')
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Select::make('currency')
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
                    ])
                    ->columns(2),
            ]);
    }
}
