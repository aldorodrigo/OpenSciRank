<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\EvaluatorQueue;
use App\Filament\Widgets\EvaluatorTasksOverview;
use App\Livewire\MessageThread;
use App\Models\AdminTask;
use App\Models\Conversation;
use App\Models\Journal;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        // Título del desk (page shell) + botón de acceso del evaluador inyectado
        // por el site-header del panel — cubre que el desk carga (Fase 3) y la
        // diferenciación/acceso del evaluador. Los widgets (stats/cola) se
        // hidratan lazy vía Livewire, así que su contenido no está en el primer
        // render y no se asserta acá.
        $response->assertSee(__('evaluator_desk.title'));
        $response->assertSee(__('evaluator_access.button'));
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

    public function test_evaluator_can_open_evaluation_when_assigned_via_task_only(): void
    {
        $evaluator = $this->evaluator();
        $owner = User::factory()->create();
        // assigned_evaluator_id NULL a propósito: la asignación existe solo en la task.
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Task-only Journal',
            'slug' => 'task-only-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'submitted',
        ]);
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_IN_PROGRESS,
            'assigned_to' => $evaluator->id,
        ]);

        // Antes del fix esto daba 404 (getEloquentQuery scopeaba solo por
        // assigned_evaluator_id, que acá es NULL).
        $this->actingAs($evaluator)
            ->get('/admin/journals/'.$journal->id.'/evaluate')
            ->assertOk();
    }

    public function test_task_assignment_syncs_journal_evaluator(): void
    {
        $evaluator = $this->evaluator();
        $owner = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Sync Journal',
            'slug' => 'sync-'.uniqid(),
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

        $task->assignToUser($evaluator);

        $this->assertSame($evaluator->id, $journal->fresh()->assigned_evaluator_id);
    }

    public function test_evaluator_cannot_evaluate_their_own_journal_coi(): void
    {
        $evaluator = $this->evaluator();
        // Caso COI: la revista es del propio evaluador y, por error, quedó
        // asignada a él. Aun así no debe poder evaluarla.
        $journal = Journal::create([
            'user_id' => $evaluator->id,
            'title' => 'Own Journal',
            'slug' => 'own-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'submitted',
            'assigned_evaluator_id' => $evaluator->id,
        ]);

        $this->actingAs($evaluator)
            ->get('/admin/journals/'.$journal->id.'/evaluate')
            ->assertForbidden();
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

    public function test_app_dashboard_shows_evaluator_access_for_evaluators(): void
    {
        $response = $this->actingAs($this->evaluator())->get(route('app.dashboard'));

        $response->assertOk();
        // Banner de acceso en el dashboard de /app + botón en el topbar.
        $response->assertSee(__('evaluator_access.banner_cta'));
        $response->assertSee(__('evaluator_access.button'));
    }

    public function test_app_dashboard_hides_evaluator_access_from_regular_users(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('app.dashboard'));

        $response->assertOk();
        $response->assertDontSee(__('evaluator_access.banner_title'));
    }

    public function test_task_title_falls_back_to_related_when_params_missing(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $journal->update(['title' => 'Nature Test']);

        // title_params null (task vieja creada sin params) NO debe mostrar ":name".
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => null,
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $this->assertStringNotContainsString(':name', $task->renderedTitle());
        $this->assertStringContainsString('Nature Test', $task->renderedTitle());
    }

    public function test_app_banner_shows_pending_task_count(): void
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

        $response = $this->actingAs($evaluator)->get(route('app.dashboard'));

        $response->assertOk();
        $response->assertSee(trans_choice('evaluator_access.banner_pending', 1, ['count' => 1]));
    }

    public function test_desk_stats_link_to_filtered_task_lists(): void
    {
        Livewire::actingAs($this->evaluator())
            ->test(EvaluatorTasksOverview::class)
            ->assertSee('tableFilters[status][values][0]=pending')
            ->assertSee('tableFilters[overdue][isActive]=true')
            ->assertSee('activeTab=completed');
    }

    public function test_unread_messages_stat_links_to_the_evaluation(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $editor = User::factory()->create();

        // Hilo anclado al journal, con un mensaje del editor sin leer por el evaluador.
        $conv = Conversation::create([
            'subject' => 'Evaluación',
            'subject_type' => Journal::class,
            'subject_id' => $journal->id,
            'started_by_user_id' => $evaluator->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);
        $conv->addParticipant($evaluator, Conversation::ROLE_EVALUATOR);
        $conv->addParticipant($editor, Conversation::ROLE_EDITOR);
        Message::create([
            'conversation_id' => $conv->id,
            'user_id' => $editor->id,
            'body' => 'Falta un dato.',
        ]);

        Livewire::actingAs($evaluator)
            ->test(EvaluatorTasksOverview::class)
            ->assertSee('/journals/'.$journal->id.'/evaluate');
    }

    public function test_thread_shows_task_link_for_evaluator_but_not_editor(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_IN_PROGRESS,
            'assigned_to' => $evaluator->id,
        ]);
        $conv = Conversation::create([
            'subject' => 'Evaluación',
            'subject_type' => Journal::class,
            'subject_id' => $journal->id,
            'started_by_user_id' => $evaluator->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);
        $conv->addParticipant($evaluator, Conversation::ROLE_EVALUATOR);
        $conv->addParticipant($journal->user, Conversation::ROLE_EDITOR);

        $taskUrl = url('/admin/admin-tasks/'.$task->id);

        // Evaluador: ve "Ver tarea".
        Livewire::actingAs($evaluator)
            ->test(MessageThread::class, ['conversation' => $conv, 'role' => 'evaluator'])
            ->assertSee($taskUrl);

        // Editor: NO ve el link a la tarea.
        Livewire::actingAs($journal->user)
            ->test(MessageThread::class, ['conversation' => $conv, 'role' => 'editor'])
            ->assertDontSee($taskUrl);
    }

    public function test_desk_queue_shows_active_tasks_only(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);

        $active = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $done = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_COMPLETED,
            'completed_at' => now(),
            'assigned_to' => $evaluator->id,
        ]);

        // La cola del escritorio muestra solo activas; las completadas viven en
        // el tab "Completadas" de /admin/admin-tasks.
        Livewire::actingAs($evaluator)
            ->test(EvaluatorQueue::class)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$done]);
    }

    public function test_evaluator_does_not_see_payment_or_courtesy_in_tasks_table(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        // Task de cortesía (sin payment_id): el badge "Cortesía" NO debe verse.
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks');

        $response->assertOk();
        $response->assertDontSee('Cortesía');
    }

    public function test_related_link_points_to_public_journal_page_for_evaluator(): void
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

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks');

        $response->assertOk();
        // El link "Relacionado" apunta a la ficha pública, no a la evaluación ni al edit.
        $response->assertSee(route('journal.show', ['slug' => $journal->slug]));
        $response->assertDontSee('/journals/'.$journal->id.'/evaluate');
        $response->assertDontSee('/journals/'.$journal->id.'/edit');
    }

    public function test_task_detail_hides_admin_edit_link_for_evaluator(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks/'.$task->id);

        $response->assertOk();
        $response->assertDontSee('Editar (admin)');
        $response->assertSee('Ver ficha pública');
    }

    public function test_task_detail_shows_instructions_and_hides_internal_sections_for_evaluator(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
            'notes' => 'Nota interna secreta del staff',
        ]);

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks/'.$task->id);

        $response->assertOk();
        // Instrucciones visibles para el evaluador…
        $response->assertSee(__('evaluator_task.instructions_title'));
        // …pero notas internas e historial ocultos.
        $response->assertDontSee('Nota interna secreta del staff');
        $response->assertDontSee(__('Notas internas'));
        $response->assertDontSee(__('admin.activity.title'));
    }

    public function test_evaluator_does_not_see_awaiting_payment_tab(): void
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

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks');

        $response->assertOk();
        // Ni el tab ni la opción de filtro "Pago pendiente".
        $response->assertDontSee('Pago pendiente');
    }

    public function test_evaluator_sees_their_pending_task_in_admin_tasks_table(): void
    {
        $evaluator = $this->evaluator();
        $journal = $this->journalFor($evaluator);
        $journal->update(['title' => 'Revista Visible']);
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => 'Revista Visible'],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_PENDING,
            'assigned_to' => $evaluator->id,
        ]);

        $response = $this->actingAs($evaluator)->get('/admin/admin-tasks');

        $response->assertOk();
        $response->assertSee('Revista Visible');
    }
}
