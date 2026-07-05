<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Shield: regenerar permisos a partir de Resources/Pages/Widgets actuales
        // y asignarlos todos al super_admin. Idempotente — re-correrlo no duplica.
        // Necesario tras migrate:fresh porque la tabla permissions queda vacía y
        // el rol super_admin se crea sin permisos en define_via_gate=false.
        // --ignore-existing-policies: sin este flag, shield:generate SOBRESCRIBE
        // las policies existentes con su template default en cada reseed, borrando
        // el scoping por asignación del rol evaluator (roadmap #35).
        $this->command->info('Generando permisos Filament Shield...');
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--ignore-existing-policies' => true]);
        Artisan::call('shield:super-admin', ['--user' => 1, '--panel' => 'admin']);
        $this->command->info('Shield: permisos generados y super_admin asignado.');

        // Datos de configuración/catálogo: se ejecutan en todos los entornos,
        // producción incluida (roles, criterios, productos, cupones, ajustes,
        // catálogo de revistas de referencia y contenidos del blog).
        $this->call([
            EvaluatorRoleSeeder::class,
            EvaluationCategorySeeder::class,
            CriteriaItemSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            SettingSeeder::class,
            JournalSeeder::class,
            CmsCategorySeeder::class,
            CmsPostSeeder::class,
        ]);

        // Fixtures de desarrollo/QA: NUNCA en producción (issue #56). Crean un
        // editor de prueba con password conocido y revistas ficticias. Se
        // ejecutan solo en local/testing; en prod el guard interno del propio
        // seeder es la segunda barrera si alguien lo invoca con --class.
        if (app()->environment('local', 'testing')) {
            $this->call([
                EditorTestJournalsSeeder::class,
            ]);
        } else {
            $this->command->warn(
                'Fixtures de prueba omitidas (entorno '.app()->environment().'): '
                .'EditorTestJournalsSeeder solo corre en local/testing.'
            );
        }
    }
}
