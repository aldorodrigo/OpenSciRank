<?php

namespace App\Observers;

use App\Models\AdminTask;
use App\Models\Payment;

/**
 * Sprint 3.6 #32 sub-tarea 10: cuando un Payment pasa a status `refunded`,
 * cancelar automáticamente todas las admin_tasks abiertas asociadas.
 *
 * Hook listo para que Sprint 4 #6 (reembolsos vía Stripe) lo dispare al
 * cambiar el status. Mientras tanto cualquier cambio manual del status
 * en Filament/tinker también propaga.
 */
class PaymentObserver
{
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        if ($payment->status !== 'refunded') {
            return;
        }

        $cancelled = AdminTask::cancelByPayment(
            $payment->id,
            __('admin_tasks.cancelled_by_refund', ['tx' => $payment->transaction_id ?? '—'])
        );

        if ($cancelled > 0) {
            activity()
                ->performedOn($payment)
                ->causedBy(auth()->user())
                ->withProperties(['admin_tasks_cancelled' => $cancelled])
                ->log("Tareas asociadas al pago canceladas automáticamente por reembolso ({$cancelled})");
        }
    }
}
