<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Book;
use App\Models\Product;
use App\Services\StripePaymentService;

class BookPaymentCheckout extends Component
{
    public Book $book;

    public ?int $selectedPlan = null;

    /**
     * Sprint 3 #20: addon de listing destacado. Solo se ofrece cuando el
     * plan seleccionado es book-listing (junto con el listing gratuito) o
     * cuando el libro ya está listed (entonces el destacado puede comprarse
     * como producto principal por separado vía $standaloneFeatured).
     */
    public bool $featuredAddon = false;

    public bool $processing = false;

    public function mount(Book $book)
    {
        $this->book = $book;

        if ($this->book->user_id !== auth()->id()) {
            abort(403);
        }

        // Si el libro ya está listed, el plan por defecto es el destacado
        // standalone (no tiene sentido recomprar el listing). Para el resto
        // de los casos, el primer producto listable.
        $firstProduct = $this->availablePlans->first();
        if ($firstProduct) {
            $this->selectedPlan = $firstProduct->id;
        }
    }

    #[Computed]
    public function getProductsProperty()
    {
        // Mantenido para retrocompatibilidad con la vista actual: lista
        // completa de productos activos (incluye el featured) sin filtros.
        return Product::where('is_active', true)->get();
    }

    /**
     * Planes principales que pueden seleccionarse en función del estado del
     * libro. Filtra el featured cuando el libro NO está listed (en ese caso
     * sólo se ofrece como addon del listing).
     */
    #[Computed]
    public function getAvailablePlansProperty()
    {
        $query = Product::where('is_active', true)->whereIn('slug', [
            'book-listing',
            'book-listing-featured-1y',
        ]);

        if ($this->book->status !== 'listed') {
            // Destacado standalone requiere libro listed (ver ProductValidator).
            $query->where('slug', '!=', 'book-listing-featured-1y');
        }

        return $query->get();
    }

    #[Computed]
    public function getFeaturedProductProperty(): ?Product
    {
        return Product::where('slug', 'book-listing-featured-1y')
            ->where('is_active', true)
            ->first();
    }

    /**
     * El addon "destacar listing" se ofrece sólo cuando el plan principal
     * elegido es book-listing. Si el plan principal ya es el featured
     * standalone, no tiene sentido sumar el addon.
     */
    public function getCanOfferFeaturedAddonProperty(): bool
    {
        $product = Product::find($this->selectedPlan);
        return $product?->slug === 'book-listing';
    }

    public function selectPlan(int $planId)
    {
        $this->selectedPlan = $planId;

        if (! $this->canOfferFeaturedAddon) {
            $this->featuredAddon = false;
        }
    }

    public function toggleFeaturedAddon(): void
    {
        $this->featuredAddon = ! $this->featuredAddon;
    }

    /**
     * Total a pagar incluyendo el addon featured cuando corresponde.
     */
    public function getTotalProperty(): float
    {
        $plan = Product::find($this->selectedPlan);
        $total = (float) ($plan?->price ?? 0);

        if ($this->featuredAddon && $this->canOfferFeaturedAddon && $this->featuredProduct) {
            $total += (float) $this->featuredProduct->price;
        }

        return $total;
    }

    public function processPayment()
    {
        $product = Product::find($this->selectedPlan);
        if (!$product) return;

        $this->processing = true;

        try {
            $service = app(StripePaymentService::class);

            $addonIds = [];
            if ($this->featuredAddon && $this->canOfferFeaturedAddon && $this->featuredProduct) {
                $addonIds[] = $this->featuredProduct->id;
            }

            $session = $service->createCheckoutSession(
                user: auth()->user(),
                product: $product,
                payable: $this->book,
                successUrl: route('app.book.checkout.success', ['book' => $this->book->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: route('app.book.checkout', ['book' => $this->book->id]),
                addonProductIds: $addonIds,
            );

            return redirect($session->url);
        } catch (\InvalidArgumentException $e) {
            $this->processing = false;
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->processing = false;
            session()->flash('error', __('Error processing the payment. Please try again.'));
            \Illuminate\Support\Facades\Log::error('Stripe checkout error', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.book-payment-checkout')->layout('components.layouts.app', [
            'title' => __('Payment') . ' - ' . $this->book->getTranslationWithFallback('title') . ' - Editorial Standards Platform',
        ]);
    }
}
