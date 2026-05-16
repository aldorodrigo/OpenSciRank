<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * Sprint 3.7 #39 — descarga del archivo .ics para una consultoría agendada.
 *
 * Solo disponible cuando status=scheduled y scheduled_for no es null.
 * Autorizado para el editor dueño de la task o el evaluador asignado.
 */
class ConsultingIcsController extends Controller
{
    public function download(AdminTask $task): Response
    {
        /** @var User $user */
        $user = auth()->user();

        // Autorización: solo el editor o el evaluador asignado pueden descargar
        $isEditor   = $task->editor()?->id === $user->id;
        $isAssignee = $task->assignee?->id === $user->id;

        if (! $isEditor && ! $isAssignee) {
            abort(403);
        }

        if ($task->status !== AdminTask::STATUS_SCHEDULED || ! $task->scheduled_for) {
            abort(404, 'La sesión aún no está agendada.');
        }

        $content = $this->buildIcs($task);

        return response($content, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"consulting-{$task->id}.ics\"",
        ]);
    }

    private function buildIcs(AdminTask $task): string
    {
        $start  = $task->scheduled_for->utc();
        $end    = $start->copy()->addHour();
        $now    = now()->utc();

        $dtStamp  = $now->format('Ymd\THis\Z');
        $dtStart  = $start->format('Ymd\THis\Z');
        $dtEnd    = $end->format('Ymd\THis\Z');

        $editor      = $task->editor();
        $assignee    = $task->assignee;
        $productName = $task->payment?->product?->getTranslationWithFallback('name') ?? 'Consultoría Editorial';
        $title       = $task->renderedTitle();
        $summary     = $this->icsEscape("{$productName} — {$title}");
        $location    = $this->icsEscape($task->consulting_meet_url ?? 'Online');

        $descParts = [];
        if ($editor) {
            $descParts[] = "Editor: {$editor->name}";
        }
        if ($assignee) {
            $descParts[] = "Evaluador: {$assignee->name}";
        }
        if ($task->consulting_meet_url) {
            $descParts[] = "Enlace: {$task->consulting_meet_url}";
        }
        $description = $this->icsEscape(implode('\n', $descParts));

        $uid = "consulting-{$task->id}@editorialstandards.com";

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Editorial Standards Platform//Consulting//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$dtStamp}",
            "DTSTART:{$dtStart}",
            "DTEND:{$dtEnd}",
            "SUMMARY:{$summary}",
            "DESCRIPTION:{$description}",
            "LOCATION:{$location}",
            'BEGIN:VALARM',
            'TRIGGER:-PT1H',
            'ACTION:DISPLAY',
            'DESCRIPTION:Recordatorio de sesión de consultoría',
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }

    /** Escapa caracteres especiales según RFC 5545. */
    private function icsEscape(string $text): string
    {
        $text = str_replace(['\\', ';', ','], ['\\\\', '\;', '\,'], $text);
        // Los saltos de línea literales \n ya vienen escapados como \\n desde implode
        return $text;
    }
}
