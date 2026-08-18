<?php

namespace App\Filament\Actions;

use App\Models\Book;
use App\Support\BookListing;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

/**
 * Acciones Filament para resolver la revisión de listado de un libro (issue #75).
 *
 * Antes había que abrir la ficha, encontrar la pestaña "Estado Interno", cambiar
 * el select y guardar — sin registrar fecha de aprobación ni avisarle al editor.
 * Ahora el super_admin resuelve con un botón: listar es un click y las dos
 * decisiones que devuelven la pelota al editor piden motivo.
 *
 * Todo el efecto vive en BookListing; acá sólo están el formulario, las guardas
 * de rol y la retroalimentación de UI.
 *
 * Funciona en dos contextos, igual que BookCourtesyActions:
 *  - Header/página (un solo libro): pasar `$resolve` (`fn () => $this->getRecord()`).
 *  - Fila de tabla: omitir `$resolve`; Filament inyecta el `$record` de la fila.
 *
 * Los libros no se evalúan: esto es revisión de listado, y por eso queda fuera
 * del alcance del rol `evaluator` (que no tiene permisos sobre Book).
 */
class BookListingActions
{
    /**
     * Listar: publica el libro en el directorio. Un click, sin motivo.
     *
     * El modal advierte qué datos le faltan a la ficha, pero no bloquea: el
     * listado ya está pagado (o exonerado) y ninguno de esos campos es
     * obligatorio en el wizard.
     *
     * @param  Closure(): Book|null  $resolve
     */
    public static function approve(?Closure $resolve = null): Action
    {
        return Action::make('list_book')
            ->label(__('admin.book.action_list'))
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn ($record = null): bool => self::allowed(self::book($record, $resolve)))
            ->requiresConfirmation()
            ->modalHeading(__('admin.book.modal_list_heading'))
            ->modalDescription(function ($record = null) use ($resolve): string {
                $book = self::book($record, $resolve);
                $missing = $book ? BookListing::missingForPublication($book) : [];

                if ($missing === []) {
                    return __('admin.book.modal_list_ready');
                }

                return __('admin.book.modal_list_missing').' '.implode(' · ', $missing);
            })
            ->action(function ($record = null) use ($resolve): void {
                $book = self::book($record, $resolve);

                if (! self::guard($book)) {
                    return;
                }

                BookListing::resolve($book, BookListing::DECISION_LIST);

                Notification::make()
                    ->title(__('admin.book.notif_listed'))
                    ->body(__('admin.book.notif_listed_body'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Pedir correcciones: el libro vuelve al editor y la tarea queda abierta.
     *
     * @param  Closure(): Book|null  $resolve
     */
    public static function requestChanges(?Closure $resolve = null): Action
    {
        return self::withNotes(
            name: 'request_book_changes',
            label: __('admin.book.action_request_changes'),
            icon: 'heroicon-o-arrow-uturn-left',
            color: 'warning',
            heading: __('admin.book.modal_changes_heading'),
            description: __('admin.book.modal_changes_desc'),
            decision: BookListing::DECISION_REQUEST_CHANGES,
            notifTitle: __('admin.book.notif_changes_requested'),
            notifBody: __('admin.book.notif_changes_requested_body'),
            resolve: $resolve,
        );
    }

    /**
     * Rechazar: el libro no se publica y la tarea se cierra.
     *
     * @param  Closure(): Book|null  $resolve
     */
    public static function reject(?Closure $resolve = null): Action
    {
        return self::withNotes(
            name: 'reject_book_listing',
            label: __('admin.book.action_reject'),
            icon: 'heroicon-o-x-circle',
            color: 'danger',
            heading: __('admin.book.modal_reject_heading'),
            description: __('admin.book.modal_reject_desc'),
            decision: BookListing::DECISION_REJECT,
            notifTitle: __('admin.book.notif_rejected'),
            notifBody: __('admin.book.notif_rejected_body'),
            resolve: $resolve,
        );
    }

    /**
     * Ver la ficha pública tal como va a quedar listada, para revisar el
     * contenido antes de decidir.
     *
     * Abre `/book/{slug}` en una pestaña nueva. Funciona con el libro todavía
     * sin publicar porque la ruta deja pasar al dueño y al super_admin (#75);
     * para el resto del mundo sigue siendo 404 hasta que esté `listed`.
     *
     * @param  Closure(): Book|null  $resolve
     */
    public static function preview(?Closure $resolve = null): Action
    {
        return Action::make('preview_public')
            ->label(fn ($record = null): string => self::book($record, $resolve)?->status === 'listed'
                ? __('admin.book.action_view_public')
                : __('admin.book.action_preview')
            )
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->visible(fn ($record = null): bool => (auth()->user()?->hasRole('super_admin') ?? false)
                && filled(self::book($record, $resolve)?->slug)
            )
            ->url(fn ($record = null): ?string => filled($slug = self::book($record, $resolve)?->slug)
                ? route('book.show', ['slug' => $slug])
                : null,
                shouldOpenInNewTab: true
            );
    }

    /**
     * Listar en lote los que estén en revisión. Correcciones y rechazo no van en
     * lote porque cada uno necesita su propio motivo.
     */
    public static function bulkApprove(): BulkAction
    {
        return BulkAction::make('list_books')
            ->label(__('admin.book.action_bulk_list'))
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
            ->requiresConfirmation()
            ->modalHeading(__('admin.book.modal_bulk_list_heading'))
            ->modalDescription(__('admin.book.modal_bulk_list_desc'))
            ->action(function (Collection $records): void {
                $listed = 0;
                $skipped = 0;

                foreach ($records as $book) {
                    if (BookListing::isPendingReview($book)) {
                        BookListing::resolve($book, BookListing::DECISION_LIST);
                        $listed++;
                    } else {
                        $skipped++;
                    }
                }

                if ($listed === 0) {
                    Notification::make()
                        ->title(__('admin.book.notif_bulk_none'))
                        ->warning()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('admin.book.notif_listed'))
                    ->body(__('admin.book.notif_bulk_listed_body', ['listed' => $listed, 'skipped' => $skipped]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Las tres acciones comparten forma; las dos que devuelven la pelota al
     * editor sólo difieren en la copia y en la decisión.
     *
     * @param  Closure(): Book|null  $resolve
     */
    private static function withNotes(
        string $name,
        string $label,
        string $icon,
        string $color,
        string $heading,
        string $description,
        string $decision,
        string $notifTitle,
        string $notifBody,
        ?Closure $resolve,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(fn ($record = null): bool => self::allowed(self::book($record, $resolve)))
            ->requiresConfirmation()
            ->modalHeading($heading)
            ->modalDescription($description)
            ->schema([
                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.book.review_notes'))
                    ->helperText(__('admin.book.review_notes_help'))
                    ->required()
                    ->minLength(10)
                    ->maxLength(2000)
                    ->rows(4),
            ])
            ->action(function (array $data, $record = null) use ($resolve, $decision, $notifTitle, $notifBody): void {
                $book = self::book($record, $resolve);

                if (! self::guard($book)) {
                    return;
                }

                BookListing::resolve($book, $decision, $data['notes']);

                Notification::make()
                    ->title($notifTitle)
                    ->body($notifBody)
                    ->success()
                    ->send();
            });
    }

    /** Resolver el listado es decisión editorial: sólo super_admin. */
    private static function allowed(?Book $book): bool
    {
        return (auth()->user()?->hasRole('super_admin') ?? false)
            && BookListing::isPendingReview($book);
    }

    /**
     * Revalida al ejecutar: entre que se renderizó el botón y se confirmó el
     * modal, otro admin pudo haber resuelto el mismo libro.
     */
    private static function guard(?Book $book): bool
    {
        if (self::allowed($book)) {
            return true;
        }

        Notification::make()
            ->title(__('admin.book.notif_review_unavailable'))
            ->danger()
            ->send();

        return false;
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
