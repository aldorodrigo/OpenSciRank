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

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Plazos SLA';

    protected static ?string $title = 'Plazos y SLA';

    protected static ?int $navigationSort = 10;

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
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Evaluaciones')
                    ->description('Plazos para evaluación inicial y re-evaluación de revistas. Días hábiles (L-V).')
                    ->schema([
                        TextInput::make('sla_evaluation_business_days')
                            ->label('Plazo estándar (días hábiles)')
                            ->helperText('Tiempo máximo para completar una evaluación o re-evaluación regular.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required(),

                        TextInput::make('sla_evaluation_express_business_days')
                            ->label('Plazo Express (días hábiles)')
                            ->helperText('Tiempo máximo para evaluaciones con uplift Express (+$50).')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Otros procesos')
                    ->description('Plazos en días calendario.')
                    ->schema([
                        TextInput::make('sla_listing_calendar_days')
                            ->label('Revisión de listado (días)')
                            ->helperText('Tiempo para revisar y aprobar listings gratuitos de revistas o de libros pagos.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),

                        TextInput::make('sla_consulting_calendar_days')
                            ->label('Plan de Acción + Consultoría (días)')
                            ->helperText('Tiempo para programar y dar la sesión de consultoría tras el pago.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),

                        TextInput::make('sla_orphan_calendar_days')
                            ->label('Pago huérfano (días)')
                            ->helperText('Tiempo para resolver un pago cuyo recurso no se encontró al procesar el webhook.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(14)
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
                ->label('Guardar cambios')
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
            ->title('Configuración SLA actualizada')
            ->body('Los nuevos plazos se aplicarán a las próximas tareas generadas.')
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Configuración');
    }

    public static function getNavigationLabel(): string
    {
        return __('Plazos SLA');
    }

    public function getTitle(): string
    {
        return __('Plazos y SLA');
    }
}
