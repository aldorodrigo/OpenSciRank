<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\EditorMessagesInbox;
use App\Models\AdminTask;
use App\Models\Conversation;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Roadmap #35 — en /app/messages, el hilo activo muestra un link al recurso
 * (revista / libro / consultoría). Consultas generales no muestran link.
 */
class EditorMessagesResourceLinkTest extends TestCase
{
    use RefreshDatabase;

    private function conversationFor(User $editor, string $subjectType, ?int $subjectId): Conversation
    {
        $conv = Conversation::create([
            'subject' => 'Hilo',
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'started_by_user_id' => $editor->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);
        $conv->addParticipant($editor, Conversation::ROLE_EDITOR);

        return $conv;
    }

    public function test_journal_conversation_links_to_public_page(): void
    {
        $editor = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $editor->id,
            'title' => 'My Journal',
            'slug' => 'my-journal-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
        ]);
        $conv = $this->conversationFor($editor, Journal::class, $journal->id);

        Livewire::actingAs($editor)
            ->test(EditorMessagesInbox::class)
            ->set('activeConversationId', $conv->id)
            ->assertSee(route('journal.show', ['slug' => $journal->slug]));
    }

    public function test_consulting_task_conversation_links_to_consulting_panel(): void
    {
        $editor = User::factory()->create();
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_CONSULTING,
            'title_key' => 'tasks.consulting',
            'related_type' => User::class,
            'related_id' => $editor->id,
            'status' => AdminTask::STATUS_PENDING,
        ]);
        $conv = $this->conversationFor($editor, AdminTask::class, $task->id);

        Livewire::actingAs($editor)
            ->test(EditorMessagesInbox::class)
            ->set('activeConversationId', $conv->id)
            ->assertSee(route('app.consulting'));
    }

    public function test_inbox_treats_evaluator_participant_as_evaluator_and_shows_task_link(): void
    {
        $editor = User::factory()->create();
        $evaluator = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $editor->id,
            'title' => 'Assigned Journal',
            'slug' => 'assigned-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'submitted',
        ]);
        $task = AdminTask::create([
            'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
            'title_key' => 'tasks.evaluate_journal',
            'title_params' => ['name' => $journal->title],
            'related_type' => Journal::class,
            'related_id' => $journal->id,
            'status' => AdminTask::STATUS_IN_PROGRESS,
            'assigned_to' => $evaluator->id,
        ]);
        $conv = $this->conversationFor($evaluator, Journal::class, $journal->id);
        // El hilo lo abrió el evaluador → participa como ROLE_EVALUATOR.
        $conv->participants()->where('user_id', $evaluator->id)
            ->update(['role' => Conversation::ROLE_EVALUATOR]);

        Livewire::actingAs($evaluator)
            ->test(EditorMessagesInbox::class)
            ->set('activeConversationId', $conv->id)
            ->assertSee(url('/admin/admin-tasks/'.$task->id));
    }

    public function test_general_conversation_has_no_resource_link(): void
    {
        $editor = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $editor->id,
            'title' => 'Other',
            'slug' => 'other-'.uniqid(),
            'primary_locale' => 'es',
            'status' => 'evaluated',
        ]);
        // Consulta general: sin subject.
        $conv = $this->conversationFor($editor, '', null);

        Livewire::actingAs($editor)
            ->test(EditorMessagesInbox::class)
            ->set('activeConversationId', $conv->id)
            ->assertDontSee(route('journal.show', ['slug' => $journal->slug]));
    }
}
