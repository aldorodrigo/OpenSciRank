<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sprint 3.7 #39 — checkout para el "Pack Lanzamiento Editorial" (new-journal-consulting).
 *
 * A diferencia de PaymentCheckout (que siempre recibe un Journal o Book como
 * payable), aquí el payable es el propio User (no hay revista asociada todavía).
 * El servicio StripePaymentService acepta cualquier Model como payable, por lo
 * que no necesita modificaciones; solo se le pasa el User.
 *
 * El `getTranslationWithFallback('title')` no existe en User, así que en el
 * product_data.description de Stripe usamos el nombre del usuario directamente
 * (línea ya resuelta en createCheckoutSession vía la descripción del producto).
 */
class NewJournalConsultingCheckoutController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $product = Product::where('slug', 'new-journal-consulting')
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return redirect()->route('app.consulting')
                ->with('error', 'El producto "Pack Lanzamiento Editorial" no está disponible en este momento. Contactanos para más información.');
        }

        $service = app(StripePaymentService::class);

        try {
            $session = $service->createCheckoutSession(
                user: $user,
                product: $product,
                payable: $user,
                successUrl: route('app.consulting') . '?payment=ok',
                cancelUrl: route('app.consulting'),
            );

            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()->route('app.consulting')
                ->with('error', 'No fue posible iniciar el pago: ' . $e->getMessage());
        }
    }
}
