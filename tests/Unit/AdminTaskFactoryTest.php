<?php

namespace Tests\Unit;

use App\Models\AdminTask;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminTaskFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTaskFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('sla_evaluation_business_days', 15, 'int', 'sla');
        Setting::set('sla_evaluation_express_business_days', 5, 'int', 'sla');
        Setting::set('sla_listing_calendar_days', 7, 'int', 'sla');
        Setting::set('sla_consulting_calendar_days', 7, 'int', 'sla');
        Setting::set('sla_orphan_calendar_days', 2, 'int', 'sla');

        $this->user = User::create([
            'name' => 'Editor',
            'email' => 'factory-test@example.com',
            'password' => bcrypt('secret'),
        ]);
    }

    protected function product(string $slug, float $price = 99): Product
    {
        return Product::create([
            'slug' => $slug,
            'price' => $price,
            'currency' => 'USD',
            'is_active' => true,
            'primary_locale' => 'es',
            'name' => ['es' => $slug],
        ]);
    }

    protected function journal(string $title = 'Test Journal'): Journal
    {
        return Journal::create([
            'user_id' => $this->user->id,
            'slug' => 'test-journal-'.uniqid(),
            'title' => ['es' => $title],
            'status' => 'submitted',
        ]);
    }

    protected function payment(Product $product, Journal $journal, array $metadata = []): Payment
    {
        return Payment::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'provider' => 'stripe',
            'transaction_id' => 'pi_test_'.uniqid(),
            'amount' => $product->price,
            'currency' => 'USD',
            'status' => 'completed',
            'payable_type' => Journal::class,
            'payable_id' => $journal->id,
            'metadata' => $metadata,
        ]);
    }

    public function test_evaluation_payment_creates_evaluate_task(): void
    {
        $product = $this->product('journal-evaluation');
        $journal = $this->journal('Nature');
        $payment = $this->payment($product, $journal);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, ['is_renewal' => '0']);

        $this->assertCount(1, $tasks);
        $this->assertSame(AdminTask::TYPE_EVALUATE_JOURNAL, $tasks[0]->type);
        $this->assertSame(AdminTask::PRIORITY_NORMAL, $tasks[0]->priority);
        $this->assertSame($payment->id, $tasks[0]->payment_id);
        $this->assertSame($journal->id, $tasks[0]->related_id);
        $this->assertSame(Journal::class, $tasks[0]->related_type);
        $this->assertNotNull($tasks[0]->due_at);
    }

    public function test_express_evaluation_creates_high_priority_task(): void
    {
        $product = $this->product('journal-evaluation');
        $journal = $this->journal();
        $payment = $this->payment($product, $journal, ['is_express' => true]);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, ['is_renewal' => '0']);

        $this->assertCount(1, $tasks);
        $this->assertSame(AdminTask::PRIORITY_HIGH, $tasks[0]->priority);
    }

    public function test_reevaluation_payment_creates_reevaluate_task(): void
    {
        $product = $this->product('journal-reevaluation');
        $journal = $this->journal();
        $payment = $this->payment($product, $journal);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, []);

        $this->assertCount(1, $tasks);
        $this->assertSame(AdminTask::TYPE_REEVALUATE_JOURNAL, $tasks[0]->type);
    }

    public function test_renewal_payment_creates_renewal_evaluation_task(): void
    {
        $product = $this->product('seal-renewal-2y', 149);
        $journal = $this->journal();
        $journal->update(['pending_renewal_years' => 2]);
        $payment = $this->payment($product, $journal);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, ['is_renewal' => '1']);

        $this->assertCount(1, $tasks);
        $this->assertSame(AdminTask::TYPE_RENEWAL_EVALUATION, $tasks[0]->type);
        $this->assertSame(2, $tasks[0]->title_params['years']);
    }

    public function test_consulting_addon_creates_additional_task(): void
    {
        $product = $this->product('journal-evaluation');
        $journal = $this->journal();
        $payment = $this->payment($product, $journal);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, [
            'is_renewal' => '0',
            'addon_slugs' => 'action-plan-consulting',
        ]);

        $this->assertCount(2, $tasks);
        $this->assertSame(AdminTask::TYPE_EVALUATE_JOURNAL, $tasks[0]->type);
        $this->assertSame(AdminTask::TYPE_CONSULTING, $tasks[1]->type);
    }

    public function test_featured_book_alone_creates_no_task(): void
    {
        $product = $this->product('book-listing-featured-1y', 29);
        $journal = $this->journal(); // placeholder; el factory ve el slug y decide
        $payment = $this->payment($product, $journal);

        $tasks = AdminTaskFactory::fromPayment($payment, $journal, []);

        $this->assertCount(0, $tasks);
    }

    public function test_journal_listing_creates_review_task(): void
    {
        $journal = $this->journal();

        $task = AdminTaskFactory::forJournalListing($journal);

        $this->assertSame(AdminTask::TYPE_REVIEW_LISTING_JOURNAL, $task->type);
        $this->assertNull($task->payment_id); // free flow, no payment
        $this->assertSame($journal->id, $task->related_id);
    }

    public function test_request_journal_listing_creates_task_on_first_call(): void
    {
        // Sprint 4 #61: primer listado → crea la tarea de revisión.
        $journal = $this->journal();

        $task = AdminTaskFactory::requestJournalListing($journal);

        $this->assertSame(AdminTask::TYPE_REVIEW_LISTING_JOURNAL, $task->type);
        $this->assertSame(AdminTask::STATUS_PENDING, $task->status);
        $this->assertSame(1, AdminTask::where('related_id', $journal->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_JOURNAL)
            ->count());
    }

    public function test_request_journal_listing_reuses_open_task_on_resubmit(): void
    {
        // Sprint 4 #61: reenviar NO debe crear una segunda tarea (fix duplicados).
        $journal = $this->journal();
        $first = AdminTaskFactory::requestJournalListing($journal);

        // El revisor pide cambios → changes_requested.
        AdminTaskFactory::markChangesRequested($journal, [AdminTask::TYPE_REVIEW_LISTING_JOURNAL]);
        $this->assertSame(AdminTask::STATUS_CHANGES_REQUESTED, $first->fresh()->status);

        // El editor reenvía → reutiliza la MISMA tarea, marcada "reenviada".
        $second = AdminTaskFactory::requestJournalListing($journal);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(AdminTask::STATUS_RESUBMITTED, $second->status);
        $this->assertSame(1, AdminTask::where('related_id', $journal->id)
            ->where('type', AdminTask::TYPE_REVIEW_LISTING_JOURNAL)
            ->count());
    }

    public function test_mark_changes_requested_only_touches_open_tasks_of_given_types(): void
    {
        // Sprint 4 #61: solo las tareas abiertas de los tipos indicados pasan a changes_requested.
        $journal = $this->journal();

        $listing = AdminTaskFactory::forJournalListing($journal);
        $completed = AdminTask::create([
            'type' => AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
            'title_key' => 'tasks.review_listing_journal',
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_COMPLETED,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);

        $touched = AdminTaskFactory::markChangesRequested($journal, [AdminTask::TYPE_REVIEW_LISTING_JOURNAL]);

        $this->assertSame(1, $touched);
        $this->assertSame(AdminTask::STATUS_CHANGES_REQUESTED, $listing->fresh()->status);
        // La completada no se toca.
        $this->assertSame(AdminTask::STATUS_COMPLETED, $completed->fresh()->status);
    }

    public function test_orphan_payment_creates_high_priority_task(): void
    {
        $product = $this->product('journal-evaluation');
        $journal = $this->journal();
        $payment = $this->payment($product, $journal);

        $task = AdminTaskFactory::forOrphanPayment($payment, Journal::class, 999);

        $this->assertSame(AdminTask::TYPE_ORPHAN_PAYMENT, $task->type);
        $this->assertSame(AdminTask::PRIORITY_HIGH, $task->priority);
        $this->assertSame($payment->id, $task->payment_id);
        $this->assertNull($task->related_id); // payable no encontrado
    }
}
