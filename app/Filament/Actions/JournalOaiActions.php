<?php

namespace App\Filament\Actions;

use App\Jobs\HarvestJournalArticles;
use App\Models\Journal;
use App\Services\Metrics\JournalMetricsService;
use App\Services\OaiPmhService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Factory de acciones Filament reutilizables sobre un Journal: probar conexión OAI,
 * cosechar artículos, refrescar métricas, registrar snapshot manual de Scholar y
 * destrabar una cosecha.
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
            ->visible(fn ($record = null): bool => ! empty(self::journal($record, $resolve)?->oai_base_url))
            ->action(function ($record = null) use ($resolve): void {
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
            ->visible(fn ($record = null): bool => ! empty(self::journal($record, $resolve)?->oai_base_url))
            ->requiresConfirmation()
            ->modalHeading(__('admin.journal.modal_harvest_heading'))
            ->modalDescription(__('admin.journal.modal_harvest_desc'))
            ->action(function ($record = null) use ($resolve): void {
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
            ->visible(fn ($record = null): bool => self::journal($record, $resolve) !== null)
            ->requiresConfirmation()
            ->modalHeading(__('admin.metrics.confirm_refresh_heading'))
            ->modalDescription(__('admin.metrics.confirm_refresh_body'))
            ->action(function ($record = null) use ($resolve): void {
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
     * Registrar manualmente un snapshot de Google Scholar (que no tiene API).
     * Abre un modal con h_index/total_citations/URL de perfil/notas y persiste el
     * snapshot vía JournalMetricsService. Misma lógica que el header
     * `register_scholar` de MetricSnapshotsRelationManager.
     */
    public static function registerScholar(?Closure $resolve = null): Action
    {
        return Action::make('register_scholar')
            ->label(__('admin.metrics.action_register_scholar'))
            ->icon('heroicon-o-academic-cap')
            ->color('warning')
            ->visible(fn ($record = null): bool => self::journal($record, $resolve) !== null)
            ->form([
                Forms\Components\TextInput::make('h_index')
                    ->label(__('admin.metrics.h_index'))
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Forms\Components\TextInput::make('total_citations')
                    ->label(__('admin.metrics.total_citations'))
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('profile_url')
                    ->label(__('admin.metrics.scholar_profile_url'))
                    ->url()
                    ->placeholder('https://scholar.google.com/citations?user=...'),
                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.metrics.notes'))
                    ->rows(2),
            ])
            ->action(function (array $data, $record = null) use ($resolve): void {
                $journal = self::journal($record, $resolve);

                app(JournalMetricsService::class)->registerScholarSnapshot(
                    journal: $journal,
                    hIndex: $data['h_index'] !== null ? (int) $data['h_index'] : null,
                    totalCitations: isset($data['total_citations']) && $data['total_citations'] !== null
                        ? (int) $data['total_citations']
                        : null,
                    capturedByUserId: auth()->id(),
                    profileUrl: $data['profile_url'] ?? null,
                    notes: $data['notes'] ?? null,
                );

                Notification::make()
                    ->title(__('admin.metrics.notif_scholar_registered'))
                    ->success()
                    ->send();
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
            ->visible(fn ($record = null): bool => in_array(self::journal($record, $resolve)?->oai_harvest_status, ['queued', 'running'], true))
            ->requiresConfirmation()
            ->modalHeading(__('admin.journal.modal_reset_harvest_heading'))
            ->modalDescription(__('admin.journal.modal_reset_harvest_desc'))
            ->action(function ($record = null) use ($resolve): void {
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
     * Resuelve el journal objetivo. El `$record` inyectado por Filament tiene
     * prioridad SOLO si es un Journal (fila de tabla o página cuyo record es un
     * Journal). En una página cuyo record es otra cosa —p. ej. ViewAdminTask
     * inyecta el AdminTask— se ignora y se usa el `$resolve` (que devuelve el
     * journal relacionado). Sin tipar el parámetro para no reventar con el
     * TypeError cuando Filament inyecta un modelo que no es Journal.
     */
    private static function journal(mixed $record, ?Closure $resolve): ?Journal
    {
        if ($record instanceof Journal) {
            return $record;
        }

        return $resolve ? $resolve() : null;
    }
}
