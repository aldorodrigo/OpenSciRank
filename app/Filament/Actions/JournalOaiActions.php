<?php

namespace App\Filament\Actions;

use App\Jobs\HarvestJournalArticles;
use App\Models\Journal;
use App\Services\Metrics\JournalMetricsService;
use App\Services\OaiPmhService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Factory de acciones Filament reutilizables sobre un Journal: probar conexión OAI,
 * cosechar artículos, refrescar métricas y destrabar una cosecha.
 *
 * Funciona en dos contextos:
 *  - Header/página (un solo journal): pasar `$resolve` (`fn () => $this->getRecord()`).
 *  - Fila de tabla (un journal por fila): omitir `$resolve`; Filament inyecta el
 *    `$record` de la fila en los closures.
 *
 * Cada closure resuelve el journal con `self::journal($record, $resolve)`: usa el
 * `$record` inyectado si existe (tabla) y si no, el `$resolve` (header/página).
 *
 * @param  Closure(): Journal|null  $resolve
 */
class JournalOaiActions
{
    /**
     * Probar conexión OAI: identify() + muestra de 3 registros. Solo lectura.
     */
    public static function testConnection(?Closure $resolve = null): Action
    {
        return Action::make('test_oai_connection')
            ->label(__('admin.journal.action_test_oai'))
            ->icon('heroicon-o-signal')
            ->color('gray')
            ->visible(fn (?Journal $record = null): bool => ! empty(self::journal($record, $resolve)?->oai_base_url))
            ->action(function (?Journal $record = null) use ($resolve): void {
                $journal = self::journal($record, $resolve);

                try {
                    $service = app(OaiPmhService::class);
                    $info = $service->identify($journal->oai_base_url);
                    $samples = $service->previewRecords(
                        $journal->oai_base_url,
                        $journal->oai_set_spec,
                        $journal->oai_metadata_prefix ?: 'oai_dc',
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
    public static function harvest(?Closure $resolve = null): Action
    {
        return Action::make('harvest_oai')
            ->label(__('admin.journal.action_harvest'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->visible(fn (?Journal $record = null): bool => ! empty(self::journal($record, $resolve)?->oai_base_url))
            ->requiresConfirmation()
            ->modalHeading(__('admin.journal.modal_harvest_heading'))
            ->modalDescription(__('admin.journal.modal_harvest_desc'))
            ->action(function (?Journal $record = null) use ($resolve): void {
                $journal = self::journal($record, $resolve);
                $journal->update(['oai_harvest_status' => 'queued']);

                HarvestJournalArticles::dispatch($journal, causedByUserId: auth()->id());

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
    public static function refreshMetrics(?Closure $resolve = null): Action
    {
        return Action::make('refresh_metrics')
            ->label(__('admin.metrics.action_refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('admin.metrics.confirm_refresh_heading'))
            ->modalDescription(__('admin.metrics.confirm_refresh_body'))
            ->action(function (?Journal $record = null) use ($resolve): void {
                $journal = self::journal($record, $resolve);
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

    /**
     * Destrabar una cosecha clavada en `queued`/`running` sin tocar la consola:
     * borra el lock viejo de WithoutOverlapping (que quedó tras un worker muerto
     * a mitad de job) y devuelve el estado a `idle`. No re-encola — para eso está
     * el botón "cosechar". Ver incidente #58.
     */
    public static function reset(?Closure $resolve = null): Action
    {
        return Action::make('reset_harvest')
            ->label(__('admin.journal.action_reset_harvest'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (?Journal $record = null): bool => in_array(self::journal($record, $resolve)?->oai_harvest_status, ['queued', 'running'], true))
            ->requiresConfirmation()
            ->modalHeading(__('admin.journal.modal_reset_harvest_heading'))
            ->modalDescription(__('admin.journal.modal_reset_harvest_desc'))
            ->action(function (?Journal $record = null) use ($resolve): void {
                $journal = self::journal($record, $resolve);

                // El lock de WithoutOverlapping vive con clave
                // `...:oai-harvest-<id>` en cache/cache_locks (store database).
                $needle = '%oai-harvest-'.$journal->id.'%';
                foreach (['cache_locks', 'cache'] as $table) {
                    DB::table($table)->where('key', 'like', $needle)->delete();
                }

                $journal->update(['oai_harvest_status' => 'idle']);

                Notification::make()
                    ->title(__('admin.journal.notif_reset_harvest'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Resuelve el journal objetivo: el `$record` inyectado por Filament (fila de
     * tabla) tiene prioridad; si no hay, se usa el `$resolve` (header/página).
     */
    private static function journal(?Journal $record, ?Closure $resolve): ?Journal
    {
        return $record ?? ($resolve ? $resolve() : null);
    }
}
