<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use App\Notifications\EvaluationCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Roadmap #35 — el correo "Evaluación completada" incluye el link al informe
 * PDF (siempre) y al certificado (solo si la revista quedó certificada).
 */
class EvaluationCompletedEmailTest extends TestCase
{
    use RefreshDatabase;

    private function lines(EvaluationCompleted $notification, User $owner): Collection
    {
        $mail = $notification->toMail($owner);

        return collect($mail->introLines)->merge($mail->outroLines);
    }

    public function test_email_includes_report_link(): void
    {
        $owner = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Evaluated Journal',
            'slug' => 'evaluated-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'current_score' => 62.0,
            'evaluated_at' => now(),
        ]);

        $reportUrl = route('app.journal.report.pdf', $journal);

        $this->assertTrue(
            $this->lines(new EvaluationCompleted($journal), $owner)
                ->contains(fn (string $line) => str_contains($line, $reportUrl))
        );
    }

    public function test_email_includes_certificate_link_when_certified(): void
    {
        $owner = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Certified Journal',
            'slug' => 'certified-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'certified',
            'current_score' => 84.0,
            'evaluated_at' => now(),
            'seal_status' => 'active',
            'seal_awarded_at' => now(),
            'seal_expires_at' => now()->addYear(),
        ]);

        $certUrl = route('app.journal.certificate.pdf', $journal);

        $this->assertTrue(
            $this->lines(new EvaluationCompleted($journal), $owner)
                ->contains(fn (string $line) => str_contains($line, $certUrl))
        );
    }

    public function test_non_certified_email_omits_certificate_link(): void
    {
        $owner = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Evaluated Not Certified',
            'slug' => 'notcert-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'current_score' => 55.0,
            'evaluated_at' => now(),
        ]);

        $certUrl = route('app.journal.certificate.pdf', $journal);

        $this->assertFalse(
            $this->lines(new EvaluationCompleted($journal), $owner)
                ->contains(fn (string $line) => str_contains($line, $certUrl))
        );
    }
}
