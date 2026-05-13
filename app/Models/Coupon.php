<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cupón de descuento aplicable en el checkout.
 *
 * Casos de uso actuales:
 *  - `RENEW_EARLY_10`: 10% off automático cuando el editor entra a renovar
 *    su sello en la ventana D-60..D-30. Sólo se aplica a productos cuyo slug
 *    empieza con `seal-renewal-`. (Sprint 3 #13)
 *
 * Importante: este modelo es la fuente de verdad LOCAL. El cupón en Stripe
 * debe crearse manualmente desde el Dashboard de Stripe con el mismo ID que
 * aparece en `code` (= `stripe_id` por convención). Nunca creamos cupones
 * en Stripe desde código para evitar duplicados accidentales en producción.
 */
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'expires_at',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'used_count' => 'integer',
    ];

    /**
     * Stripe usa el mismo identificador que el `code` local. Mantenemos un
     * accessor explícito para que el resto del código no asuma que ambos
     * campos son siempre iguales.
     */
    public function getStripeIdAttribute(): string
    {
        return $this->code;
    }

    /**
     * Indica si el cupón está disponible para usarse (activo y con cupos).
     */
    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
