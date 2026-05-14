<?php

namespace App\Observers;

use App\Models\AdminTask;
use App\Models\Book;

/**
 * Sprint 3.6 #32: auto-cerrar la task TYPE_REVIEW_LISTING_BOOK cuando
 * el admin cambia el status del libro a un estado terminal de listing.
 *
 * Books no tienen una página Filament de review dedicada (a diferencia
 * de Journals que tienen ReviewListing), entonces enganchamos al modelo
 * directamente via "updated" event.
 */
class BookObserver
{
    /**
     * Estados terminales del flujo de listing de libros.
     */
    protected const TERMINAL_STATUSES = ['listed', 'rejected'];

    public function updated(Book $book): void
    {
        if (! $book->wasChanged('status')) {
            return;
        }

        if (! in_array($book->status, self::TERMINAL_STATUSES, true)) {
            return;
        }

        // Solo cerramos si NO fue una resubmisión (pending_listing/submitted)
        // pasando a un estado terminal — listed/rejected son cierres reales.
        $closedCount = 0;
        AdminTask::query()
            ->where('related_type', Book::class)
            ->where('related_id', $book->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_BOOK)
            ->whereIn('status', AdminTask::STATUSES_OPEN)
            ->get()
            ->each(function (AdminTask $task) use ($book, &$closedCount) {
                $task->complete("Cerrada automáticamente: libro pasó a status {$book->status}");
                $closedCount++;
            });

        if ($closedCount > 0) {
            activity()
                ->performedOn($book)
                ->causedBy(auth()->user())
                ->withProperties(['admin_tasks_closed' => $closedCount, 'new_status' => $book->status])
                ->log("Tareas de revisión de listado cerradas automáticamente ({$closedCount})");
        }
    }
}
