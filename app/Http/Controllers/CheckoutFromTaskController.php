<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Sprint 3.7 #44 — checkout iniciado desde un link de pago de una admin_task.
 *
 * El admin crea una task con producto seleccionado → se genera signed URL →
 * el editor clickea → este controller resuelve product + payable desde la
 * task y crea la Stripe Checkout Session con metadata.admin_task_id para
 * que el webhook asocie el Payment vía solicited_by_admin_task_id.
 *
 * La ruta está protegida por:
 *  - middleware 'signed' (token expira en 30 días)
 *  - middleware 'auth' (el editor debe estar logueado)
 *  - autorización: el user logueado debe ser el editor (related.user) del recurso
 */
class CheckoutFromTaskController extends Controller
{
    public function __construct(protected StripePaymentService $stripeService)
    {
    }

    public function show(Request $request, AdminTask $task): RedirectResponse
    {
        // Sprint 3.7 #44: destino de error apropiado según el flujo.
        // El editor que clickea desde un mensaje vuelve a /app/messages para
        // ver el contexto. Si no estaba logueado, signed middleware ya redirigió.
        $errorRedirect = url('/app/messages');

        $product = $this->resolveProduct($task);
        if (! $product) {
            return redirect($errorRedirect)->with('error', __(
                'Este link de pago está mal configurado: el producto fue eliminado o nunca se asoció. Avisale al admin para que regenere el link.'
            ));
        }

        $payable = $this->resolvePayable($task, $product);
        if (! $payable) {
            return redirect($errorRedirect)->with('error', __(
                'Este link de pago no se puede procesar: el producto :product requiere un :resource del editor, pero no se encontró. Pedile al admin que regenere el link con el recurso correcto.',
                [
                    'product' => $product->getTranslationWithFallback('name'),
                    'resource' => \App\Support\PaymentPayableResolver::expectedPayableLabel($product),
                ]
            ));
        }

        // Autorización: el user logueado tiene que ser el editor del recurso.
        $editor = $this->resolveEditor($payable);
        if (! $editor) {
            return redirect($errorRedirect)->with('error', __(
                'No se pudo identificar al editor de este pago. Contactá al equipo.'
            ));
        }

        if (Auth::id() !== $editor->id) {
            abort(403, __('No autorizado: este link de pago es para otro editor.'));
        }

        // Si la task ya fue marcada como completed/cancelled, link inválido.
        if ($task->status === AdminTask::STATUS_COMPLETED) {
            return redirect($errorRedirect)->with('error', __(
                'Este link de pago ya no es válido: la gestión asociada fue marcada como completada. Si tenés alguna duda, escribinos por mensaje.'
            ));
        }

        if ($task->status === AdminTask::STATUS_CANCELLED) {
            return redirect($errorRedirect)->with('error', __(
                'Este link de pago ya no es válido: la gestión asociada fue cancelada. Si tenés alguna duda, escribinos por mensaje.'
            ));
        }

        // Si ya hay un payment solicitado completado, no permitir pagar otra vez.
        $existing = $task->solicitedPayment;
        if ($existing && $existing->status === 'completed') {
            return redirect($errorRedirect)->with('info', __(
                'Este pago ya fue completado el :date. ¡Gracias!',
                ['date' => $existing->created_at->format('d/m/Y H:i')]
            ));
        }

        // Si hay un payment pending del mismo task (intento previo), avisar
        if ($existing && $existing->status === 'pending') {
            // Permitimos reintentar el checkout. Stripe genera una nueva session,
            // si el editor completa, el webhook actualiza ese mismo Payment.
            Log::info('Reintento de checkout sobre payment pending', [
                'task_id' => $task->id,
                'payment_id' => $existing->id,
            ]);
        }

        // Sprint 3.7 #44 + #46: resolver la success URL según el tipo de payable.
        // Journal/Book usan rutas dedicadas con su propio webhook fallback.
        // User-payable (support-credit, new-journal-consulting) usa este mismo
        // controller en /success para hacer fallback de webhook (en local
        // Stripe no puede alcanzar localhost/webhook, sin esto el Payment
        // nunca se crea y la task queda colgada en awaiting_payment).
        $successUrl = match (true) {
            $payable instanceof Journal => route('app.checkout.success', ['journal' => $payable->id]).'?session_id={CHECKOUT_SESSION_ID}',
            $payable instanceof Book => route('app.book.checkout.success', ['book' => $payable->id]).'?session_id={CHECKOUT_SESSION_ID}',
            $payable instanceof User => route('checkout.from-task.success', ['task' => $task->id]).'?session_id={CHECKOUT_SESSION_ID}',
            default => route('checkout.from-task.success', ['task' => $task->id]).'?session_id={CHECKOUT_SESSION_ID}',
        };

        try {
            $session = $this->stripeService->createCheckoutSession(
                user: $editor,
                product: $product,
                payable: $payable,
                successUrl: $successUrl,
                cancelUrl: url('/app/messages'),
                adminTaskId: $task->id,
            );

            return redirect($session->url);
        } catch (\InvalidArgumentException $e) {
            // ProductValidator rechazó la combinación (status de journal, etc.)
            Log::warning('Checkout from task rejected by validator', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return redirect($errorRedirect)->with('error', __(
                'No se puede procesar este pago por el estado actual del recurso: :reason',
                ['reason' => $e->getMessage()]
            ));
        } catch (\Throwable $e) {
            Log::error('Checkout from task failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return redirect($errorRedirect)->with('error', __(
                'No se pudo iniciar el checkout por un error técnico. Probá de nuevo en unos minutos o contactanos por mensaje.'
            ));
        }
    }

