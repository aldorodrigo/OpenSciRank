<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CriteriaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CriteriaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First ensure categories exist
        $this->call(EvaluationCategorySeeder::class);

        // Clear existing criteria to avoid mix with old 13-category system
        CriteriaItem::query()->delete();

        $categories = [
            [
                'name' => 'Identidad Editorial',
                'items' => [
                    ['code' => '1.1', 'name' => 'ISSN visible y válido', 'is_core' => true, 'weight' => 5],
                    ['code' => '1.2', 'name' => 'Institución editora claramente identificada', 'is_core' => true, 'weight' => 5],
                    ['code' => '1.3', 'name' => 'Comité editorial visible', 'is_core' => true, 'weight' => 5],
                    ['code' => '1.4', 'name' => 'Información de contacto institucional visible', 'is_core' => false, 'weight' => 5],
                ],
            ],
            [
                'name' => 'Transparencia del Proceso Editorial',
                'items' => [
                    ['code' => '2.1', 'name' => 'Política de revisión por pares visible', 'is_core' => true, 'weight' => 9],
                    ['code' => '2.2', 'name' => 'Normas para autores claras', 'is_core' => true, 'weight' => 8],
                    ['code' => '2.3', 'name' => 'Proceso editorial claramente descrito', 'is_core' => false, 'weight' => 8],
                ],
            ],
            [
                'name' => 'Ética Editorial',
                'items' => [
                    ['code' => '3.1', 'name' => 'Política de ética editorial', 'is_core' => true, 'weight' => 5],
                    ['code' => '3.2', 'name' => 'Política antiplagio', 'is_core' => true, 'weight' => 5],
                    ['code' => '3.3', 'name' => 'Política de retractación o corrección', 'is_core' => false, 'weight' => 5],
                    ['code' => '3.4', 'name' => 'Declaración de conflictos de interés', 'is_core' => false, 'weight' => 5],
                ],
            ],
            [
                'name' => 'Acceso y Derechos',
                'items' => [
                    ['code' => '4.1', 'name' => 'Modelo de acceso claramente informado', 'is_core' => true, 'weight' => 5],
                    ['code' => '4.2', 'name' => 'Licencia o condiciones de uso visibles', 'is_core' => true, 'weight' => 5],
                    ['code' => '4.3', 'name' => 'Derechos de autor claramente informados', 'is_core' => false, 'weight' => 5],
                ],
            ],
            [
                'name' => 'Infraestructura Técnica',
                'items' => [
                    ['code' => '5.1', 'name' => 'Sitio web funcional', 'is_core' => true, 'weight' => 7],
                    ['code' => '5.2', 'name' => 'Archivo de números o publicaciones accesible', 'is_core' => false, 'weight' => 7],
                    ['code' => '5.3', 'name' => 'Identificadores persistentes (DOI, ORCID)', 'is_core' => false, 'weight' => 6],
                ],
            ],
        ];

        $order = 1;
        foreach ($categories as $categoryData) {
            $category = Category::where('name', $categoryData['name'])->first();
            
            if (!$category) {
                $this->command->warn("Category not found: {$categoryData['name']}");
                continue;
            }

            foreach ($categoryData['items'] as $item) {
                CriteriaItem::create([
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'category_id' => $category->id,
                    'weight' => $item['weight'],
                    'is_core' => $item['is_core'],
                    'type' => $item['is_core'] ? 'core' : 'advanced',
                    'is_active' => true,
                    'order' => $order++,
                ]);
            }
        }

        $this->command->info("✅ Created {$order} evaluation criteria items for the master model.");
    }
}
