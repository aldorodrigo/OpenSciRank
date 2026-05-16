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
            ExportColumn::make('id')->label(__('admin.export.book.id')),
            ExportColumn::make('title')
                ->label(__('admin.export.book.title'))
                ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('title')),
            ExportColumn::make('slug')->label(__('admin.export.book.slug')),
            ExportColumn::make('subtitle')
                ->label(__('admin.export.book.subtitle'))
                ->formatStateUsing(fn (Book $record): string => $record->getTranslationWithFallback('subtitle')),
            ExportColumn::make('user.name')->label(__('admin.export.book.owner')),
            ExportColumn::make('user.email')->label(__('admin.export.book.owner_email')),
            ExportColumn::make('status')->label(__('admin.export.book.status')),
            ExportColumn::make('isbn')->label(__('admin.export.book.isbn')),
            ExportColumn::make('isbn_print')->label(__('admin.export.book.isbn_print')),
            ExportColumn::make('isbn_online')->label(__('admin.export.book.isbn_online')),
            ExportColumn::make('doi')->label(__('admin.export.book.doi')),
            ExportColumn::make('publisher')->label(__('admin.export.book.publisher')),
            ExportColumn::make('publisher_country')->label(__('admin.export.book.publisher_country')),
            ExportColumn::make('publisher_city')->label(__('admin.export.book.publisher_city')),
            ExportColumn::make('publication_year')->label(__('admin.export.book.pub_year')),
            ExportColumn::make('primary_language')->label(__('admin.export.book.language')),
            ExportColumn::make('book_type')->label(__('admin.export.book.type')),
            ExportColumn::make('total_pages')->label(__('admin.export.book.pages')),
            ExportColumn::make('is_open_access')
                ->label(__('admin.export.book.open_access'))
                ->formatStateUsing(fn ($state): string => $state === null ? '' : ($state ? 'Sí' : 'No')),
            ExportColumn::make('license_type')->label(__('admin.export.book.license')),
            ExportColumn::make('has_peer_review')
                ->label(__('admin.export.book.peer_review'))
                ->formatStateUsing(fn ($state): string => $state === null ? '' : ($state ? 'Sí' : 'No')),
            ExportColumn::make('current_score')->label(__('admin.export.book.score')),
            ExportColumn::make('listed_at')->label(__('admin.export.book.listed_at')),
            ExportColumn::make('approval_date')->label(__('admin.export.book.approved_at')),
            ExportColumn::make('created_at')->label(__('admin.export.book.created_at')),
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
