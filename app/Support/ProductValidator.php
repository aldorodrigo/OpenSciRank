<?php

namespace App\Support;

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
     * Rules:
     *  - journal-evaluation   → journal must be in draft / listed / requires_changes_evaluation
     *  - journal-reevaluation → journal must be in evaluated / certified / rejected
     *  - seal-renewal-*       → journal must have been certified at least once (seal_awarded_at IS NOT NULL)
     *  - book products and standalone addons are NOT validated here (not journal-specific)
     */
    public static function validateForJournal(Product $product, Journal $journal): void
    {
        $slug = $product->slug;

        if ($slug === 'journal-evaluation') {
            $allowed = ['draft', 'listed', 'requires_changes_evaluation'];
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

        // All other slugs (book-listing, express-evaluation, premium-report,
        // action-plan-consulting, institutional-pack, etc.) are allowed without
        // journal-status constraints.
    }
}
