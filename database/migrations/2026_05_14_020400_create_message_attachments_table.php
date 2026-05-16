<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #40 — adjuntos de mensajes.
 *
 * Storage: filesystem 'private' bajo `conversations/{conv_id}/{uuid}.{ext}`.
 * Servido via signed URL protegida por policy (sólo participantes del hilo).
 * Tamaño máximo: 10 MB por archivo. Tipos: PDF, DOCX, XLSX, imágenes, TXT, CSV.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();

            $table->string('original_name', 255);
            $table->string('stored_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');

            $table->foreignId('uploaded_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};
