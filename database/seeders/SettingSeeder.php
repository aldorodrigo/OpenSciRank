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

            // ── Documentos: certificado + informe (#65) ─────────────────
            // Membrete institucional.
            [
                'key' => 'institution_name',
                'value' => 'Editorial Standards Platform',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Nombre de la institución que aparece en el membrete de certificado e informe.',
            ],
            [
                'key' => 'institution_address',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Dirección postal de la institución (membrete).',
            ],
            [
                'key' => 'institution_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Teléfono de contacto (membrete).',
            ],
            [
                'key' => 'institution_email',
                'value' => 'contacto@editorialstandards.com',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Correo de contacto (membrete).',
            ],
            [
                'key' => 'institution_website',
                'value' => 'https://editorialstandards.com',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Sitio web institucional (membrete).',
            ],

            // Logos (path relativo en disco public). Vacíos hasta que el admin los cargue.
            [
                'key' => 'logo_primary',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Logo principal del membrete (path en disco public).',
            ],
            [
                'key' => 'logo_secondary_1',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Logo secundario / acreditadora 1 (path en disco public).',
            ],
            [
                'key' => 'logo_secondary_2',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Logo secundario / acreditadora 2 (path en disco public).',
            ],

            // Firmantes: nombre + cargo + imagen de firma.
            [
                'key' => 'signatory_1_name',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Nombre del firmante 1.',
            ],
            [
                'key' => 'signatory_1_title',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Cargo del firmante 1.',
            ],
            [
                'key' => 'signatory_1_signature',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Imagen de firma del firmante 1 (path en disco public).',
            ],
            [
                'key' => 'signatory_2_name',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Nombre del firmante 2.',
            ],
            [
                'key' => 'signatory_2_title',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Cargo del firmante 2.',
            ],
            [
                'key' => 'signatory_2_signature',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Imagen de firma del firmante 2 (path en disco public).',
            ],
            [
                'key' => 'signatory_3_name',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Nombre del firmante 3.',
            ],
            [
                'key' => 'signatory_3_title',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Cargo del firmante 3.',
            ],
            [
                'key' => 'signatory_3_signature',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Imagen de firma del firmante 3 (path en disco public).',
            ],

            // Textos.
            [
                'key' => 'report_intro',
                'value' => \App\Support\DocumentBranding::DEFAULT_REPORT_INTRO,
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Párrafo introductorio del informe de evaluación (editable).',
            ],
            [
                'key' => 'certificate_description',
                'value' => \App\Support\DocumentBranding::DEFAULT_CERTIFICATE_DESCRIPTION,
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Descripción corta del certificado que resalta a la revista. Admite el token {journal}.',
            ],
            [
                'key' => 'certificate_footer_note',
                'value' => '',
                'type' => 'string',
                'group' => 'documents',
                'description' => 'Leyenda / nota legal opcional al pie del certificado.',
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
