<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\SealExpired;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 4 auditoría de correos: los listeners de mail pueblan email_logs.
 */
class EmailLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sent_email_is_logged_as_sent_with_metadata(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'name' => 'Jane']);
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista Log',
            'slug' => 'log-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'seal_status' => 'expired',
            'seal_expires_at' => now()->subDay(),
        ]);

        $user->notify(new SealExpired($journal));

        $log = EmailLog::first();

        $this->assertNotNull($log, 'No se registró la fila en email_logs');
        $this->assertSame(EmailLog::STATUS_SENT, $log->status);
        $this->assertSame($user->email, $log->recipient_email);
        $this->assertSame(SealExpired::class, $log->notification_class);
        $this->assertNotNull($log->ses_message_id);
        $this->assertNotNull($log->sent_at);
        // El notifiable quedó enlazado vía el contexto de NotificationSending.
        $this->assertSame($user->getMorphClass(), $log->notifiable_type);
        $this->assertEquals($user->id, $log->notifiable_id);
    }

    public function test_html_body_is_not_stored_by_default(): void
    {
        config()->set('mail_logging.store_html', false);

        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista Log 2',
            'slug' => 'log2-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'seal_status' => 'expired',
            'seal_expires_at' => now()->subDay(),
        ]);

        $user->notify(new SealExpired($journal));

        $this->assertNull(EmailLog::first()->html_body);
    }
}
