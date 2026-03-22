<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Journal;
use App\Models\Product;
use App\Services\StripePaymentService;

class PaymentCheckout extends Component
{
    public Journal $journal;
    public bool $isRenewal = false;

    public ?int $selectedPlan = null;
    public array $selectedAddons = [];
    public bool $processing = false;

    public function mount(Journal $journal)
    {
        $this->journal = $journal;

        if ($this->journal->user_id !== auth()->id()) {
            abort(403);
        }

        // Detect if this is a renewal request
        $this->isRenewal = request()->routeIs('app.renew');

        $firstProduct = $this->products->first();
        if ($firstProduct) {
            $this->selectedPlan = $firstProduct->id;
        }
    }

    #[Computed]
    public function getProductsProperty()
    {
        if ($this->isRenewal) {
            return Product::where('is_active', true)
                ->where('slug', 'seal-renewal-2y')
                ->get();
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

    #[Computed]
    public function getAddonsProperty()
    {
        if ($this->isRenewal) {
            return collect();
        }

        return Product::where('is_active', true)
            ->whereIn('slug', ['express-evaluation', 'premium-report'])
            ->get();
    }

    public function toggleAddon(int $addonId)
    {
        if (in_array($addonId, $this->selectedAddons)) {
            $this->selectedAddons = array_values(array_diff($this->selectedAddons, [$addonId]));
        } else {
            $this->selectedAddons[] = $addonId;
        }
    }

    #[Computed]
    public function getTotalProperty(): float
    {
        $total = 0;
        $mainProduct = Product::find($this->selectedPlan);
        if ($mainProduct) {
            $total += $mainProduct->price;
        }
        foreach ($this->selectedAddons as $addonId) {
            $addon = Product::find($addonId);
            if ($addon) {
                $total += $addon->price;
            }
        }
        return $total;
    }

    public function selectPlan(int $planId)
    {
        $this->selectedPlan = $planId;
    }

    public function processPayment()
    {
        $product = Product::find($this->selectedPlan);
        if (!$product) return;

        $this->processing = true;

        try {
            $service = app(StripePaymentService::class);

            // Build line items: main product + addons
            $addonProducts = Product::whereIn('id', $this->selectedAddons)->get();
            $cancelRoute = $this->isRenewal ? 'app.renew' : 'app.checkout';

            $session = $service->createCheckoutSession(
                user: auth()->user(),
                product: $product,
                payable: $this->journal,
                successUrl: route('app.checkout.success', ['journal' => $this->journal->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: route($cancelRoute, ['journal' => $this->journal->id]),
                metadata: [
                    'is_renewal' => $this->isRenewal ? '1' : '0',
                    'addon_ids' => implode(',', $this->selectedAddons),
                ],
            );

            return redirect($session->url);
        } catch (\Exception $e) {
            $this->processing = false;
            session()->flash('error', 'Error al procesar el pago. Por favor, inténtalo de nuevo.');
            \Illuminate\Support\Facades\Log::error('Stripe checkout error', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.payment-checkout')->layout('components.layouts.app', [
            'title' => 'Pago - ' . $this->journal->title . ' - Editorial Standards Platform',
        ]);
    }
}
