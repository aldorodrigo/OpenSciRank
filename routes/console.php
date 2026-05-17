<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('seal:check-expiration')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3 #20: baja el flag is_featured de libros vencidos. Corre a las
// 04:00 (1h después del cron de sello) para evitar overlap y mantener cada
// trabajo identificable en el log de scheduler.
Schedule::command('books:check-featured')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3.6 #32: detecta admin_tasks vencidas y manda TaskOverdue al
// assignee (o super_admin si no hay asignado). Cooldown de 24h en cache
// para no re-notificar la misma task en corridas sucesivas.
Schedule::command('tasks:check-overdue')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3.6 #32: recordatorio 24h antes de cada sesión de consultoría
// agendada. Envía ConsultingReminder al editor + evaluador asignado y
// marca consulting_reminder_sent_at para no repetir.
Schedule::command('consulting:send-reminders')
    ->dailyAt('09:30')
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3.7 #44 — digest diario de conversaciones a las 9:00 hora local.
// REEMPLAZA el cron `messages:notify-pending` (deprecated): un único email/día
// con todos los hilos donde el user tiene mensajes sin leer. Excluye mensajes
// con link de pago (esos se notifican al instante). Corre cada hora para
// cubrir todas las zonas horarias de los users.
Schedule::command('messages:daily-digest')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3.7 #39: expira propuestas de consultoría no respondidas y
// devuelve la task a `pending` para que el evaluador proponga nuevas.
Schedule::command('consulting:expire-proposals')
    ->dailyAt('09:45')
    ->withoutOverlapping()
    ->onOneServer();

// Sprint 3.7 — regenera sitemap.xml diariamente a las 03:30. El archivo
// queda en public/sitemap.xml y Apache lo sirve directo sin pasar por PHP.
// La ruta /sitemap.xml tiene fallback on-demand por si el cron no corrió.
Schedule::command('sitemap:generate')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();

// Roadmap #64 — refresca h-index/citas/2yr_mean_citedness de journals certified
// vigentes vía OpenAlex (primario) + Crossref (contraste). Primer día del mes
// a las 03:15 UTC, después del cron de sello y antes del de featured.
Schedule::command('metrics:refresh-journals')
    ->monthlyOn(1, '03:15')
    ->withoutOverlapping()
    ->onOneServer();
