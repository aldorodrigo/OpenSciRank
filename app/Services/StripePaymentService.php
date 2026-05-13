<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewRenewalEvaluation;
use App\Notifications\PaymentOrphan;
use App\Support\ProductValidator;
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

        // Add-ons como line_items adicionales (Plan de Acción, etc.)
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
            ], $metadata),
        ];

        if ($couponCode) {
            $sessionParams['discounts'] = [
                ['coupon' => $couponCode],
            ];
        }

        // Backend guard: reject forbidden product × journal combinations before
        // hitting Stripe. The same check runs in PaymentCheckout::mount(), but
        // this layer catches direct URL bypasses.
        if ($payable instanceof Journal) {
            ProductValidator::validateForJournal($product, $payable);
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
        } else {
            $payable->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        return $payment;
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