    /**
     * El producto vive en el title_params del task (lo agregamos al crear con
     * link de pago) o en la metadata del notes. Más limpio: usar relación
     * directa en el modelo. Pero por ahora resolvemos del title_params.
     *
     * En Sprint 3 vamos a guardar el product_id como title_param 'product_id'
     * cuando el admin crea la task con link de pago.
     */
    protected function resolveProduct(AdminTask $task): ?Product
    {
        $productId = $task->title_params['product_id'] ?? null;
        if (! $productId) return null;

        return Product::where('id', $productId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * El payable se infiere del related de la task según el tipo de producto,
     * con fallback a los recursos del editor si el related no es compatible.
     *
     * Usa el helper centralizado `PaymentPayableResolver` para mantener la
     * lógica consistente con `MessageThread::createTaskFromMessage()`.
     */
    protected function resolvePayable(AdminTask $task, Product $product): ?\Illuminate\Database\Eloquent\Model
    {
        $related = $task->related;
        $editor = $task->editor();
        if (! $editor) return null;

        return \App\Support\PaymentPayableResolver::resolveCorrectPayableFor($product, $related, $editor);
    }

    protected function resolveEditor(\Illuminate\Database\Eloquent\Model $payable): ?User
    {
        if ($payable instanceof User) return $payable;
        return $payable->user ?? null;
    }

    /**
     * Sprint 3.7 #46 — success handler para checkouts task-based con payable=User.
     *
     * Hace el mismo fallback de webhook que `CheckoutSuccessController::journal/book`:
     * si Stripe confirmó el pago pero el webhook todavía no llegó (típico en local
     * porque Stripe no puede alcanzar localhost), creamos el Payment leyendo la
     * Session vía API y disparamos todos los side effects asociados (activar
     * task, notificar, etc.).
     *
     * Sin esto, los pagos task-based con payable=User (support-credit,
     * new-journal-consulting) quedarían "perdidos" en local — Stripe cobra
     * la tarjeta pero el sistema nunca se entera y la task queda colgada en
     * awaiting_payment.
     */
    public function success(Request $request, AdminTask $task): RedirectResponse
    {
        $editor = $task->editor();
        if (! $editor) {
            return redirect('/app/messages')->with('error', __(
                'No se pudo identificar al editor de esta tarea. Contactanos por mensaje.'
            ));
        }

        if (Auth::id() !== $editor->id) {
            abort(403, __('No autorizado: este pago corresponde a otro editor.'));
        }

        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return redirect('/app/messages')->with('error', __(
                'No se recibió la confirmación de Stripe. Si pagaste, esperá unos minutos y revisá tus mensajes.'
            ));
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect('/app/messages')->with('error', __(
                    'El pago no se completó en Stripe. Si querés intentar de nuevo, volvé al mensaje original y reabrí el link.'
                ));
            }

            // Webhook fallback: si el webhook aún no creó el Payment, lo creamos
            // acá leyendo directo de Stripe. Idempotente — si ya existe, skip.
            $existingPayment = Payment::where('transaction_id', $session->payment_intent)->first();

            if (! $existingPayment) {
                $this->stripeService->createPaymentFromSession($session);

                Log::info('CheckoutFromTask: Created payment as webhook fallback', [
                    'task_id' => $task->id,
                    'session_id' => $sessionId,
                ]);
            }

            return redirect('/app/messages')->with('success', __(
                '¡Pago confirmado! Te respondemos a la brevedad por este mismo hilo.'
            ));
        } catch (\Throwable $e) {
            Log::warning('CheckoutFromTask: Could not verify session', [
                'task_id' => $task->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect('/app/messages')->with('warning', __(
                'Pago iniciado pero no pudimos verificarlo en línea. Si Stripe te cobró, te confirmaremos por email en breve.'
            ));
        }
    }
}
