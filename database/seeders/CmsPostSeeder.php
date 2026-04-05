<?php

namespace Database\Seeders;

use App\Models\CmsPost;
use Illuminate\Database\Seeder;

class CmsPostSeeder extends Seeder
{
    public function run(): void
    {
        CmsPost::truncate();

        $posts = [
            [
                'user_id' => 1,
                'title' => 'Cómo preparar tu revista científica para la indexación',
                'slug' => 'como-preparar-tu-revista-para-la-indexacion',
                'category' => 'guias',
                'emoji' => '📋',
                'excerpt' => 'Una guía paso a paso para que tu revista cumpla con los criterios básicos antes de postular a la evaluación de Editorial Standards Platform.',
                'content' => '<h2>Introducción</h2>
<p>Preparar una revista científica para la indexación requiere atención a múltiples aspectos editoriales, técnicos y de contenido. En esta guía te explicamos los pasos fundamentales que debes seguir.</p>
<h2>1. Revisión de políticas editoriales</h2>
<p>Antes de postular, asegúrate de que tu revista cuenta con políticas claras de revisión por pares, ética editorial y acceso abierto. Estos son requisitos fundamentales que los evaluadores verificarán en primer lugar.</p>
<h2>2. Metadatos y estándares técnicos</h2>
<p>Tu revista debe implementar estándares como DOI, ISSN, y preferiblemente OAI-PMH para la interoperabilidad. La correcta estructuración de metadatos es clave para la visibilidad.</p>
<h2>3. Regularidad y contenido</h2>
<p>Mantener una periodicidad constante y publicar contenido de calidad revisado por pares es esencial. Se recomienda tener al menos dos años de publicación continua.</p>
<h2>Conclusión</h2>
<p>Seguir estos pasos te acercará significativamente a cumplir los criterios de evaluación. Recuerda que cada indicador tiene un peso específico en la calificación final.</p>',
                'read_time' => '8 min',
                'is_featured' => true,
                'published_at' => '2026-03-10 10:00:00',
            ],
            [
                'user_id' => 1,
                'title' => 'El estado de la Ciencia Abierta en América Latina en 2026',
                'slug' => 'estado-ciencia-abierta-america-latina-2026',
                'category' => 'ciencia-abierta',
                'emoji' => '🌍',
                'excerpt' => 'Un análisis del crecimiento del acceso abierto en la región, los desafíos pendientes y las políticas nacionales que impulsan el cambio.',
                'content' => '<h2>El panorama actual</h2>
<p>América Latina se ha consolidado como una de las regiones líderes en Ciencia Abierta a nivel mundial. Países como Brasil, México, Colombia y Argentina han implementado políticas nacionales que favorecen el acceso abierto a la producción científica.</p>
<h2>Crecimiento del acceso abierto</h2>
<p>Las estadísticas de 2026 muestran que más del 70% de las publicaciones científicas de la región están disponibles en acceso abierto, superando significativamente el promedio mundial.</p>
<h2>Desafíos pendientes</h2>
<p>A pesar de los avances, persisten desafíos como la sostenibilidad financiera de las revistas de acceso abierto, la necesidad de infraestructura tecnológica robusta y la profesionalización de los equipos editoriales.</p>
<h2>Perspectivas futuras</h2>
<p>Las iniciativas regionales como Redalyc, SciELO y LA Referencia continúan fortaleciendo el ecosistema de Ciencia Abierta, con nuevas herramientas y servicios para editores y investigadores.</p>',
                'read_time' => '12 min',
                'is_featured' => false,
                'published_at' => '2026-03-05 10:00:00',
            ],
            [
                'user_id' => 1,
                'title' => 'Los 10 criterios más importantes para obtener la certificación',
                'slug' => 'criterios-mas-importantes-certificacion',
                'category' => 'criterios',
                'emoji' => '🏆',
                'excerpt' => 'Aprende cuáles son los indicadores con mayor peso en nuestra metodología y cómo asegurarte de cumplirlos.',
                'content' => '<h2>¿Cómo funciona la evaluación?</h2>
<p>La evaluación de Editorial Standards Platform se basa en un conjunto de indicadores agrupados en categorías. Cada indicador tiene un peso específico y algunos son excluyentes: si no se cumplen, la revista no puede superar el 49% de puntuación.</p>
<h2>Indicadores excluyentes</h2>
<p>Los indicadores marcados como excluyentes son los más críticos. Incluyen aspectos como la revisión por pares, la transparencia editorial, la regularidad de publicación, la asignación de DOIs y el acceso abierto.</p>
<h2>Indicadores de mayor peso</h2>
<p>Además de los excluyentes, otros indicadores con alto peso incluyen la internacionalización del comité editorial, la indexación en bases de datos reconocidas y la implementación de estándares de metadatos.</p>
<h2>Recomendaciones</h2>
<p>Para maximizar tu puntuación, enfócate primero en cumplir todos los indicadores excluyentes y luego trabaja en los de mayor peso. Recuerda que necesitas un mínimo del 75% para obtener el Sello de Estándares Editoriales.</p>',
                'read_time' => '6 min',
                'is_featured' => false,
                'published_at' => '2026-02-28 10:00:00',
            ],
            [
                'user_id' => 1,
                'title' => 'De 45% a 82%: la historia de Revista de Biociencias',
                'slug' => 'caso-exito-revista-biociencias',
                'category' => 'casos-de-exito',
                'emoji' => '✅',
                'excerpt' => 'Entrevistamos a los editores de esta publicación que logró mejorar significativamente su puntuación en menos de un año implementando mejoras clave.',
                'content' => '<h2>El punto de partida</h2>
<p>Cuando la Revista de Biociencias se evaluó por primera vez en Editorial Standards Platform, obtuvo una puntuación del 45%. Los editores decidieron tomar esto como una oportunidad de mejora.</p>
<h2>Las mejoras implementadas</h2>
<p>El equipo editorial se enfocó en tres áreas clave: implementación de DOIs para todos los artículos, formalización del proceso de revisión por pares con formularios estructurados, y migración a un sistema OJS actualizado con soporte OAI-PMH.</p>
<h2>El resultado</h2>
<p>Tras 10 meses de trabajo, la revista se reevaluó y alcanzó un 82%, obteniendo el Sello de Estándares Editoriales. Los editores destacan que el proceso les ayudó a profesionalizar toda su operación editorial.</p>
<h2>Lecciones aprendidas</h2>
<p>"Lo más importante fue entender que la evaluación no es solo un sello, sino una hoja de ruta para la mejora continua", comenta el editor en jefe.</p>',
                'read_time' => '10 min',
                'is_featured' => false,
                'published_at' => '2026-02-20 10:00:00',
            ],
            [
                'user_id' => 1,
                'title' => 'Editorial Standards Platform incorpora evaluación de libros académicos',
                'slug' => 'nueva-evaluacion-libros-academicos',
                'category' => 'novedades',
                'emoji' => '🚀',
                'excerpt' => 'Anunciamos la expansión de nuestra plataforma para incluir monografías y libros científicos de acceso abierto.',
                'content' => '<h2>Una nueva etapa</h2>
<p>Nos complace anunciar que Editorial Standards Platform ahora ofrece un servicio de listado para libros académicos y monografías científicas de acceso abierto.</p>
<h2>¿Cómo funciona?</h2>
<p>Los editores y autores pueden enviar sus libros académicos a través de nuestro formulario de registro. El proceso incluye una revisión de los metadatos, la calidad editorial y la accesibilidad del contenido.</p>
<h2>Beneficios para los autores</h2>
<p>Los libros listados en nuestra plataforma ganan visibilidad en el ecosistema de publicación científica, con metadatos estandarizados y mayor descubrimiento por parte de investigadores y bibliotecas.</p>
<h2>Cómo participar</h2>
<p>El proceso de registro es sencillo y accesible. Visita nuestra sección de libros para conocer los requisitos y comenzar el proceso de envío.</p>',
                'read_time' => '4 min',
                'is_featured' => false,
                'published_at' => '2026-02-10 10:00:00',
            ],
            [
                'user_id' => 1,
                'title' => 'OAI-PMH: qué es y por qué tu revista debe implementarlo',
                'slug' => 'oai-pmh-que-es-por-que-implementarlo',
                'category' => 'indexacion',
                'emoji' => '🔗',
                'excerpt' => 'Explicamos el protocolo de interoperabilidad OAI-PMH, cómo funciona y sus beneficios para la visibilidad de tu publicación.',
                'content' => '<h2>¿Qué es OAI-PMH?</h2>
<p>El protocolo OAI-PMH (Open Archives Initiative Protocol for Metadata Harvesting) es un estándar que permite la recolección automatizada de metadatos de repositorios y revistas científicas.</p>
<h2>¿Cómo funciona?</h2>
<p>OAI-PMH funciona mediante un mecanismo de petición-respuesta. Los recolectores (harvesters) solicitan metadatos a los proveedores de datos mediante verbos estandarizados como ListRecords, GetRecord e Identify.</p>
<h2>Beneficios para tu revista</h2>
<p>Implementar OAI-PMH permite que tu revista sea descubierta y recolectada automáticamente por agregadores como BASE, CORE, LA Referencia y otros servicios de indexación. Esto aumenta significativamente la visibilidad de tus publicaciones.</p>
<h2>Cómo implementarlo</h2>
<p>La mayoría de los sistemas de gestión editorial como OJS incluyen soporte nativo para OAI-PMH. Solo necesitas activarlo y configurar los metadatos correctamente. En Editorial Standards Platform, verificamos automáticamente la implementación de OAI-PMH como parte del proceso de evaluación.</p>',
                'read_time' => '7 min',
                'is_featured' => false,
                'published_at' => '2026-01-28 10:00:00',
            ],
        ];

        foreach ($posts as $post) {
            CmsPost::create($post);
        }
    }
}
