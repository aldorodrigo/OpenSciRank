<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Product;

class ProductValidator
{
    /**
     * Validate that a product can be purchased for the given journal.
     *
     * Throws \InvalidArgumentException with a translated message if the
     * combination is forbidden. Safe to call from both the Livewire frontend
     * and StripePaymentService (defense in depth).
     *
     * Rules (2026-05-13):
     *  - journal-evaluation   → draft / listed
     *    Primera evaluación de la revista. requires_changes_evaluation NO está
     *    acá: la resubmisión tras pedido de cambios es gratuita.
     *  - journal-reevaluation → evaluated / certified / rejected
     *    Cubre los tres casos post-evaluación: mejorar puntaje no certificado
     *    (evaluated), refinar puntaje ya certificado (certified), reintento
     *    tras rechazo (rejected).
     *  - seal-renewal-*       → seal_awarded_at IS NOT NULL
     *  - book products y addons standalone NO se validan acá.
     */
    public static function validateForJournal(Product $product, Journal $journal): void
    {
        $slug = $product->slug;

        if ($slug === 'journal-evaluation') {
            $allowed = ['draft', 'listed'];
            if (! in_array($journal->status, $allowed, true)) {
                throw new \InvalidArgumentException(
                    __('checkout.evaluation_invalid_status')
                );
            }

            return;
        }

        if ($slug === 'journal-reevaluation') {
            $allowed = ['evaluated', 'certified', 'rejected'];
            if (! in_array($journal->status, $allowed, true)) {
                throw new \InvalidArgumentException(
                    __('checkout.reevaluation_invalid_status')
                );
            }

            return;
        }

        // Covers seal-renewal-1y, seal-renewal-2y, seal-renewal-3y and any future variants
        if (str_starts_with($slug, 'seal-renewal-')) {
            if ($journal->seal_awarded_at === null) {
                throw new \InvalidArgumentException(
                    __('checkout.renewal_no_seal')
                );
            }

            return;
        }

        // All other slugs (book-listing, action-plan-consulting,
        // institutional-pack, legacy express-evaluation, etc.) are allowed
        // without journal-status constraints.
    }

    /**
     * Validate that a product can be purchased for the given book.
     *
     * Reglas:
     *  - book-listing             → cualquier estado (es el flujo estándar)
     *  - book-listing-featured-1y → sólo si el libro ya está listed
     *    (cuando viaja como producto principal). Si viaja como addon junto
     *    a book-listing, esta validación no aplica porque el listing aún
     *    no se procesó — el caller decide cuál llamada hacer.
     */
    public static function validateForBook(Product $product, Book $book): void
    {
        $slug = $product->slug;

        if ($slug === 'book-listing-featured-1y') {
            if ($book->status !== 'listed') {
                throw new \InvalidArgumentException(
                    __('checkout.featured_requires_listed')
                );
            }
        }
    }
}
