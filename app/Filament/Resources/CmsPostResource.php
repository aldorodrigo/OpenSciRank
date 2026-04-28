<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsPostResource\Pages;
use App\Models\CmsCategory;
use App\Models\CmsPost;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                        Tabs::make('title_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\TextInput::make('title.es')
                                        ->label('Título (ES)')
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, Forms\Set $set) {
                                            if (filled($state)) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\TextInput::make('title.en')
                                        ->label('Title (EN)')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\TextInput::make('title.pt')
                                        ->label('Título (PT)')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Forms\Components\Select::make('primary_locale')
                            ->label('Idioma principal')
                            ->options(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'])
                            ->default('es')
                            ->required(),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Tabs::make('excerpt_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\Textarea::make('excerpt.es')
                                        ->label('Extracto (ES)')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\Textarea::make('excerpt.en')
                                        ->label('Excerpt (EN)')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\Textarea::make('excerpt.pt')
                                        ->label('Extrato (PT)')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                            ]),

                        Tabs::make('content_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\RichEditor::make('content.es')
                                        ->label('Contenido (ES)'),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\RichEditor::make('content.en')
                                        ->label('Content (EN)'),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\RichEditor::make('content.pt')
                                        ->label('Conteúdo (PT)'),
                                ]),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Configuración')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label(__('Categoría'))
                            ->options(fn () => CmsCategory::ordered()->get()->mapWithKeys(fn ($c) => [$c->slug => $c->getTranslationWithFallback('name')])->toArray())
                            ->searchable()
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
                    ->formatStateUsing(fn (CmsPost $record): string => Str::limit($record->getTranslationWithFallback('title'), 50))
                    ->searchable(),

                Tables\Columns\TextColumn::make('cat_label')
                    ->label(__('Categoría')),

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
                    ->options(fn () => CmsCategory::ordered()->get()->mapWithKeys(fn ($c) => [$c->slug => $c->getTranslationWithFallback('name')])->toArray()),

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
