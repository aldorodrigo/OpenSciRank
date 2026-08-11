<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * #64 — la fecha de "última actualización" de /terms y /privacy es fija
 * (config/legal.php), no `date()`.
 */
class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function legalPages(): array
    {
        return [
            'terms' => ['/terms', 'terms'],
            'privacy' => ['/privacy', 'privacy'],
        ];
    }

    #[DataProvider('legalPages')]
    public function test_shows_the_configured_date_not_todays_date(string $url, string $document): void
    {
        config(["legal.{$document}.updated_at" => '2026-03-17']);

        $this->get($url)
            ->assertOk()
            ->assertSee('datetime="2026-03-17"', false)
            ->assertSee('March 17, 2026')
            ->assertDontSee(now()->format('d/m/Y'));
    }

    /**
     * El bug original: `date('d/m/Y')` hacía que el documento afirmara haberse
     * actualizado hoy, todos los días. Al viajar en el tiempo la fecha mostrada
     * no puede moverse.
     */
    #[DataProvider('legalPages')]
    public function test_the_date_does_not_drift_with_the_passage_of_time(string $url, string $document): void
    {
        config(["legal.{$document}.updated_at" => '2026-03-17']);

        $this->get($url)->assertOk()->assertSee('March 17, 2026');

        $this->travel(400)->days();

        $this->get($url)
            ->assertOk()
            ->assertSee('datetime="2026-03-17"', false)
            ->assertSee('March 17, 2026')
            // Con el `date()` viejo acá aparecía la fecha del futuro simulado.
            ->assertDontSee(now()->format('d/m/Y'))
            ->assertDontSee(now()->isoFormat('LL'));
    }

    #[DataProvider('legalPages')]
    public function test_the_date_is_formatted_in_the_active_locale(string $url, string $document): void
    {
        config(["legal.{$document}.updated_at" => '2026-03-17']);

        // Formato largo y sin ambigüedad — `d/m/Y` se lee distinto según el país.
        $this->get("/es{$url}")->assertOk()->assertSee('17 de marzo de 2026');
        $this->get("/pt{$url}")->assertOk()->assertSee('17 de março de 2026');
    }

    public function test_configured_dates_are_valid_past_iso_dates(): void
    {
        foreach (['terms', 'privacy'] as $document) {
            $raw = config("legal.{$document}.updated_at");

            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}$/',
                (string) $raw,
                "legal.{$document}.updated_at debe ser una fecha ISO (YYYY-MM-DD)."
            );

            $this->assertTrue(
                Carbon::parse($raw)->lessThanOrEqualTo(now()),
                "legal.{$document}.updated_at no puede estar en el futuro."
            );
        }
    }
}
