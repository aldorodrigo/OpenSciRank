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

        foreach ($journals as $journal) {
            $journal->update(['seal_status' => 'expiring_soon']);

            if ($journal->user) {
                $journal->user->notify(new SealExpiringSoon($journal));
            }

            $this->line("  ⚠ Expiring soon: {$journal->title} (expires {$journal->seal_expires_at->format('d/m/Y')})");
        }

        return $journals->count();
    }

    private function processExpired(): int
    {
        $journals = Journal::whereIn('seal_status', ['active', 'expiring_soon'])
            ->whereNotNull('seal_expires_at')
            ->where('seal_expires_at', '<', now())
            ->get();

        foreach ($journals as $journal) {
            $journal->update([
                'seal_status' => 'expired',
                'status' => 'evaluated',
            ]);

            if ($journal->user) {
                $journal->user->notify(new SealExpired($journal));
            }

            $this->line("  ✗ Expired: {$journal->title}");
        }

        return $journals->count();
    }
}
