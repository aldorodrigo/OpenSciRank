<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\DocumentBranding;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Configuración de los documentos descargables (certificado + informe) — #65.
 *
 * Membrete institucional, logos, firmantes y textos configurables, guardados
 * en el grupo `documents` del modelo Setting. Los PDF (JournalPdfController)
 * los consumen vía App\Support\DocumentBranding.
 */
class CertificateSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 51;

    protected string $view = 'filament.pages.certificate-settings';

    public ?array $data = [];

    /**
     * Configuración de documentos: exclusiva del super_admin.
     * Cierra acceso por navegación y por URL directa.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * Claves del grupo `documents` que administra esta página.
     *
     * @return array<int,string>
     */
    private function keys(): array
    {
        return [
            'institution_name', 'institution_address', 'institution_phone',
            'institution_email', 'institution_website',
            'logo_primary', 'logo_secondary_1', 'logo_secondary_2',
            'signatory_1_name', 'signatory_1_title', 'signatory_1_signature',
            'signatory_2_name', 'signatory_2_title', 'signatory_2_signature',
            'signatory_3_name', 'signatory_3_title', 'signatory_3_signature',
            'report_intro', 'certificate_description', 'certificate_footer_note',
        ];
    }

    public function mount(): void
    {
        $defaults = [
            'report_intro' => DocumentBranding::DEFAULT_REPORT_INTRO,
            'certificate_description' => DocumentBranding::DEFAULT_CERTIFICATE_DESCRIPTION,
        ];

        $state = [];
        foreach ($this->keys() as $key) {
            $state[$key] = Setting::get($key, $defaults[$key] ?? null);
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.documents.section_institution'))
                    ->description(__('admin.documents.section_institution_desc'))
                    ->schema([
                        TextInput::make('institution_name')
                            ->label(__('admin.documents.institution_name'))
                            ->maxLength(255),
                        TextInput::make('institution_website')
                            ->label(__('admin.documents.institution_website'))
                            ->maxLength(255),
                        Textarea::make('institution_address')
                            ->label(__('admin.documents.institution_address'))
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('institution_phone')
                            ->label(__('admin.documents.institution_phone'))
                            ->maxLength(255),
                        TextInput::make('institution_email')
                            ->label(__('admin.documents.institution_email'))
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make(__('admin.documents.section_logos'))
                    ->description(__('admin.documents.section_logos_desc'))
                    ->schema([
                        FileUpload::make('logo_primary')
                            ->label(__('admin.documents.logo_primary'))
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('documents')
                            ->maxSize(2048),
                        FileUpload::make('logo_secondary_1')
                            ->label(__('admin.documents.logo_secondary_1'))
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('documents')
                            ->maxSize(2048),
                        FileUpload::make('logo_secondary_2')
                            ->label(__('admin.documents.logo_secondary_2'))
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('documents')
                            ->maxSize(2048),
                    ])
                    ->columns(3),

                Section::make(__('admin.documents.section_signatories'))
                    ->description(__('admin.documents.section_signatories_desc'))
                    ->schema($this->signatoryFields())
                    ->columns(3),

                Section::make(__('admin.documents.section_texts'))
                    ->description(__('admin.documents.section_texts_desc'))
                    ->schema([
                        Textarea::make('certificate_description')
                            ->label(__('admin.documents.certificate_description'))
                            ->helperText(__('admin.documents.certificate_description_help'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('report_intro')
                            ->label(__('admin.documents.report_intro'))
                            ->helperText(__('admin.documents.report_intro_help'))
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('certificate_footer_note')
                            ->label(__('admin.documents.certificate_footer_note'))
                            ->helperText(__('admin.documents.certificate_footer_note_help'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Campos de los 3 firmantes (nombre + cargo + imagen de firma).
     *
     * @return array<int,\Filament\Forms\Components\Field>
     */
    private function signatoryFields(): array
    {
        $fields = [];

        for ($i = 1; $i <= 3; $i++) {
            $fields[] = TextInput::make("signatory_{$i}_name")
                ->label(__('admin.documents.signatory_name', ['n' => $i]))
                ->maxLength(255);
            $fields[] = TextInput::make("signatory_{$i}_title")
                ->label(__('admin.documents.signatory_title', ['n' => $i]))
                ->maxLength(255);
            $fields[] = FileUpload::make("signatory_{$i}_signature")
                ->label(__('admin.documents.signatory_signature', ['n' => $i]))
                ->image()
                ->disk('public')
                ->directory('documents/signatures')
                ->maxSize(1024);
        }

        return $fields;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.common.save_changes'))
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($this->keys() as $key) {
            Setting::set($key, (string) ($data[$key] ?? ''), 'string', 'documents');
        }

        Notification::make()
            ->title(__('admin.documents.saved'))
            ->body(__('admin.documents.saved_body'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.documents.navigation');
    }

    public function getTitle(): string
    {
        return __('admin.documents.title');
    }
}
