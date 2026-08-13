<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Repara archivos públicos que quedaron en el disco privado.
 *
 * Contexto: los FileUpload de Filament no declaraban `->disk('public')`, así
 * que heredaban `config('filament.default_filesystem_disk')` = FILESYSTEM_DISK
 * = `local` (raíz `storage/app/private`). Las vistas, en cambio, generan la
 * URL `/storage/...` esperando el disco `public` (raíz `storage/app/public`,
 * expuesto por el symlink). Resultado: logos/portadas subidos desde el admin
 * daban 404 — el archivo existía, pero del otro lado del muro.
 *
 * Este comando mueve esos archivos al disco público y avisa si falta el
 * symlink. Es idempotente: nunca pisa un archivo ya existente en destino.
 */
class StorageRepairPublicFiles extends Command
{
    protected $signature = 'storage:repair-public-files {--dry-run : Sólo listar lo que se movería}';

    protected $description = 'Mueve al disco público los archivos subidos por error al disco privado (logos, portadas, blog, etc.)';

    /**
     * Directorios que SIEMPRE deben vivir en el disco público porque se
     * sirven por URL directa. El resto de `storage/app/private` (exports de
     * Filament, adjuntos de conversaciones, livewire-tmp) se queda donde está.
     */
    private const PUBLIC_DIRECTORIES = [
        'journal-logos',
        'book-covers',
        'book-toc',
        'books',
        'book-chapters',
        'book-supplementary',
        'blog',
        'documents',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $private = Storage::disk('local');
        $public = Storage::disk('public');

        $moved = 0;
        $skipped = 0;

        foreach (self::PUBLIC_DIRECTORIES as $directory) {
            if (! $private->exists($directory)) {
                continue;
            }

            foreach ($private->allFiles($directory) as $path) {
                if ($public->exists($path)) {
                    $this->warn("  ya existe en público, se omite: {$path}");
                    $skipped++;

                    continue;
                }

                $this->line(($dryRun ? '  [dry-run] ' : '  movido: ').$path);

                if (! $dryRun) {
                    $public->put($path, $private->get($path));
                    $private->delete($path);
                }

                $moved++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "Se moverían {$moved} archivo(s) ({$skipped} ya presentes en el disco público)."
            : "Movidos {$moved} archivo(s) ({$skipped} ya presentes en el disco público).");

        $link = public_path('storage');

        if (! File::exists($link)) {
            $this->error('Falta el symlink public/storage — corré: php artisan storage:link');

            return self::FAILURE;
        }

        if (is_link($link) && ! is_dir($link)) {
            $this->error('El symlink public/storage apunta a una ruta inexistente ('.readlink($link).') — borralo y corré: php artisan storage:link');

            return self::FAILURE;
        }

        $this->info('Symlink public/storage OK → '.(is_link($link) ? readlink($link) : $link));

        return self::SUCCESS;
    }
}
