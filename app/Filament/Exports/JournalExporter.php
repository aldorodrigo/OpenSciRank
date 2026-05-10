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
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('title')
                ->label('Título')
                ->formatStateUsing(fn (Journal $record): string => $record->getTranslationWithFallback('title')),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('user.name')->label('Propietario'),
            ExportColumn::make('user.email')->label('Email Propietario'),
            ExportColumn::make('status')->label('Estado'),
            ExportColumn::make('current_score')->label('Puntuación (%)'),
            ExportColumn::make('current_level')->label('Nivel'),
            ExportColumn::make('assignedEvaluator.name')->label('Evaluador'),
            ExportColumn::make('issn_print')->label('ISSN Impreso'),
            ExportColumn::make('issn_online')->label('ISSN Online'),
            ExportColumn::make('publisher')->label('Editorial'),
            ExportColumn::make('url')->label('URL'),
            ExportColumn::make('country_code')->label('País'),
            ExportColumn::make('start_year')->label('Año Inicio'),
            ExportColumn::make('is_open_access')
                ->label('Acceso Abierto')
                ->formatStateUsing(fn ($state): string => $state ? 'Sí' : 'No'),
            ExportColumn::make('seal_status')->label('Estado Sello'),
            ExportColumn::make('seal_expires_at')->label('Sello Vence'),
            ExportColumn::make('listed_at')->label('Fecha Listado'),
            ExportColumn::make('evaluated_at')->label('Fecha Evaluación'),
            ExportColumn::make('created_at')->label('Fecha Registro'),
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
