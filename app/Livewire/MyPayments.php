<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Sprint 3.6 #30: pantalla "Mis pagos" del editor.
 *
 * Lista paginada de pagos del usuario autenticado con filtros básicos
 * (estado, producto, rango de fechas) y un botón por fila para abrir
 * el desglose (modal con Payment::breakdown() — items + total).
 */
class MyPayments extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $productFilter = '';

    public bool $showDetailModal = false;

    public ?int $selectedPaymentId = null;

    /** Modo embebido en otro componente (sin header del site ni padding extra). */
    public bool $asModal = false;

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'productFilter' => ['except' => ''],
    ];

    public function updating($name, $value): void
    {
        if (in_array($name, ['statusFilter', 'productFilter'], true)) {
            $this->resetPage();
        }
    }

    public function showDetail(int $paymentId): void
    {
        // Asegurarse que el payment pertenece al usuario
        $exists = Payment::where('id', $paymentId)
            ->where('user_id', auth()->id())
            ->exists();

        if (! $exists) {
            return;
        }

        $this->selectedPaymentId = $paymentId;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedPaymentId = null;
    }

    public function getPaymentsProperty()
    {
        return Payment::query()
            ->where('user_id', auth()->id())
            ->with(['product', 'payable'])
            ->when($this->statusFilter, fn (Builder $q) => $q->where('status', $this->statusFilter))
            ->when($this->productFilter, fn (Builder $q) => $q->whereHas('product', fn ($q2) => $q2->where('slug', $this->productFilter)))
            ->latest('created_at')
            ->paginate(10);
    }

    public function getSelectedPaymentProperty(): ?Payment
    {
        if (! $this->selectedPaymentId) {
            return null;
        }

        return Payment::with(['product', 'payable'])
            ->where('user_id', auth()->id())
            ->find($this->selectedPaymentId);
    }

    public function render()
    {
        return view('livewire.my-payments', [
            'payments' => $this->payments,
            'selectedPayment' => $this->selectedPayment,
        ])->layout('components.layouts.app', [
            'title' => __('Mis pagos').' - Editorial Standards Platform',
        ]);
    }
}
