<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Notifications\SealExpired;
use App\Notifications\SealExpiringSoon;
use Illuminate\Console\Command;

class CheckSealExpiration extends Command
{
    protected $signature = 'seal:check-expiration';
    protected $description = 'Check for expiring and expired Editorial Standards Seals';

    /**
     * Días mínimos entre notificaciones automáticas al mismo editor para
     * un mismo journal. Evita el "email diario" si el cron corre a diario.
     * Se sigue reseteando `seal_notified_at` cuando el estado del sello
     * cruza un threshold (de active→expiring_soon y de expiring_soon→expired),
     * para garantizar al menos un aviso por cada warming.
     */
    private const RENOTIFY_COOLDOWN_DAYS = 7;

    public function handle(): int
    {
        $this->info('Checking seal expirations...');

        $expiringSoon = $this->processExpiringSoon();
        $expired = $this->processExpired();

        $this->info("Done. Expiring soon: {$expiringSoon}, Expired: {$expired}");

        return self::SUCCESS;
    }

    private function processExpiringSoon(): int
    {
        $journals = Journal::where('seal_status', 'active')
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '>', now())
            ->where('seal_expires_at', '<=', now()->addDays(30))
            ->get();

        $notified = 0;
        foreach ($journals as $journal) {
            // Cruzó el threshold 30d: reseteamos la marca para garantizar el aviso.
            $journal->update([
                'seal_status' => 'expiring_soon',
                'seal_notified_at' => null,
            ]);

            if ($journal->user && $this->shouldNotify($journal)) {
                $journal->user->notify(new SealExpiringSoon($journal));
                $journal->update(['seal_notified_at' => now()]);

                activity()
                    ->performedOn($journal)
                    ->withProperties(['seal_expires_at' => $journal->seal_expires_at?->toDateString()])
                    ->log('Sello marcado como próximo a vencer (automático)');

                $this->line("  ⚠ Expiring soon: {$journal->title} (expires {$journal->seal_expires_at->format('d/m/Y')})");
                $notified++;
            }
        }

        return $notified;
    }

    private function processExpired(): int
    {
        $journals = Journal::whereIn('seal_status', ['active', 'expiring_soon'])
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '<', now())
            ->get();

        $notified = 0;
        foreach ($journals as $journal) {
            // Cruzó el threshold 0d (vencimiento): reseteamos para garantizar
            // el aviso aunque ya se haya notificado el "próximo a vencer".
            $journal->update([
                'seal_status' => 'expired',
                'status' => 'evaluated',
                'seal_notified_at' => null,
            ]);

            if ($journal->user && $this->shouldNotify($journal)) {
                $journal->user->notify(new SealExpired($journal));
                $journal->update(['seal_notified_at' => now()]);

                activity()
                    ->performedOn($journal)
                    ->withProperties(['seal_expires_at' => $journal->seal_expires_at?->toDateString()])
                    ->log('Sello vencido: estado revertido a evaluada (automático)');

                $this->line("  ✗ Expired: {$journal->title}");
                $notified++;
            }
        }

        return $notified;
    }

    /**
     * El cron se autolimita: sólo notifica si nunca se notificó o si pasaron
     * más de RENOTIFY_COOLDOWN_DAYS desde el último aviso. La marca puede ser
     * reseteada arriba (cuando se cruza un threshold) o por admin desde
     * Filament; en ambos casos volverá a notificar.
     */
    private function shouldNotify(Journal $journal): bool
    {
        if ($journal->seal_notified_at === null) {
            return true;
        }

        return $journal->seal_notified_at->lt(now()->subDays(self::RENOTIFY_COOLDOWN_DAYS));
    }
}
