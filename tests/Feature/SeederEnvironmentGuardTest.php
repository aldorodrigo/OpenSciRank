<?php

namespace Tests\Feature;

use Database\Seeders\EditorTestJournalsSeeder;
use Database\Seeders\UserGuideDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Issue #56: las fixtures de prueba/demo (editor con password conocido y datos
 * ficticios) NUNCA deben materializarse en producción.
 */
class SeederEnvironmentGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_test_seeder_no_crea_datos_en_produccion(): void
    {
        app()->detectEnvironment(fn () => 'production');

        (new EditorTestJournalsSeeder)->setContainer($this->app)->run();

        $this->assertDatabaseMissing('users', ['email' => EditorTestJournalsSeeder::EDITOR_EMAIL]);
        $this->assertDatabaseCount('journals', 0);
    }

    public function test_user_guide_demo_seeder_no_crea_datos_en_produccion(): void
    {
        app()->detectEnvironment(fn () => 'production');

        (new UserGuideDemoSeeder)->setContainer($this->app)->run();

        $this->assertDatabaseMissing('users', ['email' => UserGuideDemoSeeder::EDITOR_EMAIL]);
        $this->assertDatabaseMissing('users', ['email' => UserGuideDemoSeeder::EVALUATOR_EMAIL]);
    }

    public function test_editor_test_seeder_si_crea_el_editor_fuera_de_produccion(): void
    {
        Storage::fake('public');
        // Entorno por defecto en tests = testing → el guard deja pasar.

        $this->seed(EditorTestJournalsSeeder::class);

        $this->assertDatabaseHas('users', ['email' => EditorTestJournalsSeeder::EDITOR_EMAIL]);
    }

    public function test_ambos_seeders_comparten_el_mismo_email_de_editor(): void
    {
        // La fuente única de verdad evita el drift de la colisión del issue #56.
        $this->assertSame(
            EditorTestJournalsSeeder::EDITOR_EMAIL,
            UserGuideDemoSeeder::EDITOR_EMAIL,
        );
    }
}
