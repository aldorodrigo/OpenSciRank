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
                Section::make(__('admin.product.section_content'))
                    ->schema([
                        Tabs::make('name_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    TextInput::make('name.es')
                                        ->label(__('admin.product.name') . ' (' . __('admin.common.lang_suffix.es') . ')')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                                Tab::make('EN')->schema([
                                    TextInput::make('name.en')
                                        ->label(__('admin.product.name') . ' (' . __('admin.common.lang_suffix.en') . ')')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    TextInput::make('name.pt')
                                        ->label(__('admin.product.name') . ' (' . __('admin.common.lang_suffix.pt') . ')')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Tabs::make('description_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    RichEditor::make('description.es')
                                        ->label(__('admin.product.description') . ' (' . __('admin.common.lang_suffix.es') . ')'),
                                ]),
                                Tab::make('EN')->schema([
                                    RichEditor::make('description.en')
                                        ->label(__('admin.product.description') . ' (' . __('admin.common.lang_suffix.en') . ')'),
                                ]),
                                Tab::make('PT')->schema([
                                    RichEditor::make('description.pt')
                                        ->label(__('admin.product.description') . ' (' . __('admin.common.lang_suffix.pt') . ')'),
                                ]),
                            ]),

                        Select::make('primary_locale')
                            ->label(__('admin.common.language_primary'))
                            ->options([
                                'es' => __('admin.common.language_options.es'),
                                'en' => __('admin.common.language_options.en'),
                                'pt' => __('admin.common.language_options.pt'),
                            ])
                            ->default('es')
                            ->required(),

                        TextInput::make('slug')
                            ->label(__('admin.product.slug'))
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.product.slug_help')),
                    ])
                    ->columns(2),

                Section::make(__('admin.product.section_pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('admin.product.price'))
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Select::make('currency')
                            ->label(__('admin.product.currency'))
                            ->options([
                                'USD' => __('admin.product.currencies.USD'),
                                'ARS' => __('admin.product.currencies.ARS'),
                                'EUR' => __('admin.product.currencies.EUR'),
                                'MXN' => __('admin.product.currencies.MXN'),
                                'COP' => __('admin.product.currencies.COP'),
                                'CLP' => __('admin.product.currencies.CLP'),
                                'PEN' => __('admin.product.currencies.PEN'),
                            ])
                            ->required()
                            ->default('USD'),

                        Toggle::make('is_active')
                            ->label(__('admin.product.active_buy'))
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
