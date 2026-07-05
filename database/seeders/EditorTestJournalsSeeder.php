<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeder de datos de prueba: 6 revistas reales (OJS iberoamericanas) en estado
 * `draft`, todas propiedad de un único usuario editor de prueba.
 *
 * Sirve para loguearse como editor y probar de punta a punta los flujos del
 * dashboard (envío, pago, evaluación) con datos realistas y logos reales.
 *
 * Los logos viven commiteados en database/seeders/logos/ y se copian al disco
 * `public` bajo journal-logos/ en cada corrida (idempotente).
 *
 * Editor de prueba: editor.prueba@editorialstandards.com / password
 */
class EditorTestJournalsSeeder extends Seeder
{
    public const EDITOR_EMAIL = 'editor.prueba@editorialstandards.com';

    public const EDITOR_PASSWORD = 'password';

    public function run(): void
    {
        // Guard de entorno (issue #56): estas son fixtures de prueba con un
        // password conocido. Nunca deben materializarse en producción, ni
        // siquiera si se invoca el seeder directamente con --class.
        if (app()->environment('production')) {
            $this->command?->warn('EditorTestJournalsSeeder omitido: no se siembran fixtures de prueba en producción.');

            return;
        }

        // 1. Editor de prueba: User común, SIN rol Spatie (un editor no tiene rol).
        $editor = User::firstOrCreate(
            ['email' => self::EDITOR_EMAIL],
            [
                'name' => 'Editor de Prueba',
                'password' => Hash::make(self::EDITOR_PASSWORD),
            ]
        );

        // 2. Datos de las 6 revistas. Campos no confirmados en la fuente => null
        //    (no inventamos ISSN/DOI/año que no consten en el sitio del journal).
        $journals = [
            [
                'title' => 'Ciencia Latina Revista Científica Multidisciplinar',
                'abbreviated_name' => 'Ciencia Latina',
                'logo_file' => 'ciencia-latina.jpg',
                'issn_print' => null,
                'issn_online' => null,
                'publisher' => 'Ciencia Latina Revista Científica Multidisciplinar',
                'publishing_institution' => 'Ciencia Latina',
                'url' => 'https://ciencialatina.org/',
                'country_code' => 'PY',
                'subject_areas' => ['sciences', 'social_sciences', 'medicine', 'education', 'humanities'],
                'publication_languages' => ['es'],
                'start_year' => null,
                'publication_frequency' => null,
                'peer_review_type' => 'double_blind',
                'assigns_doi' => null,
                'license_type' => null,
                'institutional_email' => null,
                'editor_name' => null,
                'charges_apc' => null,
                'apc_amount' => null,
            ],
            [
                'title' => 'Revista Veritas de Difusão Científica',
                'abbreviated_name' => 'Veritas',
                'logo_file' => 'veritas.png',
                'issn_print' => '2595-2021',
                'issn_online' => '2965-6052',
                'publisher' => 'Revista Veritas de Difusão Científica',
                'publishing_institution' => 'Revista Veritas',
                'url' => 'https://revistaveritas.org/index.php/veritas',
                'country_code' => 'BR',
                'subject_areas' => ['sciences', 'medicine', 'engineering', 'education', 'social_sciences'],
                'publication_languages' => ['pt', 'es', 'en'],
                'start_year' => null,
                'publication_frequency' => null,
                'peer_review_type' => 'double_blind',
                'assigns_doi' => null,
                'license_type' => null,
                'institutional_email' => null,
                'editor_name' => null,
                'charges_apc' => null,
                'apc_amount' => null,
            ],
            [
                'title' => 'Revista Científica Internacional Arandu UTIC',
                'abbreviated_name' => 'Arandu UTIC',
                'logo_file' => 'arandu-utic.png',
                'issn_print' => '2311-7559',
                'issn_online' => '2409-2401',
                'publisher' => 'Universidad Tecnológica Intercontinental (UTIC)',
                'publishing_institution' => 'Universidad Tecnológica Intercontinental (UTIC)',
                'url' => 'https://revista.utic.edu.py/revista.ojs/index.php/revistas',
                'country_code' => 'PY',
                'subject_areas' => ['education', 'social_sciences', 'sciences', 'engineering', 'law', 'economics'],
                'publication_languages' => ['es', 'en'],
                'start_year' => 2014,
                'publication_frequency' => 'biannual',
                'peer_review_type' => 'double_blind',
                'assigns_doi' => null,
                'license_type' => 'CC-BY',
                'institutional_email' => 'arandu.utic@gmail.com',
                'editor_name' => null,
                'charges_apc' => false,
                'apc_amount' => null,
            ],
            [
                'title' => 'Revista Científica de Salud y Desarrollo Humano (Vitalia)',
                'abbreviated_name' => 'Vitalia',
                'logo_file' => 'vitalia.png',
                'issn_print' => null,
                'issn_online' => '3005-2610',
                'publisher' => 'Centro Latinoamericano de Investigación y Ciencias (CLIC)',
                'publishing_institution' => 'Centro Latinoamericano de Investigación y Ciencias (CLIC)',
                'url' => 'https://revistavitalia.org/index.php/vitalia',
                'country_code' => 'PY',
                'subject_areas' => ['medicine', 'social_sciences', 'sciences'],
                'publication_languages' => ['es', 'pt', 'en'],
                'start_year' => 2020,
                'publication_frequency' => 'quarterly',
                'peer_review_type' => 'double_blind',
                'assigns_doi' => null,
                'license_type' => 'CC-BY',
                'institutional_email' => 'editor@revistavitalia.org',
                'editor_name' => 'Dra. Adriana La Fuente',
                'charges_apc' => true,
                'apc_amount' => 120,
            ],
            [
                'title' => 'Emergentes - Revista Científica',
                'abbreviated_name' => 'Emergentes',
                'logo_file' => 'emergentes.png',
                'issn_print' => null,
                'issn_online' => '2959-7692',
                'publisher' => 'INDEXA',
                'publishing_institution' => 'INDEXA',
                'url' => 'https://revistaemergentes.org/index.php/cts',
                'country_code' => 'US',
                'subject_areas' => ['social_sciences', 'humanities', 'medicine', 'engineering', 'education', 'economics', 'law'],
                'publication_languages' => ['es', 'en'],
                'start_year' => 2020,
                'publication_frequency' => 'biannual',
                'peer_review_type' => 'double_blind',
                'assigns_doi' => true,
                'license_type' => 'CC-BY',
                'institutional_email' => 'editor@revistaemergentes.org',
                'editor_name' => 'Mgtr. Rolando Junior Ortega Carrasco',
                'charges_apc' => true,
                'apc_amount' => 120,
            ],
            [
                'title' => 'Revista Ciencia y Reflexión',
                'abbreviated_name' => 'Ciencia y Reflexión',
                'logo_file' => 'ciencia-y-reflexion.png',
                'issn_print' => null,
                'issn_online' => '3045-5537',
                'publisher' => 'INDEXA EAS',
                'publishing_institution' => 'INDEXA EAS',
                'url' => 'https://cienciayreflexion.org/index.php/Revista',
                'country_code' => 'ES',
                'subject_areas' => ['sciences', 'social_sciences', 'education', 'humanities'],
                'publication_languages' => ['es', 'en', 'pt'],
                'start_year' => 2022,
                'publication_frequency' => 'quarterly',
                'peer_review_type' => 'double_blind',
                'assigns_doi' => true,
                'license_type' => 'CC-BY',
                'institutional_email' => 'contacto@cienciayreflexion.org',
                'editor_name' => null,
                'charges_apc' => null,
                'apc_amount' => null,
            ],
        ];

        // 3. Insertar/actualizar cada revista + copiar su logo a storage/public.
        Storage::disk('public')->makeDirectory('journal-logos');

        foreach ($journals as $data) {
            $title = $data['title'];
            $slug = Str::slug($title);
            $publisher = $data['publisher'];
            $publishingInstitution = $data['publishing_institution'];
            $abbreviated = $data['abbreviated_name'];

            // Copiar logo commiteado -> disco público. Si falta el archivo, logo=null.
            $logoPath = null;
            $logoFile = $data['logo_file'];
            $src = database_path("seeders/logos/{$logoFile}");
            if (File::exists($src)) {
                $dest = 'journal-logos/'.$slug.'.'.pathinfo($logoFile, PATHINFO_EXTENSION);
                File::copy($src, Storage::disk('public')->path($dest));
                $logoPath = $dest;
            } else {
                $this->command->warn("Logo no encontrado para «{$title}»: {$src}");
            }

            // Campos de solo-scalar que van directo a la fila.
            unset($data['title'], $data['abbreviated_name'], $data['logo_file'],
                $data['publisher'], $data['publishing_institution']);

            Journal::updateOrCreate(
                ['slug' => $slug],
                array_merge($data, [
                    'user_id' => $editor->id,
                    'primary_locale' => 'es',
                    'status' => 'draft',
                    'title' => ['es' => $title, 'en' => $title],
                    'abbreviated_name' => ['es' => $abbreviated, 'en' => $abbreviated],
                    'publishing_institution' => ['es' => $publishingInstitution, 'en' => $publishingInstitution],
                    'description' => [
                        'es' => "Revista científica de acceso abierto publicada por {$publisher}.",
                        'en' => "Open access scientific journal published by {$publisher}.",
                    ],
                    'is_open_access' => true,
                    'access_type' => 'full_oa',
                    'apc_currency' => 'USD',
                    'target_audience' => ['researchers', 'professors', 'students'],
                    'editorial_board_visible' => true,
                    'logo' => $logoPath,
                    'current_score' => null,
                    'evaluated_at' => null,
                    'oai_metadata_prefix' => 'oai_dc',
                ])
            );
        }

        $this->command->info('EditorTestJournalsSeeder: '.count($journals).' revistas draft creadas para el editor de prueba.');
        $this->command->info('Editor: '.self::EDITOR_EMAIL.' / '.self::EDITOR_PASSWORD);
    }
}
