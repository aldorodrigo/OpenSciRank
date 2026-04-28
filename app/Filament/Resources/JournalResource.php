<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalResource\Pages;
use App\Filament\Resources\JournalResource\RelationManagers;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\EvaluatorAssigned;
use App\Notifications\SealExpired;
use App\Notifications\SealExpiringSoon;
use App\Services\OaiPmhService;
use Filament\Notifications\Notification;
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

class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | UnitEnum | null $navigationGroup = 'Content';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Journal Details')
                    ->tabs([
                        Tab::make('Información Básica')
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo de la Revista')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Imagen del logo oficial de la revista. Máximo 2 MB.')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('journal-logos')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                                Tabs::make('title_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('title.es')
                                                ->label('Título (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Nombre completo y oficial de la revista.')
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, ?string $state) => filled($state) ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
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
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Idioma principal del registro; se usa como fallback cuando una traducción está vacía.')
                                    ->options(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'])
                                    ->default('es')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'URL amigable generada automáticamente a partir del título.')
                                    ->required()
                                    ->maxLength(255),
                                Tabs::make('abbreviated_name_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.es')
                                                ->label('Nombre Abreviado (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Abreviatura estándar según ISO 4, si existe.')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.en')
                                                ->label('Abbreviated name (EN)')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.pt')
                                                ->label('Nome Abreviado (PT)')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Propietario')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Usuario que registró la revista en el sistema.')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Estado actual del proceso de publicación/indexación.')
                                    ->options([
                                        'draft' => 'Borrador',
                                        'submitted' => 'Enviada para Evaluación',
                                        'requires_changes_listing' => 'Correcciones (Listado)',
                                        'requires_changes_evaluation' => 'Correcciones (Evaluación)',
                                        'pending_listing' => 'Pendiente de Listar',
                                        'listed' => 'Revista Listada',
                                        'evaluated' => 'Revista Evaluada',
                                        'certified' => 'Revista Certificada',
                                        'rejected' => 'Rechazada',
                                    ])
                                    ->required()
                                    ->default('draft'),
                                Forms\Components\Select::make('assigned_evaluator_id')
                                    ->label('Evaluador Asignado')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Evaluador responsable de revisar esta revista.')
                                    ->relationship('assignedEvaluator', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('issn_print')
                                    ->label('ISSN (Impreso)')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Número de serie internacional para la versión impresa.')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('issn_online')
                                    ->label('ISSN (Online)')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Número de serie internacional para la versión electrónica.')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('publisher')
                                    ->label('Editorial')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Nombre de la editorial o casa publicadora.')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Dirección web principal de la revista.')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('country_code')
                                    ->label('Código País')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Código ISO de 2 letras del país de la editorial.')
                                    ->maxLength(2),
                                Forms\Components\TextInput::make('start_year')
                                    ->label('Año de Inicio')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Año en que la revista comenzó a publicarse.')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                                Tabs::make('description_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('description.es')
                                                ->label('Descripción (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Enfoque temático, misión y alcance editorial de la revista.'),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('description.en')
                                                ->label('Description (EN)'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('description.pt')
                                                ->label('Descrição (PT)'),
                                        ]),
                                    ]),
                                Forms\Components\TagsInput::make('subject_areas')
                                    ->label('Áreas Temáticas')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Disciplinas principales que cubre la revista.'),
                                Forms\Components\TagsInput::make('target_audience')
                                    ->label('Público Objetivo')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'A quién está dirigida la revista.'),
                                Forms\Components\TagsInput::make('publication_languages')
                                    ->label('Idiomas de Publicación')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Idiomas en los que se publican artículos.'),
                            ])->columns(2),

                        Tab::make('Acceso Abierto')
                            ->schema([
                                Forms\Components\Toggle::make('is_open_access')
                                    ->label('Es Acceso Abierto')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Indica si todos los artículos están disponibles gratuitamente.')
                                    ->live(),
                                Forms\Components\Select::make('access_type')
                                    ->label('Tipo de Acceso')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Completo: todo gratuito. Híbrido: parcial. Restringido: suscripción.')
                                    ->options([
                                        'full_oa' => 'Acceso Abierto Completo',
                                        'hybrid' => 'Híbrido',
                                        'restricted' => 'Restringido',
                                    ]),
                                Forms\Components\Toggle::make('articles_accessible_without_registration')
                                    ->label('Artículos Accesibles sin Registro')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Se pueden leer sin crear una cuenta?'),
                                Forms\Components\Toggle::make('allows_self_archiving')
                                    ->label('Permite Auto-archivo')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Los autores pueden depositar sus artículos en repositorios?'),
                                Forms\Components\TextInput::make('open_access_policy_url')
                                    ->label('URL Política de Acceso Abierto')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Enlace a la página de la política de acceso abierto.')
                                    ->url(),
                                Forms\Components\Toggle::make('has_embargo')
                                    ->label('Tiene Embargo')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Los artículos tienen restricción temporal antes de ser abiertos?')
                                    ->live(),
                                Forms\Components\TextInput::make('embargo_months')
                                    ->label('Meses de Embargo')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Duración del período de restricción.')
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('has_embargo')),
                            ])->columns(2),

                        Tab::make('Copyright y Licencias')
                            ->schema([
                                Forms\Components\Select::make('license_type')
                                    ->label('Tipo de Licencia')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Licencia bajo la cual se publican los artículos.')
                                    ->options([
                                        'CC-BY' => 'CC-BY',
                                        'CC-BY-SA' => 'CC-BY-SA',
                                        'CC-BY-NC' => 'CC-BY-NC',
                                        'CC-BY-ND' => 'CC-BY-ND',
                                        'CC-BY-NC-SA' => 'CC-BY-NC-SA',
                                        'CC-BY-NC-ND' => 'CC-BY-NC-ND',
                                        'Copyright' => 'Copyright',
                                        'Other' => 'Otra',
                                    ]),
                                Forms\Components\TextInput::make('license_url')
                                    ->label('URL de Licencia')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Enlace al texto completo de la licencia.')
                                    ->url(),
                                Forms\Components\Toggle::make('authors_retain_copyright')
                                    ->label('Autores Retienen Copyright')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Los autores mantienen los derechos de autor tras publicar?'),
                                Forms\Components\Toggle::make('allows_commercial_reuse')
                                    ->label('Permite Uso Comercial')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Se permite reutilizar el contenido con fines comerciales?'),
                                Tabs::make('copyright_policy_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.es')
                                                ->label('Política de Copyright (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Texto o URL de la política de derechos de autor.'),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.en')
                                                ->label('Copyright Policy (EN)'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.pt')
                                                ->label('Política de Copyright (PT)'),
                                        ]),
                                    ]),
                                Forms\Components\Toggle::make('licenses_visible_in_articles')
                                    ->label('Licencias Visibles en Artículos')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Cada artículo muestra la licencia bajo la que se publica?'),
                            ])->columns(2),

                        Tab::make('Editorial')
                            ->schema([
                                Tabs::make('publishing_institution_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.es')
                                                ->label('Institución Editora (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Universidad u organización que publica la revista.'),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.en')
                                                ->label('Publishing Institution (EN)'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.pt')
                                                ->label('Instituição Editora (PT)'),
                                        ]),
                                    ]),
                                Tabs::make('editor_name_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('editor_name.es')
                                                ->label('Nombre del Editor (ES)')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: 'Director/a o editor/a jefe de la revista.'),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('editor_name.en')
                                                ->label('Editor Name (EN)'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('editor_name.pt')
                                                ->label('Nome do Editor (PT)'),
                                        ]),
                                    ]),
                                Forms\Components\TextInput::make('institutional_email')
                                    ->label('Email Institucional')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Correo con dominio institucional (no Gmail/Yahoo).')
                                    ->email(),
                                Forms\Components\Toggle::make('editorial_board_visible')
                                    ->label('Comité Editorial Visible')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿El sitio web muestra nombres y afiliaciones del comité?'),
                                Forms\Components\TextInput::make('editorial_board_url')
                                    ->label('URL Comité Editorial')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Enlace a la página donde se lista el comité.')
                                    ->url(),
                                Forms\Components\Select::make('peer_review_type')
                                    ->label('Tipo de Revisión por Pares')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Método de evaluación de artículos por expertos.')
                                    ->options([
                                        'double_blind' => 'Doble Ciego',
                                        'single_blind' => 'Simple Ciego',
                                        'open' => 'Abierta',
                                        'post_publication' => 'Post Publicación',
                                    ]),
                                Forms\Components\Select::make('publication_frequency')
                                    ->label('Frecuencia de Publicación')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Con qué frecuencia se publican nuevos números.')
                                    ->options([
                                        'annual' => 'Anual',
                                        'biannual' => 'Semestral',
                                        'quarterly' => 'Trimestral',
                                        'bimonthly' => 'Bimestral',
                                        'monthly' => 'Mensual',
                                        'continuous' => 'Continua',
                                    ]),
                            ])->columns(2),

                        Tab::make('Modelo de Negocio')
                            ->schema([
                                Forms\Components\Toggle::make('charges_apc')
                                    ->label('Cobra APC')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Los autores pagan para publicar?')
                                    ->live(),
                                Forms\Components\TextInput::make('apc_amount')
                                    ->label('Monto APC')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Costo por artículo publicado.')
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\TextInput::make('apc_currency')
                                    ->label('Moneda APC')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Moneda del cobro (ej. USD, EUR).')
                                    ->maxLength(3)
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\Toggle::make('has_apc_waivers')
                                    ->label('Tiene Exenciones de APC')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Se ofrecen descuentos o exenciones?')
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\TagsInput::make('funding_sources')
                                    ->label('Fuentes de Financiamiento')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Cómo se financia la publicación?'),
                                Forms\Components\Toggle::make('has_advertising')
                                    ->label('Tiene Publicidad')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿El sitio muestra anuncios publicitarios?'),
                                Forms\Components\Toggle::make('business_model_transparent')
                                    ->label('Modelo de Negocio Transparente')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Se publica información clara sobre costos y financiamiento?'),
                            ])->columns(2),

                        Tab::make('Ética')
                            ->schema([
                                Forms\Components\Toggle::make('has_ethics_policy')
                                    ->label('Tiene Política de Ética')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Tiene publicada una política de ética editorial?'),
                                Forms\Components\Toggle::make('adheres_to_cope')
                                    ->label('Adhiere a COPE')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Sigue las directrices del Committee on Publication Ethics?'),
                                Forms\Components\Toggle::make('has_antiplagiarism_policy')
                                    ->label('Tiene Política Antiplagio')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Verifica la originalidad con herramientas antiplagio?'),
                                Forms\Components\TextInput::make('antiplagiarism_tool')
                                    ->label('Herramienta Antiplagio')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Software utilizado (ej. Turnitin, iThenticate).'),
                                Forms\Components\Toggle::make('has_conflict_of_interest_policy')
                                    ->label('Tiene Política de Conflicto de Interés')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Se exige declarar conflictos de interés?'),
                                Forms\Components\Toggle::make('declares_ai_use')
                                    ->label('Declara Uso de IA')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Tiene política sobre el uso de IA en artículos?'),
                                Forms\Components\Toggle::make('assigns_doi')
                                    ->label('Asigna DOI')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: '¿Cada artículo tiene un DOI único?'),
                            ])->columns(2),

                        Tab::make('Evaluación')
                            ->schema([
                                Forms\Components\TextInput::make('current_score')
                                    ->label('Puntuación Actual')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Resultado numérico de la evaluación (0-100).')
                                    ->numeric()
                                    ->suffix('%')
                                    ->disabled(),
                                Forms\Components\TextInput::make('current_level')
                                    ->label('Nivel Actual')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Nivel asignado según la puntuación (A, B, C).')
                                    ->disabled(),
                                Forms\Components\DateTimePicker::make('evaluated_at')
                                    ->label('Fecha de Evaluación')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Fecha de la última evaluación realizada.')
                                    ->disabled(),
                                Forms\Components\Textarea::make('evaluation_notes')
                                    ->label('Observaciones de Evaluación')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Notas del evaluador sobre la revista.')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('OAI Cosecha')
                            ->schema([
                                Forms\Components\TextInput::make('oai_base_url')
                                    ->label('URL Base OAI-PMH')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Endpoint OAI-PMH para cosechar artículos automáticamente.')
                                    ->placeholder('https://revista.edu/oai')
                                    ->url()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('oai_set_spec')
                                    ->label('Set Spec (opcional)')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Subconjunto de registros a cosechar.')
                                    ->dehydrated(),
                                Forms\Components\Select::make('oai_metadata_prefix')
                                    ->label('Prefijo de Metadatos')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: 'Formato de metadatos del endpoint.')
                                    ->options([
                                        'oai_dc' => 'Dublin Core (oai_dc)',
                                        'marcxml' => 'MARCXML',
                                        'oai_datacite' => 'DataCite',
                                    ])
                                    ->default('oai_dc')
                                    ->dehydrated(),
                                Forms\Components\Placeholder::make('oai_last_harvested_at')
                                    ->label('Última Cosecha')
                                    ->content(fn (?Journal $record): string => $record?->oai_last_harvested_at?->format('d/m/Y H:i') ?? 'Nunca')
                                    ->hiddenOn('create'),
                                Forms\Components\Placeholder::make('articles_count')
                                    ->label('Artículos Cosechados')
                                    ->content(fn (?Journal $record): string => $record ? (string) $record->harvestedArticles()->count() : '0')
                                    ->hiddenOn('create'),
                            ])->columns(2),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->formatStateUsing(fn (Journal $record): string => $record->getTranslationWithFallback('title'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propietario')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state, Journal $record): string =>
                        $state === 'certified' && $record->seal_expires_at?->isPast() ? 'gray' :
                        match ($state) {
                            'draft' => 'gray',
                            'submitted' => 'warning',
                            'requires_changes_listing' => 'danger',
                            'requires_changes_evaluation' => 'danger',
                            'pending_listing' => 'info',
                            'listed' => 'success',
                            'evaluated' => 'primary',
                            'certified' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->formatStateUsing(fn (string $state, Journal $record): string =>
                        $state === 'certified' && $record->seal_expires_at?->isPast()
                            ? '<s>Certificada</s>'
                            : match ($state) {
                                'draft' => 'Borrador',
                                'submitted' => 'Enviada',
                                'requires_changes_listing' => 'Correcciones (Listado)',
                                'requires_changes_evaluation' => 'Correcciones (Evaluación)',
                                'pending_listing' => 'Pendiente de Listar',
                                'listed' => 'Listada',
                                'evaluated' => 'Evaluada',
                                'certified' => 'Certificada',
                                'rejected' => 'Rechazada',
                                default => $state,
                            }
                    )
                    ->html()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedEvaluator.name')
                    ->label('Evaluador')
                    ->placeholder('Sin asignar')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('current_score')
                    ->label('Nota')
                    ->numeric(2)
                    ->suffix('%')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('listed_at')
                    ->label('Listado')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label('Evaluado')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->description(fn (Journal $record): string =>
                        $record->evaluated_at ? $record->evaluated_at->locale('es')->diffForHumans() : ''
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('seal_expires_at')
                    ->label('Vence Sello')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (Journal $record): string =>
                        $record->seal_expires_at?->isPast() ? 'danger' :
                        ($record->seal_expires_at?->diffInDays(now()) <= 60 ? 'warning' : 'success')
                    )
                    ->description(fn (Journal $record): string => collect([
                        $record->seal_expires_at
                            ? ($record->seal_expires_at->isPast()
                                ? (int) now()->diffInDays($record->seal_expires_at) . ' días atrasado'
                                : (int) now()->diffInDays($record->seal_expires_at) . ' días para vencimiento')
                            : null,
                        $record->seal_notified_at
                            ? 'Notificado: ' . $record->seal_notified_at->format('d/m/Y')
                            : 'Sin notificar',
                    ])->filter()->implode(' · '))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'submitted' => 'Enviada',
                        'requires_changes_listing' => 'Correcciones (Listado)',
                        'requires_changes_evaluation' => 'Correcciones (Evaluación)',
                        'pending_listing' => 'Pendiente de Listar',
                        'listed' => 'Listada',
                        'evaluated' => 'Evaluada',
                        'certified' => 'Certificada',
                        'rejected' => 'Rechazada',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_evaluator_id')
                    ->label('Evaluador')
                    ->relationship('assignedEvaluator', 'name'),
                Tables\Filters\SelectFilter::make('seal_status')
                    ->label('Estado del Sello')
                    ->options([
                        'active' => 'Activo',
                        'expiring_soon' => 'Próximo a vencer',
                        'expired' => 'Vencido',
                    ]),
                Tables\Filters\Filter::make('seal_expiring_30d')
                    ->label('Sello vence en 30 días')
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '>', now())
                        ->where('seal_expires_at', '<=', now()->addDays(30))
                    ),
                Tables\Filters\Filter::make('seal_expired')
                    ->label('Sello vencido')
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '<', now())
                    ),
                Tables\Filters\Filter::make('seal_not_notified')
                    ->label('Sin notificar')
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '<=', now()->addDays(30))
                        ->whereNull('seal_notified_at')
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('assign_evaluator')
                    ->label('Asignar')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (Journal $record): bool => in_array($record->status, ['submitted', 'requires_changes_evaluation', 'evaluated']))
                    ->form([
                        Forms\Components\Select::make('assigned_evaluator_id')
                            ->label('Evaluador')
                            ->options(fn () => \App\Models\User::role('evaluator')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Journal $record, array $data): void {
                        $record->update([
                            'assigned_evaluator_id' => $data['assigned_evaluator_id'],
                        ]);

                        // Notify assigned evaluator via email
                        $evaluator = User::find($data['assigned_evaluator_id']);
                        if ($evaluator) {
                            $evaluator->notify(new EvaluatorAssigned($record));
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Evaluador asignado correctamente')
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\Action::make('evaluate')
                    ->label('Evaluar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->visible(fn (Journal $record): bool => in_array($record->status, ['submitted', 'requires_changes_evaluation', 'evaluated']))
                    ->url(fn (Journal $record): string => static::getUrl('evaluate', ['record' => $record])),

                \Filament\Actions\Action::make('review_listing')
                    ->label('Revisar Solicitud de Listado')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(fn (Journal $record): bool => in_array($record->status, ['pending_listing', 'requires_changes_listing']))
                    ->url(fn (Journal $record): string => static::getUrl('review_listing', ['record' => $record])),

                \Filament\Actions\Action::make('view_evaluation')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (Journal $record): bool => $record->isEvaluated())
                    ->url(fn (Journal $record): string => static::getUrl('evaluate', ['record' => $record])),

                \Filament\Actions\Action::make('harvest_oai')
                    ->label('Cosechar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (Journal $record): bool => !empty($record->oai_base_url))
                    ->requiresConfirmation()
                    ->modalHeading('Cosechar Artículos OAI-PMH')
                    ->modalDescription('Se obtendrán artículos del endpoint OAI-PMH configurado. Esto puede tardar unos minutos.')
                    ->action(function (Journal $record): void {
                        try {
                            $service = app(OaiPmhService::class);
                            $count = $service->listRecords($record);
                            Notification::make()
                                ->title('✅ Cosecha completada')
                                ->body("{$count} artículo(s) obtenidos.")
                                ->success()
                                ->duration(8000)
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Error en la cosecha')
                                ->body($e->getMessage())
                                ->danger()
                                ->duration(8000)
                                ->send();
                        }
                    }),

                \Filament\Actions\Action::make('notify_seal')
                    ->label('Notificar')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn (Journal $record): bool =>
                        $record->seal_expires_at !== null
                        && $record->seal_expires_at->lte(now()->addDays(30))
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Enviar recordatorio de renovación')
                    ->modalDescription(fn (Journal $record): string =>
                        $record->seal_notified_at
                            ? "Ya se envió un recordatorio el {$record->seal_notified_at->format('d/m/Y H:i')}. ¿Deseas enviar otro?"
                            : "Se enviará un email al editor de \"{$record->getTranslationWithFallback('title')}\" recordándole que su sello " .
                              ($record->seal_expires_at->isPast() ? 'ha vencido.' : "vence el {$record->seal_expires_at->format('d/m/Y')}.")
                    )
                    ->action(function (Journal $record): void {
                        $owner = $record->user;
                        if (!$owner) return;

                        if ($record->seal_expires_at->isPast()) {
                            $owner->notify(new SealExpired($record));
                        } else {
                            $owner->notify(new SealExpiringSoon($record));
                        }

                        $record->update(['seal_notified_at' => now()]);

                        Notification::make()
                            ->title('Recordatorio enviado')
                            ->body("Se notificó a {$owner->name} ({$owner->email})")
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\ForceDeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
                ])->tooltip('Acciones')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulk_notify_seal')
                        ->label('Enviar recordatorio de renovación')
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Enviar recordatorio masivo')
                        ->modalDescription('Se enviará un email de recordatorio de renovación a los editores de las revistas seleccionadas que tengan sello por vencer o vencido.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $sent = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if (!$record->seal_expires_at || !$record->user) {
                                    $skipped++;
                                    continue;
                                }

                                if (!$record->seal_expires_at->lte(now()->addDays(30))) {
                                    $skipped++;
                                    continue;
                                }

                                $owner = $record->user;

                                if ($record->seal_expires_at->isPast()) {
                                    $owner->notify(new SealExpired($record));
                                } else {
                                    $owner->notify(new SealExpiringSoon($record));
                                }

                                $record->update(['seal_notified_at' => now()]);
                                $sent++;
                            }

                            Notification::make()
                                ->title('Recordatorios enviados')
                                ->body("Enviados: {$sent} | Omitidos: {$skipped}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HarvestedArticlesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournals::route('/'),
            'create' => Pages\CreateJournal::route('/create'),
            'edit' => Pages\EditJournal::route('/{record}/edit'),
            'evaluate' => Pages\EvaluateJournal::route('/{record}/evaluate'),
            'review_listing' => Pages\ReviewListing::route('/{record}/review-listing'),
        ];
    }
}
