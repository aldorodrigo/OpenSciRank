<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ListingRequested;
use Illuminate\Support\Facades\DB;

/**
 * Otorga servicios de listado sin cobro.
 *
 * Concentra el efecto completo de una cortesía para que la acción Filament sea
 * una cáscara delgada (formulario + notificación de UI) y la lógica quede
 * cubierta por tests.
 *
 * El libro NO se publica directo: entra al mismo flujo de revisión que un libro
 * pagado (`pending_listing` + AdminTask de tipo `review_listing_book`). Lo único
 * que cambia es que el Payment asociado es de cortesía (monto 0).
 *
 * @see \App\Support\CourtesyPayment  registro del pago exonerado
 * @see \App\Filament\Actions\BookCourtesyActions  punto de entrada en el admin
 */
class CourtesyListing
{
    /**
     * Estados del libro desde los que tiene sentido regalar el listado.
     *
     * Se excluye `requires_changes_listing` (esa resubmisión ya es gratuita por
     * diseño) y `submitted`/`pending_listing`/`listed` (ya pagaron o ya están en
     * la cola de revisión).
     */
    public const ELIGIBLE_BOOK_STATUSES = ['draft', 'rejected'];

    /**
     * ¿El libro puede recibir la cortesía de listado?
     *
     * Necesita un editor asociado: `payments.user_id` es NOT NULL y es quien
     * recibe la exoneración.
     */
    public static function isEligible(?Book $book): bool
    {
        return $book !== null
            && $book->user !== null
            && in_array($book->status, self::ELIGIBLE_BOOK_STATUSES, true);
    }

    /**
     * Exonera el costo del listado de un libro y lo manda a revisión.
     *
     * @param  string  $reason  Motivo de la cortesía (queda en el pago y en el historial).
     * @param  User|null  $grantedBy  Admin que autoriza; por defecto el usuario autenticado.
     * @return Payment El pago de cortesía creado.
     *
     * @throws \InvalidArgumentException si el libro no es elegible.
     */
    public static function forBook(Book $book, string $reason, ?User $grantedBy = null): Payment
    {
        if (! self::isEligible($book)) {
            throw new \InvalidArgumentException(
                "El libro #{$book->id} no es elegible para cortesía de listado (status: {$book->status})."
            );
        }

        $reason = trim($reason);
        $previousStatus = $book->status;
        $grantedBy ??= auth()->user();

        $payment = DB::transaction(function () use ($book, $reason, $previousStatus, $grantedBy): Payment {
            $payment = CourtesyPayment::record(
                payable: $book,
                beneficiary: $book->user,
                product: Product::where('slug', 'book-listing')->first(),
                reason: $reason,
                grantedBy: $grantedBy,
                extraMetadata: ['previous_status' => $previousStatus],
            );

            $book->update([
                'status' => 'pending_listing',
                'submitted_at' => now(),
                'submission_date' => now()->toDateString(),
            ]);

            // Mismo camino que el webhook de Stripe: el slug book-listing resuelve
            // a TYPE_REVIEW_LISTING_BOOK, así la task queda idéntica a la de un
            // libro pagado pero ligada al pago de cortesía.
            AdminTaskFactory::fromPayment($payment, $book);

            activity()
                ->performedOn($book)
                ->causedBy($grantedBy)
                ->withProperties([
                    'previous_status' => $previousStatus,
                    'new_status' => 'pending_listing',
                    'courtesy' => true,
                    'reason' => $reason,
                    'payment_id' => $payment->id,
                    'list_price' => $payment->metadata['list_price'] ?? null,
                ])
                ->log("Libro publicado de cortesía: {$reason}");

            return $payment;
        });

        // El editor recibe el mismo aviso que cuando solicita un listado: su
        // libro entró en revisión. La cortesía en sí es información interna.
        $book->user->notify(new ListingRequested($book->fresh()));

        return $payment;
    }
}
