<?php

namespace App\Filament\Resources;

use App\Filament\Exports\JournalExporter;
use App\Filament\Resources\JournalResource\Pages;
use App\Filament\Resources\JournalResource\RelationManagers;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\EvaluatorAssigned;
use App\Notifications\SealExpired;
use App\Notifications\SealExpiringSoon;
use App\Services\OaiPmhService;
use BackedEnum;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.content');
    }

    /**
     * Roadmap #35 — un evaluator solo ve journals que tiene asignados
     * (assigned_evaluator_id). super_admin ve todo, sin scoping.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            // Roadmap #35 — el evaluador ve un journal si lo tiene asignado por
            // assigned_evaluator_id O si tiene una task de evaluación abierta
            // asignada (assigned_to). Sin esta segunda vía, una asignación hecha
            // solo desde la task (sin sincronizar assigned_evaluator_id) daba 404
            // en /admin/journals/{id}/evaluate (resolveRecord usa este scope).
            $query->where(function (Builder $q) use ($user) {
                $q->where('assigned_evaluator_id', $user->id)
                    ->orWhereIn('id', \App\Models\AdminTask::query()
                        ->where('related_type', Journal::class)
                        ->whereIn('type', [
                            \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL,
                            \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL,
                            \App\Models\AdminTask::TYPE_RENEWAL_EVALUATION,
                        ])
                        ->whereIn('status', \App\Models\AdminTask::STATUSES_OPEN)
                        ->where('assigned_to', $user->id)
                        ->select('related_id')
                    );
            });
        }

        return $query;
    }

    /**
     * Roadmap #35 — el evaluator interactúa SOLO con Tareas, nunca navega el
     * recurso Journals. Ocultamos el item del nav pero NO revocamos los permisos
     * Journal: EvaluateJournal::authorizeResourceAccess() depende de canViewAny()
     * (= ViewAny:Journal), así que quitar el permiso rompería la página de
     * evaluación. La lista queda además bloqueada por URL en ListJournals::mount().
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Journal Details')
                    ->tabs([
                        Tab::make(__('admin.journal.tab_basic'))
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label(__('admin.journal.logo'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.logo_tooltip'))
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
                                                ->label(__('admin.journal.title').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.title_tooltip'))
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, ?string $state) => filled($state) ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('title.en')
                                                ->label(__('admin.journal.title').' ('.__('admin.common.lang_suffix.en').')')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('title.pt')
                                                ->label(__('admin.journal.title').' ('.__('admin.common.lang_suffix.pt').')')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Select::make('primary_locale')
                                    ->label(__('admin.common.language_primary'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.language_primary_tooltip'))
                                    ->options([
                                        'es' => __('admin.common.language_options.es'),
                                        'en' => __('admin.common.language_options.en'),
                                        'pt' => __('admin.common.language_options.pt'),
                                    ])
                                    ->default('es')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.slug_tooltip'))
                                    ->required()
                                    ->maxLength(255),
                                Tabs::make('abbreviated_name_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.es')
                                                ->label(__('admin.journal.short_name').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.short_name_tooltip'))
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.en')
                                                ->label(__('admin.journal.short_name').' ('.__('admin.common.lang_suffix.en').')')
                                                ->maxLength(255),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('abbreviated_name.pt')
                                                ->label(__('admin.journal.short_name').' ('.__('admin.common.lang_suffix.pt').')')
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label(__('admin.journal.owner'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.owner_tooltip'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label(__('admin.journal.status'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.status_tooltip'))
                                    ->options([
                                        'draft' => __('admin.journal_status.draft'),
                                        'submitted' => __('admin.journal_status.submitted'),
                                        'requires_changes_listing' => __('admin.journal_status.requires_changes_listing'),
                                        'requires_changes_evaluation' => __('admin.journal_status.requires_changes_evaluation'),
                                        'pending_listing' => __('admin.journal_status.pending_listing'),
                                        'listed' => __('admin.journal_status.listed'),
                                        'evaluated' => __('admin.journal_status.evaluated'),
                                        'certified' => __('admin.journal_status.certified'),
                                        'rejected' => __('admin.journal_status.rejected'),
                                    ])
                                    ->required()
                                    ->default('draft'),
                                Forms\Components\Select::make('assigned_evaluator_id')
                                    ->label(__('admin.journal.evaluator'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.evaluator_tooltip'))
                                    ->relationship('assignedEvaluator', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('issn_print')
                                    ->label(__('admin.journal.issn_print'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_issn_print'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('issn_online')
                                    ->label(__('admin.journal.issn_online'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_issn_online'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('publisher')
                                    ->label(__('admin.journal.publisher'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_publisher'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label(__('admin.journal.url'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_url'))
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('country_code')
                                    ->label(__('admin.journal.country_code'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_country_code'))
                                    ->maxLength(2),
                                Forms\Components\TextInput::make('start_year')
                                    ->label(__('admin.journal.start_year'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_start_year'))
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                                Tabs::make('description_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('description.es')
                                                ->label(__('admin.journal.description').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.description_tooltip')),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('description.en')
                                                ->label(__('admin.journal.description').' ('.__('admin.common.lang_suffix.en').')'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('description.pt')
                                                ->label(__('admin.journal.description').' ('.__('admin.common.lang_suffix.pt').')'),
                                        ]),
                                    ]),
                                Forms\Components\TagsInput::make('subject_areas')
                                    ->label(__('admin.journal.topics'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_topics')),
                                Forms\Components\TagsInput::make('target_audience')
                                    ->label(__('admin.journal.target_audience'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_audience')),
                                Forms\Components\TagsInput::make('publication_languages')
                                    ->label(__('admin.journal.publication_languages'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_pub_languages')),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_open_access'))
                            ->schema([
                                Forms\Components\Toggle::make('is_open_access')
                                    ->label(__('admin.journal.is_open_access'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_is_open_access'))
                                    ->live(),
                                Forms\Components\Select::make('access_type')
                                    ->label(__('admin.journal.access_type'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_access_type'))
                                    ->options([
                                        'full_oa' => __('admin.journal.access_full_open'),
                                        'hybrid' => __('admin.journal.access_hybrid'),
                                        'restricted' => __('admin.journal.access_restricted'),
                                    ]),
                                Forms\Components\Toggle::make('articles_accessible_without_registration')
                                    ->label(__('admin.journal.open_articles'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_open_articles')),
                                Forms\Components\Toggle::make('allows_self_archiving')
                                    ->label(__('admin.journal.self_archiving'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_self_archiving')),
                                Forms\Components\TextInput::make('open_access_policy_url')
                                    ->label(__('admin.journal.oa_policy_url'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_oa_policy_url'))
                                    ->url(),
                                Forms\Components\Toggle::make('has_embargo')
                                    ->label(__('admin.journal.has_embargo'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_has_embargo'))
                                    ->live(),
                                Forms\Components\TextInput::make('embargo_months')
                                    ->label(__('admin.journal.embargo_months'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_embargo_months'))
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('has_embargo')),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_copyright'))
                            ->schema([
                                Forms\Components\Select::make('license_type')
                                    ->label(__('admin.journal.license_type'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_license_type'))
                                    ->options([
                                        'CC-BY' => 'CC-BY',
                                        'CC-BY-SA' => 'CC-BY-SA',
                                        'CC-BY-NC' => 'CC-BY-NC',
                                        'CC-BY-ND' => 'CC-BY-ND',
                                        'CC-BY-NC-SA' => 'CC-BY-NC-SA',
                                        'CC-BY-NC-ND' => 'CC-BY-NC-ND',
                                        'Copyright' => 'Copyright',
                                        'Other' => __('admin.journal.license_other'),
                                    ]),
                                Forms\Components\TextInput::make('license_url')
                                    ->label(__('admin.journal.license_url'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_license_url'))
                                    ->url(),
                                Forms\Components\Toggle::make('authors_retain_copyright')
                                    ->label(__('admin.journal.authors_retain_copyright'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_authors_retain')),
                                Forms\Components\Toggle::make('allows_commercial_reuse')
                                    ->label(__('admin.journal.allows_commercial_use'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_commercial')),
                                Tabs::make('copyright_policy_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.es')
                                                ->label(__('admin.journal.copyright_policy').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_copyright_policy')),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.en')
                                                ->label(__('admin.journal.copyright_policy').' ('.__('admin.common.lang_suffix.en').')'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\Textarea::make('copyright_policy.pt')
                                                ->label(__('admin.journal.copyright_policy').' ('.__('admin.common.lang_suffix.pt').')'),
                                        ]),
                                    ]),
                                Forms\Components\Toggle::make('licenses_visible_in_articles')
                                    ->label(__('admin.journal.licenses_visible'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_licenses_visible')),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_editorial'))
                            ->schema([
                                Tabs::make('publishing_institution_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.es')
                                                ->label(__('admin.journal.editorial_institution').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_inst')),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.en')
                                                ->label(__('admin.journal.editorial_institution').' ('.__('admin.common.lang_suffix.en').')'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('publishing_institution.pt')
                                                ->label(__('admin.journal.editorial_institution').' ('.__('admin.common.lang_suffix.pt').')'),
                                        ]),
                                    ]),
                                Tabs::make('editor_name_tabs')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make('ES')->schema([
                                            Forms\Components\TextInput::make('editor_name.es')
                                                ->label(__('admin.journal.editor_name').' ('.__('admin.common.lang_suffix.es').')')
                                                ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_editor')),
                                        ]),
                                        Tab::make('EN')->schema([
                                            Forms\Components\TextInput::make('editor_name.en')
                                                ->label(__('admin.journal.editor_name').' ('.__('admin.common.lang_suffix.en').')'),
                                        ]),
                                        Tab::make('PT')->schema([
                                            Forms\Components\TextInput::make('editor_name.pt')
                                                ->label(__('admin.journal.editor_name').' ('.__('admin.common.lang_suffix.pt').')'),
                                        ]),
                                    ]),
                                Forms\Components\TextInput::make('institutional_email')
                                    ->label(__('admin.journal.institutional_email'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_inst_email'))
                                    ->email(),
                                Forms\Components\Toggle::make('editorial_board_visible')
                                    ->label(__('admin.journal.editorial_board_visible'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_board')),
                                Forms\Components\TextInput::make('editorial_board_url')
                                    ->label(__('admin.journal.editorial_board_url'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_board_url'))
                                    ->url(),
                                Forms\Components\Select::make('peer_review_type')
                                    ->label(__('admin.journal.peer_review_type'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_peer_type'))
                                    ->options([
                                        'double_blind' => __('admin.journal.peer_double_blind'),
                                        'single_blind' => __('admin.journal.peer_single_blind'),
                                        'open' => __('admin.journal.peer_open'),
                                        'post_publication' => __('admin.journal.peer_post_publication'),
                                    ]),
                                Forms\Components\Select::make('publication_frequency')
                                    ->label(__('admin.journal.frequency'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_frequency'))
                                    ->options([
                                        'annual' => __('admin.journal.freq_annual'),
                                        'biannual' => __('admin.journal.freq_biannual'),
                                        'quarterly' => __('admin.journal.freq_quarterly'),
                                        'bimonthly' => __('admin.journal.freq_bimonthly'),
                                        'monthly' => __('admin.journal.freq_monthly'),
                                        'continuous' => __('admin.journal.freq_continuous'),
                                    ]),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_business'))
                            ->schema([
                                Forms\Components\Toggle::make('charges_apc')
                                    ->label(__('admin.journal.charges_apc'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_charges_apc'))
                                    ->live(),
                                Forms\Components\TextInput::make('apc_amount')
                                    ->label(__('admin.journal.apc_amount'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_apc_amount'))
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\TextInput::make('apc_currency')
                                    ->label(__('admin.journal.apc_currency'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_apc_currency'))
                                    ->maxLength(3)
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\Toggle::make('has_apc_waivers')
                                    ->label(__('admin.journal.apc_waivers'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_apc_waivers'))
                                    ->visible(fn (Get $get) => $get('charges_apc')),
                                Forms\Components\TagsInput::make('funding_sources')
                                    ->label(__('admin.journal.funding_sources'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_funding')),
                                Forms\Components\Toggle::make('has_advertising')
                                    ->label(__('admin.journal.has_ads'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_ads')),
                                Forms\Components\Toggle::make('business_model_transparent')
                                    ->label(__('admin.journal.transparent_business'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_transparent')),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_ethics'))
                            ->schema([
                                Forms\Components\Toggle::make('has_ethics_policy')
                                    ->label(__('admin.journal.has_ethics_policy'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_ethics')),
                                Forms\Components\Toggle::make('adheres_to_cope')
                                    ->label(__('admin.journal.cope_member'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_cope')),
                                Forms\Components\Toggle::make('has_antiplagiarism_policy')
                                    ->label(__('admin.journal.has_antiplagiarism'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_antiplag')),
                                Forms\Components\TextInput::make('antiplagiarism_tool')
                                    ->label(__('admin.journal.antiplagiarism_tool'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_antiplag_tool')),
                                Forms\Components\Toggle::make('has_conflict_of_interest_policy')
                                    ->label(__('admin.journal.has_coi_policy'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_coi')),
                                Forms\Components\Toggle::make('declares_ai_use')
                                    ->label(__('admin.journal.declares_ai_use'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_ai')),
                                Forms\Components\Toggle::make('assigns_doi')
                                    ->label(__('admin.journal.assigns_doi'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_doi')),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_evaluation'))
                            ->schema([
                                Forms\Components\TextInput::make('current_score')
                                    ->label(__('admin.journal.current_score'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_score'))
                                    ->numeric()
                                    ->suffix('%')
                                    ->disabled(),
                                Forms\Components\TextInput::make('current_level')
                                    ->label(__('admin.journal.current_level'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_level'))
                                    ->disabled(),
                                Forms\Components\DateTimePicker::make('evaluated_at')
                                    ->label(__('admin.journal.evaluation_date'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_eval_date'))
                                    ->disabled(),
                                Forms\Components\Textarea::make('evaluation_notes')
                                    ->label(__('admin.journal.evaluation_notes'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_eval_notes'))
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_metrics'))
                            ->schema([
                                Forms\Components\Placeholder::make('h_index_display')
                                    ->label(__('admin.journal.metrics_h_index'))
                                    ->content(fn (?Journal $record): string => $record?->h_index !== null ? (string) $record->h_index : '—'),
                                Forms\Components\Placeholder::make('total_citations_display')
                                    ->label(__('admin.journal.metrics_total_citations'))
                                    ->content(fn (?Journal $record): string => $record?->total_citations !== null ? number_format($record->total_citations) : '—'),
                                Forms\Components\Placeholder::make('mean_citedness_display')
                                    ->label(__('admin.journal.metrics_mean_citedness'))
                                    ->content(fn (?Journal $record): string => $record?->mean_citedness_2y !== null ? number_format((float) $record->mean_citedness_2y, 3) : '—'),
                                Forms\Components\Placeholder::make('metrics_source_display')
                                    ->label(__('admin.journal.metrics_source'))
                                    ->content(fn (?Journal $record): string => $record?->metrics_source ? __('admin.journal.metrics_source_'.$record->metrics_source) : '—'),
                                Forms\Components\Placeholder::make('metrics_updated_display')
                                    ->label(__('admin.journal.metrics_updated_at'))
                                    ->content(fn (?Journal $record): string => $record?->metrics_updated_at?->format('d/m/Y H:i') ?? '—'),
                                Forms\Components\Placeholder::make('openalex_venue_id_display')
                                    ->label(__('admin.journal.metrics_openalex_id'))
                                    ->content(fn (?Journal $record): string => $record?->openalex_venue_id ?: '—'),
                                Forms\Components\Textarea::make('metrics_notes')
                                    ->label(__('admin.journal.metrics_notes'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make(__('admin.journal.tab_oai'))
                            ->schema([
                                Forms\Components\TextInput::make('oai_base_url')
                                    ->label(__('admin.journal.oai_base_url'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_oai_base'))
                                    ->placeholder('https://revista.edu/oai')
                                    ->url()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('oai_set_spec')
                                    ->label(__('admin.journal.oai_set_spec'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_oai_set'))
                                    ->dehydrated(),
                                Forms\Components\Select::make('oai_metadata_prefix')
                                    ->label(__('admin.journal.oai_metadata_prefix'))
                                    ->hintIcon('heroicon-o-information-circle', tooltip: __('admin.journal.tooltip_oai_prefix'))
                                    ->options([
                                        'oai_dc' => 'Dublin Core (oai_dc)',
                                        'marcxml' => 'MARCXML',
                                        'oai_datacite' => 'DataCite',
                                    ])
                                    ->default('oai_dc')
                                    ->dehydrated(),
                                Forms\Components\Placeholder::make('oai_last_harvested_at')
                                    ->label(__('admin.journal.oai_last_harvest'))
                                    ->content(fn (?Journal $record): string => $record?->oai_last_harvested_at?->format('d/m/Y H:i') ?? '—')
                                    ->hiddenOn('create'),
                                Forms\Components\Placeholder::make('articles_count')
                                    ->label(__('admin.journal.oai_articles_harvested'))
                                    ->content(fn (?Journal $record): string => $record ? (string) $record->harvestedArticles()->count() : '0')
                                    ->hiddenOn('create'),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.journal.title'))
                    ->formatStateUsing(fn (Journal $record): string => $record->getTranslationWithFallback('title'))
                    // title/abbreviated_name son columnas JSON (HasTranslations). El LIKE
                    // sobre JSON en MySQL es sensible a mayúsculas/acentos; usamos los
                    // macros whereTranslatableLike/orderByTranslatable (AppServiceProvider).
                    ->searchable(query: fn ($query, string $search) => $query->whereTranslatableLike(['title', 'abbreviated_name'], $search))
                    ->sortable(query: fn ($query, string $direction) => $query->orderByTranslatable('title', $direction))
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('issn_print')
                    ->label('ISSN')
                    ->placeholder('—')
                    ->formatStateUsing(fn (Journal $record): string => $record->issn_print ?: ($record->issn_online ?: '—'))
                    ->description(fn (Journal $record): ?string => $record->issn_print && $record->issn_online ? 'Online: '.$record->issn_online : null
                    )
                    ->searchable(query: fn ($query, string $search) => $query->where('issn_print', 'like', "%{$search}%")
                        ->orWhere('issn_online', 'like', "%{$search}%")
                    )
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.journal.owner'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.journal.status'))
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state, Journal $record): string => $state === 'certified' && $record->seal_expires_at?->isPast() ? 'gray' :
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
                    ->formatStateUsing(fn (string $state, Journal $record): string => $state === 'certified' && $record->seal_expires_at?->isPast()
                            ? '<s>'.__('admin.journal_status.certified_short').'</s>'
                            : match ($state) {
                                'draft' => __('admin.journal_status.draft'),
                                'submitted' => __('admin.journal_status.submitted'),
                                'requires_changes_listing' => __('admin.journal_status.requires_changes_listing'),
                                'requires_changes_evaluation' => __('admin.journal_status.requires_changes_evaluation'),
                                'pending_listing' => __('admin.journal_status.pending_listing'),
                                'listed' => __('admin.journal_status.listed_short'),
                                'evaluated' => __('admin.journal_status.evaluated_short'),
                                'certified' => __('admin.journal_status.certified_short'),
                                'rejected' => __('admin.journal_status.rejected'),
                                default => $state,
                            }
                    )
                    ->html()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedEvaluator.name')
                    ->label(__('admin.journal.evaluator_short'))
                    ->placeholder(__('admin.common.unassigned'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('current_score')
                    ->label(__('admin.journal.score_short'))
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('listed_at')
                    ->label(__('admin.journal.listed_short'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label(__('admin.journal.evaluated_short'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->description(fn (Journal $record): string => $record->evaluated_at ? $record->evaluated_at->locale(app()->getLocale())->diffForHumans() : ''
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('seal_expires_at')
                    ->label(__('admin.journal.seal_expires_short'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (Journal $record): string => $record->seal_expires_at?->isPast() ? 'danger' :
                        ($record->seal_expires_at?->diffInDays(now()) <= 60 ? 'warning' : 'success')
                    )
                    ->description(fn (Journal $record): string => collect([
                        $record->seal_expires_at
                            ? ($record->seal_expires_at->isPast()
                                ? (int) now()->diffInDays($record->seal_expires_at).' días atrasado'
                                : (int) now()->diffInDays($record->seal_expires_at).' días para vencimiento')
                            : null,
                        $record->seal_notified_at
                            ? 'Notificado: '.$record->seal_notified_at->format('d/m/Y')
                            : 'Sin notificar',
                    ])->filter()->implode(' · '))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.journal.status'))
                    ->options([
                        'draft' => __('admin.journal_status.draft'),
                        'submitted' => __('admin.journal_status.submitted'),
                        'requires_changes_listing' => __('admin.journal_status.requires_changes_listing'),
                        'requires_changes_evaluation' => __('admin.journal_status.requires_changes_evaluation'),
                        'pending_listing' => __('admin.journal_status.pending_listing'),
                        'listed' => __('admin.journal_status.listed_short'),
                        'evaluated' => __('admin.journal_status.evaluated_short'),
                        'certified' => __('admin.journal_status.certified_short'),
                        'rejected' => __('admin.journal_status.rejected'),
                    ]),
                Tables\Filters\SelectFilter::make('assigned_evaluator_id')
                    ->label(__('admin.journal.evaluator_short'))
                    ->relationship('assignedEvaluator', 'name'),
                Tables\Filters\SelectFilter::make('seal_status')
                    ->label(__('admin.journal.filter_seal_status'))
                    ->options([
                        'active' => __('admin.seal_status.active'),
                        'expiring_soon' => __('admin.seal_status.expiring_soon'),
                        'expired' => __('admin.seal_status.expired'),
                    ]),
                Tables\Filters\Filter::make('seal_expiring_30d')
                    ->label(__('admin.journal.filter_seal_expiring_30'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '>', now())
                        ->where('seal_expires_at', '<=', now()->addDays(30))
                    ),
                Tables\Filters\Filter::make('seal_expired')
                    ->label(__('admin.journal.filter_seal_expired'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '<', now())
                    ),
                Tables\Filters\Filter::make('seal_not_notified')
                    ->label(__('admin.journal.filter_not_notified'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('seal_expires_at')
                        ->where('seal_expires_at', '<=', now()->addDays(30))
                        ->whereNull('seal_notified_at')
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('admin.common.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    // Roadmap #35 — exportar el padrón de revistas es del super_admin.
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                    ->exporter(JournalExporter::class),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('assign_evaluator')
                        ->label(__('admin.journal.action_assign'))
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->visible(fn (Journal $record): bool => in_array($record->status, ['submitted', 'requires_changes_evaluation', 'evaluated'])
                            && (auth()->user()?->hasRole('super_admin') ?? false)
                        )
                        ->form([
                            Forms\Components\Select::make('assigned_evaluator_id')
                                ->label(__('admin.journal.evaluator_short'))
                                // Roadmap #35 — COI: excluir al dueño/editor de la
                                // revista de la lista de evaluadores asignables.
                                ->options(fn (Journal $record) => \App\Models\User::role('evaluator')
                                    ->where('id', '!=', $record->user_id)
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Journal $record, array $data): void {
                            $evaluator = User::find($data['assigned_evaluator_id']);

                            if (! $evaluator) {
                                return;
                            }

                            // Roadmap #35 — COI: no permitir asignar al dueño de la
                            // revista como evaluador (defensa por si burla el filtro).
                            if ((int) $evaluator->id === (int) $record->user_id) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('evaluator_coi.self_assign'))
                                    ->danger()
                                    ->send();

                                return;
                            }

                            // Roadmap #35 — asignación unificada: sincroniza
                            // assigned_evaluator_id + assigned_to de la(s) task(s)
                            // abierta(s) y devuelve la task para notificar sobre ella.
                            $task = $record->assignEvaluator($evaluator);

                            // Una sola notificación con deep-link correcto: si hay
                            // task abierta usamos TaskAssigned (link a la Tarea); si
                            // no (ej. journal ya 'evaluated', pre-asignación antes de
                            // pagar re-evaluación) caemos a EvaluatorAssigned, cuyo CTA
                            // apunta a la página de evaluación del journal.
                            $evaluator->notify($task
                                ? new \App\Notifications\TaskAssigned($task)
                                : new EvaluatorAssigned($record)
                            );

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'evaluator_id' => $evaluator->id,
                                    'evaluator_name' => $evaluator->name,
                                    'synced_task_id' => $task?->id,
                                ])
                                ->log("Evaluador asignado: {$evaluator->name}");

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.journal.notif_evaluator_assigned'))
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('evaluate')
                        ->label(__('admin.journal.action_evaluate'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('warning')
                        ->visible(fn (Journal $record): bool => in_array($record->status, ['submitted', 'requires_changes_evaluation']))
                        ->url(fn (Journal $record): string => static::getUrl('evaluate', ['record' => $record])),

                    // Sprint 3.6 #36: Forzar evaluación sin pago. Casos de uso:
                    // evaluación de cortesía, casos institucionales, evaluación
                    // interna de prueba. Sube status a submitted, crea AdminTask
                    // sin payment_id con metadata.manual_override = motivo.
                    \Filament\Actions\Action::make('force_evaluation')
                        ->label(__('admin.journal.action_evaluate_no_payment'))
                        ->icon('heroicon-o-gift')
                        ->color('emerald')
                        ->visible(fn (Journal $record): bool => in_array(
                            $record->status,
                            ['draft', 'listed', 'evaluated', 'certified', 'rejected'],
                            true
                        ))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.journal.modal_force_eval_heading'))
                        ->modalDescription(__('admin.journal.modal_force_eval_desc'))
                        ->schema([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label(__('admin.journal.force_eval_reason'))
                                ->helperText(__('admin.journal.force_eval_reason_help'))
                                ->required()
                                ->minLength(10)
                                ->maxLength(500)
                                ->rows(3),
                        ])
                        ->action(function (Journal $record, array $data): void {
                            $reason = trim($data['reason']);
                            $previousStatus = $record->status;

                            // Determinar tipo de task según el status previo
                            $taskType = in_array($previousStatus, ['draft', 'listed'], true)
                                ? \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL
                                : \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL;

                            $record->update([
                                'status' => 'submitted',
                                'submitted_at' => now(),
                            ]);

                            \App\Models\AdminTask::create([
                                'type' => $taskType,
                                'title_key' => $taskType === \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL
                                    ? 'tasks.evaluate_journal'
                                    : 'tasks.reevaluate_journal',
                                'title_params' => [
                                    'name' => $record->getTranslationWithFallback('title'),
                                ],
                                'payment_id' => null,
                                'related_type' => Journal::class,
                                'related_id' => $record->id,
                                'status' => \App\Models\AdminTask::STATUS_PENDING,
                                'priority' => \App\Models\AdminTask::PRIORITY_NORMAL,
                                'due_at' => \App\Models\AdminTask::calculateDueAt($taskType),
                                'notes' => "Cortesía — sin pago.\n\nMotivo: {$reason}\n\nAutorizado por: ".(auth()->user()?->name ?? 'admin').".\nStatus previo: {$previousStatus}",
                            ]);

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'previous_status' => $previousStatus,
                                    'new_status' => 'submitted',
                                    'manual_override' => true,
                                    'reason' => $reason,
                                    'task_type' => $taskType,
                                ])
                                ->log("Evaluación forzada sin pago (cortesía): {$reason}");

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.journal.notif_eval_created'))
                                ->body(__('admin.journal.notif_eval_created_body'))
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('review_listing')
                        ->label(__('admin.journal.action_review_listing'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('info')
                        ->visible(fn (Journal $record): bool => in_array($record->status, ['pending_listing', 'requires_changes_listing']))
                        ->url(fn (Journal $record): string => static::getUrl('review_listing', ['record' => $record])),

                    \Filament\Actions\Action::make('view_evaluation')
                        ->label(__('admin.journal.action_view_evaluation'))
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->visible(fn (Journal $record): bool => in_array(
                            $record->status,
                            ['evaluated', 'certified', 'rejected'],
                            true
                        ))
                        ->url(fn (Journal $record): string => static::getUrl('evaluate', ['record' => $record])),

                    \Filament\Actions\Action::make('download_certificate')
                        ->label(__('Download Certificate (PDF)'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (Journal $record) => route('app.journal.certificate.pdf', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Journal $record): bool => $record->status === 'certified'
                            && $record->seal_status === 'active'
                            && $record->seal_expires_at
                            && $record->seal_expires_at->isFuture()
                        ),

                    \Filament\Actions\Action::make('download_report')
                        ->label(__('Download Evaluation Report (PDF)'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (Journal $record) => route('app.journal.report.pdf', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Journal $record): bool => $record->evaluated_at !== null),

                    \Filament\Actions\Action::make('harvest_oai')
                        ->label(__('admin.journal.action_harvest'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->visible(fn (Journal $record): bool => ! empty($record->oai_base_url))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.journal.modal_harvest_heading'))
                        ->modalDescription(__('admin.journal.modal_harvest_desc'))
                        ->action(function (Journal $record): void {
                            try {
                                $service = app(OaiPmhService::class);
                                $count = $service->listRecords($record);

                                activity()
                                    ->performedOn($record)
                                    ->causedBy(auth()->user())
                                    ->withProperties([
                                        'articles_count' => $count,
                                        'oai_base_url' => $record->oai_base_url,
                                    ])
                                    ->log("Cosecha OAI-PMH ejecutada: {$count} artículo(s)");

                                Notification::make()
                                    ->title(__('admin.journal.notif_harvest_ok'))
                                    ->body(__('admin.journal.notif_harvest_ok_body', ['count' => $count]))
                                    ->success()
                                    ->duration(8000)
                                    ->send();
                            } catch (\Exception $e) {
                                activity()
                                    ->performedOn($record)
                                    ->causedBy(auth()->user())
                                    ->withProperties(['error' => $e->getMessage()])
                                    ->log('Cosecha OAI-PMH fallida');

                                Notification::make()
                                    ->title(__('admin.journal.notif_harvest_err'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->duration(8000)
                                    ->send();
                            }
                        }),

                    \Filament\Actions\Action::make('notify_seal')
                        ->label(__('admin.journal.action_notify'))
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->visible(fn (Journal $record): bool => $record->seal_expires_at !== null
                            && $record->seal_expires_at->lte(now()->addDays(30))
                        )
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.journal.modal_reminder_heading'))
                        ->modalDescription(fn (Journal $record): string => $record->seal_notified_at
                                ? "Ya se envió un recordatorio el {$record->seal_notified_at->format('d/m/Y H:i')}. ¿Deseas enviar otro?"
                                : "Se enviará un email al editor de \"{$record->getTranslationWithFallback('title')}\" recordándole que su sello ".
                                  ($record->seal_expires_at->isPast() ? 'ha vencido.' : "vence el {$record->seal_expires_at->format('d/m/Y')}.")
                        )
                        ->action(function (Journal $record): void {
                            $owner = $record->user;
                            if (! $owner) {
                                return;
                            }

                            $isPast = $record->seal_expires_at->isPast();

                            if ($isPast) {
                                $owner->notify(new SealExpired($record));
                            } else {
                                $owner->notify(new SealExpiringSoon($record));
                            }

                            $record->update(['seal_notified_at' => now()]);

                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'recipient' => $owner->email,
                                    'seal_expires_at' => $record->seal_expires_at?->toDateString(),
                                ])
                                ->log($isPast ? 'Recordatorio enviado: sello vencido' : 'Recordatorio enviado: sello por vencer');

                            Notification::make()
                                ->title(__('admin.journal.notif_reminder_sent'))
                                ->body(__('admin.journal.notif_reminder_sent_body', ['name' => $owner->name, 'email' => $owner->email]))
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                    \Filament\Actions\ForceDeleteAction::make(),
                    \Filament\Actions\RestoreAction::make(),
                ])->tooltip(__('admin.common.actions'))->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulk_notify_seal')
                        ->label(__('admin.journal.action_send_renewal_reminder'))
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.journal.modal_reminder_bulk_heading'))
                        ->modalDescription(__('admin.journal.modal_reminder_bulk_desc'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $sent = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if (! $record->seal_expires_at || ! $record->user) {
                                    $skipped++;

                                    continue;
                                }

                                if (! $record->seal_expires_at->lte(now()->addDays(30))) {
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
                                ->title(__('admin.journal.notif_reminders_sent'))
                                ->body(__('admin.journal.notif_reminders_sent_body', ['sent' => $sent, 'skipped' => $skipped]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    \Filament\Actions\BulkAction::make('bulk_refresh_metrics')
                        ->label(__('admin.journal.action_refresh_metrics'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.journal.modal_refresh_metrics_heading'))
                        ->modalDescription(__('admin.journal.modal_refresh_metrics_body'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $service = app(\App\Services\Metrics\JournalMetricsService::class);
                            $refreshed = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                $eligible = $record->status === 'certified'
                                    && $record->seal_expires_at
                                    && $record->seal_expires_at->isFuture();

                                if (! $eligible) {
                                    $skipped++;

                                    continue;
                                }

                                try {
                                    $service->refresh($record);
                                    $refreshed++;
                                } catch (\Throwable $e) {
                                    report($e);
                                    $skipped++;
                                }
                            }

                            Notification::make()
                                ->title(__('admin.journal.notif_metrics_refreshed'))
                                ->body(__('admin.journal.notif_metrics_refreshed_body', ['refreshed' => $refreshed, 'skipped' => $skipped]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label(__('admin.common.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        // Roadmap #35 — exportación masiva exclusiva de super_admin.
                        ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                        ->exporter(JournalExporter::class),
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
            RelationManagers\MetricSnapshotsRelationManager::class,
            RelationManagers\EditorialMembersRelationManager::class,
            \App\Filament\RelationManagers\PaymentsRelationManager::class,
            \App\Filament\RelationManagers\ActivitiesRelationManager::class,
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
