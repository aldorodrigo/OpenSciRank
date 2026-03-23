<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing products
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Product::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $products = [
            [
                'name' => 'Evaluación Editorial de Revista',
                'slug' => 'journal-evaluation',
                'description' => 'Proceso formal de evaluación editorial basado en criterios técnicos transparentes. Incluye revisión completa de indicadores editoriales, informe técnico detallado y, si se alcanza el nivel mínimo de cumplimiento (≥75 puntos), la obtención del Editorial Standards Seal con vigencia de 1 año. Plazo estándar: 15 días hábiles.',
                'price' => 99.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Re-evaluación Editorial de Revista',
                'slug' => 'journal-reevaluation',
                'description' => 'Nueva evaluación editorial para revistas que desean mejorar su puntuación o que no alcanzaron el sello en su evaluación anterior. Incluye revisión completa de todos los indicadores, nuevo informe técnico y posibilidad de obtener o mejorar el Editorial Standards Seal. Plazo estándar: 15 días hábiles.',
                'price' => 99.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Renovación del Sello Editorial — 2 Años',
                'slug' => 'seal-renewal-2y',
                'description' => 'Renovación del Editorial Standards Seal por 2 años adicionales. Incluye nueva evaluación editorial completa, informe técnico actualizado y extensión de la vigencia del sello por 24 meses. Ahorra respecto a dos renovaciones individuales.',
                'price' => 129.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Listado de Libro Académico',
                'slug' => 'book-listing',
                'description' => 'Inclusión de un libro académico o científico en el índice de la plataforma. Incluye ficha pública con metadatos completos (título, autores, editorial, ISBN, área temática), visibilidad en el buscador y presencia permanente en el directorio de publicaciones académicas.',
                'price' => 49.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Evaluación Editorial Urgente',
                'slug' => 'express-evaluation',
                'description' => 'Complemento de evaluación acelerada. La revista recibe el resultado de su evaluación editorial en un plazo máximo de 5 días hábiles (en lugar de los 15 días estándar). Debe adquirirse junto con una evaluación o re-evaluación editorial.',
                'price' => 149.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Informe Técnico Detallado Premium',
                'slug' => 'premium-report',
                'description' => 'Complemento que amplía el informe técnico estándar con recomendaciones específicas de mejora para cada criterio evaluado, ejemplos de buenas prácticas editoriales y un plan de acción priorizado para alcanzar o mejorar el Editorial Standards Seal.',
                'price' => 30.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'name' => 'Paquete Institucional — 3 Revistas',
                'slug' => 'institutional-pack',
                'description' => 'Paquete de evaluación editorial para instituciones con múltiples revistas. Incluye evaluación completa de 3 revistas con informe técnico individual para cada una. Ahorro significativo respecto a evaluaciones individuales ($297 → $199).',
                'price' => 199.00,
                'currency' => 'USD',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
