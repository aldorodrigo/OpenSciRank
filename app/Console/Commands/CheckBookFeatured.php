<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

/**
 * Sprint 3 #20: baja el flag is_featured de los libros cuyo featured_until
 * ya pasó. featured_until queda intacto para fines de auditoría (cuándo
 * venció, cuándo se renovó, etc.) — el cron sólo apaga la luz visible.
 *
 * Decisión deliberada: este comando vive separado de seal:check-expiration
 * porque opera sobre otra entidad y otra fecha. Compartir un solo cron sería
 * ahorrar 30s de cómputo a cambio de ensuciar responsabilidades.
 */
class CheckBookFeatured extends Command
{
    protected $signature = 'books:check-featured';

    protected $description = 'Clear the is_featured flag from books whose featured_until has passed.';

    public function handle(): int
    {
        $today = now()->toDateString();

        $cleared = Book::query()
            ->where('is_featured', true)
            ->where('featured_until', '<', $today)
            ->update(['is_featured' => false]);

        $this->info("Cleared featured flag from {$cleared} books.");

        return self::SUCCESS;
    }
}
