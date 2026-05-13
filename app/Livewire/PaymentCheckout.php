<?php

namespace App\Livewire;

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

    public bool $processing = false;

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

        // Show evaluation products based on journal status
        $slugs = ['journal-evaluation'];

        // If already evaluated, show re-evaluation instead
        if (in_array($this->journal->status, ['evaluated', 'certified', 'requires_changes_evaluation', 'listed'])) {
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

    #[Computed]
    public function getTotalProperty(): float
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

            $session = $service->createCheckoutSession(
                user: auth()->user(),
                product: $product,
                payable: $this->journal,
                successUrl: route('app.checkout.success', ['journal' => $this->journal->id]).'?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: route($cancelRoute, ['journal' => $this->journal->id]),
                metadata: [
                    'is_renewal' => $this->isRenewal ? '1' : '0',
                    'addon_ids' => implode(',', $this->selectedAddons),
                    'express' => $useExpress ? '1' : '0',
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
