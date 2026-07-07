<?php

namespace App\Filament\Pages;

use App\Models\EmailLog;
use App\Models\Journal;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Panel de observabilidad de la cola de correo (Fase 4 auditoría de correos).
 *
 * Muestra conteos de jobs en cola (`jobs`), jobs fallidos (`failed_jobs`) y
 * correos enviados hoy (`email_logs`), más un listado de los últimos fallos
 * con acciones para reintentar/vaciar la cola. Consulta las tablas jobs y
 * failed_jobs con Query Builder crudo (no son modelos Eloquent del dominio).
 *
 * Exclusiva del super_admin: el evaluator no necesita visibilidad de la
 * infraestructura de colas del sistema.
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
     * Cantidad de jobs pendientes en la cola `mail`.
     */
    public function getMailQueueCount(): int
    {
        return DB::table('jobs')->where('queue', 'mail')->count();
    }

    /**
     * Total de jobs pendientes en cualquier cola (contexto general).
     */
    public function getTotalQueueCount(): int
    {
        return DB::table('jobs')->count();
    }

    /**
     * Total de jobs fallidos acumulados.
     */
    public function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    /**
     * Jobs pendientes en la cola `harvest` (cosechas OAI-PMH esperando al worker).
     */
    public function getHarvestQueueCount(): int
    {
        return DB::table('jobs')->where('queue', 'harvest')->count();
    }

    /**
     * Revistas cuya última cosecha OAI agotó reintentos (estado `failed`).
     */
    public function getHarvestFailedCount(): int
    {
        return Journal::query()->where('oai_harvest_status', 'failed')->count();
    }

    /**
     * Correos enviados exitosamente hoy (según EmailLog.status = sent).
     */
    public function getSentTodayCount(): int
    {
        return EmailLog::query()
            ->where('status', EmailLog::STATUS_SENT)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Últimos ~25 jobs fallidos, con el nombre del job y el primer renglón
     * de la excepción extraídos del payload/exception crudos.
     *
     * @return array<int, array{id: int, uuid: string, queue: string, failed_at: ?string, job_name: string, exception_line: string}>
     */
    public function getFailedJobsList(): array
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(25)
            ->get()
            ->map(function ($row): array {
                $payload = json_decode($row->payload ?? '', true);
                $jobName = $payload['displayName'] ?? ($payload['job'] ?? '-');

                $exceptionLine = '-';
                if (! empty($row->exception)) {
                    $lines = explode("\n", (string) $row->exception);
                    $exceptionLine = trim($lines[0] ?? '-');
                }

                return [
                    'id' => $row->id,
                    'uuid' => $row->uuid,
                    'queue' => $row->queue,
                    'failed_at' => $row->failed_at,
                    'job_name' => $jobName,
                    'exception_line' => $exceptionLine,
                ];
            })
            ->all();
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
