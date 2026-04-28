<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsCategoryResource\Pages;
use App\Models\CmsCategory;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CmsCategoryResource extends Resource
{
    protected static ?string $model = CmsCategory::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | UnitEnum | null $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Categorías';
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';
    protected static ?int $navigationSort = 2;

    protected static array $palettes = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' => 'Indigo',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' => 'Emerald',
        'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400' => 'Teal',
        'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' => 'Amber',
        'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400' => 'Purple',
        'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-400' => 'Pink',
        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' => 'Blue',
        'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' => 'Rose',
        'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400' => 'Cyan',
        'bg-lime-100 text-lime-700 dark:bg-lime-900/40 dark:text-lime-400' => 'Lime',
        'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400' => 'Orange',
        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400' => 'Gray',
    ];

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Categoría')
                    ->schema([
                        Tabs::make('name_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\TextInput::make('name.es')
                                        ->label('Nombre (ES)')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, Forms\Get $get, Forms\Set $set) {
                                            if (filled($state) && empty($get('slug'))) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\TextInput::make('name.en')
                                        ->label('Name (EN)')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\TextInput::make('name.pt')
                                        ->label('Nome (PT)')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('primary_locale')
                            ->label('Idioma principal')
                            ->options(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'])
                            ->default('es')
                            ->required(),

                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options(self::$palettes)
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->formatStateUsing(fn (CmsCategory $record): string => $record->getTranslationWithFallback('name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->formatStateUsing(fn (?string $state): string => self::$palettes[$state] ?? '—'),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCmsCategories::route('/'),
            'create' => Pages\CreateCmsCategory::route('/create'),
            'edit' => Pages\EditCmsCategory::route('/{record}/edit'),
        ];
    }
}
