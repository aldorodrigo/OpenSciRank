<?php

namespace App\Filament\Pages;

use App\Support\PageVisibility;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * Administración de menús y visibilidad de páginas públicas (Roadmap #62).
 *
 * Para cada página de `config/site_pages.php` el admin decide dos cosas
 * independientes:
 *   - Accesible → si se apaga, la ruta devuelve 404 (middleware `page:{clave}`).
 *   - En el menú → si se apaga, el link desaparece de header/mobile/footer.
 *
 * Deshabilitar implica sacar del menú: un link a un 404 no se pinta nunca.
 */
class MenuSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 53;

    protected string $view = 'filament.pages.menu-settings';

    public ?array $data = [];

    /**
     * Igual que SlaSettings: la configuración del sitio público es exclusiva
     * del super_admin, y se cierra tanto por navegación como por URL directa.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill($this->currentState());
    }

    /**
     * Estado actual en el formato del formulario: `[clave => [enabled, in_menu]]`.
     *
     * @return array<string, array<string, bool>>
     */
    protected function currentState(): array
    {
        return collect(PageVisibility::all())
            ->map(fn (array $page) => [
                'enabled' => $page['enabled'],
                'in_menu' => $page['in_menu'],
            ])
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        $rows = [];

        foreach (PageVisibility::all() as $key => $page) {
            $rows[] = Grid::make(12)
                ->schema([
                    Placeholder::make("label_{$key}")
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            '<span class="font-medium">'.e(__("admin.menus.pages.{$key}")).'</span>'
                            .'<span class="ms-2 text-sm text-gray-500 dark:text-gray-400">'.e($page['path']).'</span>'
                            .($page['locked']
                                ? '<span class="ms-2 text-xs text-gray-400">'.e(__('admin.menus.locked_hint')).'</span>'
                                : '')
                        ))
                        ->columnSpan(6),

                    Toggle::make("{$key}.enabled")
                        ->label(__('admin.menus.enabled'))
                        ->inline(false)
                        ->live()
                        // La home no se puede apagar: dejaría el sitio sin entrada.
                        ->disabled($page['locked'])
                        ->columnSpan(3),

                    Toggle::make("{$key}.in_menu")
                        ->label(__('admin.menus.in_menu'))
                        ->inline(false)
                        // Sin link propio en header/footer (la home entra por el logo).
                        ->visible($page['has_menu_link'])
                        // Una página apagada no puede estar en el menú.
                        ->disabled(fn (Get $get): bool => ! $get("{$key}.enabled"))
                        ->columnSpan(3),
                ]);
        }

        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.menus.section'))
                    ->description(__('admin.menus.section_desc'))
                    ->schema($rows),
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
        PageVisibility::save($this->form->getState());

        // Releemos el estado normalizado (p. ej. la home vuelve a "accesible"
        // aunque el payload dijera otra cosa) para que la UI no mienta.
        $this->form->fill($this->currentState());

        Notification::make()
            ->title(__('admin.menus.saved'))
            ->body(__('admin.menus.saved_body'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.menus.navigation');
    }

    public function getTitle(): string
    {
        return __('admin.menus.title');
    }
}
