<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CronHealthWidget;
use App\Filament\Widgets\FailedJobsWidget;
use App\Filament\Widgets\OaiHarvestQueueWidget;
use App\Filament\Widgets\QueueHealthOverview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

/**
 * #59 — panel de administración de colas (super_admin). Consolida:
 *  - Salud del worker + conteos de colas (QueueHealthOverview).
 *  - Salud de los cron (CronHealthWidget).
 *  - Cosechas OAI operables por revista (OaiHarvestQueueWidget).
 *  - Gestión granular de jobs fallidos (FailedJobsWidget).
 *
 * La página solo hospeda los widgets (patrón EvaluatorDesk) y mantiene las
 * acciones globales de reintentar/vaciar todos los fallidos en el header.
 */
class QueueMonitor extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 52;

    protected string $view = 'filament.pages.queue-monitor';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.queue_monitor.navigation');
    }

    public function getTitle(): string
    {
        return __('admin.queue_monitor.title');
    }

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            QueueHealthOverview::class,
            CronHealthWidget::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getFooterWidgets(): array
    {
        return [
            OaiHarvestQueueWidget::class,
            FailedJobsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry_failed')
                ->label(__('admin.queue_monitor.retry_failed'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription(__('admin.queue_monitor.retry_failed_confirm'))
                ->action(function (): void {
                    Artisan::call('queue:retry', ['id' => ['all']]);

                    Notification::make()
                        ->title(__('admin.queue_monitor.retry_failed_success'))
                        ->success()
                        ->send();
                }),

            Action::make('flush_failed')
                ->label(__('admin.queue_monitor.flush_failed'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription(__('admin.queue_monitor.flush_failed_confirm'))
                ->action(function (): void {
                    Artisan::call('queue:flush');

                    Notification::make()
                        ->title(__('admin.queue_monitor.flush_failed_success'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
