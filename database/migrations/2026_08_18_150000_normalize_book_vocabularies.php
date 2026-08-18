<?php

use App\Support\BookVocabulary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Issue #71 — normaliza los libros existentes al vocabulario canónico.
 *
 * Hasta acá convivían dos juegos de valores: el que grababa el wizard público y
 * el que ofrecía el admin. Las filas viejas quedaron con los valores del admin
 * (`libro_academico`, `posgrado`, `free`, `immediate`, `pdf`) y las nuevas con
 * los del wizard. El mapa de equivalencias vive en `BookVocabulary::LEGACY`.
 *
 * No es reversible: una vez normalizado no se sabe si un `monograph` venía de
 * `libro_cientifico` o de `libro_academico`. `down()` es un no-op deliberado.
 */
return new class extends Migration
{
    public function up(): void
    {
        $single = BookVocabulary::SINGLE_VALUE_COLUMNS;
        $multi = BookVocabulary::MULTI_VALUE_COLUMNS;

        DB::table('books')
            ->select(array_merge(['id'], array_keys($single), array_keys($multi)))
            ->orderBy('id')
            ->chunkById(200, function ($books) use ($single, $multi): void {
                foreach ($books as $book) {
                    $changes = [];

                    foreach ($single as $column => $vocabulary) {
                        $current = $book->{$column} ?? null;
                        $normalized = BookVocabulary::normalize($vocabulary, $current);

                        if ($normalized !== $current) {
                            $changes[$column] = $normalized;
                        }
                    }

                    foreach ($multi as $column => $vocabulary) {
                        $current = $book->{$column} ?? null;

                        if ($current === null || $current === '') {
                            continue;
                        }

                        $decoded = json_decode($current, true);

                        if (! is_array($decoded) || $decoded === []) {
                            continue;
                        }

                        $normalized = BookVocabulary::normalizeMany($vocabulary, $decoded);

                        if ($normalized !== $decoded) {
                            $changes[$column] = json_encode($normalized, JSON_UNESCAPED_UNICODE);
                        }
                    }

                    if ($changes !== []) {
                        DB::table('books')->where('id', $book->id)->update($changes);
                    }
                }
            });

        // Los roles de autor tenían el mismo problema, con menos variantes.
        DB::table('book_authors')
            ->select(['id', 'role'])
            ->orderBy('id')
            ->chunkById(500, function ($authors): void {
                foreach ($authors as $author) {
                    $normalized = BookVocabulary::normalize('author_role', $author->role);

                    if ($normalized !== $author->role) {
                        DB::table('book_authors')->where('id', $author->id)->update(['role' => $normalized]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible: el mapa legacy → canónico no es biyectivo.
    }
};
