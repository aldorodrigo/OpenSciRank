<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Product::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $products = [
            [
                'slug' => 'journal-evaluation',
                'primary_locale' => 'es',
                'price' => 99.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Evaluación Editorial de Revista',
                    'en' => 'Journal Editorial Evaluation',
                    'pt' => 'Avaliação Editorial de Revista',
                ],
                'description' => [
                    'es' => 'Proceso formal de evaluación editorial basado en criterios técnicos transparentes. Incluye revisión completa de indicadores editoriales, informe técnico detallado y, si se alcanza el nivel mínimo de cumplimiento (≥75 puntos), la obtención del Editorial Standards Seal con vigencia de 1 año. Plazo estándar: 15 días hábiles.',
                    'en' => 'Formal editorial evaluation process based on transparent technical criteria. Includes a full review of editorial indicators, a detailed technical report and, if the minimum compliance level (≥75 points) is reached, the Editorial Standards Seal valid for 1 year. Standard turnaround: 15 business days.',
                    'pt' => 'Processo formal de avaliação editorial baseado em critérios técnicos transparentes. Inclui revisão completa dos indicadores editoriais, relatório técnico detalhado e, se atingido o nível mínimo de conformidade (≥75 pontos), obtenção do Editorial Standards Seal com validade de 1 ano. Prazo padrão: 15 dias úteis.',
                ],
            ],
            [
                'slug' => 'journal-reevaluation',
                'primary_locale' => 'es',
                'price' => 99.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Re-evaluación Editorial de Revista',
                    'en' => 'Journal Editorial Re-evaluation',
                    'pt' => 'Reavaliação Editorial de Revista',
                ],
                'description' => [
                    'es' => 'Nueva evaluación editorial para revistas que desean mejorar su puntuación o que no alcanzaron el sello en su evaluación anterior. Incluye revisión completa de todos los indicadores, nuevo informe técnico y posibilidad de obtener o mejorar el Editorial Standards Seal. Plazo estándar: 15 días hábiles.',
                    'en' => 'A new editorial evaluation for journals that wish to improve their score or did not achieve the seal in their previous review. Includes a full review of all indicators, a new technical report, and the chance to earn or improve the Editorial Standards Seal. Standard turnaround: 15 business days.',
                    'pt' => 'Nova avaliação editorial para revistas que desejam melhorar sua pontuação ou que não obtiveram o selo na avaliação anterior. Inclui revisão completa de todos os indicadores, novo relatório técnico e a possibilidade de obter ou melhorar o Editorial Standards Seal. Prazo padrão: 15 dias úteis.',
                ],
            ],
            [
                'slug' => 'seal-renewal-2y',
                'primary_locale' => 'es',
                'price' => 129.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Renovación del Sello Editorial — 2 Años',
                    'en' => 'Editorial Seal Renewal — 2 Years',
                    'pt' => 'Renovação do Selo Editorial — 2 Anos',
                ],
                'description' => [
                    'es' => 'Renovación del Editorial Standards Seal por 2 años adicionales. Incluye nueva evaluación editorial completa, informe técnico actualizado y extensión de la vigencia del sello por 24 meses. Ahorra respecto a dos renovaciones individuales.',
                    'en' => 'Renewal of the Editorial Standards Seal for 2 additional years. Includes a full new editorial evaluation, an updated technical report and a 24-month extension of the seal. Cheaper than two individual renewals.',
                    'pt' => 'Renovação do Editorial Standards Seal por mais 2 anos. Inclui nova avaliação editorial completa, relatório técnico atualizado e extensão da validade do selo por 24 meses. Economize em relação a duas renovações individuais.',
                ],
            ],
            [
                'slug' => 'book-listing',
                'primary_locale' => 'es',
                'price' => 49.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Listado de Libro Académico',
                    'en' => 'Academic Book Listing',
                    'pt' => 'Listagem de Livro Acadêmico',
                ],
                'description' => [
                    'es' => 'Inclusión de un libro académico o científico en el índice de la plataforma. Incluye ficha pública con metadatos completos (título, autores, editorial, ISBN, área temática), visibilidad en el buscador y presencia permanente en el directorio de publicaciones académicas.',
                    'en' => 'Listing of an academic or scientific book in the platform index. Includes a public record with complete metadata (title, authors, publisher, ISBN, subject area), search visibility and permanent presence in the directory of academic publications.',
                    'pt' => 'Inclusão de um livro acadêmico ou científico no índice da plataforma. Inclui ficha pública com metadados completos (título, autores, editora, ISBN, área temática), visibilidade na busca e presença permanente no diretório de publicações acadêmicas.',
                ],
            ],
            [
                'slug' => 'express-evaluation',
                'primary_locale' => 'es',
                'price' => 149.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Evaluación Editorial Urgente',
                    'en' => 'Express Editorial Evaluation',
                    'pt' => 'Avaliação Editorial Urgente',
                ],
                'description' => [
                    'es' => 'Complemento de evaluación acelerada. La revista recibe el resultado de su evaluación editorial en un plazo máximo de 5 días hábiles (en lugar de los 15 días estándar). Debe adquirirse junto con una evaluación o re-evaluación editorial.',
                    'en' => 'Express evaluation add-on. The journal receives its editorial evaluation result within a maximum of 5 business days (instead of the standard 15). Must be purchased together with an evaluation or re-evaluation.',
                    'pt' => 'Complemento de avaliação acelerada. A revista recebe o resultado da sua avaliação editorial em até 5 dias úteis (em vez dos 15 padrão). Deve ser adquirido junto com uma avaliação ou reavaliação editorial.',
                ],
            ],
            [
                'slug' => 'premium-report',
                'primary_locale' => 'es',
                'price' => 30.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Informe Técnico Detallado Premium',
                    'en' => 'Premium Detailed Technical Report',
                    'pt' => 'Relatório Técnico Detalhado Premium',
                ],
                'description' => [
                    'es' => 'Complemento que amplía el informe técnico estándar con recomendaciones específicas de mejora para cada criterio evaluado, ejemplos de buenas prácticas editoriales y un plan de acción priorizado para alcanzar o mejorar el Editorial Standards Seal.',
                    'en' => 'Add-on that extends the standard technical report with specific improvement recommendations for each evaluated criterion, examples of editorial best practices and a prioritised action plan to reach or improve the Editorial Standards Seal.',
                    'pt' => 'Complemento que amplia o relatório técnico padrão com recomendações específicas de melhoria para cada critério avaliado, exemplos de boas práticas editoriais e um plano de ação priorizado para alcançar ou melhorar o Editorial Standards Seal.',
                ],
            ],
            [
                'slug' => 'institutional-pack',
                'primary_locale' => 'es',
                'price' => 199.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Paquete Institucional — 3 Revistas',
                    'en' => 'Institutional Pack — 3 Journals',
                    'pt' => 'Pacote Institucional — 3 Revistas',
                ],
                'description' => [
                    'es' => 'Paquete de evaluación editorial para instituciones con múltiples revistas. Incluye evaluación completa de 3 revistas con informe técnico individual para cada una. Ahorro significativo respecto a evaluaciones individuales ($297 → $199).',
                    'en' => 'Editorial evaluation pack for institutions with multiple journals. Includes full evaluation of 3 journals with an individual technical report for each. Significant savings compared to individual evaluations ($297 → $199).',
                    'pt' => 'Pacote de avaliação editorial para instituições com múltiplas revistas. Inclui avaliação completa de 3 revistas com relatório técnico individual para cada uma. Economia significativa em relação a avaliações individuais ($297 → $199).',
                ],
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
