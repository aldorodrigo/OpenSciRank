<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewRenewalEvaluation;
use App\Notifications\PaymentOrphan;
use App\Support\AdminTaskFactory;
use App\Support\ProductValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    /**
     * Uplift fijo (USD) cobrado cuando el editor selecciona "Servicio Express"
     * en el checkout. Roadmap #15 (2026-05-10): Express ya no es un SKU
     * público — viaja como line_item adicional descrito a Stripe.
     */
    public const EXPRESS_UPLIFT_AMOUNT = 50.00;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for a payable entity (Journal or Book).
     *
     * @param  array<int>  $addonProductIds  IDs de productos add-on a sumar como line_items adicionales.
     * @param  bool  $expressUplift  Si true, agrega un line_item con el uplift Express (+$50).
     */
    public function createCheckoutSession(
        User $user,
        Product $product,
        Model $payable,
        string $successUrl,
        string $cancelUrl,
        ?string $couponCode = null,
        array $metadata = [],
        array $addonProductIds = [],
        bool $expressUplift = false,
    ): Session {
        $currency = strtolower($product->currency);

        $lineItems = [
            [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $product->getTranslationWithFallback('name'),
                        'description' => $product->getTranslationWithFallback('description')
                            ?: "Plan {$product->getTranslationWithFallback('name')} - {$payable->getTranslationWithFallback('title')}",
                    ],
                    'unit_amount' => (int) ($product->price * 100), // Stripe uses cents
                ],
                'quantity' => 1,
            ],
        ];

        // Add-ons como line_items adicionales (Plan de Acción, Featured, etc.)
        $addonSlugsCsv = '';
        if (! empty($addonProductIds)) {
            $addons = Product::whereIn('id', $addonProductIds)->where('is_active', true)->get();
            foreach ($addons as $addon) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($addon->currency),
                        'product_data' => [
                            'name' => $addon->getTranslationWithFallback('name'),
                            'description' => $addon->getTranslationWithFallback('description') ?: $addon->getTranslationWithFallback('name'),
                        ],
                        'unit_amount' => (int) ($addon->price * 100),
                    ],
                    'quantity' => 1,
                ];
            }
            // Persistimos los slugs en metadata para que el webhook pueda
            // ejecutar la lógica post-pago (p.ej. activar featured en libros)
            // sin necesidad de re-leer la línea de Stripe.
            $addonSlugsCsv = $addons->pluck('slug')->implode(',');
        }

        // Express uplift: line_item virtual (+$50) — no es un Product en DB,
        // se factura como ajuste descrito al cliente en Stripe Checkout.
        if ($expressUplift) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __('Express service (+5 days turnaround)'),
                        'description' => __('Reduces standard evaluation turnaround from 15 to 5 business days.'),
                    ],
                    'unit_amount' => (int) (self::EXPRESS_UPLIFT_AMOUNT * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $user->email,
            'metadata' => array_merge([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'payable_type' => get_class($payable),
                'payable_id' => $payable->id,
                'coupon_code' => $couponCode,
                'addon_slugs' => $addonSlugsCsv,
            ], $metadata),
        ];

        if ($couponCode) {
            $sessionParams['discounts'] = [
                ['coupon' => $couponCode],
            ];
        }

        // Backend guard: reject forbidden product × payable combinations before
        // hitting Stripe. La misma comprobación corre en el componente Livewire
        // de checkout, pero esta capa cubre los bypasses por URL directa.
        if ($payable instanceof Journal) {
            ProductValidator::validateForJournal($product, $payable);
        }

        if ($payable instanceof Book) {
            ProductValidator::validateForBook($product, $payable);
        }

        return Session::create($sessionParams);
    }

    /**
     * Create a Payment record from a completed Stripe Checkout Session.
     */
    public function createPaymentFromSession(Session $session): Payment
    {
        $metadata = $session->metadata;

        // Guard against duplicate processing (webhook + success controller race)
        $existing = Payment::where('transaction_id', $session->payment_intent)->first();
        if ($existing) {
            return $existing;
        }

        // Buscar el payable antes de crear el Payment para detectar soft-deletes
        $payableClass = $metadata->payable_type;
        $payable = $payableClass::find($metadata->payable_id);

        // Nota de error en caso de payable no encontrado (puede ser soft-deleted)
        $errorNote = $payable === null
            ? 'Payable no encontrado al procesar el webhook (posible soft-delete)'
            : null;

        $payment = Payment::create([
            'user_id' => $metadata->user_id,
            'product_id' => $metadata->product_id,
            'provider' => 'stripe',
            'transaction_id' => $session->payment_intent,
            'amount' => $session->amount_total / 100,
            'currency' => strtoupper($session->currency),
            'status' => 'completed',
            'payable_type' => $metadata->payable_type,
            'payable_id' => $metadata->payable_id,
            'metadata' => [
                'stripe_session_id' => $session->id,
                'payment_intent' => $session->payment_intent,
                'customer_email' => $session->customer_details?->email,
                // Roadmap #15: registramos si el pago incluyó el uplift Express
                // para que el admin pueda priorizar la evaluación (5d vs 15d).
                'is_express' => (($metadata->express ?? '0') === '1'),
            ],
            'error_note' => $errorNote,
        ]);

        if ($payable === null) {
            // Pago huérfano: el recurso fue eliminado entre el checkout y el webhook.
            // Registrar en activity log con causer=null (Sistema)
            activity()
                ->performedOn($payment)
                ->withProperties([
                    'payable_type' => $metadata->payable_type,
                    'payable_id' => $metadata->payable_id,
                    'stripe_session_id' => $session->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ])
                ->log('Pago huérfano registrado: payable no encontrado al procesar el webhook');

            // Generar task de seguimiento (Sprint 3.6 #32)
            AdminTaskFactory::forOrphanPayment(
                $payment,
                $metadata->payable_type,
                (int) $metadata->payable_id
            );

            // Notificar al admin
            $admin = \App\Models\User::where('email', config('app.admin_email', 'admin@editorialstandards.com'))->first();
            if ($admin) {
                $admin->notify(new PaymentOrphan($payment));
            }

            return $payment;
        }

        // Update the payable entity status
        $isRenewal = ($metadata->is_renewal ?? '0') === '1';

        if ($isRenewal && $payable instanceof Journal) {
            // Política Opción B (2026-05-10): la renovación pagada NO extiende
            // el sello inmediatamente. El journal vuelve al flujo de evaluación
            // y sólo se invoca renewSeal() si la nueva evaluación aprueba.
            $years = $this->resolveRenewalYears($payment->product?->slug);

            $payable->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'pending_renewal_years' => $years,
            ]);

            // Marcar el Payment como esperando evaluación para tracking
            $payment->update([
                'metadata' => array_merge((array) $payment->metadata, [
                    'awaiting_evaluation' => true,
                    'renewal_years' => $years,
                ]),
            ]);

            // Notificar al admin que hay una renovación pendiente de evaluación
            $admin = User::where('email', config('app.admin_email', 'admin@editorialstandards.com'))->first();
            if ($admin) {
                $admin->notify(new NewRenewalEvaluation($payable, $years, (float) $payment->amount, $payment->currency));
            }
        } elseif ($payable instanceof Book) {
            // Sprint 3 #20: pago aplicado a un Book.
            // El destacado puede llegar como producto principal (cuando el
            // libro ya está listed y el editor sólo compra el addon) o como
            // addon de la compra del listing inicial. Buscamos su slug en
            // ambos lugares.
            $mainSlug = $payment->product?->slug;
            $addonSlugs = (array) ($metadata->addon_slugs ?? []);
            if (is_string($addonSlugs)) {
                // Stripe metadata sólo soporta strings escalares: aceptamos
                // CSV ("slug1,slug2") como respaldo al formato por línea.
                $addonSlugs = array_filter(array_map('trim', explode(',', $addonSlugs)));
            }
            $boughtFeatured = $mainSlug === 'book-listing-featured-1y'
                || in_array('book-listing-featured-1y', $addonSlugs, true);

            if ($boughtFeatured) {
                $this->applyBookFeatured($payable);
            }

            // Si el producto principal es el destacado puro (libro ya listed),
            // no tocamos el status del libro — sigue listed. Si en cambio es
            // book-listing (con o sin addon featured), mantenemos el flujo
            // estándar de submission.
            if ($mainSlug === 'book-listing-featured-1y') {
                // Solo addon: el libro ya está listed, no resetear estado.
                $payable->save();
            } else {
                $payable->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }
        } else {
            $payable->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        // Sprint 3.6 #32: generar task(s) de admin a partir del pago.
        // El factory decide qué tipo según el slug del producto + flags
        // (is_renewal, is_express) y los addons del checkout.
        AdminTaskFactory::fromPayment($payment, $payable, (array) $metadata);

        return $payment;
    }

    /**
     * Activa o extiende el destacado de un libro por 1 año.
     *
     * Si el libro ya tenía un featured_until vigente, sumamos 12 meses al
     * vencimiento actual (extensión). Si estaba vencido o nunca tuvo, el
     * período arranca hoy. Decisión Sprint 3 #20: el editor pagó por una
     * ventana de 12 meses, no por reemplazarla.
     */
    protected function applyBookFeatured(Book $book): void
    {
        $today = now()->toDateString();
        $baseDate = $book->featured_until && now()->lt($book->featured_until)
            ? $book->featured_until->toDateString()
            : $today;

        $book->is_featured = true;
        $book->featured_until = Carbon::parse($baseDate)->addYear()->toDateString();
        $book->save();
    }

    /**
     * Extrae los años de renovación a partir del slug del producto.
     * Soporta `seal-renewal-1y`, `seal-renewal-2y`, `seal-renewal-3y`.
     */
    protected function resolveRenewalYears(?string $slug): int
    {
        if ($slug && preg_match('/^seal-renewal-(\d+)y$/', $slug, $m)) {
            return max(1, (int) $m[1]);
        }

        // Fallback histórico: el único producto de renovación previo era 2y.
        return 2;
    }
}
