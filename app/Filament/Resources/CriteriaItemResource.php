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

    protected static string | UnitEnum | null $navigationGroup = 'Evaluación';

    protected static ?string $modelLabel = 'Indicador de Evaluación';

    protected static ?string $pluralModelLabel = 'Indicadores de Evaluación';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Información del Indicador')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('1.1, 2.3, etc.')
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Indicador')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Configuración')
                    ->schema([
                        Forms\Components\TextInput::make('weight')
                            ->label('Peso')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->maxValue(10)
                            ->helperText('Peso del indicador en el cálculo de la nota (0-10)'),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'core' => 'Core (Básico)',
                                'advanced' => 'Avanzado',
                                'excellence' => 'Excelencia',
                            ])
                            ->default('core')
                            ->required(),

                        Forms\Components\Toggle::make('is_core')
                            ->label('¿Es Excluyente?')
                            ->helperText('Si no se cumple, la revista no puede aprobar'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),

                        Forms\Components\TextInput::make('order')
                            ->label('Orden')
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
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Indicador')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Peso')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'core' => 'primary',
                        'advanced' => 'warning',
                        'excellence' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'core' => 'Core',
                        'advanced' => 'Avanzado',
                        'excellence' => 'Excelencia',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_core')
                    ->label('Excl.')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'core' => 'Core',
                        'advanced' => 'Avanzado',
                        'excellence' => 'Excelencia',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_core')
                    ->label('Excluyentes'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activos'),
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
