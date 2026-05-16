<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Sprint 3.7 #40 — adjunto de un mensaje.
 *
 * Vive en disk 'private' (storage/app/private). Sirve via signed URL
 * desde un controller protegido por policy (sólo participantes del hilo
 * que contiene el mensaje pueden descargar).
 */
class MessageAttachment extends Model
{
    public const MAX_BYTES = 10 * 1024 * 1024; // 10 MB

    public const ALLOWED_MIME_TYPES = [
        // Documentos
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        // Imágenes
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];

    protected $fillable = [
        'message_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function humanSize(): string
    {
        $kb = $this->size_bytes / 1024;
        if ($kb < 1024) return number_format($kb, 0).' KB';
        return number_format($kb / 1024, 1).' MB';
    }

    /**
     * Eliminación física del archivo cuando se elimina el registro.
     */
    protected static function booted(): void
    {
        static::deleted(function (MessageAttachment $attachment) {
            if (Storage::disk('local')->exists($attachment->stored_path)) {
                Storage::disk('local')->delete($attachment->stored_path);
            }
        });
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
