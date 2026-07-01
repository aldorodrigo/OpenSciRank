<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminTask;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Roadmap #35 — cobertura del escritorio y la experiencia acotada del rol
 * evaluator: routing (aterriza en el desk, no en Journals/SLA), autorización
 * de EvaluateJournal por asignación, y la asignación unificada.
 */
class EvaluatorExperienceTest extends TestCase
{
    use RefreshDatabase;

    private const EVALUATOR_PERMISSIONS = [
        'ViewAny:Journal', 'View:Journal', 'Update:Journal',
        'ViewAny:AdminTask', 'View:AdminTask', 'Update:AdminTask',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::EVALUATOR_PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('super_admin', 'web');
        $evaluatorRole = Role::findOrCreate('evaluator', 'web');
        $evaluatorRole->givePermissionTo(self::EVALUATOR_PERMISSIONS);

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function evaluator(): User
    {
        return tap(User::factory()->create())->assignRole('evaluator');
    }

    private function journalFor(User $evaluator, string $status = 'submitted'): Journal
    {
        $owner = User::factory()->create();

        return Journal::create([
            'user_id' => $owner->id,
            'title' => 'Revista de Prueba',
            'slug' => 'revista-'.uniqid(),
            'primary_locale' => 'es',
            'status' => $status,
            'assigned_evaluator_id' => $evaluator->id,
        ]);
    }

    public function test_evaluator_landing_on_dashboard_is_redirected_to_desk(): void
    {
        $response = $this->actingAs($this->evaluator())->get('/admin');

        $response->assertRedirect('/admin/evaluator-desk');
    }

    public function test_evaluator_can_open_their_desk(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $response = $this->actingAs($evaluator)->get('/admin/evaluator-desk');

        $response->assertOk();
        // Título del desk (page shell) + badge de rol inyectado por el
        // site-header del panel — cubre que el desk carga (Fase 3) y la
        // diferenciación visual del evaluador (Fase 5). Los widgets (stats/cola)
        // se hidratan lazy vía Livewire, así que su contenido interno no está en
        // el primer render y no se asserta acá.
        $response->assertSee(__('evaluator_desk.title'));
        $response->assertSee(__('role_badge.evaluator'));
    }

    public function test_super_admin_cannot_access_the_evaluator_desk(): void
    {
        $admin = tap(User::factory()->create())->assignRole('super_admin');

        $this->actingAs($admin)->get('/admin/evaluator-desk')->assertForbidden();
    }

    public function test_evaluator_is_blocked_from_sla_settings(): void
    {
        $this->actingAs($this->evaluator())->get('/admin/sla-settings')->assertForbidden();
    }

    public function test_evaluator_is_redirected_away_from_the_journals_list(): void
    {
        $this->actingAs($this->evaluator())
            ->get('/admin/journals')
            ->assertRedirect('/admin/evaluator-desk');
    }

    public function test_evaluator_can_open_evaluation_for_an_assigned_journal(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);

        $response = $this->actingAs($evaluator)
            ->get('/admin/journals/'.$journal->id.'/evaluate');

        $response->assertOk();
        // Fase 4 — la sección "Consultar al editor" (mensajería anclada al
        // Journal) está presente en la página de evaluación.
        $response->assertSee(__('evaluator_msg.section_title'));
    }

    public function test_evaluator_cannot_open_evaluation_for_an_unassigned_journal(): void
    {
        $evaluator = $this->evaluator();
        $other = User::factory()->create();
        $journal = $this->journalFor($evaluator);
        $journal->update(['assigned_evaluator_id' => $other->id]);

        // El journal no asignado queda inaccesible: el scoping de
        // getEloquentQuery lo esconde (404) y, si se resolviera, el
        // abort_unless de mount() lo cortaría (403). Cualquiera bloquea.
        $status = $this->actingAs($evaluator)
            ->get('/admin/journals/'.$journal->id.'/evaluate')
            ->status();

        $this->assertContains($status, [403, 404]);
    }

    public function test_assign_evaluator_syncs_journal_field_and_open_task(): void
    {
        $evaluator = $this->evaluator();
        $owner = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Revista X',
            'slug' => 'revista-x-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'submitted',
        ]);

        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
        ]);

        $returned = $journal->assignEvaluator($evaluator);

        $this->assertSame($task->id, $returned?->id);
        $this->assertSame($evaluator->id, $journal->fresh()->assigned_evaluator_id);
        $this->assertSame($evaluator->id, $task->fresh()->assigned_to);
    }
}
