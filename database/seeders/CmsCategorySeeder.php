<?php

namespace Database\Seeders;

use App\Models\CmsCategory;
use Illuminate\Database\Seeder;

class CmsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'guias',
                'name' => ['es' => 'Guías', 'en' => 'Guides', 'pt' => 'Guias'],
                'color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400',
                'sort_order' => 10,
            ],
            [
                'slug' => 'ciencia-abierta',
                'name' => ['es' => 'Ciencia Abierta', 'en' => 'Open Science', 'pt' => 'Ciência Aberta'],
                'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                'sort_order' => 20,
            ],
            [
                'slug' => 'indexacion',
                'name' => ['es' => 'Indexación', 'en' => 'Indexing', 'pt' => 'Indexação'],
                'color' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400',
                'sort_order' => 30,
            ],
            [
                'slug' => 'criterios',
                'name' => ['es' => 'Criterios', 'en' => 'Criteria', 'pt' => 'Critérios'],
                'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                'sort_order' => 40,
            ],
            [
                'slug' => 'casos-de-exito',
                'name' => ['es' => 'Casos de Éxito', 'en' => 'Success Stories', 'pt' => 'Casos de Sucesso'],
                'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400',
                'sort_order' => 50,
            ],
            [
                'slug' => 'novedades',
                'name' => ['es' => 'Novedades', 'en' => 'News', 'pt' => 'Novidades'],
                'color' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-400',
                'sort_order' => 60,
            ],
        ];

        foreach ($categories as $cat) {
            CmsCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'color' => $cat['color'],
                    'primary_locale' => 'es',
                    'sort_order' => $cat['sort_order'],
                ]
            );
        }
    }
}
