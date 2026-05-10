<?php

namespace App\Filament\Exports;

use App\Models\Book;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookExporter extends Exporter
{
    protected static ?string $model = Book::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('title')
                ->label('Título')
                ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('title')),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('subtitle')
                ->label('Subtítulo')
                ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('subtitle')),
            ExportColumn::make('user.name')->label('Propietario'),
            ExportColumn::make('user.email')->label('Email Propietario'),
            ExportColumn::make('status')->label('Estado'),
            ExportColumn::make('isbn')->label('ISBN'),
            ExportColumn::make('isbn_print')->label('ISBN Impreso'),
            ExportColumn::make('isbn_online')->label('ISBN Online'),
            ExportColumn::make('doi')->label('DOI'),
            ExportColumn::make('publisher')->label('Editorial'),
            ExportColumn::make('publisher_country')->label('País Editorial'),
            ExportColumn::make('publisher_city')->label('Ciudad Editorial'),
            ExportColumn::make('publication_year')->label('Año Publicación'),
            ExportColumn::make('primary_language')->label('Idioma Principal'),
            ExportColumn::make('book_type')->label('Tipo de Libro'),
            ExportColumn::make('total_pages')->label('Páginas'),
            ExportColumn::make('is_open_access')
                ->label('Acceso Abierto')
                ->formatStateUsing(fn ($state): string => $state === null ? '' : ($state ? 'Sí' : 'No')),
            ExportColumn::make('license_type')->label('Licencia'),
            ExportColumn::make('has_peer_review')
                ->label('Revisión por Pares')
                ->formatStateUsing(fn ($state): string => $state === null ? '' : ($state ? 'Sí' : 'No')),
            ExportColumn::make('current_score')->label('Puntuación'),
            ExportColumn::make('listed_at')->label('Fecha Listado'),
            ExportColumn::make('approval_date')->label('Fecha Aprobación'),
            ExportColumn::make('created_at')->label('Fecha Registro'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de libros se ha completado con ' . number_format($export->successful_rows) . ' ' . str('fila')->plural($export->successful_rows) . ' exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron al exportarse.';
        }

        return $body;
    }
}
