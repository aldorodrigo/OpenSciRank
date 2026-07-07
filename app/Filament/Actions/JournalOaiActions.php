<?php

namespace App\Filament\Actions;

use App\Jobs\HarvestJournalArticles;
use App\Models\Journal;
use App\Services\Metrics\JournalMetricsService;
use App\Services\OaiPmhService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Factory de acciones Filament reutilizables sobre un Journal: probar conexión OAI,
 * cosechar artículos y refrescar métricas de impacto.
 *
 * Se reusa en contextos distintos (header de relation manager, header de la página
 * Evaluar) que resuelven el journal de formas diferentes; por eso cada método recibe
 * un `Closure` que devuelve el Journal actual (`fn () => $this->getOwnerRecord()`,
 * `fn () => $this->getRecord()`, etc.). Evita mantener copias divergentes de la misma
 * lógica.
 *
 * @param  Closure(): Journal  $resolve
 */
class JournalOaiActions
{
    /**
     * Probar conexión OAI: identify() + muestra de 3 registros. Solo lectura.
     */
    public static function testConnection(Closure $resolve): Action
    {
        return Action::make('test_oai_connection')
            ->label(__('admin.journal.action_test_oai'))
            ->icon('heroicon-o-signal')
            ->color('gray')
            ->visible(fn (): bool => ! empty($resolve()->oai_base_url))
            ->action(function () use ($resolve): void {
                $record = $resolve();

                try {
                    $service = app(OaiPmhService::class);
                    $info = $service->identify($record->oai_base_url);
                    $samples = $service->previewRecords(
                        $record->oai_base_url,
                        $record->oai_set_spec,
                        $record->oai_metadata_prefix ?: 'oai_dc',
                        3,
                    );

                    $titles = collect($samples)
                        ->pluck('title')
                        ->filter()
                        ->map(fn (string $t) => '• '.Str::limit($t, 80))
                        ->implode("\n");

                    Notification::make()
                        ->title(__('admin.journal.notif_test_oai_ok', [
                            'repo' => $info['repositoryName'] ?: '—',
                        ]))
                        ->body(trim(sprintf(
                            "%s: %s\n%s",
                            __('admin.journal.oai_granularity'),
                            $info['granularity'] ?: '—',
                            $titles ?: __('admin.journal.notif_test_oai_no_samples'),
                        )))
                        ->success()
                        ->duration(12000)
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title(__('admin.journal.notif_test_oai_err'))
                        ->body($e->getMessage())
                        ->danger()
                        ->duration(10000)
                        ->send();
                }
            });
    }

    /**
     * Cosechar OAI: marca el estado en `queued` y despacha el job a la cola `harvest`.
     */
    public static function harvest(Closure $resolve): Action
    {
        return Action::make('harvest_oai')
            ->label(__('admin.journal.action_harvest'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->visible(fn (): bool => ! empty($resolve()->oai_base_url))
            ->requiresConfirmation()
            ->modalHeading(__('admin.journal.modal_harvest_heading'))
            ->modalDescription(__('admin.journal.modal_harvest_desc'))
            ->action(function () use ($resolve): void {
                $record = $resolve();
                $record->update(['oai_harvest_status' => 'queued']);

                HarvestJournalArticles::dispatch($record, causedByUserId: auth()->id());

                Notification::make()
                    ->title(__('admin.journal.notif_harvest_queued'))
                    ->body(__('admin.journal.notif_harvest_queued_body'))
                    ->success()
                    ->duration(8000)
                    ->send();
            });
    }

    /**
     * Refrescar métricas (OpenAlex + Crossref). Si ninguna fuente devuelve datos avisa
     * el motivo; si hay éxito parcial, lo advierte. Mismo comportamiento que el header
     * de MetricSnapshotsRelationManager.
     */
    public static function refreshMetrics(Closure $resolve): Action
    {
        return Action::make('refresh_metrics')
            ->label(__('admin.metrics.action_refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('admin.metrics.confirm_refresh_heading'))
            ->modalDescription(__('admin.metrics.confirm_refresh_body'))
            ->action(function () use ($resolve): void {
                $journal = $resolve();
                $result = app(JournalMetricsService::class)->refresh($journal);

                if (! $result->hasAnyData()) {
                    Notification::make()
                        ->title(__('admin.metrics.notif_refresh_failed'))
                        ->body($result->errorSummary() ?: __('admin.metrics.notif_refresh_failed_body'))
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                $notification = Notification::make()
                    ->title(__('admin.metrics.notif_refreshed'))
                    ->success();

                if ($result->hasErrors()) {
                    $notification
                        ->body(__('admin.metrics.notif_refresh_partial', ['detail' => $result->errorSummary()]))
                        ->warning();
                }

                $notification->send();
            });
    }
}
