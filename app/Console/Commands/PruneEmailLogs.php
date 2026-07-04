<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

/**
 * Purga el log de correos (email_logs) según la retención configurada
 * (mail_logging.retention_days, 90 por defecto). `recipient_email` es dato
 * personal (RGPD): esta retención acotada es la salvaguarda.
 */
class PruneEmailLogs extends Command
{
    protected $signature = 'email-logs:prune';

    protected $description = 'Borra filas de email_logs más antiguas que la retención configurada.';

    public function handle(): int
    {
        $days = (int) config('mail_logging.retention_days', 90);
        $cutoff = Date::now()->subDays($days);

        $deleted = EmailLog::where('created_at', '<', $cutoff)->delete();

        $this->info("email_logs: {$deleted} filas purgadas (anteriores a {$cutoff->toDateString()}, retención {$days} días).");

        return self::SUCCESS;
    }
}
