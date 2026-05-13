<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/**
 * Seeder de cupones de la plataforma.
 *
 * IMPORTANTE: Este seeder sólo registra el cupón en la base de datos LOCAL.
 * El cupón equivalente en Stripe debe crearse manualmente desde el Dashboard
 * de Stripe con el mismo ID (`RENEW_EARLY_10`), 10% off, sin fecha de
 * expiración (la app controla la ventana D-60..D-30). NO se crea desde
 * código para evitar duplicados accidentales en producción.
 */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'RENEW_EARLY_10'],
            [
                'type' => 'percent',
                'value' => 10.00,
                'expires_at' => null,
                'max_uses' => null,
                'used_count' => 0,
                'is_active' => true,
            ]
        );
    }
}
