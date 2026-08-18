<?php

namespace App\Filament\Actions;

use App\Models\Book;
use App\Support\CourtesyListing;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;

/**
 * Factory de acciones Filament de cortesía sobre un Book.
 *
 * "Publicar de cortesía" exonera el costo del listado (`book-listing`) para
 * convenios institucionales, canjes o compensaciones. El libro entra al flujo
 * normal de revisión — no se publica directo — y la exoneración queda registrada
 * como un Payment de monto 0.
 *
 * Es el equivalente para libros de `force_evaluation` en JournalResource (#36).
 * Todo el efecto vive en CourtesyListing::forBook(); acá solo está el formulario
 * y la retroalimentación de UI.
 *
 * Funciona en dos contextos, igual que JournalOaiActions:
 *  - Header/página (un solo libro): pasar `$resolve` (`fn () => $this->getRecord()`).
 *  - Fila de tabla: omitir `$resolve`; Filament inyecta el `$record` de la fila.
 */
class BookCourtesyActions
{
    /**
     * @param  Closure(): Book|null  $resolve
     */
    public static function listing(?Closure $resolve = null): Action
    {
        return Action::make('courtesy_listing')
            ->label(__('admin.book.action_courtesy_listing'))
            ->icon('heroicon-o-gift')
            ->color('emerald')
            // Exonerar un cobro es decisión comercial: solo super_admin.
            ->visible(fn ($record = null): bool => (auth()->user()?->hasRole('super_admin') ?? false)
                && CourtesyListing::isEligible(self::book($record, $resolve))
            )
            ->requiresConfirmation()
            ->modalHeading(__('admin.book.modal_courtesy_heading'))
            ->modalDescription(__('admin.book.modal_courtesy_desc'))
            ->schema([
                Forms\Components\Textarea::make('reason')
                    ->label(__('admin.book.courtesy_reason'))
                    ->helperText(__('admin.book.courtesy_reason_help'))
                    ->required()
                    ->minLength(10)
                    ->maxLength(500)
                    ->rows(3),
            ])
            ->action(function (array $data, $record = null) use ($resolve): void {
                $book = self::book($record, $resolve);

                if (! CourtesyListing::isEligible($book)) {
                    Notification::make()
                        ->title(__('admin.book.notif_courtesy_unavailable'))
                        ->danger()
                        ->send();

                    return;
                }

                CourtesyListing::forBook($book, $data['reason']);

                Notification::make()
                    ->title(__('admin.book.notif_courtesy_done'))
                    ->body(__('admin.book.notif_courtesy_done_body'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Resuelve el libro objetivo. El `$record` inyectado por Filament tiene
     * prioridad solo si es un Book; si no, cae al `$resolve` de la página.
     */
    private static function book(mixed $record, ?Closure $resolve): ?Book
    {
        if ($record instanceof Book) {
            return $record;
        }

        return $resolve ? $resolve() : null;
    }
}
