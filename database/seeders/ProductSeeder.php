<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Migración de slug 2026-05-13 (roadmap #17): el viejo "premium-report"
        // pasa a "action-plan-consulting" con nuevo precio y propuesta de valor.
        // Renombramos in-place ANTES del updateOrCreate para no dejar registros
        // huérfanos cuando el seeder corre sobre una base ya poblada.
        DB::table('products')
            ->where('slug', 'premium-report')
            ->update(['slug' => 'action-plan-consulting']);

        $products = [
            // ────────────────────────────────────────────────────────────────
            // EVALUACIONES (Journals)
            // ────────────────────────────────────────────────────────────────
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
                // Ajuste 2026-05-13: re-evaluación implica el mismo trabajo de
                // admin que la inicial (revisar 18 criterios), por lo tanto
                // se igualan los precios. La diferencia que justifica un SKU
                // separado es el flujo: no se entra como revista nueva.
                'price' => 99.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Re-evaluación Editorial de Revista',
                    'en' => 'Journal Editorial Re-evaluation',
                    'pt' => 'Reavaliação Editorial de Revista',
                ],
                'description' => [
                    'es' => 'Nueva evaluación editorial para revistas con datos ya cargados que desean mejorar su puntuación o que no alcanzaron el sello en su evaluación anterior. Reutiliza la ficha existente, incluye revisión completa de todos los indicadores, nuevo informe técnico y posibilidad de obtener o mejorar el Editorial Standards Seal. Plazo estándar: 15 días hábiles.',
                    'en' => 'A new editorial evaluation for journals with data already on file that wish to improve their score or did not achieve the seal in their previous review. Reuses the existing record, includes a full review of all indicators, a new technical report and the chance to earn or improve the Editorial Standards Seal. Standard turnaround: 15 business days.',
                    'pt' => 'Nova avaliação editorial para revistas com dados já cadastrados que desejam melhorar sua pontuação ou que não obtiveram o selo na avaliação anterior. Reutiliza o cadastro existente, inclui revisão completa de todos os indicadores, novo relatório técnico e possibilidade de obter ou melhorar o Editorial Standards Seal. Prazo padrão: 15 dias úteis.',
                ],
            ],

            // ────────────────────────────────────────────────────────────────
            // RENOVACIONES DEL SELLO — escalera 1/2/3 años (roadmap #14)
            // ────────────────────────────────────────────────────────────────
            [
                'slug' => 'seal-renewal-1y',
                'primary_locale' => 'es',
                'price' => 89.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Renovación del Sello Editorial — 1 Año',
                    'en' => 'Editorial Seal Renewal — 1 Year',
                    'pt' => 'Renovação do Selo Editorial — 1 Ano',
                ],
                'description' => [
                    'es' => 'Renovación del Editorial Standards Seal por 1 año adicional. Incluye nueva evaluación editorial completa, informe técnico actualizado y extensión de la vigencia del sello por 12 meses.',
                    'en' => 'Renewal of the Editorial Standards Seal for 1 additional year. Includes a full new editorial evaluation, an updated technical report and a 12-month extension of the seal.',
                    'pt' => 'Renovação do Editorial Standards Seal por mais 1 ano. Inclui nova avaliação editorial completa, relatório técnico atualizado e extensão da validade do selo por 12 meses.',
                ],
            ],
            [
                'slug' => 'seal-renewal-2y',
                'primary_locale' => 'es',
                // Roadmap #14: sube $129 → $149 para mantener el ahorro relativo
                // (~16%) frente a la nueva opción de 1 año a $89.
                'price' => 149.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Renovación del Sello Editorial — 2 Años',
                    'en' => 'Editorial Seal Renewal — 2 Years',
                    'pt' => 'Renovação do Selo Editorial — 2 Anos',
                ],
                'description' => [
                    'es' => 'Renovación del Editorial Standards Seal por 2 años adicionales. Incluye nueva evaluación editorial completa, informe técnico actualizado y extensión de la vigencia del sello por 24 meses. Ahorrá ~16% frente a dos renovaciones anuales.',
                    'en' => 'Renewal of the Editorial Standards Seal for 2 additional years. Includes a full new editorial evaluation, an updated technical report and a 24-month extension of the seal. Save ~16% compared to two yearly renewals.',
                    'pt' => 'Renovação do Editorial Standards Seal por mais 2 anos. Inclui nova avaliação editorial completa, relatório técnico atualizado e extensão da validade do selo por 24 meses. Economize ~16% em relação a duas renovações anuais.',
                ],
            ],
            [
                'slug' => 'seal-renewal-3y',
                'primary_locale' => 'es',
                'price' => 199.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Renovación del Sello Editorial — 3 Años',
                    'en' => 'Editorial Seal Renewal — 3 Years',
                    'pt' => 'Renovação do Selo Editorial — 3 Anos',
                ],
                'description' => [
                    'es' => 'Renovación del Editorial Standards Seal por 3 años adicionales. Incluye nueva evaluación editorial completa, informe técnico actualizado y extensión de la vigencia del sello por 36 meses. Ahorrá ~25% frente a tres renovaciones anuales — el mejor valor.',
                    'en' => 'Renewal of the Editorial Standards Seal for 3 additional years. Includes a full new editorial evaluation, an updated technical report and a 36-month extension of the seal. Save ~25% compared to three yearly renewals — best value.',
                    'pt' => 'Renovação do Editorial Standards Seal por mais 3 anos. Inclui nova avaliação editorial completa, relatório técnico atualizado e extensão da validade do selo por 36 meses. Economize ~25% em relação a três renovações anuais — melhor valor.',
                ],
            ],

            // ────────────────────────────────────────────────────────────────
            // LIBROS
            // ────────────────────────────────────────────────────────────────
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
            // Sprint 3 #20: addon de "listing destacado" — posición preferente
            // en el directorio y badge "Destacado" durante 12 meses. Se puede
            // sumar al listing gratuito o renovar de forma independiente.
            [
                'slug' => 'book-listing-featured-1y',
                'primary_locale' => 'es',
                'price' => 29.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Listing Destacado 1 año',
                    'en' => 'Featured Listing 1 year',
                    'pt' => 'Listing em Destaque 1 ano',
                ],
                'description' => [
                    'es' => 'Tu libro con posición preferente en el directorio y badge "Destacado" por 12 meses.',
                    'en' => 'Your book with preferred position in the directory and a "Featured" badge for 12 months.',
                    'pt' => 'Seu livro com posição preferencial no diretório e selo "Em Destaque" por 12 meses.',
                ],
            ],

            // ────────────────────────────────────────────────────────────────
            // ADD-ONS / SERVICIOS COMPLEMENTARIOS
            // ────────────────────────────────────────────────────────────────
            // Roadmap #15: Express deja de ser SKU público — se aplica como
            // uplift +$50 en el checkout cuando el plan principal es evaluación
            // o re-evaluación. Mantenemos el registro inactivo por trazabilidad
            // histórica de pagos previos (FK product_id en payments).
            [
                'slug' => 'express-evaluation',
                'primary_locale' => 'es',
                'price' => 149.00,
                'currency' => 'USD',
                'is_active' => false,
                'name' => [
                    'es' => 'Evaluación Editorial Urgente (legado)',
                    'en' => 'Express Editorial Evaluation (legacy)',
                    'pt' => 'Avaliação Editorial Urgente (legado)',
                ],
                'description' => [
                    'es' => 'Producto legado. El servicio Express ahora se ofrece como complemento de +$50 directamente en el checkout de evaluación o re-evaluación.',
                    'en' => 'Legacy product. Express service is now offered as a +$50 add-on directly in the evaluation or re-evaluation checkout.',
                    'pt' => 'Produto legado. O serviço Express agora é oferecido como complemento de +$50 diretamente no checkout de avaliação ou reavaliação.',
                ],
            ],

            // Roadmap #17: reposicionamiento del antiguo "premium-report".
            // El slug viejo se renombra arriba (DB::table->update) antes del
            // updateOrCreate para preservar el FK en payments.product_id.
            [
                'slug' => 'action-plan-consulting',
                'primary_locale' => 'es',
                'price' => 215.00,
                'currency' => 'USD',
                'is_active' => true,
                'name' => [
                    'es' => 'Plan de Acción + Consultoría',
                    'en' => 'Action Plan + Consulting',
                    'pt' => 'Plano de Ação + Consultoria',
                ],
                'description' => [
                    'es' => 'Recomendaciones específicas por criterio evaluado, una sesión de consultoría de 30 minutos con el evaluador y un roadmap priorizado para alcanzar o mejorar el Editorial Standards Seal. Complementa (no reemplaza) las observaciones generales del informe estándar.',
                    'en' => 'Specific recommendations per evaluated criterion, a 30-minute consulting session with the evaluator and a prioritised roadmap to reach or improve the Editorial Standards Seal. Complements (does not replace) the general observations of the standard report.',
                    'pt' => 'Recomendações específicas por critério avaliado, uma sessão de consultoria de 30 minutos com o avaliador e um roadmap priorizado para alcançar ou melhorar o Editorial Standards Seal. Complementa (não substitui) as observações gerais do relatório padrão.',
                ],
            ],

            // Paquete institucional desactivado 2026-05-10, ver roadmap #22 — se conserva el diseño para una posible reactivación futura.
            [
                'slug' => 'institutional-pack',
                'primary_locale' => 'es',
                'price' => 199.00,
                'currency' => 'USD',
                'is_active' => false,
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
            $slug = $product['slug'];
            unset($product['slug']);
            Product::updateOrCreate(['slug' => $slug], $product);
        }
    }
}
