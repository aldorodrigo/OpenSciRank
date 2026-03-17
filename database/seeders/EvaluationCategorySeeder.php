<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class EvaluationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Identificación y normalización de la revista', 'weight' => 10, 'is_active' => true],
            ['name' => 'Editorial y gestión institucional', 'weight' => 8, 'is_active' => true],
            ['name' => 'Equipo editorial y gobierno', 'weight' => 10, 'is_active' => true],
            ['name' => 'Alcance, enfoque y contenido', 'weight' => 10, 'is_active' => true],
            ['name' => 'Periodicidad y continuidad', 'weight' => 6, 'is_active' => true],
            ['name' => 'Proceso de evaluación por pares', 'weight' => 10, 'is_active' => true],
            ['name' => 'Ética editorial y buenas prácticas', 'weight' => 10, 'is_active' => true],
            ['name' => 'Acceso abierto y derechos', 'weight' => 10, 'is_active' => true],
            ['name' => 'Modelo de negocio y financiamiento', 'weight' => 8, 'is_active' => true],
            ['name' => 'Infraestructura técnica y visibilidad', 'weight' => 6, 'is_active' => true],
            ['name' => 'Indexación y reconocimiento externo', 'weight' => 6, 'is_active' => true],
            ['name' => 'Transparencia y experiencia del usuario', 'weight' => 4, 'is_active' => true],
            ['name' => 'Gestión editorial interna (sistema)', 'weight' => 2, 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info("✅ Created " . count($categories) . " evaluation categories.");
    }
}
