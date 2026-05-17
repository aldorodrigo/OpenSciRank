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
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('admin.cms_category.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.cms_category.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.cms_category.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.content');
    }

    // Paleta CMS — solo colores compatibles con BRAND.md.
    // Removed: Indigo, Purple, Pink (off-brand desde Sprint 5 #52).
    protected static array $palettes = [
        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' => 'Blue',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' => 'Emerald',
        'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400' => 'Teal',
        'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' => 'Amber',
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
                Section::make(__('admin.cms_category.section'))
                    ->schema([
                        Tabs::make('name_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\TextInput::make('name.es')
                                        ->label(__('admin.cms_category.name') . ' (' . __('admin.common.lang_suffix.es') . ')')
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
                                        ->label(__('admin.cms_category.name') . ' (' . __('admin.common.lang_suffix.en') . ')')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\TextInput::make('name.pt')
                                        ->label(__('admin.cms_category.name') . ' (' . __('admin.common.lang_suffix.pt') . ')')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.cms_category.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('primary_locale')
                            ->label(__('admin.common.language_primary'))
                            ->options([
                                'es' => __('admin.common.language_options.es'),
                                'en' => __('admin.common.language_options.en'),
                                'pt' => __('admin.common.language_options.pt'),
                            ])
                            ->default('es')
                            ->required(),

                        Forms\Components\Select::make('color')
                            ->label(__('admin.cms_category.color'))
                            ->options(self::$palettes)
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.cms_category.order'))
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
                    ->label(__('admin.cms_category.order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.cms_category.name'))
                    ->formatStateUsing(fn (CmsCategory $record): string => $record->getTranslationWithFallback('name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.cms_category.slug'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('color')
                    ->label(__('admin.cms_category.color'))
                    ->formatStateUsing(fn (?string $state): string => self::$palettes[$state] ?? '—'),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label(__('admin.cms_category.posts'))
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
