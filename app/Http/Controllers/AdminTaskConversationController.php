<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\Conversation;
use App\Notifications\NewConversationOpened;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Sprint 3.7 #40 — Crea o redirige al hilo de conversación de una AdminTask
 * desde la vista de Filament.
 */
class AdminTaskConversationController extends Controller
{
    public function open(AdminTask $task): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('super_admin'), 403);

        // Si ya existe, redirigir directo
        if ($task->conversation) {
            return redirect()->to('/admin/conversations/'.$task->conversation->id);
        }

        // Crear conversación anclada a la task
        $conversation = Conversation::create([
            'subject' => 'Tarea: '.$task->renderedTitle(),
            'subject_type' => AdminTask::class,
            'subject_id' => $task->id,
            'started_by_user_id' => auth()->id(),
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        // Agregar admin que abrió
        $conversation->addParticipant(auth()->user(), Conversation::ROLE_ADMIN);

        // Agregar asignado si lo hay y es distinto
        if ($task->assignee && $task->assignee->id !== auth()->id()) {
            $conversation->addParticipant($task->assignee, Conversation::ROLE_ADMIN);
        }

        // Agregar editor (dueño del recurso)
        $editor = $task->editor();
        if ($editor) {
            $conversation->addParticipant($editor, Conversation::ROLE_EDITOR);

            // Notificar al editor
            try {
                $editor->notify(new NewConversationOpened($conversation));
            } catch (\Throwable $e) {
                Log::warning('NewConversationOpened not sent (task open): '.$e->getMessage(), [
                    'task_id' => $task->id,
                    'conversation_id' => $conversation->id,
                ]);
            }
        }

        return redirect()->to('/admin/conversations/'.$conversation->id)
            ->with('thread_info', 'Conversación creada y el editor fue notificado.');
    }
}
