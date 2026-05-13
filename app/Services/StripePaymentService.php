<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\PaymentOrphan;
use App\Support\ProductValidator;
use Illuminate\Database\Eloquent\Model;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for a payable entity (Journal or Book).
     */
    public function createCheckoutSession(
        User $user,
        Product $product,
        Model $payable,
        string $successUrl,
        string $cancelUrl,
        ?string $couponCode = null,
        array $metadata = [],
    ): Session {
        $lineItems = [
            [
                'price_data' => [
                    'currency' => strtolower($product->currency),
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

        if ($isRenewal) {
            // Renovación del sello: extender 2 años
            $payable->renewSeal(2);
        } else {
            $payable->update(['status' => 'submitted']);
        }

        return $payment;
    }
}
