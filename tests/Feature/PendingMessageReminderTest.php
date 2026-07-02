<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\MessageThread;
use App\Models\Conversation;
use App\Models\Journal;
use App\Models\Message;
use App\Models\User;
use App\Notifications\PendingMessageReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Roadmap #35 — recordatorio "mensaje pendiente" al editor (evaluador/admin),
 * con reglas anti-spam (no-respuesta, cooldown, tope, muteo).
 */
class PendingMessageReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Conversation, 1: User, 2: User}
     */
    private function scenario(): array
    {
        $editor = User::factory()->create();
        $evaluator = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $editor->id,
            'title' => 'Journal',
            'slug' => 'j-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'submitted',
        ]);
        $conv = Conversation::create([
            'subject' => 'Consulta',
            'subject_type' => Journal::class,
            'subject_id' => $journal->id,
            'started_by_user_id' => $evaluator->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);
        $conv->addParticipant($evaluator, Conversation::ROLE_EVALUATOR);
        $conv->addParticipant($editor, Conversation::ROLE_EDITOR);
        // Último mensaje del evaluador → el editor "no contestó".
        Message::create([
            'conversation_id' => $conv->id,
            'user_id' => $evaluator->id,
            'body' => '¿Podés confirmar el ISSN?',
        ]);

        return [$conv, $editor, $evaluator];
    }

    private function remind(Conversation $conv, User $evaluator): void
    {
        Livewire::actingAs($evaluator)
            ->test(MessageThread::class, ['conversation' => $conv, 'role' => 'evaluator'])
            ->call('remindEditor');
    }

    public function test_evaluator_reminds_editor_and_records_it(): void
    {
        Notification::fake();
        [$conv, $editor, $evaluator] = $this->scenario();

        $this->remind($conv, $evaluator);

        Notification::assertSentTo($editor, PendingMessageReminder::class);
        $this->assertSame(1, (int) $conv->fresh()->reminder_count);
        $this->assertNotNull($conv->fresh()->last_reminder_at);
    }

    public function test_blocked_when_editor_already_replied(): void
    {
        [$conv, $editor] = $this->scenario();
        Message::create(['conversation_id' => $conv->id, 'user_id' => $editor->id, 'body' => 'Listo']);

        $this->assertSame('pending_message_reminder.blocked_replied', $conv->fresh()->reminderBlockReason($editor)['key']);
    }

    public function test_blocked_by_cooldown_reports_remaining_hours(): void
    {
        [$conv, $editor] = $this->scenario();
        $conv->update(['last_reminder_at' => now()->subHours(4), 'reminder_count' => 1]);

        $reason = $conv->fresh()->reminderBlockReason($editor);

        $this->assertSame('pending_message_reminder.blocked_cooldown', $reason['key']);
        // Default 24h de cooldown, pasaron 4 → quedan 20.
        $this->assertSame(20, $reason['params']['remaining']);
    }

    public function test_blocked_by_max_total_reports_the_cap(): void
    {
        [$conv, $editor] = $this->scenario();
        // Cooldown ya cumplido, pero alcanzó el tope.
        $conv->update(['last_reminder_at' => now()->subDays(2), 'reminder_count' => Conversation::REMINDER_MAX_TOTAL]);

        $reason = $conv->fresh()->reminderBlockReason($editor);

        $this->assertSame('pending_message_reminder.blocked_max', $reason['key']);
        $this->assertSame(Conversation::REMINDER_MAX_TOTAL, $reason['params']['max']);
    }

    public function test_blocked_when_editor_muted(): void
    {
        [$conv, $editor] = $this->scenario();
        $conv->participants()->where('user_id', $editor->id)->update(['email_muted' => true]);

        $this->assertSame('pending_message_reminder.blocked_muted', $conv->fresh()->reminderBlockReason($editor)['key']);
    }

    public function test_closed_thread_is_blocked(): void
    {
        [$conv, $editor] = $this->scenario();
        $conv->update(['status' => Conversation::STATUS_CLOSED]);

        $this->assertSame('pending_message_reminder.blocked_closed', $conv->fresh()->reminderBlockReason($editor)['key']);
    }

    public function test_editor_reply_resets_reminder_counter(): void
    {
        [$conv, $editor, $evaluator] = $this->scenario();
        $conv->update(['reminder_count' => 2, 'last_reminder_at' => now()]);

        Livewire::actingAs($editor)
            ->test(MessageThread::class, ['conversation' => $conv, 'role' => 'editor'])
            ->set('newBody', 'Ahí va el dato')
            ->call('send');

        $this->assertSame(0, (int) $conv->fresh()->reminder_count);
        $this->assertNull($conv->fresh()->last_reminder_at);
    }
}
