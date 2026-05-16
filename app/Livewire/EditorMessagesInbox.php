<?php

namespace App\Livewire;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Journal;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Notifications\NewConversationOpened;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Sprint 3.7 #40 — bandeja de mensajes del editor (/app/messages).
 *
 * Sidebar izquierdo: lista de conversaciones propias.
 * Panel derecho: MessageThread del hilo seleccionado.
 * Modal: "+ Nueva consulta".
 */
class EditorMessagesInbox extends Component
{
    use WithFileUploads;

    // ── Estado del sidebar ────────────────────────────────────────────

    /** ID de la conversación activa. */
    public ?int $activeConversationId = null;

    /** Filtro: all | unread | closed */
    public string $filter = 'all';

    /** Búsqueda por asunto. */
    public string $search = '';

    // ── Estado del modal "+ Nueva consulta" ───────────────────────────

    public bool $showNewModal = false;

    public string $newSubject = '';

    /** general | journal | book | task */
    public string $newAboutType = 'general';

    public ?int $newAboutId = null;

    public string $newBody = '';

    public array $newFiles = [];

    // ── Validation ───────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'newSubject' => 'required|string|max:200',
            'newAboutType' => 'required|in:general,journal,book,task',
            'newAboutId' => 'nullable|integer',
            'newBody' => 'required|string|max:10000',
            'newFiles' => 'array|max:5',
            'newFiles.*' => [
                'file',
                'max:'.(MessageAttachment::MAX_BYTES / 1024),
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,gif,svg',
            ],
        ];
    }

    // ── Lifecycle ────────────────────────────────────────────────────

    public function mount(): void
    {
        // Si se pasa un ID de conversación en la URL, seleccionarlo
        if (request()->route('conversation')) {
            $conv = Conversation::forUser(auth()->user())
                ->find(request()->route('conversation'));
            if ($conv) {
                $this->activeConversationId = $conv->id;
            }
        }
    }

    // ── Computed ─────────────────────────────────────────────────────

    #[Computed]
    public function conversations(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $query = Conversation::forUser($user)
            ->with(['messages' => fn ($q) => $q->latest()->limit(1), 'participants'])
            ->orderByDesc('last_message_at');

        if ($this->filter === 'closed') {
            $query->where('status', Conversation::STATUS_CLOSED);
        } elseif ($this->filter === 'unread') {
            $query->where('status', Conversation::STATUS_OPEN);
        } else {
            $query->whereIn('status', [Conversation::STATUS_OPEN, Conversation::STATUS_CLOSED]);
        }

        if ($this->search !== '') {
            $query->where('subject', 'like', '%'.$this->search.'%');
        }

        $conversations = $query->get();

        // Para filtro 'unread', filtrar en memoria los que tienen no leídos
        if ($this->filter === 'unread') {
            $conversations = $conversations->filter(
                fn (Conversation $c) => $c->unreadCountFor($user) > 0
            );
        }

        return $conversations;
    }

    #[Computed]
    public function totalUnread(): int
    {
        $user = auth()->user();

        return Conversation::forUser($user)
            ->where('status', Conversation::STATUS_OPEN)
            ->get()
            ->sum(fn (Conversation $c) => $c->unreadCountFor($user));
    }

    #[Computed]
    public function activeConversation(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversation::forUser(auth()->user())
            ->find($this->activeConversationId);
    }

    #[Computed]
    public function myJournals(): \Illuminate\Support\Collection
    {
        return Journal::where('user_id', auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    #[Computed]
    public function myBooks(): \Illuminate\Support\Collection
    {
        return Book::where('user_id', auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    #[Computed]
    public function myTasks(): \Illuminate\Support\Collection
    {
        // Consultorías del editor: tasks donde related es el journal del editor
        return AdminTask::where('type', AdminTask::TYPE_CONSULTING)
            ->whereHas('related', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereNotIn('status', AdminTask::STATUSES_TERMINAL)
            ->get(['id', 'title_key', 'title_params']);
    }

    // ── Actions ──────────────────────────────────────────────────────

    public function selectConversation(int $id): void
    {
        $conv = Conversation::forUser(auth()->user())->find($id);
        if ($conv) {
            $this->activeConversationId = $id;
        }
    }

    public function updatedSearch(): void
    {
        $this->activeConversationId = null;
        unset($this->conversations);
    }

    public function updatedFilter(): void
    {
        unset($this->conversations);
    }

    public function openNewModal(): void
    {
        $this->showNewModal = true;
        $this->resetNewForm();
    }

    public function closeNewModal(): void
    {
        $this->showNewModal = false;
        $this->resetNewForm();
    }

    private function resetNewForm(): void
    {
        $this->newSubject = '';
        $this->newAboutType = 'general';
        $this->newAboutId = null;
        $this->newBody = '';
        $this->newFiles = [];
        $this->resetValidation();
    }

    /**
     * Crear una nueva conversación con el primer mensaje y notificar al super_admin.
     */
    public function createConversation(): void
    {
        $this->validate();

        $user = auth()->user();

        // Resolver subject polimórfico
        [$subjectType, $subjectId] = $this->resolveSubjectMorph();

        // Crear conversación
        $conversation = Conversation::create([
            'subject' => $this->newSubject,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'started_by_user_id' => $user->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        // Agregar editor como participante
        $conversation->addParticipant($user, Conversation::ROLE_EDITOR);

        // Agregar super_admin(s) como participantes
        $superAdmins = User::role('super_admin')->get();
        foreach ($superAdmins as $admin) {
            $conversation->addParticipant($admin, Conversation::ROLE_ADMIN);
        }

        // Crear primer mensaje
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $this->newBody,
        ]);

        // Guardar adjuntos del primer mensaje
        foreach ($this->newFiles as $file) {
            $mime = $file->getMimeType();
            if (! in_array($mime, MessageAttachment::ALLOWED_MIME_TYPES, true)) {
                continue;
            }

            $path = $file->storeAs(
                'conversations/'.$conversation->id,
                \Str::uuid().'.'.$file->getClientOriginalExtension(),
                'local'
            );

            MessageAttachment::create([
                'message_id' => $message->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $mime,
                'size_bytes' => $file->getSize(),
                'uploaded_by_user_id' => $user->id,
            ]);
        }

        // Marcar leído por el autor
        $conversation->markReadBy($user);

        // Notificar a los super_admins — defensive try/catch
        foreach ($superAdmins as $admin) {
            try {
                $admin->notify(new NewConversationOpened($conversation));
            } catch (\Throwable $e) {
                Log::warning('NewConversationOpened not sent: '.$e->getMessage(), [
                    'conversation_id' => $conversation->id,
                ]);
            }
        }

        $this->showNewModal = false;
        $this->resetNewForm();
        $this->activeConversationId = $conversation->id;
        unset($this->conversations, $this->totalUnread);

        session()->flash('thread_info', 'Consulta enviada correctamente.');
    }

    /**
     * Resuelve el morph del subject según newAboutType/newAboutId.
     *
     * @return array{string|null, int|null}
     */
    private function resolveSubjectMorph(): array
    {
        return match ($this->newAboutType) {
            'journal' => $this->newAboutId
                ? [Journal::class, $this->newAboutId]
                : [null, null],
            'book' => $this->newAboutId
                ? [Book::class, $this->newAboutId]
                : [null, null],
            'task' => $this->newAboutId
                ? [AdminTask::class, $this->newAboutId]
                : [null, null],
            default => [null, null],
        };
    }

    // ── Render ───────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.editor-messages-inbox')
            ->layout('components.layouts.app', [
                'title' => 'Mensajes — Editorial Standards Platform',
            ]);
    }
}
