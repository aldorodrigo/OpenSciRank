<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // ============================================
            // SECCIÓN 1: Identificación Básica
            // ============================================
            $table->string('subtitle')->nullable();
            $table->string('book_type')->nullable(); // libro_cientifico, libro_academico, libro_tecnico, manual, capitulo_libro
            $table->string('primary_language')->nullable();
            $table->string('secondary_language')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('edition')->nullable();
            $table->string('isbn')->nullable();
            $table->string('doi')->nullable();
            $table->string('landing_url')->nullable();
            $table->string('cover_image')->nullable();

            // ============================================
            // SECCIÓN 3: Editorial y Publicación
            // ============================================
            $table->string('publisher_country')->nullable();
            $table->string('publisher_city')->nullable();
            $table->string('collection_series')->nullable();
            $table->string('sponsor_entity')->nullable();
            $table->date('exact_publication_date')->nullable();
            $table->integer('total_pages')->nullable();
            $table->string('format')->nullable(); // pdf, epub, print, hybrid

            // ============================================
            // SECCIÓN 4: Resumen y Contenido Académico
            // ============================================
            $table->text('abstract')->nullable();
            $table->json('keywords')->nullable();
            $table->json('knowledge_areas')->nullable();
            $table->string('main_discipline')->nullable();
            $table->string('secondary_discipline')->nullable();
            $table->string('academic_level')->nullable(); // pregrado, posgrado, investigacion
            $table->text('table_of_contents')->nullable();
            $table->string('table_of_contents_file')->nullable();

            // ============================================
            // SECCIÓN 5: Acceso Abierto y Derechos
            // ============================================
            $table->boolean('is_open_access')->nullable();
            $table->string('access_type')->nullable(); // immediate, embargo, closed
            $table->string('license_type')->nullable(); // cc_by, cc_by_sa, cc_by_nc, copyright
            $table->string('rights_holder')->nullable();
            $table->boolean('allows_reuse')->nullable();
            $table->boolean('allows_commercial_use')->nullable();

            // ============================================
            // SECCIÓN 6: Modelo de Negocio
            // ============================================
            $table->string('publication_model')->nullable(); // free, pay_download, pay_print, sponsored
            $table->decimal('access_cost', 10, 2)->nullable();
            $table->decimal('author_apc', 10, 2)->nullable();
            $table->json('funded_by')->nullable(); // university, project, author, other

            // ============================================
            // SECCIÓN 7: Calidad y Evaluación Editorial
            // ============================================
            $table->boolean('has_peer_review')->nullable();
            $table->string('review_type')->nullable(); // single_blind, double_blind
            $table->boolean('has_editorial_committee')->nullable();
            $table->boolean('has_editorial_standards')->nullable();
            $table->boolean('has_antiplagiarism')->nullable();
            $table->boolean('has_ethics_code')->nullable();

            // ============================================
            // SECCIÓN 8: Indexación y Visibilidad
            // ============================================
            $table->boolean('is_indexed')->nullable();
            $table->json('indexes')->nullable(); // google_books, google_scholar, doab, latindex, etc.
            $table->integer('citation_count')->nullable();
            $table->text('available_metrics')->nullable();

            // ============================================
            // SECCIÓN 9: Archivos y Recursos
            // ============================================
            $table->string('main_file')->nullable();
            $table->json('chapter_files')->nullable();
            $table->json('supplementary_files')->nullable();
            $table->string('download_url')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_checksum')->nullable();

            // ============================================
            // SECCIÓN 10: Estado Interno
            // ============================================
            $table->date('submission_date')->nullable();
            $table->date('approval_date')->nullable();
            $table->foreignId('responsible_editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['responsible_editor_id']);
            $table->dropColumn([
                // Section 1
                'subtitle', 'book_type', 'primary_language', 'secondary_language',
                'publication_year', 'edition', 'isbn', 'doi', 'landing_url', 'cover_image',
                // Section 3
                'publisher_country', 'publisher_city', 'collection_series', 'sponsor_entity',
                'exact_publication_date', 'total_pages', 'format',
                // Section 4
                'abstract', 'keywords', 'knowledge_areas', 'main_discipline',
                'secondary_discipline', 'academic_level', 'table_of_contents', 'table_of_contents_file',
                // Section 5
                'is_open_access', 'access_type', 'license_type', 'rights_holder',
                'allows_reuse', 'allows_commercial_use',
                // Section 6
                'publication_model', 'access_cost', 'author_apc', 'funded_by',
                // Section 7
                'has_peer_review', 'review_type', 'has_editorial_committee',
                'has_editorial_standards', 'has_antiplagiarism', 'has_ethics_code',
                // Section 8
                'is_indexed', 'indexes', 'citation_count', 'available_metrics',
                // Section 9
                'main_file', 'chapter_files', 'supplementary_files',
                'download_url', 'file_size', 'file_checksum',
                // Section 10
                'submission_date', 'approval_date', 'responsible_editor_id', 'internal_notes',
            ]);
        });
    }
};
