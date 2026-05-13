<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Notifications\SealExpired;
use App\Notifications\SealExpiringEarlyReminder;
use App\Notifications\SealExpiringLastWeek;
use App\Notifications\SealExpiringSoon;
use App\Notifications\SealExpiringUrgent;
use App\Notifications\SealRecoveryOffer;
use Illuminate\Console\Command;

class CheckSealExpiration extends Command
{
    protected $signature = 'seal:check-expiration';

    protected $description = 'Check for expiring and expired Editorial Standards Seals';

    /**
     * Días mínimos entre notificaciones automáticas al mismo editor para
     * un mismo journal. Evita el "email diario" si el cron corre a diario.
     *
     * El cooldown se resetea (seal_notified_at = null) en tres momentos:
     *  1. Al cruzar el threshold active → expiring_soon (≤30 días)
     *  2. Al cruzar el threshold expiring_soon → expired (≤0 días)
     *  3. Al cruzar el sub-threshold D-7 (≤7 días), para garantizar que
     *     SealExpiringLastWeek siempre se envíe aunque SealExpiringUrgent
     *     se haya enviado sólo 8 días antes (el cooldown de 7d lo bloquearía
     *     sin este reset adicional). Decisión Sprint 3 #12: reset en D-7
     *     en lugar de bajar el cooldown global.
     */
    private const RENOTIFY_COOLDOWN_DAYS = 7;

    public function handle(): int
    {
        $this->info('Checking seal expirations...');

        // Una sola pasada: journals con sello en ventana D-60 a D+30
        $journals = Journal::whereIn('seal_status', ['active', 'expiring_soon', 'expired'])
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '>=', now()->subDays(20))
            ->where('seal_expires_at', '<=', now()->addDays(61))
            ->with('user')
            ->get();

        $notified = 0;

        foreach ($journals as $journal) {
            $daysToExpiry = (int) now()->diffInDays($journal->seal_expires_at, false);

            // ── Transiciones de estado ──────────────────────────────────────
            // active → expiring_soon cuando ≤30 días: reset para garantizar aviso
            if ($journal->seal_status === 'active' && $daysToExpiry <= 30 && $daysToExpiry >= 0) {
                $journal->update([
                    'seal_status' => 'expiring_soon',
                    'seal_notified_at' => null,
                ]);
                $journal->refresh();
            }

            // active|expiring_soon → expired cuando <0 días: reset para garantizar aviso
            if (in_array($journal->seal_status, ['active', 'expiring_soon']) && $daysToExpiry < 0) {
                $journal->update([
                    'seal_status' => 'expired',
                    'status' => 'evaluated',
                    'seal_notified_at' => null,
                ]);
                $journal->refresh();

                activity()
                    ->performedOn($journal)
                    ->withProperties(['seal_expires_at' => $journal->seal_expires_at?->toDateString()])
                    ->log('Sello vencido: estado revertido a evaluada (automático)');
            }

            // Sub-threshold reset: al cruzar D-7 reseteamos para garantizar
            // SealExpiringLastWeek aunque SealExpiringUrgent se envió hace <7d.
            if ($journal->seal_status === 'expiring_soon'
                && $daysToExpiry >= 0
                && $daysToExpiry <= 7
                && $journal->seal_notified_at !== null
                && ! $this->shouldNotify($journal)
            ) {
                $journal->update(['seal_notified_at' => null]);
                $journal->refresh();
            }

            // ── Selección de notificación según umbral ────────────────────
            if (! $journal->user || ! $this->shouldNotify($journal)) {
                continue;
            }

            if ($daysToExpiry >= 30 && $daysToExpiry < 60) {
                // D-60 a D-30 (exclusive): aviso temprano, sello aún active
                $journal->user->notify(new SealExpiringEarlyReminder($journal, $daysToExpiry));
                $label = "Early reminder ({$daysToExpiry}d): {$journal->title}";
            } elseif ($daysToExpiry >= 15 && $daysToExpiry < 30) {
                // D-30 a D-15: urgencia suave (ya en expiring_soon)
                $journal->user->notify(new SealExpiringSoon($journal));
                $label = "Expiring soon ({$daysToExpiry}d): {$journal->title}";

                activity()
                    ->performedOn($journal)
                    ->withProperties(['seal_expires_at' => $journal->seal_expires_at?->toDateString()])
                    ->log('Sello marcado como próximo a vencer (automático)');
            } elseif ($daysToExpiry >= 7 && $daysToExpiry < 15) {
                // D-15 a D-7: urgencia
                $journal->user->notify(new SealExpiringUrgent($journal, $daysToExpiry));
                $label = "Urgent ({$daysToExpiry}d): {$journal->title}";
            } elseif ($daysToExpiry >= 0 && $daysToExpiry < 7) {
                // D-7 a D-0: última semana
                $journal->user->notify(new SealExpiringLastWeek($journal, $daysToExpiry));
                $label = "Last week ({$daysToExpiry}d): {$journal->title}";
            } elseif ($daysToExpiry >= -7 && $daysToExpiry < 0) {
                // D+0 a D+7: expiración reciente → SealExpired
                $journal->user->notify(new SealExpired($journal));
                $label = "Expired ({$daysToExpiry}d): {$journal->title}";
            } elseif ($daysToExpiry >= -20 && $daysToExpiry < -7) {
                // D+7 a D+20: oferta de recuperación
                $journal->user->notify(new SealRecoveryOffer($journal));
                $label = "Recovery offer ({$daysToExpiry}d): {$journal->title}";
            } else {
                // Fuera de ventana: no hacer nada
                continue;
            }

            $journal->update(['seal_notified_at' => now()]);
            $this->line("  [seal:check] {$label}");
            $notified++;
        }

        $this->info("Done. Notified: {$notified}");

        return self::SUCCESS;
    }

    /**
     * El cron se autolimita: sólo notifica si nunca se notificó o si pasaron
     * más de RENOTIFY_COOLDOWN_DAYS desde el último aviso. La marca puede ser
     * reseteada (cuando se cruza un threshold o sub-threshold) o por admin
     * desde Filament; en ambos casos volverá a notificar.
     */
    private function shouldNotify(Journal $journal): bool
    {
        if ($journal->seal_notified_at === null) {
            return true;
        }

        return $journal->seal_notified_at->lt(now()->subDays(self::RENOTIFY_COOLDOWN_DAYS));
    }
}
