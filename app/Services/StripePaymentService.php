<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
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
                        'name' => $product->name,
                        'description' => $product->description ?? "Plan {$product->name} - {$payable->title}",
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
        ]);

        // Update the payable entity status
        $payableClass = $metadata->payable_type;
        $payable = $payableClass::find($metadata->payable_id);

        if ($payable) {
            $isRenewal = ($metadata->is_renewal ?? '0') === '1';

            if ($isRenewal) {
                // Seal renewal: extend by 2 years
                $payable->renewSeal(2);
            } else {
                $payable->update(['status' => 'submitted']);
            }
        }

        return $payment;
    }
}
