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
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.cms_post.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.cms_post.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.cms_post.plural');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('admin.cms_post.section_content'))
                    ->schema([
                        Tabs::make('title_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\TextInput::make('title.es')
                                        ->label(__('admin.cms_post.title') . ' (' . __('admin.common.lang_suffix.es') . ')')
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
                                        ->label(__('admin.cms_post.title') . ' (' . __('admin.common.lang_suffix.en') . ')')
                                        ->maxLength(255),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\TextInput::make('title.pt')
                                        ->label(__('admin.cms_post.title') . ' (' . __('admin.common.lang_suffix.pt') . ')')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Forms\Components\Select::make('primary_locale')
                            ->label(__('admin.common.language_primary'))
                            ->options([
                                'es' => __('admin.common.language_options.es'),
                                'en' => __('admin.common.language_options.en'),
                                'pt' => __('admin.common.language_options.pt'),
                            ])
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
                                        ->label(__('admin.cms_post.excerpt') . ' (' . __('admin.common.lang_suffix.es') . ')')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\Textarea::make('excerpt.en')
                                        ->label(__('admin.cms_post.excerpt') . ' (' . __('admin.common.lang_suffix.en') . ')')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\Textarea::make('excerpt.pt')
                                        ->label(__('admin.cms_post.excerpt') . ' (' . __('admin.common.lang_suffix.pt') . ')')
                                        ->maxLength(500)
                                        ->rows(3),
                                ]),
                            ]),

                        Tabs::make('content_tabs')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('ES')->schema([
                                    Forms\Components\RichEditor::make('content.es')
                                        ->label(__('admin.cms_post.content') . ' (' . __('admin.common.lang_suffix.es') . ')'),
                                ]),
                                Tab::make('EN')->schema([
                                    Forms\Components\RichEditor::make('content.en')
                                        ->label(__('admin.cms_post.content') . ' (' . __('admin.common.lang_suffix.en') . ')'),
                                ]),
                                Tab::make('PT')->schema([
                                    Forms\Components\RichEditor::make('content.pt')
                                        ->label(__('admin.cms_post.content') . ' (' . __('admin.common.lang_suffix.pt') . ')'),
                                ]),
                            ]),
                    ])
                    ->columns(2),

                Section::make(__('admin.cms_post.section_config'))
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label(__('admin.cms_post.category'))
                            ->options(fn () => CmsCategory::ordered()->get()->mapWithKeys(fn ($c) => [$c->slug => $c->getTranslationWithFallback('name')])->toArray())
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('emoji')
                            ->label(__('admin.cms_post.emoji'))
                            ->options([
                                '📋' => __('admin.cms_post.emoji_options.doc'),
                                '🌍' => __('admin.cms_post.emoji_options.world'),
                                '🏆' => __('admin.cms_post.emoji_options.trophy'),
                                '✅' => __('admin.cms_post.emoji_options.check'),
                                '🚀' => __('admin.cms_post.emoji_options.launch'),
                                '🔗' => __('admin.cms_post.emoji_options.link'),
                                '📝' => __('admin.cms_post.emoji_options.writing'),
                                '📊' => __('admin.cms_post.emoji_options.stats'),
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
                            ->placeholder(__('admin.cms_post.emoji_placeholder')),

                        Forms\Components\FileUpload::make('image_path')
                            ->label(__('admin.cms_post.image'))
                            ->image()
                            ->disk('public')
                            ->directory('blog')
                            ->nullable(),

                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.cms_post.featured')),

                        Forms\Components\TextInput::make('read_time')
                            ->label(__('admin.cms_post.reading_time'))
                            ->placeholder(__('admin.cms_post.reading_time_ph'))
                            ->maxLength(20),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('admin.cms_post.published_at'))
                            ->helperText(__('admin.cms_post.published_at_help')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.cms_post.title'))
                    ->formatStateUsing(fn (CmsPost $record): string => Str::limit($record->getTranslationWithFallback('title'), 50))
                    ->searchable(),

                Tables\Columns\TextColumn::make('cat_label')
                    ->label(__('admin.cms_post.category')),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.cms_post.featured'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('admin.cms_post.status_published'))
                    ->date()
                    ->placeholder(__('admin.cms_post.status_draft')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.cms_post.updated_at'))
                    ->since(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('admin.cms_post.category'))
                    ->options(fn () => CmsCategory::ordered()->get()->mapWithKeys(fn ($c) => [$c->slug => $c->getTranslationWithFallback('name')])->toArray()),

                Tables\Filters\TernaryFilter::make('published')
                    ->label(__('admin.cms_post.status'))
                    ->placeholder(__('admin.common.all'))
                    ->trueLabel(__('admin.cms_post.filter_published'))
                    ->falseLabel(__('admin.cms_post.filter_drafts'))
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
