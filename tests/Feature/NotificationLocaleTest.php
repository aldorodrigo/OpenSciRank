<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use App\Notifications\SealExpired;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

/**
 * Regresión del bug de la auditoría de correos (2026-07-04): 17 notificaciones
 * hacían `App::setLocale($notifiable->preferred_locale ?? 'es')` — atributo
 * inexistente (la columna es `locale`) — forzando español para todos.
 *
 * Tras eliminar esa línea, Laravel traduce cada correo al idioma del
 * destinatario vía `User::preferredLocale()` (`HasLocalePreference`), también
 * al despachar a una colección con locales mixtos y desde crons sin request.
 *
 * Se envía por el pipeline real (transporte `array`) — NO `toMail()` directo,
 * que no ejercita el wrapping de locale del NotificationSender.
 */
class NotificationLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('mail.default', 'array');
        Mail::purge('array');
    }

    private function makeJournal(User $owner): Journal
    {
        return Journal::create([
            'user_id' => $owner->id,
            'title' => 'Revista de Prueba',
            'slug' => 'locale-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
            'seal_status' => 'expired',
            'seal_expires_at' => now()->subDay(),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, \Symfony\Component\Mailer\SentMessage> */
    private function sentMessages(): \Illuminate\Support\Collection
    {
        return Mail::mailer('array')->getSymfonyTransport()->messages();
    }

    public function test_email_renders_in_recipient_locale_english(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'name' => 'Jane']);
        $journal = $this->makeJournal($user);

        $user->notify(new SealExpired($journal));

        $subject = $this->sentMessages()->first()->getOriginalMessage()->getSubject();
        $this->assertStringContainsString('has expired', $subject);
    }

    public function test_email_renders_in_recipient_locale_portuguese(): void
    {
        $user = User::factory()->create(['locale' => 'pt', 'name' => 'João']);
        $journal = $this->makeJournal($user);

        $user->notify(new SealExpired($journal));

        $subject = $this->sentMessages()->first()->getOriginalMessage()->getSubject();
        $this->assertStringContainsString('expirou', $subject);
    }

    public function test_null_locale_falls_back_to_app_default(): void
    {
        // Sin `locale`, `User::preferredLocale()` cae al locale por defecto de
        // la app (en producción, español). Se fija explícito para determinismo.
        config()->set('app.locale', 'es');

        $user = User::factory()->create(['locale' => null, 'name' => 'Ana']);
        $journal = $this->makeJournal($user);

        $user->notify(new SealExpired($journal));

        $subject = $this->sentMessages()->first()->getOriginalMessage()->getSubject();
        $this->assertStringContainsString('expiró', $subject);
    }

    public function test_collection_send_uses_each_recipient_locale(): void
    {
        $english = User::factory()->create(['locale' => 'en', 'name' => 'Jane']);
        $portuguese = User::factory()->create(['locale' => 'pt', 'name' => 'João']);
        $journal = $this->makeJournal($english);

        NotificationFacade::send([$english, $portuguese], new SealExpired($journal));

        $subjects = $this->sentMessages()
            ->map(fn ($m) => $m->getOriginalMessage()->getSubject());

        $this->assertTrue($subjects->contains(fn ($s) => str_contains($s, 'has expired')), 'Falta el asunto en inglés');
        $this->assertTrue($subjects->contains(fn ($s) => str_contains($s, 'expirou')), 'Falta el asunto en portugués');
    }
}
