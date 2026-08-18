<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Issue #75 — observaciones del revisor de listado, visibles para el editor.
 *
 * `EditorDashboard::showObservations()` lee `evaluation_notes` tanto para
 * revistas como para libros, pero la columna sólo existía en `journals`: al
 * editor de un libro con correcciones pedidas el modal le decía siempre
 * "No observations recorded.". Usamos el mismo nombre que en `journals` para
 * que ese método siga sirviendo a los dos sin ramificar.
 *
 * Es distinto de `internal_notes`, que es una nota privada del admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->text('evaluation_notes')->nullable()->after('internal_notes');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn('evaluation_notes');
        });
    }
};
