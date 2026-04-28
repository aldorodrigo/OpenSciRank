<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | UnitEnum | null $navigationGroup = 'Content';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Book Details')
                    ->tabs([
                        // ============================================
                        // TAB 1: Identificación Básica
                        // ============================================
                        Tab::make('Identificación Básica')
                            ->schema([
                                Tabs::make('title_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('title.es')
                                                ->label('Título del Libro (ES)')
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, ?string $state) => filled($state) ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('title.en')
                                                ->label('Book Title (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('title.pt')
                                                ->label('Título do Livro (PT)')
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
                                    ->maxLength(255),
                                Tabs::make('subtitle_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('subtitle.es')
                                                ->label('Subtítulo (ES)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('subtitle.en')
                                                ->label('Subtitle (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('subtitle.pt')
                                                ->label('Subtítulo (PT)')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Select::make('book_type')
                                    ->label('Tipo de Obra')
                                    ->options([
                                        'libro_cientifico' => 'Libro Científico',
                                        'libro_academico' => 'Libro Académico',
                                        'libro_tecnico' => 'Libro Técnico',
                                        'manual' => 'Manual',
                                        'capitulo_libro' => 'Capítulo de Libro',
                                    ]),
                                Forms\Components\Select::make('primary_language')
                                    ->label('Idioma Principal')
                                    ->options([
                                        'es' => 'Español',
                                        'en' => 'Inglés',
                                        'pt' => 'Portugués',
                                        'fr' => 'Francés',
                                        'de' => 'Alemán',
                                        'it' => 'Italiano',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                Forms\Components\Select::make('secondary_language')
                                    ->label('Idioma Secundario')
                                    ->options([
                                        'es' => 'Español',
                                        'en' => 'Inglés',
                                        'pt' => 'Portugués',
                                        'fr' => 'Francés',
                                        'de' => 'Alemán',
                                        'it' => 'Italiano',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                Forms\Components\TextInput::make('publication_year')
                                    ->label('Año de Publicación')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                                Forms\Components\TextInput::make('edition')
                                    ->label('Edición')
                                    ->placeholder('1ª, 2ª, revisada, etc.')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('isbn')
                                    ->label('ISBN')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('doi')
                                    ->label('DOI')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('landing_url')
                                    ->label('URL del Libro / Landing Page')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('cover_image')
                                    ->label('Portada del Libro')
                                    ->image()
                                    ->directory('book-covers')
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Propietario')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])->columns(2),

                        // ============================================
                        // TAB 2: Autores y Responsabilidades
                        // ============================================
                        Tab::make('Autores y Responsabilidades')
                            ->schema([
                                Forms\Components\Repeater::make('authors')
                                    ->label('Autores / Editores / Traductores')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('role')
                                            ->label('Rol')
                                            ->options([
                                                'author' => 'Autor',
                                                'editor' => 'Editor Académico',
                                                'translator' => 'Traductor',
                                                'coordinator' => 'Coordinador / Compilador',
                                            ])
                                            ->default('author')
                                            ->required(),
                                        Forms\Components\TextInput::make('full_name')
                                            ->label('Nombre Completo')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('orcid')
                                            ->label('ORCID')
                                            ->placeholder('0000-0000-0000-0000')
                                            ->maxLength(19),
                                        Forms\Components\TextInput::make('affiliation')
                                            ->label('Afiliación Institucional')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('country_code')
                                            ->label('País')
                                            ->maxLength(2)
                                            ->placeholder('PE, MX, ES...'),
                                        Forms\Components\Hidden::make('order'),
                                    ])
                                    ->columns(3)
                                    ->orderColumn('order')
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['full_name'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        // ============================================
                        // TAB 3: Editorial y Publicación
                        // ============================================
                        Tab::make('Editorial y Publicación')
                            ->schema([
                                Forms\Components\TextInput::make('publisher')
                                    ->label('Editorial')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('publisher_country')
                                    ->label('País de la Editorial')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('publisher_city')
                                    ->label('Ciudad')
                                    ->maxLength(100),
                                Tabs::make('collection_series_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('collection_series.es')
                                                ->label('Colección / Serie (ES)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('collection_series.en')
                                                ->label('Collection / Series (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('collection_series.pt')
                                                ->label('Coleção / Série (PT)')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Tabs::make('sponsor_entity_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('sponsor_entity.es')
                                                ->label('Entidad Patrocinadora (ES)')
                                                ->placeholder('Universidad, centro de investigación, etc.')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('sponsor_entity.en')
                                                ->label('Sponsor Entity (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('sponsor_entity.pt')
                                                ->label('Entidade Patrocinadora (PT)')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\DatePicker::make('exact_publication_date')
                                    ->label('Fecha Exacta de Publicación'),
                                Forms\Components\TextInput::make('total_pages')
                                    ->label('Número Total de Páginas')
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\Select::make('format')
                                    ->label('Formato')
                                    ->options([
                                        'pdf' => 'PDF',
                                        'epub' => 'EPUB',
                                        'print' => 'Impreso',
                                        'hybrid' => 'Híbrido',
                                    ]),
                            ])->columns(2),

                        // ============================================
                        // TAB 4: Resumen y Contenido Académico
                        // ============================================
                        Tab::make('Contenido Académico')
                            ->schema([
                                Tabs::make('abstract_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('abstract.es')
                                                ->label('Resumen / Abstract (ES)')
                                                ->rows(5),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('abstract.en')
                                                ->label('Abstract (EN)')
                                                ->rows(5),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('abstract.pt')
                                                ->label('Resumo (PT)')
                                                ->rows(5),
                                        ]),
                                    ]),
                                Forms\Components\TagsInput::make('keywords')
                                    ->label('Palabras Clave (Keywords)')
                                    ->columnSpanFull(),
                                Forms\Components\TagsInput::make('knowledge_areas')
                                    ->label('Áreas del Conocimiento')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('main_discipline')
                                    ->label('Disciplina Principal')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('secondary_discipline')
                                    ->label('Disciplina Secundaria')
                                    ->maxLength(255),
                                Forms\Components\Select::make('academic_level')
                                    ->label('Nivel Académico')
                                    ->options([
                                        'pregrado' => 'Pregrado',
                                        'posgrado' => 'Posgrado',
                                        'investigacion' => 'Investigación',
                                    ]),
                                Tabs::make('table_of_contents_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('table_of_contents.es')
                                                ->label('Tabla de Contenidos (ES)')
                                                ->rows(6),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('table_of_contents.en')
                                                ->label('Table of Contents (EN)')
                                                ->rows(6),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('table_of_contents.pt')
                                                ->label('Sumário (PT)')
                                                ->rows(6),
                                        ]),
                                    ]),
                                Forms\Components\FileUpload::make('table_of_contents_file')
                                    ->label('Tabla de Contenidos (Archivo)')
                                    ->directory('book-toc')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->columnSpanFull(),
                            ])->columns(2),

                        // ============================================
                        // TAB 5: Acceso Abierto y Derechos
                        // ============================================
                        Tab::make('Acceso Abierto y Derechos')
                            ->schema([
                                Forms\Components\Toggle::make('is_open_access')
                                    ->label('¿Es de Acceso Abierto?')
                                    ->live(),
                                Forms\Components\Select::make('access_type')
                                    ->label('Tipo de Acceso')
                                    ->options([
                                        'immediate' => 'Abierto Inmediato',
                                        'embargo' => 'Embargo',
                                        'closed' => 'Cerrado',
                                    ])
                                    ->visible(fn (Get $get) => $get('is_open_access')),
                                Forms\Components\Select::make('license_type')
                                    ->label('Licencia')
                                    ->options([
                                        'CC-BY' => 'CC BY',
                                        'CC-BY-SA' => 'CC BY-SA',
                                        'CC-BY-NC' => 'CC BY-NC',
                                        'CC-BY-ND' => 'CC BY-ND',
                                        'CC-BY-NC-SA' => 'CC BY-NC-SA',
                                        'CC-BY-NC-ND' => 'CC BY-NC-ND',
                                        'copyright' => 'Copyright Tradicional',
                                        'other' => 'Otra',
                                    ]),
                                Tabs::make('rights_holder_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('rights_holder.es')
                                                ->label('Titular de Derechos (ES)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('rights_holder.en')
                                                ->label('Rights Holder (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('rights_holder.pt')
                                                ->label('Titular de Direitos (PT)')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Toggle::make('allows_reuse')
                                    ->label('¿Permite Reutilización?'),
                                Forms\Components\Toggle::make('allows_commercial_use')
                                    ->label('¿Permite Uso Comercial?'),
                            ])->columns(2),

                        // ============================================
                        // TAB 6: Modelo de Negocio
                        // ============================================
                        Tab::make('Modelo de Negocio')
                            ->schema([
                                Forms\Components\Select::make('publication_model')
                                    ->label('Modelo de Publicación')
                                    ->options([
                                        'free' => 'Gratuito',
                                        'pay_download' => 'Pago por Descarga',
                                        'pay_print' => 'Pago por Impresión',
                                        'sponsored' => 'Patrocinado',
                                    ])
                                    ->live(),
                                Forms\Components\TextInput::make('access_cost')
                                    ->label('Costo de Acceso (USD)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->visible(fn (Get $get) => in_array($get('publication_model'), ['pay_download', 'pay_print'])),
                                Forms\Components\TextInput::make('author_apc')
                                    ->label('Costo de Publicación para Autores (APC)')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\CheckboxList::make('funded_by')
                                    ->label('Financiado por')
                                    ->options([
                                        'university' => 'Universidad',
                                        'project' => 'Proyecto',
                                        'author' => 'Autor',
                                        'other' => 'Otro',
                                    ])
                                    ->columns(2),
                            ])->columns(2),

                        // ============================================
                        // TAB 7: Calidad y Evaluación Editorial
                        // ============================================
                        Tab::make('Calidad Editorial')
                            ->schema([
                                Forms\Components\Toggle::make('has_peer_review')
                                    ->label('¿Revisión por Pares?')
                                    ->live(),
                                Forms\Components\Select::make('review_type')
                                    ->label('Tipo de Revisión')
                                    ->options([
                                        'single_blind' => 'Simple Ciego',
                                        'double_blind' => 'Doble Ciego',
                                    ])
                                    ->visible(fn (Get $get) => $get('has_peer_review')),
                                Forms\Components\Toggle::make('has_editorial_committee')
                                    ->label('¿Comité Editorial Identificado?'),
                                Forms\Components\Toggle::make('has_editorial_standards')
                                    ->label('¿Normas Editoriales Declaradas?'),
                                Forms\Components\Toggle::make('has_antiplagiarism')
                                    ->label('¿Antiplagio Aplicado?'),
                                Forms\Components\Toggle::make('has_ethics_code')
                                    ->label('¿Código de Ética Editorial?'),
                            ])->columns(2),

                        // ============================================
                        // TAB 8: Indexación y Visibilidad
                        // ============================================
                        Tab::make('Indexación y Visibilidad')
                            ->schema([
                                Forms\Components\Toggle::make('is_indexed')
                                    ->label('¿Está Indexado?')
                                    ->live(),
                                Forms\Components\CheckboxList::make('indexes')
                                    ->label('Índices donde Aparece')
                                    ->options([
                                        'google_books' => 'Google Books',
                                        'google_scholar' => 'Google Scholar',
                                        'doab' => 'DOAB',
                                        'latindex' => 'Latindex Libros',
                                        'scopus' => 'Scopus',
                                        'wos' => 'Web of Science',
                                        'other' => 'Otros',
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('is_indexed')),
                                Forms\Components\TextInput::make('citation_count')
                                    ->label('Número de Citas')
                                    ->numeric()
                                    ->minValue(0),
                                Tabs::make('available_metrics_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('available_metrics.es')
                                                ->label('Métricas Disponibles (ES)')
                                                ->placeholder('Describir métricas disponibles')
                                                ->rows(3),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('available_metrics.en')
                                                ->label('Available Metrics (EN)')
                                                ->rows(3),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('available_metrics.pt')
                                                ->label('Métricas Disponíveis (PT)')
                                                ->rows(3),
                                        ]),
                                    ]),
                            ])->columns(2),

                        // ============================================
                        // TAB 9: Archivos y Recursos
                        // ============================================
                        Tab::make('Archivos y Recursos')
                            ->schema([
                                Forms\Components\FileUpload::make('main_file')
                                    ->label('Archivo Principal del Libro')
                                    ->directory('books')
                                    ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('chapter_files')
                                    ->label('Capítulos Individuales')
                                    ->schema([
                                        Forms\Components\TextInput::make('chapter_name')
                                            ->label('Nombre del Capítulo')
                                            ->required(),
                                        Forms\Components\FileUpload::make('file')
                                            ->label('Archivo')
                                            ->directory('book-chapters')
                                            ->acceptedFileTypes(['application/pdf']),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('supplementary_files')
                                    ->label('Material Complementario')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\FileUpload::make('file')
                                            ->label('Archivo')
                                            ->directory('book-supplementary'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('download_url')
                                    ->label('URL de Descarga')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('file_size')
                                    ->label('Tamaño del Archivo')
                                    ->placeholder('10 MB')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('file_checksum')
                                    ->label('Checksum / Hash')
                                    ->maxLength(255),
                            ])->columns(2),

                        // ============================================
                        // TAB 10: Estado Interno (Sistema)
                        // ============================================
                        Tab::make('Estado Interno')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'draft' => 'Borrador',
                                        'submitted' => 'Enviado',
                                        'requires_changes_listing' => 'Correcciones (Listado)',
                                        'listed' => 'Libro Listado',
                                    ])
                                    ->required()
                                    ->default('draft'),
                                Forms\Components\DatePicker::make('submission_date')
                                    ->label('Fecha de Postulación'),
                                Forms\Components\DatePicker::make('approval_date')
                                    ->label('Fecha de Aprobación'),
                                Forms\Components\Select::make('responsible_editor_id')
                                    ->label('Editor Responsable')
                                    ->relationship('responsibleEditor', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Textarea::make('internal_notes')
                                    ->label('Observaciones Internas')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('current_score')
                                    ->label('Puntuación Actual')
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('current_level')
                                    ->label('Nivel Actual')
                                    ->disabled(),
                            ])->columns(2),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Portada')
                    ->circular(false)
                    ->width(50)
                    ->height(70),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('title'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propietario'),
                Tables\Columns\TextColumn::make('book_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'libro_cientifico' => 'Científico',
                        'libro_academico' => 'Académico',
                        'libro_tecnico' => 'Técnico',
                        'manual' => 'Manual',
                        'capitulo_libro' => 'Capítulo',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'requires_changes_listing' => 'danger',
                        'listed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'submitted' => 'Enviado',
                        'requires_changes_listing' => 'Correcciones',
                        'listed' => 'Listado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_score')
                    ->label('Puntuación')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'submitted' => 'Enviado',
                        'requires_changes_listing' => 'Correcciones (Listado)',
                        'listed' => 'Listado',
                    ]),
                Tables\Filters\SelectFilter::make('book_type')
                    ->label('Tipo')
                    ->options([
                        'libro_cientifico' => 'Libro Científico',
                        'libro_academico' => 'Libro Académico',
                        'libro_tecnico' => 'Libro Técnico',
                        'manual' => 'Manual',
                        'capitulo_libro' => 'Capítulo de Libro',
                    ]),
                Tables\Filters\TernaryFilter::make('is_open_access')
                    ->label('Acceso Abierto'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
