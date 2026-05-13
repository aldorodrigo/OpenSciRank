<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Settings por defecto del sistema. Idempotente — re-correrlo no pisa
 * valores cambiados por el admin desde la UI (usa updateOrCreate con
 * defaults solo si la fila NO existe).
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── SLA (Sprint 3.6 #32) ────────────────────────────────────
            [
                'key' => 'sla_evaluation_business_days',
                'value' => '15',
                'type' => 'int',
                'group' => 'sla',
                'description' => 'Plazo estándar para completar una evaluación o re-evaluación, en días hábiles (L-V).',
            ],
            [
                'key' => 'sla_evaluation_express_business_days',
                'value' => '5',
                'type' => 'int',
                'group' => 'sla',
                'description' => 'Plazo para evaluación Express (+$50 uplift), en días hábiles (L-V).',
            ],
            [
                'key' => 'sla_consulting_calendar_days',
                'value' => '7',
                'type' => 'int',
                'group' => 'sla',
                'description' => 'Plazo para programar/dar la sesión de consultoría del Plan de Acción, en días calendario.',
            ],
            [
                'key' => 'sla_listing_calendar_days',
                'value' => '7',
                'type' => 'int',
                'group' => 'sla',
                'description' => 'Plazo para revisar el listado de un libro o revista, en días calendario.',
            ],
            [
                'key' => 'sla_orphan_calendar_days',
                'value' => '2',
                'type' => 'int',
                'group' => 'sla',
                'description' => 'Plazo para resolver un pago huérfano (payable soft-deleted o no encontrado), en días calendario.',
            ],
        ];

        foreach ($defaults as $row) {
            // firstOrCreate (no updateOrCreate): si el admin ya tocó el
            // valor desde la UI, no lo pisamos al re-correr el seeder.
            Setting::firstOrCreate(
                ['key' => $row['key']],
                $row
            );
        }
    }
}
