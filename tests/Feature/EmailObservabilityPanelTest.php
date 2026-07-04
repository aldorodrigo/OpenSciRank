<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Fase 4 auditoría de correos: el panel de observabilidad (EmailLogResource +
 * página QueueMonitor) renderiza para super_admin y está vedado al resto.
 * El render ejercita la tabla, la blade de QueueMonitor (queries a jobs/
 * failed_jobs) y las claves i18n.
 */
class EmailObservabilityPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super_admin', 'web');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function superAdmin(): User
    {
        return tap(User::factory()->create())->assignRole('super_admin');
    }

    public function test_super_admin_can_open_email_logs_list(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/email-logs')
            ->assertOk();
    }

    public function test_super_admin_can_open_queue_monitor(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/queue-monitor')
            ->assertOk();
    }

    public function test_regular_user_cannot_open_email_logs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/email-logs')
            ->assertForbidden();
    }

    public function test_regular_user_cannot_open_queue_monitor(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/queue-monitor')
            ->assertForbidden();
    }
}
