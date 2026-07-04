<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\SealExpiringSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Fase 5 auditoría de correos: baja global de recordatorios + List-Unsubscribe.
 */
class EmailUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    private function sealJournal(User $user): Journal
    {
        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista Baja',
            'slug' => 'baja-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'expiring_soon',
            'seal_expires_at' => now()->addDays(20),
        ]);
    }

    public function test_signed_link_marks_user_opted_out(): void
    {
        $user = User::factory()->create(['email_reminders_opted_out' => false]);
        $url = URL::signedRoute('email.unsubscribe', ['user' => $user->id]);

        $this->get($url)->assertOk();

        $this->assertTrue($user->fresh()->hasOptedOutOfReminders());
    }

    public function test_one_click_post_returns_no_content_and_opts_out(): void
    {
        $user = User::factory()->create(['email_reminders_opted_out' => false]);
        $url = URL::signedRoute('email.unsubscribe', ['user' => $user->id]);

        $this->post($url)->assertNoContent();

        $this->assertTrue($user->fresh()->hasOptedOutOfReminders());
    }

    public function test_unsigned_link_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->get('/email/unsubscribe/'.$user->id)->assertForbidden();
    }

    public function test_opted_out_user_does_not_receive_reminder(): void
    {
        $user = User::factory()->create(['email_reminders_opted_out' => true]);
        $journal = $this->sealJournal($user);

        $user->notify(new SealExpiringSoon($journal));

        // via() devolvió [] → ni se envió mail ni se registró en email_logs.
        $this->assertSame(0, EmailLog::count());
    }

    public function test_reminder_includes_list_unsubscribe_header(): void
    {
        config()->set('mail.default', 'array');

        $user = User::factory()->create(['email_reminders_opted_out' => false]);
        $journal = $this->sealJournal($user);

        $user->notify(new SealExpiringSoon($journal));

        $message = \Illuminate\Support\Facades\Mail::mailer('array')
            ->getSymfonyTransport()->messages()->first()->getOriginalMessage();

        $this->assertTrue($message->getHeaders()->has('List-Unsubscribe'));
        $this->assertStringContainsString(
            'One-Click',
            $message->getHeaders()->get('List-Unsubscribe-Post')->getBodyAsString()
        );
    }
}
