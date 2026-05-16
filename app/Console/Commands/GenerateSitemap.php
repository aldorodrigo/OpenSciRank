<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\CmsPost;
use App\Models\Journal;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Genera el sitemap.xml combinando:
 *  - Páginas estáticas públicas (home, pricing, methodology, blog, etc.)
 *  - Revistas certificadas/listadas (con slug)
 *  - Libros listed
 *  - Posts del blog publicados
 *
 * Se ejecuta diariamente a las 03:30 via schedule:work (ver routes/console.php).
 * También expuesto como endpoint `GET /sitemap.xml` que regenera on-demand si
 * el archivo no existe (defense in depth para entornos donde el cron no corre).
 *
 * Excluye intencionalmente:
 *  - Rutas privadas (/admin, /app, /api)
 *  - Rutas de evaluación, checkout y wizards
 *  - Journals/books en draft, rejected o requires_changes_*
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--path= : Ruta de output. Default: public/sitemap.xml}';

    protected $description = 'Genera sitemap.xml combinando páginas estáticas, journals, books y posts.';

    public function handle(): int
    {
        $path = $this->option('path') ?: public_path('sitemap.xml');
        $sitemap = Sitemap::create();

        $this->info('Generando sitemap.xml…');

        // ── Páginas estáticas (prioridad alta, frecuencia baja) ──
        $staticPages = [
            ['url' => '/',                  'priority' => 1.0,  'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            ['url' => '/search',            'priority' => 0.9,  'frequency' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => '/pricing',           'priority' => 0.9,  'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/methodology',       'priority' => 0.8,  'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/seal-renewal',      'priority' => 0.7,  'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/about',             'priority' => 0.6,  'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/contact',           'priority' => 0.6,  'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/blog',              'priority' => 0.8,  'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            ['url' => '/terms',             'priority' => 0.3,  'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/privacy',           'priority' => 0.3,  'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create($page['url'])
                    ->setPriority($page['priority'])
                    ->setChangeFrequency($page['frequency'])
            );
        }
        $this->info('  ✓ '.count($staticPages).' páginas estáticas');

        // ── Journals públicos (listed o certified) ──
        // Solo journals que pasaron el filtro mínimo (listed o con sello).
        // Draft, rejected, requires_changes_* NO van al sitemap.
        $journalCount = 0;
        Journal::query()
            ->whereIn('status', ['listed', 'certified', 'evaluated'])
            ->whereNotNull('slug')
            ->chunk(200, function ($journals) use ($sitemap, &$journalCount) {
                foreach ($journals as $journal) {
                    $priority = match ($journal->status) {
                        'certified' => 0.9, // sello activo = más relevante
                        'evaluated' => 0.7,
                        'listed' => 0.5,
                        default => 0.4,
                    };

                    $sitemap->add(
                        Url::create("/journal/{$journal->slug}")
                            ->setLastModificationDate($journal->updated_at ?? now())
                            ->setPriority($priority)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    );

                    // Página de articles cosechados (si tiene OAI configurado)
                    if ($journal->oai_base_url) {
                        $sitemap->add(
                            Url::create("/journal/{$journal->slug}/articles")
                                ->setLastModificationDate($journal->updated_at ?? now())
                                ->setPriority(0.4)
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        );
                    }

                    $journalCount++;
                }
            });
        $this->info("  ✓ {$journalCount} journals");

        // ── Books públicos ──
        $bookCount = 0;
        Book::query()
            ->where('status', 'listed')
            ->whereNotNull('slug')
            ->chunk(200, function ($books) use ($sitemap, &$bookCount) {
                foreach ($books as $book) {
                    $sitemap->add(
                        Url::create("/book/{$book->slug}")
                            ->setLastModificationDate($book->updated_at ?? now())
                            ->setPriority($book->is_featured ? 0.7 : 0.5)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                    $bookCount++;
                }
            });
        $this->info("  ✓ {$bookCount} books");

        // ── Posts del blog ──
        $postCount = 0;
        CmsPost::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('slug')
            ->chunk(200, function ($posts) use ($sitemap, &$postCount) {
                foreach ($posts as $post) {
                    $sitemap->add(
                        Url::create("/blog/{$post->slug}")
                            ->setLastModificationDate($post->updated_at ?? $post->published_at)
                            ->setPriority($post->is_featured ?? false ? 0.7 : 0.5)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                    $postCount++;
                }
            });
        $this->info("  ✓ {$postCount} posts del blog");

        // ── Escribir archivo ──
        $sitemap->writeToFile($path);

        $total = count($staticPages) + $journalCount + $bookCount + $postCount;
        $size = number_format(filesize($path) / 1024, 1);
        $this->newLine();
        $this->info("✅ Sitemap generado: {$path}");
        $this->info("   Total URLs: {$total} · Tamaño: {$size} KB");

        return Command::SUCCESS;
    }
}
