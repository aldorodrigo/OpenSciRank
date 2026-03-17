<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Journal;
use App\Models\Product;

class PaymentCheckout extends Component
{
    public Journal $journal;

    public ?int $selectedPlan = null;
    public string $paymentMethod = 'card';

    public function mount(Journal $journal)
    {
        $this->journal = $journal;

        // Ensure user owns this journal
        if ($this->journal->user_id !== auth()->id()) {
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

        // Simulate successful payment & attach to Journal
        $payment = \App\Models\Payment::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'amount' => $product->price,
            'currency' => $product->currency,
            'provider' => $this->paymentMethod,
            'status' => 'completed',
        ]);
        
        $payment->payable()->associate($this->journal);
        $payment->save();

        $this->journal->update([
            'status' => 'submitted',
        ]);

        session()->flash('message', '¡Pago procesado exitosamente! Tu revista ha sido enviada a revisión.');

        return redirect()->route('app.dashboard');
    }

    public function render()
    {
        return view('livewire.payment-checkout')->layout('components.layouts.app', [
            'title' => 'Pago - ' . $this->journal->title . ' - OpenSciRank',
        ]);
    }
}
