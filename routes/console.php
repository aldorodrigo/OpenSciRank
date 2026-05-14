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
