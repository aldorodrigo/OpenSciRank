<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de servicios otorgados sin cobro ("cortesía").
 *
 * Cuando el admin exonera el pago de un servicio (listado de libro, evaluación
 * institucional, canje, soporte comercial) igual queremos que quede un registro
 * en `payments`: así el recurso conserva su historial de pagos coherente, la
 * AdminTask resultante se crea por el mismo camino que un pago real
 * (`AdminTaskFactory::fromPayment`) y los reportes pueden distinguir cuánto se
 * exoneró sin inflar los ingresos.
 *
 * El pago se graba con `provider = 'courtesy'` y `amount = 0`; el precio de
 * lista exonerado queda en `metadata.list_price`.
 *
 * Es un registro interno: las pantallas del editor filtran estos pagos con
 * `Payment::notCourtesy()`.
 */
class CourtesyPayment
{
    /**
     * @param  Model  $payable  Book | Journal al que se le exonera el servicio.
     * @param  User  $beneficiary  Dueño del recurso (queda como user_id del pago).
     * @param  Product|null  $product  Producto cuyo precio se exonera.
     * @param  string  $reason  Motivo de la cortesía (obligatorio, va al metadata y a las notas).
     * @param  User|null  $grantedBy  Admin que autoriza. Por defecto, el usuario autenticado.
     * @param  array<string, mixed>  $extraMetadata  Datos adicionales de contexto.
     */
    public static function record(
        Model $payable,
        User $beneficiary,
        ?Product $product,
        string $reason,
        ?User $grantedBy = null,
        array $extraMetadata = [],
    ): Payment {
        $grantedBy ??= auth()->user();

        return Payment::create([
            'user_id' => $beneficiary->id,
            'product_id' => $product?->id,
            'provider' => Payment::PROVIDER_COURTESY,
            'transaction_id' => null,
            'amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
            'metadata' => array_merge([
                'courtesy' => true,
                'reason' => $reason,
                'granted_by_id' => $grantedBy?->id,
                'granted_by_name' => $grantedBy?->name,
                'list_price' => (float) ($product?->price ?? 0),
            ], $extraMetadata),
        ]);
    }
}
