<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Issue #75 — `submitted` deja de existir en el flujo de libros.
 *
 * Los libros nunca se evalúan: `submitted` era un estado prestado del flujo de
 * revistas que sólo escribía el camino pagado, mientras la cortesía dejaba el
 * libro en `pending_listing`. Un libro en `submitted` además no ofrecía ninguna
 * acción en el dashboard del editor y el tile lo contaba como "En Evaluación".
 *
 * Las filas que hayan quedado en ese estado son libros pagados esperando
 * revisión de listado, así que van a la cola. `submission_date` se completa si
 * faltaba, para que el admin vea desde cuándo esperan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')
            ->where('status', 'submitted')
            ->update([
                'status' => 'pending_listing',
                'submission_date' => DB::raw('COALESCE(submission_date, DATE(COALESCE(submitted_at, created_at)))'),
            ]);
    }

    /**
     * No se revierte: no hay forma de distinguir qué libros en `pending_listing`
     * venían de `submitted` y cuáles entraron por cortesía o por resubmisión.
     */
    public function down(): void
    {
        //
    }
};
