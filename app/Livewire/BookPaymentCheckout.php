<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Book;
use App\Models\Product;

class BookPaymentCheckout extends Component
{
    public Book $book;

    public ?int $selectedPlan = null;
    public string $paymentMethod = 'card';

    public function mount(Book $book)
    {
        $this->book = $book;

        // Ensure user owns this book
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

        // Simulate successful payment & attach to Book
        $payment = \App\Models\Payment::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'amount' => $product->price,
            'currency' => $product->currency,
            'provider' => $this->paymentMethod,
            'status' => 'completed',
        ]);
        
        $payment->payable()->associate($this->book);
        $payment->save();

        $this->book->update([
            'status' => 'submitted',
        ]);

        session()->flash('message', '¡Pago procesado exitosamente! Tu libro ha sido enviado a revisión.');

        return redirect()->route('app.dashboard');
    }

    public function render()
    {
        return view('livewire.book-payment-checkout')->layout('components.layouts.app', [
            'title' => 'Pago - ' . $this->book->title . ' - OpenSciRank',
        ]);
    }
}
