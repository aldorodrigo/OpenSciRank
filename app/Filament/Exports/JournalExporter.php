<?php

namespace App\Filament\Exports;

use App\Models\Journal;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class JournalExporter extends Exporter
{
    protected static ?string $model = Journal::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label(__('admin.export.journal.id')),
            ExportColumn::make('title')
                ->label(__('admin.export.journal.title'))
                ->formatStateUsing(fn (Journal $record): string => $record->getTranslationWithFallback('title')),
            ExportColumn::make('slug')->label(__('admin.export.journal.slug')),
            ExportColumn::make('user.name')->label(__('admin.export.journal.owner')),
            ExportColumn::make('user.email')->label(__('admin.export.journal.owner_email')),
            ExportColumn::make('status')->label(__('admin.export.journal.status')),
            ExportColumn::make('current_score')->label(__('admin.export.journal.score')),
            ExportColumn::make('current_level')->label(__('admin.export.journal.level')),
            ExportColumn::make('assignedEvaluator.name')->label(__('admin.export.journal.evaluator')),
            ExportColumn::make('issn_print')->label(__('admin.export.journal.issn_print')),
            ExportColumn::make('issn_online')->label(__('admin.export.journal.issn_online')),
            ExportColumn::make('publisher')->label(__('admin.export.journal.publisher')),
            ExportColumn::make('url')->label(__('admin.export.journal.url')),
            ExportColumn::make('country_code')->label(__('admin.export.journal.country')),
            ExportColumn::make('start_year')->label(__('admin.export.journal.start_year')),
            ExportColumn::make('is_open_access')
                ->label(__('admin.export.journal.open_access'))
                ->formatStateUsing(fn ($state): string => $state ? 'Sí' : 'No'),
            ExportColumn::make('seal_status')->label(__('admin.export.journal.seal_status')),
            ExportColumn::make('seal_expires_at')->label(__('admin.export.journal.seal_expires')),
            ExportColumn::make('listed_at')->label(__('admin.export.journal.listed_at')),
            ExportColumn::make('evaluated_at')->label(__('admin.export.journal.evaluated_at')),
            ExportColumn::make('created_at')->label(__('admin.export.journal.created_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de revistas se ha completado con ' . number_format($export->successful_rows) . ' ' . str('fila')->plural($export->successful_rows) . ' exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron al exportarse.';
        }

        return $body;
    }
}
