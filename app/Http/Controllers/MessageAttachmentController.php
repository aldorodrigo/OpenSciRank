<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sprint 3.7 #40 — descarga autorizada de adjuntos de mensajes.
 *
 * Sólo los participantes del hilo pueden descargar. La ruta está firmada
 * para evitar acceso directo sin autenticación.
 */
class MessageAttachmentController extends Controller
{
    public function download(Request $request, MessageAttachment $attachment): StreamedResponse
    {
        $user = auth()->user();
        $conversation = $attachment->message->conversation;

        // Verificar que el user es participante del hilo
        $isParticipant = $conversation->participants()
            ->where('user_id', $user->id)
            ->exists();

        // Super_admin puede ver todo
        $isSuperAdmin = $user->hasRole('super_admin');

        abort_unless($isParticipant || $isSuperAdmin, 403, 'No tenés permiso para descargar este archivo.');

        abort_unless(
            Storage::disk('local')->exists($attachment->stored_path),
            404,
            'Archivo no encontrado.'
        );

        return Storage::disk('local')->download(
            $attachment->stored_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'attachment; filename="'.$attachment->original_name.'"',
            ]
        );
    }
}
