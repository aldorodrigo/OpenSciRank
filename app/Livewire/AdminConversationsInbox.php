<?php

namespace App\Livewire;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Sprint 3.7 #40 — bandeja de conversaciones para admin (/admin/conversations).
 *
 * Muestra TODAS las conversaciones del sistema. Permite filtrar, buscar,
 * cerrar, archivar y asignar hilos.
 */
class AdminConversationsInbox extends Component
{
    // ── Estado del sidebar ────────────────────────────────────────────

    /** ID de la conversación activa. */
    public ?int $activeConversationId = null;

    /**
     * Sprint 3.7 #44 — pre-seleccionar la conversation si vino en el URL
     * (ej. /admin/conversations/42). Sin esto, el route param se ignoraba
     * y la página abría el inbox vacío en vez de mostrar el hilo.
     *
     * También auto-agrega al admin como participant para acceso al hilo
     * (mismo behavior que selectConversation).
     */
    public function mount(?Conversation $conversation = null): void
    {
        if ($conversation && $conversation->exists) {
            $conversation->addParticipant(auth()->user(), Conversation::ROLE_ADMIN);
            $this->activeConversationId = $conversation->id;
        }
    }

    /** Filtro de estado: all | open | closed | archived */
    public string $statusFilter = 'open';

    /** Filtro por tipo de subject. */
    public string $subjectTypeFilter = 'all';

    /** Búsqueda por asunto o participante. */
    public string $search = '';

    // ── Computed ─────────────────────────────────────────────────────

    #[Computed]
    public function conversations(): \Illuminate\Support\Collection
    {
        $query = Conversation::with([
            'startedBy',
            'messages' => fn ($q) => $q->latest()->limit(1),
            'participants',
        ])->orderByDesc('last_message_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->subjectTypeFilter !== 'all') {
            if ($this->subjectTypeFilter === 'general') {
                $query->whereNull('subject_type');
            } else {
                $morphMap = [
                    'journal' => Journal::class,
                    'book' => Book::class,
                    'task' => AdminTask::class,
                ];
                if (isset($morphMap[$this->subjectTypeFilter])) {
                    $query->where('subject_type', $morphMap[$this->subjectTypeFilter]);
                }
            }
        }

        if ($this->search !== '') {
            $query->where('subject', 'like', '%'.$this->search.'%');
        }

        return $query->get();
    }

    #[Computed]
    public function totalUnread(): int
    {
        $user = auth()->user();

        return Conversation::where('status', Conversation::STATUS_OPEN)
            ->get()
            ->sum(fn (Conversation $c) => $c->unreadCountFor($user));
    }

    #[Computed]
    public function activeConversation(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversation::find($this->activeConversationId);
    }

    // ── Actions ──────────────────────────────────────────────────────

    public function selectConversation(int $id): void
    {
        $conversation = Conversation::find($id);
        if (! $conversation) {
            return;
        }

        // Auto-join admin si no era participante
        $conversation->addParticipant(auth()->user(), Conversation::ROLE_ADMIN);

        $this->activeConversationId = $id;
    }

    public function updatedSearch(): void
    {
        unset($this->conversations);
    }

    public function updatedStatusFilter(): void
    {
        unset($this->conversations);
    }

    public function updatedSubjectTypeFilter(): void
    {
        unset($this->conversations);
    }

    /** Cerrar la conversación activa. */
    public function closeConversation(int $id): void
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->update([
            'status' => Conversation::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        unset($this->conversations, $this->activeConversation);
        session()->flash('thread_info', 'Conversación cerrada.');
    }

    /** Archivar la conversación activa. */
    public function archiveConversation(int $id): void
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->update([
            'status' => Conversation::STATUS_ARCHIVED,
            'closed_at' => now(),
        ]);

        $this->activeConversationId = null;
        unset($this->conversations, $this->activeConversation);
        session()->flash('thread_info', 'Conversación archivada.');
    }

    /** Reabrir conversación cerrada. */
    public function reopenConversation(int $id): void
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->update([
            'status' => Conversation::STATUS_OPEN,
            'closed_at' => null,
        ]);

        unset($this->conversations, $this->activeConversation);
        session()->flash('thread_info', 'Conversación reabierta.');
    }

    // ── Render ───────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin-conversations-inbox')
            ->layout('components.layouts.app', [
                'title' => 'Conversaciones — Admin',
            ]);
    }
}
