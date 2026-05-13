<?php

namespace Database\Seeders;

use App\Models\User;
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
        $this->command->info('Generando permisos Filament Shield...');
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin']);
        Artisan::call('shield:super-admin', ['--user' => 1, '--panel' => 'admin']);
        $this->command->info('Shield: permisos generados y super_admin asignado.');

        $this->call([
            EvaluationCategorySeeder::class,
            CriteriaItemSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            SettingSeeder::class,
            JournalSeeder::class,
            CmsCategorySeeder::class,
            CmsPostSeeder::class,
        ]);
    }
}
