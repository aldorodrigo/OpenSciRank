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
    public bool $processing = false;

    public function mount(Book $book)
    {
        $this->book = $book;

        if ($this->book->user_id !== auth()->id()) {
            abort(403);
        }

        $firstProduct = $this->products->first();
        if ($firstProduct) {
            $this->selectedPlan = $firstProduct->id;
        }
    }

    #[Computed]
    public function getProductsProperty()
    {
        return Product::where('is_active', true)->get();
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

            $session = $service->createCheckoutSession(
                user: auth()->user(),
                product: $product,
                payable: $this->book,
                successUrl: route('app.book.checkout.success', ['book' => $this->book->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: route('app.book.checkout', ['book' => $this->book->id]),
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
        return view('livewire.book-payment-checkout')->layout('components.layouts.app', [
            'title' => 'Pago - ' . $this->book->title . ' - Editorial Standards Platform',
        ]);
    }
}
