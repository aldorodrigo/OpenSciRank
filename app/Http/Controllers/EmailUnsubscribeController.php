<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

/**
 * Baja global de correos de recordatorio/marketing (Fase 5 auditoría). La ruta
 * es firmada (`signed`), así que no requiere sesión: el editor puede darse de
 * baja desde el pie de cualquier correo. Acepta GET (click) y POST (one-click
 * RFC 8058 desde Gmail/Yahoo).
 */
class EmailUnsubscribeController extends Controller
{
    public function __invoke(Request $request, User $user): Response
    {
        if (! $user->email_reminders_opted_out) {
            $user->forceFill(['email_reminders_opted_out' => true])->save();
        }

        // One-click (POST): el cliente de correo solo espera un 2xx.
        if ($request->isMethod('post')) {
            return response()->noContent();
        }

        App::setLocale($user->preferredLocale());

        return response()->view('emails.unsubscribed', ['user' => $user]);
    }
}
