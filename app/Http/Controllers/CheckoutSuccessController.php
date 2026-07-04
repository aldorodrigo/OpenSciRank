<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ConsultingPaymentConfirmed;
use App\Notifications\PaymentConfirmed;
use App\Services\StripePaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutSuccessController extends Controller
{
    public function journal(Request $request, Journal $journal)
    {
        if ($journal->user_id !== auth()->id()) {
            abort(403);
        }

        $result = $this->verifyAndSyncPayment($request->query('session_id'), $journal);

        return view('checkout-success', [
            'type' => __('journal'),
            'title' => $journal->getTranslationWithFallback('title'),
            'paid' => $result['paid'],
            'isRenewal' => $result['is_renewal'] ?? false,
            'sealExpiresAt' => $journal->fresh()->seal_expires_at,
        ]);
    }

    public function book(Request $request, Book $book)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $result = $this->verifyAndSyncPayment($request->query('session_id'), $book);

        return view('checkout-success', [
            'type' => __('book'),
            'title' => $book->getTranslationWithFallback('title'),
            'paid' => $result['paid'],
            'isRenewal' => false,
            'sealExpiresAt' => null,
        ]);
    }

    /**
     * Verify Stripe session and ensure payment+status are synced.
     * Acts as fallback if the webhook hasn't arrived yet.
     */
    protected function verifyAndSyncPayment(?string $sessionId, Model $payable): array
    {
        if (! $sessionId) {
            return ['paid' => false];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['paid' => false];
            }

            // Check if the webhook already created the payment
            $existingPayment = Payment::where('payable_type', get_class($payable))
                ->where('payable_id', $payable->id)
                ->where('transaction_id', $session->payment_intent)
                ->first();

            if (! $existingPayment) {
                // Webhook hasn't arrived yet — create payment as fallback
                $service = app(StripePaymentService::class);
                $service->createPaymentFromSession($session);

                Log::info('CheckoutSuccess: Created payment as webhook fallback', [
                    'session_id' => $sessionId,
                    'payable' => get_class($payable).'#'.$payable->id,
                ]);
            }

            // Ensure status is updated (skip for renewals — handled by createPaymentFromSession)
            // Sprint 3.7: guard contra downgrade de estados terminales. Si el
            // editor llega al success URL con una sesión vieja sobre una revista
            // ya certified/evaluated, NO debemos resetear su estado a submitted.
            // Solo aplicar el reset cuando viene de un estado pre-evaluación.
            $isRenewal = ($session->metadata->is_renewal ?? '0') === '1';
            $preEvaluationStatuses = ['draft', 'requires_changes_evaluation', 'pending_listing', 'requires_changes_listing', 'listed'];
            if (! $isRenewal
                && $payable->status !== 'submitted'
                && in_array($payable->status, $preEvaluationStatuses, true)
            ) {
                $payable->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }

            // Send payment confirmation email
            $payment = Payment::where('payable_type', get_class($payable))
                ->where('payable_id', $payable->id)
                ->where('transaction_id', $session->payment_intent)
                ->first();

            if ($payment) {
                // Sprint 3.7 #38: para SKUs de consultoría enviamos un mail
                // específico con los próximos pasos (qué esperar, cuándo).
                // Para new-journal-consulting payable=User → $payable es el
                // editor mismo, no tiene relación `user`.
                //
                // El correo va en su propio try/catch: un fallo de notificación
                // NO debe caer al catch externo y devolver ['paid' => false] a
                // quien SÍ pagó (el pago ya está confirmado en este punto).
                try {
                    $editor = $payable instanceof User ? $payable : $payable->user;
                    $slug = $payment->product?->slug;

                    if (in_array($slug, ['action-plan-consulting', 'new-journal-consulting'], true)) {
                        $editor->notify(new ConsultingPaymentConfirmed($payment));
                    } else {
                        $editor->notify(new PaymentConfirmed($payment));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Payment confirmation notification failed', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return ['paid' => true, 'is_renewal' => $isRenewal];
        } catch (\Exception $e) {
            Log::warning('Could not verify Stripe session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return ['paid' => false];
        }
    }
}
