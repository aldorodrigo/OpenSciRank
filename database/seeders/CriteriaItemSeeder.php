<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CriteriaItem;
use Illuminate\Database\Seeder;

class CriteriaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First ensure categories exist
        $this->call(EvaluationCategorySeeder::class);

        $categories = [
            [
                'name' => 'Identificación y normalización de la revista',
                'items' => [
                    ['code' => '1.1', 'name' => 'Título oficial de la revista claramente definido', 'is_core' => true, 'weight' => 3],
                    ['code' => '1.2', 'name' => 'ISSN válido (impreso y/o electrónico)', 'is_core' => true, 'weight' => 3],
                    ['code' => '1.3', 'name' => 'Año de inicio de publicación declarado', 'is_core' => true, 'weight' => 2],
                    ['code' => '1.4', 'name' => 'Periodicidad declarada', 'is_core' => true, 'weight' => 2],
                    ['code' => '1.5', 'name' => 'Idioma(s) de publicación especificados', 'is_core' => true, 'weight' => 2],
                    ['code' => '1.6', 'name' => 'Alcance y temática definidos', 'is_core' => true, 'weight' => 2],
                    ['code' => '1.7', 'name' => 'URL oficial activa y estable', 'is_core' => true, 'weight' => 3],
                    ['code' => '1.8', 'name' => 'Metadatos completos de la revista', 'is_core' => true, 'weight' => 2],
                ],
            ],
            [
                'name' => 'Editorial y gestión institucional',
                'items' => [
                    ['code' => '2.1', 'name' => 'Entidad editora identificada', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '2.2', 'name' => 'País de la entidad editora declarado', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '2.3', 'name' => 'Editorial con sitio web institucional', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '2.4', 'name' => 'Dirección física o institucional declarada', 'is_core' => false, 'weight' => 1, 'type' => 'advanced'],
                    ['code' => '2.5', 'name' => 'Responsables editoriales identificados', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '2.6', 'name' => 'Historial editorial público', 'is_core' => false, 'weight' => 1, 'type' => 'advanced'],
                    ['code' => '2.7', 'name' => 'Información de contacto verificable', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Equipo editorial y gobierno',
                'items' => [
                    ['code' => '3.1', 'name' => 'Editor/a jefe identificado', 'is_core' => true, 'weight' => 3],
                    ['code' => '3.2', 'name' => 'Comité editorial listado', 'is_core' => true, 'weight' => 3],
                    ['code' => '3.3', 'name' => 'Afiliaciones institucionales del comité', 'is_core' => true, 'weight' => 2],
                    ['code' => '3.4', 'name' => 'Diversidad geográfica del comité', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '3.5', 'name' => 'Roles editoriales definidos', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '3.6', 'name' => 'Comité científico o asesor identificado', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Alcance, enfoque y contenido',
                'items' => [
                    ['code' => '4.1', 'name' => 'Objetivos y alcance claramente definidos', 'is_core' => true, 'weight' => 3],
                    ['code' => '4.2', 'name' => 'Tipos de artículos aceptados declarados', 'is_core' => true, 'weight' => 2],
                    ['code' => '4.3', 'name' => 'Público objetivo identificado', 'is_core' => true, 'weight' => 2],
                    ['code' => '4.4', 'name' => 'Correspondencia entre alcance y artículos publicados', 'is_core' => true, 'weight' => 2],
                    ['code' => '4.5', 'name' => 'Instrucciones a los autores disponibles', 'is_core' => true, 'weight' => 3],
                    ['code' => '4.6', 'name' => 'Normas de citación especificadas', 'is_core' => true, 'weight' => 2],
                    ['code' => '4.7', 'name' => 'Idiomas de envío aceptados', 'is_core' => false, 'weight' => 1, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Periodicidad y continuidad',
                'items' => [
                    ['code' => '5.1', 'name' => 'Periodicidad cumplida según lo declarado', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '5.2', 'name' => 'Número mínimo de números publicados por año', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '5.3', 'name' => 'Historial de publicación continuo', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '5.4', 'name' => 'Puntualidad en la publicación de números', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '5.5', 'name' => 'Numeración correcta de volúmenes y números', 'is_core' => false, 'weight' => 1, 'type' => 'advanced'],
                    ['code' => '5.6', 'name' => 'Archivo histórico accesible', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Proceso de evaluación por pares',
                'items' => [
                    ['code' => '6.1', 'name' => 'Revisión por pares declarada', 'is_core' => true, 'weight' => 3],
                    ['code' => '6.2', 'name' => 'Tipo de revisión especificado (simple / doble ciego)', 'is_core' => true, 'weight' => 3],
                    ['code' => '6.3', 'name' => 'Procedimiento de evaluación documentado', 'is_core' => true, 'weight' => 2],
                    ['code' => '6.4', 'name' => 'Tiempos promedio de evaluación publicados', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '6.5', 'name' => 'Selección de revisores descrita', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '6.6', 'name' => 'Evaluadores externos a la entidad editora', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '6.7', 'name' => 'Política de revisión ética', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Ética editorial y buenas prácticas',
                'items' => [
                    ['code' => '7.1', 'name' => 'Código de ética editorial publicado', 'is_core' => true, 'weight' => 3],
                    ['code' => '7.2', 'name' => 'Política antiplagio explícita', 'is_core' => true, 'weight' => 3],
                    ['code' => '7.3', 'name' => 'Uso declarado de software antiplagio', 'is_core' => true, 'weight' => 2],
                    ['code' => '7.4', 'name' => 'Política de conflictos de interés', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '7.5', 'name' => 'Política de retractaciones y correcciones', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '7.6', 'name' => 'Adhesión a buenas prácticas (COPE, etc.)', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '7.7', 'name' => 'Declaración de malas prácticas editoriales', 'is_core' => false, 'weight' => 1, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Acceso abierto y derechos',
                'items' => [
                    ['code' => '8.1', 'name' => 'Política de acceso abierto clara', 'is_core' => true, 'weight' => 3],
                    ['code' => '8.2', 'name' => 'Tipo de acceso (abierto, embargo, cerrado)', 'is_core' => true, 'weight' => 2],
                    ['code' => '8.3', 'name' => 'Licencias de uso explícitas', 'is_core' => true, 'weight' => 2],
                    ['code' => '8.4', 'name' => 'Uso de licencias Creative Commons', 'is_core' => true, 'weight' => 2],
                    ['code' => '8.5', 'name' => 'Titular de derechos identificado', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '8.6', 'name' => 'Política de autoarchivo declarada', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '8.7', 'name' => 'Visibilidad de licencias en artículos', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Modelo de negocio y financiamiento',
                'items' => [
                    ['code' => '9.1', 'name' => 'Modelo de financiamiento declarado', 'is_core' => true, 'weight' => 3],
                    ['code' => '9.2', 'name' => 'Costos para autores claramente informados', 'is_core' => true, 'weight' => 3],
                    ['code' => '9.3', 'name' => 'Ausencia de cargos ocultos', 'is_core' => true, 'weight' => 2],
                    ['code' => '9.4', 'name' => 'Separación entre pago y evaluación', 'is_core' => true, 'weight' => 3],
                    ['code' => '9.5', 'name' => 'Fuente de financiamiento identificada', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '9.6', 'name' => 'Política de exención o becas (si aplica)', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Infraestructura técnica y visibilidad',
                'items' => [
                    ['code' => '10.1', 'name' => 'Plataforma editorial estable (OJS u otra)', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '10.2', 'name' => 'Identificadores persistentes (DOI por artículo)', 'is_core' => false, 'weight' => 3, 'type' => 'advanced'],
                    ['code' => '10.3', 'name' => 'Metadatos estructurados (OAI-PMH, Dublin Core)', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '10.4', 'name' => 'URL permanente por artículo', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '10.5', 'name' => 'Compatibilidad con indexadores', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                    ['code' => '10.6', 'name' => 'Preservación digital declarada (LOCKSS, PKP PN, etc.)', 'is_core' => false, 'weight' => 2, 'type' => 'advanced'],
                ],
            ],
            [
                'name' => 'Indexación y reconocimiento externo',
                'items' => [
                    ['code' => '11.1', 'name' => 'Indexada en Google Scholar', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '11.2', 'name' => 'Indexada en Latindex Catálogo', 'is_core' => false, 'weight' => 3, 'type' => 'excellence'],
                    ['code' => '11.3', 'name' => 'Indexada en DOAJ', 'is_core' => false, 'weight' => 3, 'type' => 'excellence'],
                    ['code' => '11.4', 'name' => 'Indexada en Redalyc / SciELO', 'is_core' => false, 'weight' => 3, 'type' => 'excellence'],
                    ['code' => '11.5', 'name' => 'Presencia en otros índices reconocidos', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '11.6', 'name' => 'Métricas visibles (citas, descargas)', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                ],
            ],
            [
                'name' => 'Transparencia y experiencia del usuario',
                'items' => [
                    ['code' => '12.1', 'name' => 'Sitio web navegable y accesible', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '12.2', 'name' => 'Información clara para lectores', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '12.3', 'name' => 'Información clara para autores', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '12.4', 'name' => 'Artículos accesibles sin barreras técnicas', 'is_core' => false, 'weight' => 2, 'type' => 'excellence'],
                    ['code' => '12.5', 'name' => 'Política de privacidad publicada', 'is_core' => false, 'weight' => 1, 'type' => 'excellence'],
                    ['code' => '12.6', 'name' => 'Aviso legal disponible', 'is_core' => false, 'weight' => 1, 'type' => 'excellence'],
                ],
            ],
            [
                'name' => 'Gestión editorial interna (sistema)',
                'items' => [
                    ['code' => '13.1', 'name' => 'Estados editoriales controlados', 'is_core' => false, 'weight' => 0],
                    ['code' => '13.2', 'name' => 'Historial de envíos y decisiones', 'is_core' => false, 'weight' => 0],
                    ['code' => '13.3', 'name' => 'Editor responsable asignado', 'is_core' => false, 'weight' => 0],
                    ['code' => '13.4', 'name' => 'Registro de fechas clave', 'is_core' => false, 'weight' => 0],
                    ['code' => '13.5', 'name' => 'Observaciones internas documentadas', 'is_core' => false, 'weight' => 0],
                    ['code' => '13.6', 'name' => 'Recomendación de uso', 'is_core' => false, 'weight' => 0],
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
                CriteriaItem::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'category_id' => $category->id,
                        'weight' => $item['weight'],
                        'is_core' => $item['is_core'],
                        'type' => $item['type'] ?? 'core',
                        'is_active' => true,
                        'order' => $order++,
                    ]
                );
            }
        }

        $this->command->info("✅ Created {$order} evaluation criteria items.");
    }
}
