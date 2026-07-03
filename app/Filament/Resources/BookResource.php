<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BookExporter;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
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
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.content');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Book Details')
                    ->tabs([
                        // ============================================
                        // TAB 1: Identificación Básica
                        // ============================================
                        Tab::make(__('admin.book.tab_basic'))
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
                                    ->label(__('admin.book.f.primary_locale'))
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
                                    ->label(__('admin.book.f.book_type'))
                                    ->options([
                                        'libro_cientifico' => __('admin.book.type_scientific'),
                                        'libro_academico' => __('admin.book.type_academic'),
                                        'libro_tecnico' => __('admin.book.type_technical'),
                                        'manual' => __('admin.book.type_manual'),
                                        'capitulo_libro' => __('admin.book.type_chapter'),
                                    ]),
                                Forms\Components\Select::make('primary_language')
                                    ->label(__('admin.book.f.primary_language'))
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
                                    ->label(__('admin.book.f.secondary_language'))
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
                                    ->label(__('admin.book.f.publication_year'))
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                                Forms\Components\TextInput::make('edition')
                                    ->label(__('admin.book.f.edition'))
                                    ->placeholder('1ª, 2ª, revisada, etc.')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('isbn')
                                    ->label('ISBN')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('doi')
                                    ->label('DOI')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('landing_url')
                                    ->label(__('admin.book.f.landing_url'))
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('cover_image')
                                    ->label(__('admin.book.f.cover_image'))
                                    ->image()
                                    ->directory('book-covers')
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label(__('admin.book.owner'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])->columns(2),

                        // ============================================
                        // TAB 2: Autores y Responsabilidades
                        // ============================================
                        Tab::make(__('admin.book.tab_authors'))
                            ->schema([
                                Forms\Components\Repeater::make('authors')
                                    ->label(__('admin.book.f.authors'))
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('role')
                                            ->label(__('admin.book.f.author_role'))
                                            ->options([
                                                'author' => __('admin.book.f.author_role_author'),
                                                'editor' => __('admin.book.f.author_role_editor'),
                                                'translator' => __('admin.book.f.author_role_translator'),
                                                'coordinator' => __('admin.book.f.author_role_coordinator'),
                                            ])
                                            ->default('author')
                                            ->required(),
                                        Forms\Components\TextInput::make('full_name')
                                            ->label(__('admin.book.f.author_full_name'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('orcid')
                                            ->label('ORCID')
                                            ->placeholder('0000-0000-0000-0000')
                                            ->maxLength(19),
                                        Forms\Components\TextInput::make('affiliation')
                                            ->label(__('admin.book.f.author_affiliation'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('country_code')
                                            ->label(__('admin.book.f.author_country'))
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
                        Tab::make(__('admin.book.tab_publishing'))
                            ->schema([
                                Forms\Components\TextInput::make('publisher')
                                    ->label(__('admin.book.f.publisher'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('publisher_country')
                                    ->label(__('admin.book.f.publisher_country'))
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('publisher_city')
                                    ->label(__('admin.book.f.publisher_city'))
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
                                    ->label(__('admin.book.f.exact_pub_date')),
                                Forms\Components\TextInput::make('total_pages')
                                    ->label(__('admin.book.f.total_pages'))
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\Select::make('format')
                                    ->label(__('admin.book.f.format'))
                                    ->options([
                                        'pdf' => 'PDF',
                                        'epub' => 'EPUB',
                                        'print' => __('admin.book.f.format_print'),
                                        'hybrid' => __('admin.book.f.format_hybrid'),
                                    ]),
                            ])->columns(2),

                        // ============================================
                        // TAB 4: Resumen y Contenido Académico
                        // ============================================
                        Tab::make(__('admin.book.tab_academic'))
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
                                    ->label(__('admin.book.f.keywords'))
                                    ->columnSpanFull(),
                                Forms\Components\TagsInput::make('knowledge_areas')
                                    ->label(__('admin.book.f.knowledge_areas'))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('main_discipline')
                                    ->label(__('admin.book.f.main_discipline'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('secondary_discipline')
                                    ->label(__('admin.book.f.secondary_discipline'))
                                    ->maxLength(255),
                                Forms\Components\Select::make('academic_level')
                                    ->label(__('admin.book.f.academic_level'))
                                    ->options([
                                        'pregrado' => __('admin.book.f.level_undergrad'),
                                        'posgrado' => __('admin.book.f.level_postgrad'),
                                        'investigacion' => __('admin.book.f.level_research'),
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
                                    ->label(__('admin.book.f.toc_file'))
                                    ->directory('book-toc')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->columnSpanFull(),
                            ])->columns(2),

                        // ============================================
                        // TAB 5: Acceso Abierto y Derechos
                        // ============================================
                        Tab::make(__('admin.book.tab_open_access'))
                            ->schema([
                                Forms\Components\Toggle::make('is_open_access')
                                    ->label(__('admin.book.f.is_open_access'))
                                    ->live(),
                                Forms\Components\Select::make('access_type')
                                    ->label(__('admin.book.f.access_type'))
                                    ->options([
                                        'immediate' => __('admin.book.f.access_immediate'),
                                        'embargo' => __('admin.book.f.access_embargo'),
                                        'closed' => __('admin.book.f.access_closed'),
                                    ])
                                    ->visible(fn (Get $get) => $get('is_open_access')),
                                Forms\Components\Select::make('license_type')
                                    ->label(__('admin.book.f.license'))
                                    ->options([
                                        'CC-BY' => 'CC BY',
                                        'CC-BY-SA' => 'CC BY-SA',
                                        'CC-BY-NC' => 'CC BY-NC',
                                        'CC-BY-ND' => 'CC BY-ND',
                                        'CC-BY-NC-SA' => 'CC BY-NC-SA',
                                        'CC-BY-NC-ND' => 'CC BY-NC-ND',
                                        'copyright' => __('admin.book.f.license_copyright'),
                                        'other' => __('admin.book.f.license_other'),
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
                                    ->label(__('admin.book.f.allows_reuse')),
                                Forms\Components\Toggle::make('allows_commercial_use')
                                    ->label(__('admin.book.f.allows_commercial_use')),
                            ])->columns(2),

                        // ============================================
                        // TAB 6: Modelo de Negocio
                        // ============================================
                        Tab::make(__('admin.book.tab_business'))
                            ->schema([
                                Forms\Components\Select::make('publication_model')
                                    ->label(__('admin.book.f.publication_model'))
                                    ->options([
                                        'free' => __('admin.book.f.model_free'),
                                        'pay_download' => __('admin.book.f.model_pay_download'),
                                        'pay_print' => __('admin.book.f.model_pay_print'),
                                        'sponsored' => __('admin.book.f.model_sponsored'),
                                    ])
                                    ->live(),
                                Forms\Components\TextInput::make('access_cost')
                                    ->label(__('admin.book.f.access_cost'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->visible(fn (Get $get) => in_array($get('publication_model'), ['pay_download', 'pay_print'])),
                                Forms\Components\TextInput::make('author_apc')
                                    ->label(__('admin.book.f.author_apc'))
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\CheckboxList::make('funded_by')
                                    ->label(__('admin.book.f.funded_by'))
                                    ->options([
                                        'university' => __('admin.book.f.funded_university'),
                                        'project' => __('admin.book.f.funded_project'),
                                        'author' => __('admin.book.f.funded_author'),
                                        'other' => __('admin.book.f.funded_other'),
                                    ])
                                    ->columns(2),
                            ])->columns(2),

                        // ============================================
                        // TAB 7: Calidad y Evaluación Editorial
                        // ============================================
                        Tab::make(__('admin.book.tab_quality'))
                            ->schema([
                                Forms\Components\Toggle::make('has_peer_review')
                                    ->label(__('admin.book.f.has_peer_review'))
                                    ->live(),
                                Forms\Components\Select::make('review_type')
                                    ->label(__('admin.book.f.review_type'))
                                    ->options([
                                        'single_blind' => __('admin.book.f.review_single_blind'),
                                        'double_blind' => __('admin.book.f.review_double_blind'),
                                    ])
                                    ->visible(fn (Get $get) => $get('has_peer_review')),
                                Forms\Components\Toggle::make('has_editorial_committee')
                                    ->label(__('admin.book.f.has_editorial_committee')),
                                Forms\Components\Toggle::make('has_editorial_standards')
                                    ->label(__('admin.book.f.has_editorial_standards')),
                                Forms\Components\Toggle::make('has_antiplagiarism')
                                    ->label(__('admin.book.f.has_antiplagiarism')),
                                Forms\Components\Toggle::make('has_ethics_code')
                                    ->label(__('admin.book.f.has_ethics_code')),
                            ])->columns(2),

                        // ============================================
                        // TAB 8: Indexación y Visibilidad
                        // ============================================
                        Tab::make(__('admin.book.tab_indexing'))
                            ->schema([
                                Forms\Components\Toggle::make('is_indexed')
                                    ->label(__('admin.book.f.is_indexed'))
                                    ->live(),
                                Forms\Components\CheckboxList::make('indexes')
                                    ->label(__('admin.book.f.indexes'))
                                    ->options([
                                        'google_books' => 'Google Books',
                                        'google_scholar' => 'Google Scholar',
                                        'doab' => 'DOAB',
                                        'latindex' => 'Latindex Libros',
                                        'scopus' => 'Scopus',
                                        'wos' => 'Web of Science',
                                        'other' => __('admin.book.f.index_other'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('is_indexed')),
                                Forms\Components\TextInput::make('citation_count')
                                    ->label(__('admin.book.f.citation_count'))
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
                        Tab::make(__('admin.book.tab_files'))
                            ->schema([
                                Forms\Components\FileUpload::make('main_file')
                                    ->label(__('admin.book.f.main_file'))
                                    ->directory('books')
                                    ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('chapter_files')
                                    ->label(__('admin.book.f.chapter_files'))
                                    ->schema([
                                        Forms\Components\TextInput::make('chapter_name')
                                            ->label(__('admin.book.f.chapter_name'))
                                            ->required(),
                                        Forms\Components\FileUpload::make('file')
                                            ->label(__('admin.book.f.file'))
                                            ->directory('book-chapters')
                                            ->acceptedFileTypes(['application/pdf']),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('supplementary_files')
                                    ->label(__('admin.book.f.supplementary_files'))
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('admin.book.f.supp_name'))
                                            ->required(),
                                        Forms\Components\FileUpload::make('file')
                                            ->label(__('admin.book.f.file'))
                                            ->directory('book-supplementary'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('download_url')
                                    ->label(__('admin.book.f.download_url'))
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('file_size')
                                    ->label(__('admin.book.f.file_size'))
                                    ->placeholder('10 MB')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('file_checksum')
                                    ->label(__('admin.book.f.file_checksum'))
                                    ->maxLength(255),
                            ])->columns(2),

                        // ============================================
                        // TAB 10: Estado Interno (Sistema)
                        // ============================================
                        Tab::make(__('admin.book.tab_internal'))
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label(__('admin.book.status'))
                                    ->options([
                                        'draft' => __('admin.book.status_draft'),
                                        'submitted' => __('admin.book.status_submitted'),
                                        'requires_changes_listing' => __('admin.book.f.status_changes_listing'),
                                        'listed' => __('admin.book.f.status_listed_book'),
                                    ])
                                    ->required()
                                    ->default('draft'),
                                Forms\Components\DatePicker::make('submission_date')
                                    ->label(__('admin.book.f.submission_date')),
                                Forms\Components\DatePicker::make('approval_date')
                                    ->label(__('admin.book.f.approval_date')),
                                Forms\Components\Select::make('responsible_editor_id')
                                    ->label(__('admin.book.f.responsible_editor'))
                                    ->relationship('responsibleEditor', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Textarea::make('internal_notes')
                                    ->label(__('admin.book.f.internal_notes'))
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('current_score')
                                    ->label(__('admin.book.f.current_score'))
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('current_level')
                                    ->label(__('admin.book.f.current_level'))
                                    ->disabled(),
                                // Sprint 3 #20: el admin puede activar/extender
                                // el destacado manualmente (regalos, soporte, etc.).
                                Forms\Components\Toggle::make('is_featured')
                                    ->label(__('admin.book.f.is_featured'))
                                    ->helperText(__('admin.book.f.is_featured_help')),
                                Forms\Components\DatePicker::make('featured_until')
                                    ->label(__('admin.book.f.featured_until'))
                                    ->helperText(__('admin.book.f.featured_until_help')),
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
                    ->label(__('admin.book.cover'))
                    ->circular(false)
                    ->width(50)
                    ->height(70),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.book.title'))
                    ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('title'))
                    // title/subtitle son JSON traducibles: buscar/ordenar vía macros.
                    ->searchable(query: fn ($query, string $search) => $query->whereTranslatableLike(['title', 'subtitle'], $search))
                    ->sortable(query: fn ($query, string $direction) => $query->orderByTranslatable('title', $direction))
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.book.owner')),
                Tables\Columns\TextColumn::make('book_type')
                    ->label(__('admin.book.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'libro_cientifico' => __('admin.book.type_scientific'),
                        'libro_academico' => __('admin.book.type_academic'),
                        'libro_tecnico' => __('admin.book.type_technical'),
                        'manual' => __('admin.book.type_manual'),
                        'capitulo_libro' => __('admin.book.type_chapter'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.book.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'requires_changes_listing' => 'danger',
                        'listed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('admin.book.status_draft'),
                        'submitted' => __('admin.book.status_submitted'),
                        'requires_changes_listing' => __('admin.book.status_changes'),
                        'listed' => __('admin.book.status_listed'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label(__('admin.book.year'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_score')
                    ->label(__('admin.book.score'))
                    ->sortable(),
                // Sprint 3 #20: columna "Destacado" — sólo prende si el flag está
                // activo Y el featured_until aún no venció.
                Tables\Columns\TextColumn::make('is_featured')
                    ->label(__('admin.book.featured'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(function ($state, Book $record): string {
                        $active = $record->is_featured
                            && $record->featured_until
                            && $record->featured_until->toDateString() >= now()->toDateString();
                        return $active
                            ? __('admin.book.featured_badge')
                            : '—';
                    })
                    ->color(fn (Book $record): string => ($record->is_featured
                        && $record->featured_until
                        && $record->featured_until->toDateString() >= now()->toDateString())
                        ? 'warning'
                        : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.book.status'))
                    ->options([
                        'draft' => __('admin.book.status_draft'),
                        'submitted' => __('admin.book.status_submitted'),
                        'requires_changes_listing' => __('admin.book.status_changes'),
                        'listed' => __('admin.book.status_listed'),
                    ]),
                Tables\Filters\SelectFilter::make('book_type')
                    ->label(__('admin.book.type'))
                    ->options([
                        'libro_cientifico' => __('admin.book.type_scientific'),
                        'libro_academico' => __('admin.book.type_academic'),
                        'libro_tecnico' => __('admin.book.type_technical'),
                        'manual' => __('admin.book.type_manual'),
                        'capitulo_libro' => __('admin.book.type_chapter'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_open_access')
                    ->label(__('admin.book.filter_open_access')),
                // Sprint 3 #20: filtro "Destacado". El estado true sólo trae los
                // realmente vigentes; el estado false trae todos los demás.
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.book.filter_featured'))
                    ->placeholder(__('admin.common.all'))
                    ->trueLabel(__('admin.book.filter_featured_only'))
                    ->falseLabel(__('admin.book.filter_featured_none'))
                    ->queries(
                        true: fn ($query) => $query->where('is_featured', true)
                            ->where('featured_until', '>=', now()->toDateString()),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->where('is_featured', false)
                                ->orWhereNull('featured_until')
                                ->orWhere('featured_until', '<', now()->toDateString());
                        }),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('admin.common.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->exporter(BookExporter::class),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    // Sprint 3 #20: bulk activar el destacado por 1 año. Si
                    // alguno de los libros ya tenía un featured_until futuro,
                    // sumamos 12 meses para no quitarle valor al editor (misma
                    // lógica de extensión que StripePaymentService::applyBookFeatured).
                    \Filament\Actions\BulkAction::make('feature_one_year')
                        ->label(__('admin.book.action_feature_year'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.book.modal_feature_heading'))
                        ->modalDescription(__('admin.book.modal_feature_desc'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $today = now()->toDateString();
                            $count = 0;
                            foreach ($records as $book) {
                                $base = $book->featured_until && now()->lt($book->featured_until)
                                    ? $book->featured_until->toDateString()
                                    : $today;
                                $book->update([
                                    'is_featured' => true,
                                    'featured_until' => \Carbon\Carbon::parse($base)->addYear()->toDateString(),
                                ]);
                                $count++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.book.notif_featured'))
                                ->body(__('admin.book.notif_featured_body', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('unfeature')
                        ->label(__('admin.book.action_unfeature'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.book.modal_unfeature_heading'))
                        ->modalDescription(__('admin.book.modal_unfeature_desc'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $count = $records->count();
                            foreach ($records as $book) {
                                $book->update(['is_featured' => false]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.book.notif_unfeatured'))
                                ->body(__('admin.book.notif_unfeatured_body', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label(__('admin.common.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(BookExporter::class),
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\PaymentsRelationManager::class,
            \App\Filament\RelationManagers\ActivitiesRelationManager::class,
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
