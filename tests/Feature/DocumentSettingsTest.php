<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\CertificateSettings;
use App\Models\Journal;
use App\Models\Setting;
use App\Models\User;
use App\Support\DocumentBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * #65 — configuración de documentos (certificado + informe): control de
 * acceso de la página, persistencia de settings y ensamblado de marca
 * (token {journal}, QR).
 */
class DocumentSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('evaluator', 'web');

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function superAdmin(): User
    {
        return tap(User::factory()->create())->assignRole('super_admin');
    }

    public function test_super_admin_can_open_document_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/certificate-settings')
            ->assertOk();
    }

    public function test_regular_user_cannot_open_document_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/certificate-settings')
            ->assertForbidden();
    }

    public function test_save_persists_document_settings(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(CertificateSettings::class)
            ->set('data.institution_name', 'Instituto Demo')
            ->set('data.institution_email', 'demo@example.com')
            ->set('data.certificate_footer_note', 'Nota legal de prueba')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Instituto Demo', Setting::get('institution_name'));
        $this->assertSame('demo@example.com', Setting::get('institution_email'));
        $this->assertSame('Nota legal de prueba', Setting::get('certificate_footer_note'));
    }

    public function test_branding_replaces_journal_token_in_certificate_description(): void
    {
        Setting::set('certificate_description', 'La revista {journal} cumple los estándares.', 'string', 'documents');

        $journal = new Journal;
        $journal->title = 'Acme Journal';

        $brand = app(DocumentBranding::class)->assemble($journal);

        $this->assertStringNotContainsString('{journal}', $brand['certificate_description']);
        $this->assertStringContainsString('cumple los estándares', $brand['certificate_description']);
    }

    public function test_qr_for_journal_returns_png_data_uri(): void
    {
        $journal = new Journal;
        $journal->slug = 'acme-journal';

        $qr = app(DocumentBranding::class)->qrForJournal($journal);

        $this->assertNotNull($qr);
        $this->assertStringStartsWith('data:image/png;base64,', $qr);
    }

    public function test_branding_omits_missing_logos_and_signatories(): void
    {
        // Sin logos ni firmantes configurados, los arrays quedan vacíos
        // (degradación limpia en el PDF).
        $brand = app(DocumentBranding::class)->assemble();

        $this->assertSame([], $brand['logos']);
        $this->assertSame([], $brand['signatories']);
    }
}
