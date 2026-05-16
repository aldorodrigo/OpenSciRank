<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CriteriaItemResource\Pages;
use App\Models\CriteriaItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class CriteriaItemResource extends Resource
{
    protected static ?string $model = CriteriaItem::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.evaluation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.criteria.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.criteria.plural');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('admin.criteria.section_info'))
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.criteria.code'))
                            ->required()
                            ->maxLength(10)
                            ->placeholder(__('admin.criteria.code_ph'))
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.criteria.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label(__('admin.criteria.category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.criteria.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('admin.criteria.section_config'))
                    ->schema([
                        Forms\Components\TextInput::make('weight')
                            ->label(__('admin.criteria.weight'))
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->maxValue(10)
                            ->helperText(__('admin.criteria.weight_help')),

                        Forms\Components\Select::make('type')
                            ->label(__('admin.criteria.type'))
                            ->options([
                                'core' => __('admin.criteria.type_core'),
                                'advanced' => __('admin.criteria.type_advanced'),
                                'excellence' => __('admin.criteria.type_excellence'),
                            ])
                            ->default('core')
                            ->required(),

                        Forms\Components\Toggle::make('is_core')
                            ->label(__('admin.criteria.is_exclusive'))
                            ->helperText(__('admin.criteria.is_exclusive_help')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.criteria.active'))
                            ->default(true),

                        Forms\Components\TextInput::make('order')
                            ->label(__('admin.criteria.order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.criteria.code'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.criteria.indicator'))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.criteria.category'))
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label(__('admin.criteria.weight'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.criteria.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'core' => 'primary',
                        'advanced' => 'warning',
                        'excellence' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'core' => __('admin.criteria.type_core_short'),
                        'advanced' => __('admin.criteria.type_advanced'),
                        'excellence' => __('admin.criteria.type_excellence'),
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_core')
                    ->label(__('admin.criteria.is_exclusive_short'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.criteria.active'))
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.criteria.type'))
                    ->options([
                        'core' => __('admin.criteria.type_core_short'),
                        'advanced' => __('admin.criteria.type_advanced'),
                        'excellence' => __('admin.criteria.type_excellence'),
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.criteria.category'))
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_core')
                    ->label(__('admin.criteria.filter_exclusive')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.criteria.filter_active')),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCriteriaItems::route('/'),
            'create' => Pages\CreateCriteriaItem::route('/create'),
            'edit' => Pages\EditCriteriaItem::route('/{record}/edit'),
        ];
    }
}
