<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalPdfTest extends TestCase
{
    use RefreshDatabase;

    private function certifiedJournalFor(User $user): Journal
    {
        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Test Journal',
            'slug' => 'test-journal-'.$user->id,
            'primary_locale' => 'es',
            'status' => 'certified',
            'seal_status' => 'active',
            'seal_awarded_at' => now()->subDays(7),
            'seal_expires_at' => now()->addYear(),
            'evaluated_at' => now()->subDays(7),
            'current_score' => 82.5,
        ]);
    }

    public function test_owner_can_download_certificate_when_journal_is_certified(): void
    {
        $user = User::factory()->create();
        $journal = $this->certifiedJournalFor($user);

        $response = $this->actingAs($user)->get(route('app.journal.certificate.pdf', $journal));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_owner_can_download_report_when_journal_has_been_evaluated(): void
    {
        $user = User::factory()->create();
        $journal = $this->certifiedJournalFor($user);

        $response = $this->actingAs($user)->get(route('app.journal.report.pdf', $journal));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_non_owner_cannot_download_certificate(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $journal = $this->certifiedJournalFor($owner);

        $response = $this->actingAs($intruder)->get(route('app.journal.certificate.pdf', $journal));

        $response->assertForbidden();
    }

    public function test_certificate_returns_404_when_journal_not_certified(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Draft Journal',
            'slug' => 'draft-journal-'.$user->id,
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'evaluated_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($user)->get(route('app.journal.certificate.pdf', $journal));

        $response->assertNotFound();
    }

    public function test_report_returns_404_when_journal_not_evaluated(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Draft Journal',
            'slug' => 'draft-journal-'.$user->id,
            'primary_locale' => 'es',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('app.journal.report.pdf', $journal));

        $response->assertNotFound();
    }

    public function test_anonymous_user_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $journal = $this->certifiedJournalFor($user);

        $response = $this->get(route('app.journal.certificate.pdf', $journal));

        $response->assertRedirect();
    }
}
