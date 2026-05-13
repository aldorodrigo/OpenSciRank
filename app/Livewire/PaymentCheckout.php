<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Journal;
use App\Models\Product;
use App\Services\StripePaymentService;
use App\Support\ProductValidator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PaymentCheckout extends Component
{
    /**
     * Uplift fijo (en USD) que se cobra cuando el editor marca el toggle
     * "Servicio Express" en el checkout de una evaluación o re-evaluación.
     * Roadmap #15 (2026-05-10): Express deja de ser SKU público y pasa a ser
     * un add-on que viaja como line_item adicional hacia Stripe.
     */
    public const EXPRESS_UPLIFT_AMOUNT = 50.00;

    public Journal $journal;

    public bool $isRenewal = false;

    public ?int $selectedPlan = null;

    public array $selectedAddons = [];

    /** Toggle del uplift Express (+$50, 5 días vs 15). */
    public bool $expressUplift = false;

    /**
     * Cupón provisto manualmente por el editor (pegado en un input). Tiene
     * precedencia absoluta sobre el auto-apply de renovación temprana: si
     * el editor escribió algo, respetamos su elección y no aplicamos nada
     * en automático aunque la fecha encaje en la ventana D-60..D-30.
     */
    public string $manualCouponCode = '';

    public bool $processing = false;

    /**
     * Stripe coupon ID que se aplica automáticamente para renovaciones
     * tempranas (60-30 días antes del vencimiento del sello). El admin
     * debe haberlo creado previamente en el Dashboard de Stripe.
     */
    public const EARLY_RENEWAL_COUPON_CODE = 'RENEW_EARLY_10';

    /** Porcentaje de descuento que aplica el cupón de renovación temprana. */
    public const EARLY_RENEWAL_DISCOUNT_PCT = 10;

    public function mount(Journal $journal)
    {
        $this->journal = $journal;

        if ($this->journal->user_id !== auth()->id()) {
            abort(403);
        }

        // Detect if this is a renewal request
        $this->isRenewal = request()->routeIs('app.renew');

        $firstProduct = $this->defaultSelectedProduct();
        if ($firstProduct) {
            $this->selectedPlan = $firstProduct->id;

            // Frontend guard: reject forbidden product × journal combinations
            // before rendering the checkout form. The Stripe service applies
            // the same check as a second line of defense.
            try {
                ProductValidator::validateForJournal($firstProduct, $this->journal);
            } catch (\InvalidArgumentException $e) {
                session()->flash('error', $e->getMessage());
                $this->redirect(route('app.dashboard'), navigate: true);

                return;
            }
        }
    }

    /**
     * Producto que se selecciona por defecto al entrar al checkout.
     * En el caso de renovaciones preferimos "2 años" como sweet spot.
     */
    protected function defaultSelectedProduct(): ?Product
    {
        $products = $this->products;

        if ($products->isEmpty()) {
            return null;
        }

        // Sweet spot: 2 años seleccionado por defecto en escenarios de renovación
        $twoYear = $products->firstWhere('slug', 'seal-renewal-2y');
        if ($twoYear) {
            return $twoYear;
        }

        return $products->first();
    }

    #[Computed]
    public function getProductsProperty()
    {
        // Renovaciones puras: mostramos los tres escalones disponibles.
        if ($this->isRenewal) {
            return Product::where('is_active', true)
                ->where('slug', 'like', 'seal-renewal-%')
                ->get()
                ->sortBy(fn ($p) => $this->renewalYearsFromSlug($p->slug))
                ->values();
        }

        // Si el journal tuvo sello alguna vez y está certificado o evaluado,
        // mostramos todas las opciones lado a lado: renovaciones (1/2/3 años)
        // + re-evaluación. El editor decide en el mismo checkout.
        if (
            in_array($this->journal->status, ['certified', 'evaluated'], true)
            && in_array($this->journal->seal_status, ['active', 'expiring_soon', 'expired'], true)
            && $this->journal->seal_awarded_at !== null
        ) {
            return Product::where('is_active', true)
                ->where(function ($q) {
                    $q->where('slug', 'like', 'seal-renewal-%')
                        ->orWhere('slug', 'journal-reevaluation');
                })
                ->get()
                // Forzamos el orden: renovaciones primero (por años asc),
                // re-evaluación al final como alternativa.
                ->sortBy(function ($p) {
                    if (str_starts_with($p->slug, 'seal-renewal-')) {
                        return $this->renewalYearsFromSlug($p->slug);
                    }
                    return 999;
                })
                ->values();
        }

        // Show evaluation products based on journal status (alineado con
        // ProductValidator 2026-05-13).
        // Default: journal-evaluation ($99) — primera evaluación, listed
        // o rejected (ciclo nuevo tras rechazo).
        $slugs = ['journal-evaluation'];

        // Re-evaluation ($99) sólo cuando ya hubo evaluación completa con
        // resultado evaluated o certified. rejected arranca ciclo nuevo y
        // requires_changes_evaluation se resubmite gratis (no llega acá).
        if (in_array($this->journal->status, ['evaluated', 'certified'])) {
            $slugs = ['journal-reevaluation'];
        }

        return Product::where('is_active', true)
            ->whereIn('slug', $slugs)
            ->get();
    }

    /**
     * Add-ons mostrados en el checkout. El SKU `express-evaluation` quedó
     * inactivo (roadmap #15); ahora Express vive como toggle separado.
     * El addon `action-plan-consulting` está disponible tanto en evaluaciones
     * como en renovaciones: el editor que renueva también quiere saber cómo
     * mejorar en el próximo ciclo. (#21)
     */
    #[Computed]
    public function getAddonsProperty()
    {
        return Product::where('is_active', true)
            ->whereIn('slug', ['action-plan-consulting'])
            ->get();
    }

    /**
     * El uplift Express sólo aplica cuando el plan principal seleccionado
     * es una evaluación o re-evaluación (no para renovaciones — ésas siguen
     * el ritmo estándar). Si el editor cambia de plan, el toggle se oculta
     * y se desactiva.
     */
    #[Computed]
    public function getCanOfferExpressProperty(): bool
    {
        $product = Product::find($this->selectedPlan);
        if (! $product) {
            return false;
        }

        return in_array($product->slug, ['journal-evaluation', 'journal-reevaluation'], true);
    }

    /**
     * Datos derivados de cada producto de renovación para mostrar el ahorro
     * en la UI. Iterar productos cuyo slug empieza con `seal-renewal-`.
     *
     * @return array<int, array{product: Product, years: int, per_year: float, savings_pct: int|null}>
     */
    #[Computed]
    public function getRenewalLadderProperty(): array
    {
        $renewals = $this->products->filter(
            fn ($p) => str_starts_with($p->slug, 'seal-renewal-')
        )->values();

        if ($renewals->isEmpty()) {
            return [];
        }

        $oneYear = $renewals->firstWhere('slug', 'seal-renewal-1y');
        $oneYearPrice = $oneYear?->price !== null ? (float) $oneYear->price : null;

        return $renewals->map(function ($product) use ($oneYearPrice) {
            $years = $this->renewalYearsFromSlug($product->slug);
            $price = (float) $product->price;
            $perYear = $years > 0 ? $price / $years : $price;

            $savingsPct = null;
            if ($oneYearPrice !== null && $years > 1) {
                $hypothetical = $oneYearPrice * $years;
                if ($hypothetical > 0) {
                    $savingsPct = (int) round((($hypothetical - $price) / $hypothetical) * 100);
                }
            }

            return [
                'product' => $product,
                'years' => $years,
                'per_year' => $perYear,
                'savings_pct' => $savingsPct,
            ];
        })->all();
    }

    protected function renewalYearsFromSlug(string $slug): int
    {
        if (preg_match('/^seal-renewal-(\d+)y$/', $slug, $m)) {
            return max(1, (int) $m[1]);
        }
        return 0;
    }

    public function toggleAddon(int $addonId)
    {
        if (in_array($addonId, $this->selectedAddons)) {
            $this->selectedAddons = array_values(array_diff($this->selectedAddons, [$addonId]));
        } else {
            $this->selectedAddons[] = $addonId;
        }
    }

    public function toggleExpress(): void
    {
        $this->expressUplift = ! $this->expressUplift;
    }

    /**
     * Hook Livewire: si el editor cambia a un plan que no permite Express,
     * desactivamos el toggle silenciosamente.
     */
    public function updatedSelectedPlan(): void
    {
        if (! $this->canOfferExpress) {
            $this->expressUplift = false;
        }
    }

    /**
     * Ventana D-60..D-30: el journal está certificado, tiene fecha de
     * vencimiento del sello, y faltan entre 30 y 60 días (inclusive) para
     * que venza. Si la ventana se cumple Y el plan seleccionado es una
     * renovación, aplicamos automáticamente el cupón RENEW_EARLY_10.
     *
     * Carbon ops: `seal_expires_at` está entre `now()->addDays(30)` y
     * `now()->addDays(60)` (ambos inclusive). Calculamos en días enteros
     * con `startOfDay()` para evitar bordes por hora.
     */
    #[Computed]
    public function getIsInEarlyRenewalWindowProperty(): bool
    {
        if (! $this->journal->seal_expires_at) {
            return false;
        }

        $expiresAt = $this->journal->seal_expires_at->copy()->startOfDay();
        $today = now()->startOfDay();

        // Si ya venció, no es renovación temprana — es tardía/recovery.
        if ($expiresAt->isPast()) {
            return false;
        }

        $daysUntilExpiry = $today->diffInDays($expiresAt, false);

        return $daysUntilExpiry >= 30 && $daysUntilExpiry <= 60;
    }

    /**
     * Producto seleccionado actualmente es una renovación de sello
     * (slug empieza con `seal-renewal-`).
     */
    protected function selectedIsRenewalProduct(): bool
    {
        $product = Product::find($this->selectedPlan);
        if (! $product) {
            return false;
        }

        return str_starts_with($product->slug, 'seal-renewal-');
    }

    /**
     * Cupón de Stripe que la app va a aplicar al crear la sesión.
     *
     * Precedencia (mayor a menor):
     *  1. `$manualCouponCode` — si el editor pegó un código a mano, manda.
     *  2. `RENEW_EARLY_10` — auto-apply para renovación temprana D-60..D-30
     *     SÓLO si el producto seleccionado es una renovación.
     *  3. null — sin cupón.
     */
    #[Computed]
    public function getAppliedCouponCodeProperty(): ?string
    {
        // 1. Manual tiene precedencia absoluta (sea válido o no — Stripe rechaza al validar).
        $manual = trim($this->manualCouponCode);
        if ($manual !== '') {
            return $manual;
        }

        // 2. Auto-apply: ventana D-60..D-30 + producto de renovación + cupón existe y usable.
        if ($this->isInEarlyRenewalWindow && $this->selectedIsRenewalProduct()) {
            $coupon = Coupon::where('code', self::EARLY_RENEWAL_COUPON_CODE)->first();
            if ($coupon && $coupon->isUsable()) {
                return $coupon->code;
            }
        }

        return null;
    }

    /**
     * Indica si el cupón aplicado es el auto-apply de renovación temprana
     * (para mostrar el badge "Descuento renovación temprana" en el resumen).
     */
    #[Computed]
    public function getIsAutoEarlyDiscountAppliedProperty(): bool
    {
        if (trim($this->manualCouponCode) !== '') {
            return false;
        }

        return $this->appliedCouponCode === self::EARLY_RENEWAL_COUPON_CODE;
    }

    /**
     * Fecha límite en la que vence la promoción D-30 (cuando cierra la
     * ventana de renovación temprana para este journal).
     */
    #[Computed]
    public function getEarlyDiscountDeadlineProperty(): ?\Carbon\Carbon
    {
        if (! $this->journal->seal_expires_at) {
            return null;
        }
        return $this->journal->seal_expires_at->copy()->subDays(30);
    }

    #[Computed]
    public function getSubtotalProperty(): float
    {
        $total = 0;
        $mainProduct = Product::find($this->selectedPlan);
        if ($mainProduct) {
            $total += (float) $mainProduct->price;
        }
        foreach ($this->selectedAddons as $addonId) {
            $addon = Product::find($addonId);
            if ($addon) {
                $total += (float) $addon->price;
            }
        }

        if ($this->expressUplift && $this->canOfferExpress) {
            $total += self::EXPRESS_UPLIFT_AMOUNT;
        }

        return $total;
    }

    /**
     * Monto del descuento aplicado SÓLO al producto principal de renovación
     * (Stripe en este modo aplica el cupón sobre el line item completo, pero
     * en la UI mostramos el monto computado sobre el plan elegido + addons
     * para que el editor entienda el ahorro estimado).
     *
     * Nota: el cálculo real lo hace Stripe en checkout. Esto es preview.
     */
    #[Computed]
    public function getDiscountAmountProperty(): float
    {
        if (! $this->isAutoEarlyDiscountApplied) {
            return 0.0;
        }

        // 10% del producto principal de renovación. No descuenta sobre
        // addons (Stripe aplica el cupón sobre la sesión completa por
        // defecto, así que esta es una aproximación conservadora UI).
        $mainProduct = Product::find($this->selectedPlan);
        if (! $mainProduct) {
            return 0.0;
        }

        return round((float) $mainProduct->price * (self::EARLY_RENEWAL_DISCOUNT_PCT / 100), 2);
    }

    #[Computed]
    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    public function selectPlan(int $planId)
    {
        $this->selectedPlan = $planId;

        // Si el nuevo plan no admite Express, apagamos el toggle.
        if (! $this->canOfferExpress) {
            $this->expressUplift = false;
        }
    }

    public function processPayment()
    {
        $product = Product::find($this->selectedPlan);
        if (! $product) {
            return;
        }

        $this->processing = true;

        try {
            $service = app(StripePaymentService::class);

            // Build line items: main product + addons
            $cancelRoute = $this->isRenewal ? 'app.renew' : 'app.checkout';

            $useExpress = $this->expressUplift && $this->canOfferExpress;
            $couponCode = $this->appliedCouponCode;

            $session = $service->createCheckoutSession(
                user: auth()->user(),
                product: $product,
                payable: $this->journal,
                successUrl: route('app.checkout.success', ['journal' => $this->journal->id]).'?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: route($cancelRoute, ['journal' => $this->journal->id]),
                couponCode: $couponCode,
                metadata: [
                    'is_renewal' => $this->isRenewal ? '1' : '0',
                    'addon_ids' => implode(',', $this->selectedAddons),
                    'express' => $useExpress ? '1' : '0',
                    'auto_early_discount' => $this->isAutoEarlyDiscountApplied ? '1' : '0',
                ],
                addonProductIds: $this->selectedAddons,
                expressUplift: $useExpress,
            );

            return redirect($session->url);
        } catch (\Exception $e) {
            $this->processing = false;
            session()->flash('error', __('Error processing the payment. Please try again.'));
            \Illuminate\Support\Facades\Log::error('Stripe checkout error', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.payment-checkout')->layout('components.layouts.app', [
            'title' => __('Payment').' - '.$this->journal->getTranslationWithFallback('title').' - Editorial Standards Platform',
        ]);
    }
}
