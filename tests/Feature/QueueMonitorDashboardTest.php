<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\FailedJobsWidget;
use App\Filament\Widgets\OaiHarvestQueueWidget;
use App\Listeners\RecordScheduledTaskFinished;
use App\Listeners\RecordWorkerHeartbeat;
use App\Models\FailedJob;
use App\Models\Journal;
use App\Models\ScheduledTaskRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * #59 — panel de administración de colas (QueueMonitor): render, salud del worker,
 * salud de crons, reset de cosecha trabada y gestión granular de fallidos.
 */
class QueueMonitorDashboardTest extends TestCase
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

    private function journal(string $status = 'queued'): Journal
    {
        $user = User::factory()->create();

        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista OAI',
            'slug' => 'revista-oai-'.$user->id,
            'primary_locale' => 'es',
            'status' => 'listed',
            'oai_base_url' => 'https://revista.test/oai',
            'oai_harvest_status' => $status,
        ]);
    }

    public function test_super_admin_can_open_queue_monitor(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/queue-monitor')
            ->assertOk();
    }

    public function test_evaluator_cannot_open_queue_monitor(): void
    {
        $evaluator = tap(User::factory()->create())->assignRole('evaluator');

        $this->actingAs($evaluator)
            ->get('/admin/queue-monitor')
            ->assertForbidden();
    }

    public function test_worker_heartbeat_is_recorded_and_throttled(): void
    {
        Cache::forget(RecordWorkerHeartbeat::CACHE_KEY);

        $listener = new RecordWorkerHeartbeat;
        $listener->handle(new Looping('database', 'harvest'));

        $first = Cache::get(RecordWorkerHeartbeat::CACHE_KEY);
        $this->assertNotNull($first);

        // Segundo latido inmediato: throttle → no reescribe.
        $listener->handle(new Looping('database', 'harvest'));
        $this->assertSame($first, Cache::get(RecordWorkerHeartbeat::CACHE_KEY));
    }

    public function test_scheduled_task_finished_is_recorded(): void
    {
        $task = new ScheduledEvent(app(CacheEventMutex::class), "'php' 'artisan' seal:check-expiration");

        (new RecordScheduledTaskFinished)->handle(new ScheduledTaskFinished($task, 1.23));

        $this->assertDatabaseHas('scheduled_task_runs', [
            'command' => 'seal:check-expiration',
            'status' => ScheduledTaskRun::STATUS_OK,
        ]);
        $this->assertSame(1230, ScheduledTaskRun::first()->runtime_ms);
    }

    public function test_reset_action_unsticks_journal_and_clears_lock(): void
    {
        $admin = $this->superAdmin();
        $journal = $this->journal('queued');

        // Lock viejo de WithoutOverlapping para esta revista.
        DB::table('cache_locks')->insert([
            'key' => 'esp-cache-laravel-queue-overlap:hash:oai-harvest-'.$journal->id,
            'owner' => 'stale',
            'expiration' => time() + 999,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(OaiHarvestQueueWidget::class)
            ->callTableAction('reset_harvest', $journal);

        $journal->refresh();
        $this->assertSame('idle', $journal->oai_harvest_status);
        $this->assertDatabaseMissing('cache_locks', [
            'key' => 'esp-cache-laravel-queue-overlap:hash:oai-harvest-'.$journal->id,
        ]);
    }

    public function test_forget_action_removes_failed_job(): void
    {
        $admin = $this->superAdmin();

        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-123',
            'connection' => 'database',
            'queue' => 'harvest',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\HarvestJournalArticles']),
            'exception' => "RuntimeException: boom\n#0 ...",
            'failed_at' => now(),
        ]);

        $failed = FailedJob::where('uuid', 'test-uuid-123')->firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(FailedJobsWidget::class)
            ->callTableAction('forget', $failed);

        $this->assertDatabaseMissing('failed_jobs', ['uuid' => 'test-uuid-123']);
    }
}
