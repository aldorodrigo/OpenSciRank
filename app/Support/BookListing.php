<?php

namespace App\Support;

use App\Models\Book;
use App\Models\User;
use App\Notifications\ChangesRequested;
use App\Notifications\ListingApproved;
use App\Notifications\ListingRejected;
use Illuminate\Support\Facades\DB;

/**
 * Ciclo de vida del listado de un libro (issue #75).
 *
 * Concentra las dos mitades del flujo para que nadie más escriba `status` de un
 * libro a mano:
 *
 *  1. **Entrada a la cola** — `enterReviewQueue()`. La usan el pago
 *     (`StripePaymentService`), el fallback del checkout
 *     (`CheckoutSuccessController`) y la cortesía (`CourtesyListing`). Antes el
 *     pago dejaba el libro en `submitted` y la cortesía en `pending_listing`:
 *     misma tarea, estados distintos según cómo entró.
 *  2. **Resolución de la revisión** — `resolve()`. Es lo que ejecutan las tres
 *     acciones del admin (listar / pedir correcciones / rechazar), espejo de
 *     `ReviewListing::save()` para revistas.
 *
 * Los libros NO se evalúan: no hay puntaje ni criterios, sólo revisión de
 * listado. `submitted` es un estado del flujo de revistas y ya no se usa acá.
 *
 * @see \App\Filament\Actions\BookListingActions  punto de entrada en el admin
 * @see \App\Observers\BookObserver  cierra la tarea al llegar a un estado terminal
 */
class BookListing
{
    /**
     * Estados desde los que un libro puede entrar a la cola de revisión.
     *
     * Se excluye `requires_changes_listing` porque esa resubmisión la maneja el
     * propio editor desde el wizard (y es gratuita), y se excluyen
     * `pending_listing`/`listed` para no reabrir algo ya resuelto.
     */
    public const ENTRY_STATUSES = ['draft', 'rejected'];

    public const STATUS_PENDING_REVIEW = 'pending_listing';

    public const DECISION_LIST = 'listed';

    public const DECISION_REQUEST_CHANGES = 'requires_changes_listing';

    public const DECISION_REJECT = 'rejected';

    /** Decisiones válidas de la revisión de listado. */
    public const DECISIONS = [
        self::DECISION_LIST,
        self::DECISION_REQUEST_CHANGES,
        self::DECISION_REJECT,
    ];

    /**
     * Manda el libro a la cola de revisión de listado.
     *
     * Es idempotente y defensiva a propósito: devuelve `false` sin tocar nada si
     * el libro no está en un estado de entrada. Eso es lo que evita que el
     * fallback del success URL degrade un libro que el webhook ya dejó en
     * `pending_listing`, o que resetee uno `listed` que compró el destacado.
     *
     * @return bool `true` si efectivamente entró a la cola.
     */
    public static function enterReviewQueue(Book $book): bool
    {
        if (! in_array($book->status, self::ENTRY_STATUSES, true)) {
            return false;
        }

        $book->update([
            'status' => self::STATUS_PENDING_REVIEW,
            'submitted_at' => now(),
            'submission_date' => now()->toDateString(),
        ]);

        return true;
    }

    /** ¿El libro está esperando que el admin resuelva su listado? */
    public static function isPendingReview(?Book $book): bool
    {
        return $book !== null && $book->status === self::STATUS_PENDING_REVIEW;
    }

