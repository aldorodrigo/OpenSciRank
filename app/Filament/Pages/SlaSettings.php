<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Página de configuración de plazos SLA del admin (Sprint 3.6 #32).
 *
 * Mini-versión de SettingsResource — cuando Sprint 4 #9 implemente el
 * resource completo, los inputs SLA migran ahí y esta página se retira.
 */
class SlaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.pages.sla-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'sla_evaluation_business_days' => Setting::get('sla_evaluation_business_days', 15),
            'sla_evaluation_express_business_days' => Setting::get('sla_evaluation_express_business_days', 5),
            'sla_consulting_calendar_days' => Setting::get('sla_consulting_calendar_days', 7),
            'sla_listing_calendar_days' => Setting::get('sla_listing_calendar_days', 7),
            'sla_orphan_calendar_days' => Setting::get('sla_orphan_calendar_days', 2),
            'sla_support_calendar_days' => Setting::get('sla_support_calendar_days', 7),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.sla.section_evaluations'))
                    ->description(__('admin.sla.section_evaluations_desc'))
                    ->schema([
                        TextInput::make('sla_evaluation_business_days')
                            ->label(__('admin.sla.standard'))
                            ->helperText(__('admin.sla.standard_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required(),

                        TextInput::make('sla_evaluation_express_business_days')
                            ->label(__('admin.sla.express'))
                            ->helperText(__('admin.sla.express_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(__('admin.sla.section_other'))
                    ->description(__('admin.sla.section_other_desc'))
                    ->schema([
                        TextInput::make('sla_listing_calendar_days')
                            ->label(__('admin.sla.listing'))
                            ->helperText(__('admin.sla.listing_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),

                        TextInput::make('sla_consulting_calendar_days')
                            ->label(__('admin.sla.consulting'))
                            ->helperText(__('admin.sla.consulting_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),

                        TextInput::make('sla_orphan_calendar_days')
                            ->label(__('admin.sla.orphan'))
                            ->helperText(__('admin.sla.orphan_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(14)
                            ->required(),

                        // Sprint 3.7 #44 — SLA para tasks de soporte creadas
                        // manualmente desde el modal de mensaje.
                        TextInput::make('sla_support_calendar_days')
                            ->label(__('admin.sla.support'))
                            ->helperText(__('admin.sla.support_help'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),
                    ])
                    ->columns(3),
            ]);
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

        foreach ($data as $key => $value) {
            Setting::set($key, (int) $value, 'int', 'sla');
        }

        Notification::make()
            ->title(__('admin.sla.saved'))
            ->body(__('admin.sla.saved_body'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.sla.navigation');
    }

    public function getTitle(): string
    {
        return __('admin.sla.title');
    }
}
