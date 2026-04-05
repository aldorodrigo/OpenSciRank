<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsPostResource\Pages;
use App\Models\CmsPost;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class CmsPostResource extends Resource
{
    protected static ?string $model = CmsPost::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | UnitEnum | null $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Blog';
    protected static ?string $modelLabel = 'Artículo';
    protected static ?string $pluralModelLabel = 'Artículos';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Contenido')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Extracto')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Configuración')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('Categoría')
                            ->options(collect(CmsPost::CATEGORIES)->mapWithKeys(fn ($cat, $key) => [$key => $cat['label']]))
                            ->required(),

                        Forms\Components\Select::make('emoji')
                            ->label('Emoji')
                            ->options([
                                '📋' => '📋 Documento',
                                '🌍' => '🌍 Mundo',
                                '🏆' => '🏆 Trofeo',
                                '✅' => '✅ Aprobado',
                                '🚀' => '🚀 Lanzamiento',
                                '🔗' => '🔗 Enlace',
                                '📝' => '📝 Escritura',
                                '📊' => '📊 Estadísticas',
                                '🔬' => '🔬 Investigación',
                                '📚' => '📚 Libros',
                                '💡' => '💡 Idea',
                                '⚙️' => '⚙️ Configuración',
                                '🎓' => '🎓 Académico',
                                '📰' => '📰 Noticias',
                                '🔍' => '🔍 Búsqueda',
                                '📈' => '📈 Crecimiento',
                                '🧪' => '🧪 Ciencia',
                                '🗂️' => '🗂️ Archivo',
                                '✍️' => '✍️ Autoría',
                                '⭐' => '⭐ Destacado',
                            ])
                            ->searchable()
                            ->placeholder('Selecciona un emoji'),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagen')
                            ->image()
                            ->disk('public')
                            ->directory('blog')
                            ->nullable(),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado'),

                        Forms\Components\TextInput::make('read_time')
                            ->label('Tiempo de lectura')
                            ->placeholder('8 min')
                            ->maxLength(20),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de publicación')
                            ->helperText('Dejar vacío para guardar como borrador.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => CmsPost::CATEGORIES[$state]['label'] ?? $state),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->date()
                    ->placeholder('Borrador'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(collect(CmsPost::CATEGORIES)->mapWithKeys(fn ($cat, $key) => [$key => $cat['label']])),

                Tables\Filters\TernaryFilter::make('published')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Publicados')
                    ->falseLabel('Borradores')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('published_at'),
                        false: fn ($query) => $query->whereNull('published_at'),
                    ),
            ])
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
            'index' => Pages\ListCmsPosts::route('/'),
            'create' => Pages\CreateCmsPost::route('/create'),
            'edit' => Pages\EditCmsPost::route('/{record}/edit'),
        ];
    }
}
