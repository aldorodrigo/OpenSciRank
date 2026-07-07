<?php

declare(strict_types=1);

namespace Tests\Feature\Oai;

use App\Filament\Resources\JournalResource\Pages\EvaluateJournal;
use App\Jobs\HarvestJournalArticles;
use App\Jobs\RefreshJournalMetricsJob;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\OaiHarvestFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * #57 — automatismos para evitar cosechas/métricas fallidas sin señal:
 * (a) una cosecha OAI que agota reintentos avisa a los super_admins;
 * (b) al certificar una revista se encolan cosecha + refresco de métricas.
 */
class OaiMetricsAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super_admin', 'web');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function journalWithOai(string $status = 'submitted'): Journal
    {
        $user = User::factory()->create();

        return Journal::create([
            'user_id' => $user->id,
            'title' => 'Revista OAI',
            'slug' => 'revista-oai-'.$user->id,
            'primary_locale' => 'es',
            'status' => $status,
            'oai_base_url' => 'https://revista.test/oai',
            'oai_metadata_prefix' => 'oai_dc',
        ]);
    }

    public function test_failed_harvest_notifies_super_admins(): void
    {
        Notification::fake();

        $admin = tap(User::factory()->create())->assignRole('super_admin');
        $journal = $this->journalWithOai('listed');

        (new HarvestJournalArticles($journal))->failed(new \RuntimeException('OAI timeout'));

        $journal->refresh();
        $this->assertSame('failed', $journal->oai_harvest_status);
        $this->assertStringContainsString('OAI timeout', (string) $journal->oai_last_harvest_error);

        Notification::assertSentTo($admin, OaiHarvestFailed::class);
    }

    /**
     * Ejecuta el save() de la página de evaluación con estado forzado a certified,
     * sin pasar por el lifecycle de Livewire (las páginas Filament con `record` no
     * se prestan a Livewire::test en este repo). Cubre la rama de certificación.
     */
    private function runCertification(User $admin, Journal $journal): void
    {
        $this->actingAs($admin);

        $page = new EvaluateJournal;
        $page->record = $journal;
        $page->scores = [];
        $page->evaluation_notes = '';
        $page->assigned_level = '';
        $page->assigned_status = 'certified';
        $page->save();
    }

    public function test_certifying_journal_with_oai_enqueues_harvest_and_metrics(): void
    {
        Queue::fake();

        $admin = tap(User::factory()->create())->assignRole('super_admin');
        $journal = $this->journalWithOai('submitted');

        $this->runCertification($admin, $journal);

        $journal->refresh();
        $this->assertSame('certified', $journal->status);
        $this->assertSame('queued', $journal->oai_harvest_status);

        Queue::assertPushed(
            HarvestJournalArticles::class,
            fn (HarvestJournalArticles $job): bool => $job->journal->id === $journal->id,
        );
        Queue::assertPushed(
            RefreshJournalMetricsJob::class,
            fn (RefreshJournalMetricsJob $job): bool => $job->journal->id === $journal->id,
        );
    }

    public function test_certifying_journal_without_oai_enqueues_metrics_only(): void
    {
        Queue::fake();

        $admin = tap(User::factory()->create())->assignRole('super_admin');
        $journal = $this->journalWithOai('submitted');
        $journal->update(['oai_base_url' => null]);

        $this->runCertification($admin, $journal);

        Queue::assertPushed(RefreshJournalMetricsJob::class);
        Queue::assertNotPushed(HarvestJournalArticles::class);
    }
}