    /**
     * Datos que le faltan al libro para que su ficha pública se vea completa.
     *
     * Son advertencias, no requisitos: el listado ya está pagado (o exonerado) y
     * ninguno de estos campos es obligatorio en el wizard, así que bloquear la
     * publicación por uno de ellos dejaría al editor esperando por algo que
     * nunca le pedimos. El admin decide con la lista a la vista.
     *
     * @return array<int, string> mensajes ya traducidos, listos para el modal.
     */
    public static function missingForPublication(Book $book): array
    {
        $missing = [];

        if (blank($book->cover_image)) {
            $missing[] = __('admin.book.missing_cover');
        }

        if (blank($book->main_file) && blank($book->download_url)) {
            $missing[] = __('admin.book.missing_file');
        }

        if (blank($book->getTranslationWithFallback('abstract'))) {
            $missing[] = __('admin.book.missing_abstract');
        }

        if (blank($book->isbn) && blank($book->isbn_print) && blank($book->isbn_online)) {
            $missing[] = __('admin.book.missing_isbn');
        }

        if ($book->authors()->count() === 0) {
            $missing[] = __('admin.book.missing_authors');
        }

        return $missing;
    }

    /**
     * Resuelve la revisión de listado.
     *
     * @param  string  $decision  Una de self::DECISIONS.
     * @param  string|null  $notes  Observaciones del revisor. Obligatorias en
     *                              correcciones y rechazo: el editor las lee en
     *                              el modal de observaciones de su panel y en el
     *                              email.
     * @param  User|null  $by  Admin que resuelve; por defecto el autenticado.
     *
     * @throws \InvalidArgumentException si la decisión no es válida o el libro no
     *                                   está en revisión.
     */
    public static function resolve(Book $book, string $decision, ?string $notes = null, ?User $by = null): void
    {
        if (! in_array($decision, self::DECISIONS, true)) {
            throw new \InvalidArgumentException("Decisión de listado inválida: {$decision}.");
        }

        if (! self::isPendingReview($book)) {
            throw new \InvalidArgumentException(
                "El libro #{$book->id} no está en revisión de listado (status: {$book->status})."
            );
        }

        $notes = filled($notes) ? trim($notes) : null;
        $by ??= auth()->user();

        DB::transaction(function () use ($book, $decision, $notes, $by): void {
            $changes = ['status' => $decision];

            if ($notes !== null) {
                $changes['evaluation_notes'] = $notes;
            }

            if ($decision === self::DECISION_LIST) {
                // Lo único que se autocompleta al publicar. `is_indexed`/`indexes`
                // son índices externos declarados por el editor y no nos
                // corresponde tocarlos.
                $changes['approval_date'] = now()->toDateString();

                if (blank($book->submission_date)) {
                    $changes['submission_date'] = now()->toDateString();
                }
            }

            $book->update($changes);

            // Los estados terminales (listed/rejected) cierran la tarea vía
            // BookObserver, que también cubre el cambio manual del select. Acá
            // sólo marcamos el caso que el observer no ve: la pelota vuelve al
            // editor y la tarea sigue abierta.
            if ($decision === self::DECISION_REQUEST_CHANGES) {
                AdminTaskFactory::markChangesRequested(
                    $book,
                    [\App\Models\AdminTask::TYPE_REVIEW_LISTING_BOOK]
                );
            }

            activity()
                ->performedOn($book)
                ->causedBy($by)
                ->withProperties([
                    'decision' => $decision,
                    'has_notes' => $notes !== null,
                    'notes' => $notes,
                ])
                ->log(self::activityDescription($decision));
        });

        $editor = $book->user;

        if ($editor === null) {
            return;
        }

        $fresh = $book->fresh();

        match ($decision) {
            self::DECISION_LIST => $editor->notify(new ListingApproved($fresh)),
            self::DECISION_REQUEST_CHANGES => $editor->notify(new ChangesRequested($fresh, 'listing', $notes)),
            self::DECISION_REJECT => $editor->notify(new ListingRejected($fresh, $notes)),
        };
    }

    private static function activityDescription(string $decision): string
    {
        return match ($decision) {
            self::DECISION_LIST => 'Listado aprobado: el libro es público en el directorio',
            self::DECISION_REQUEST_CHANGES => 'Listado devuelto al editor con correcciones',
            self::DECISION_REJECT => 'Listado rechazado',
        };
    }
}
