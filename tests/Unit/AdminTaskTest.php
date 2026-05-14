<?php

namespace Tests\Unit;

use App\Models\AdminTask;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Settings con defaults para que calculateDueAt funcione
        Setting::set('sla_evaluation_business_days', 15, 'int', 'sla');
        Setting::set('sla_evaluation_express_business_days', 5, 'int', 'sla');
        Setting::set('sla_listing_calendar_days', 7, 'int', 'sla');
        Setting::set('sla_consulting_calendar_days', 7, 'int', 'sla');
        Setting::set('sla_orphan_calendar_days', 2, 'int', 'sla');
    }

    public function test_calculates_due_at_in_business_days_for_evaluation(): void
    {
        // Lunes 11/05/2026 09:00
        $from = Carbon::parse('2026-05-11 09:00');

        $due = AdminTask::calculateDueAt(AdminTask::TYPE_EVALUATE_JOURNAL, false, $from);

        // 15 días hábiles desde lunes 11 → viernes 29 (después de saltar 2 fines de semana)
        $this->assertSame('2026-06-01', $due->toDateString());
    }

    public function test_calculates_due_at_for_express(): void
    {
        $from = Carbon::parse('2026-05-11 09:00'); // lunes

        $due = AdminTask::calculateDueAt(AdminTask::TYPE_EVALUATE_JOURNAL, true, $from);

        // 5 días hábiles desde lunes 11 → lunes 18
        $this->assertSame('2026-05-18', $due->toDateString());
    }

    public function test_calculates_due_at_in_calendar_days_for_consulting(): void
    {
        $from = Carbon::parse('2026-05-11 09:00');

        $due = AdminTask::calculateDueAt(AdminTask::TYPE_CONSULTING, false, $from);

        $this->assertSame('2026-05-18', $due->toDateString());
    }

    public function test_lifecycle_pending_to_completed(): void
    {
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['journal' => 'Test'],
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);

        $this->assertTrue($task->isOpen());
        $this->assertFalse($task->isTerminal());

        $task->start();
        $this->assertSame(AdminTask::STATUS_IN_PROGRESS, $task->status);
        $this->assertNotNull($task->started_at);

        $task->complete('All done');
        $this->assertSame(AdminTask::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertStringContainsString('All done', $task->notes);

        // Idempotente: re-completar no cambia nada
        $oldCompletedAt = $task->completed_at;
        $task->complete();
        $this->assertEquals($oldCompletedAt, $task->fresh()->completed_at);
    }

    public function test_cancel_with_reason(): void
    {
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_CONSULTING,
            'title_key' => 'tasks.consulting',
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);

        $task->cancel('Pago reembolsado');

        $this->assertSame(AdminTask::STATUS_CANCELLED, $task->status);
        $this->assertSame('Pago reembolsado', $task->cancelled_reason);
        $this->assertNotNull($task->completed_at);
    }

    public function test_overdue_scope(): void
    {
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
            'due_at' => now()->subDays(3),
        ]);

        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
            'due_at' => now()->addDays(3),
        ]);

        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'status' => AdminTask::STATUS_COMPLETED,
            'priority' => AdminTask::PRIORITY_NORMAL,
            'due_at' => now()->subDays(10), // ya completed, no cuenta
        ]);

        $this->assertSame(1, AdminTask::overdue()->count());
    }

    public function test_work_url_routes_by_type(): void
    {
        $eval = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'related_type' => 'App\Models\Journal',
            'related_id' => 42,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        $this->assertSame('/admin/journals/42/evaluate', $eval->workUrl());

        $listing = AdminTask::create([
            'type' => AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
            'title_key' => 'tasks.review_listing_journal',
            'related_type' => 'App\Models\Journal',
            'related_id' => 7,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        $this->assertSame('/admin/journals/7/review-listing', $listing->workUrl());

        $book = AdminTask::create([
            'type' => AdminTask::TYPE_REVIEW_LISTING_BOOK,
            'title_key' => 'tasks.review_listing_book',
            'related_type' => 'App\Models\Book',
            'related_id' => 9,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        $this->assertSame('/admin/books/9/edit', $book->workUrl());

        $consulting = AdminTask::create([
            'type' => AdminTask::TYPE_CONSULTING,
            'title_key' => 'tasks.consulting',
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        $this->assertSame("/admin/admin-tasks/{$consulting->id}", $consulting->workUrl());
    }

    public function test_cancel_by_payment_cascades_open_tasks(): void
    {
        // Setup mínimo de un Payment real (FK constraint en payment_id).
        $user = User::create([
            'name' => 'Test',
            'email' => 'cancel-test@example.com',
            'password' => bcrypt('secret'),
        ]);
        $product = Product::create([
            'slug' => 'cancel-test-product',
            'price' => 99,
            'currency' => 'USD',
            'is_active' => true,
            'primary_locale' => 'es',
            'name' => ['es' => 'Test'],
        ]);
        $payment = Payment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'provider' => 'stripe',
            'transaction_id' => 'pi_test_cancel',
            'amount' => 99,
            'currency' => 'USD',
            'status' => 'completed',
            'payable_type' => Journal::class,
            'payable_id' => 1,
        ]);

        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'payment_id' => $payment->id,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        AdminTask::create([
            'type' => AdminTask::TYPE_CONSULTING,
            'title_key' => 'tasks.consulting',
            'payment_id' => $payment->id,
            'status' => AdminTask::STATUS_IN_PROGRESS,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);
        AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'payment_id' => $payment->id,
            'status' => AdminTask::STATUS_COMPLETED,
            'priority' => AdminTask::PRIORITY_NORMAL,
        ]);

        $cancelled = AdminTask::cancelByPayment($payment->id, 'Reembolso test');

        $this->assertSame(2, $cancelled);
        $this->assertSame(
            2,
            AdminTask::where('payment_id', $payment->id)->where('status', AdminTask::STATUS_CANCELLED)->count()
        );
    }
}
