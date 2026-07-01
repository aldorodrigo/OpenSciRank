{{--
    Sprint 3.7 #40 — hilo de mensajes reutilizable.
    Usado en /app/messages, /admin/conversations, y embebido en ViewAdminTask.
    Props: $conversation, $role ('editor'|'admin'), $messages, $authUser
--}}
<div wire:poll.visible.5s="refreshThread"
     x-data="{
         scrollToBottom() {
             this.$nextTick(() => {
                 const el = document.getElementById('thread-messages-{{ $conversation->id }}');
                 if (el) el.scrollTop = el.scrollHeight;
             });
         },
         dragOver: false,
     }"
     x-init="scrollToBottom()"
     @message-sent.window="scrollToBottom()"
     class="flex h-full flex-col">

    {{-- Header del hilo --}}
    <div class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
        <div class="min-w-0 flex-1">
            <h2 class="truncate text-base font-semibold text-gray-900 dark:text-white">{{ $conversation->subject }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($conversation->subject_type && $conversation->subjectModel)
                    @php $subjectLabel = class_basename($conversation->subject_type); @endphp
                    {{ $subjectLabel }}:
                    @if(method_exists($conversation->subjectModel, 'getTranslationWithFallback'))
                        {{ $conversation->subjectModel->getTranslationWithFallback('title') }}
                    @elseif(method_exists($conversation->subjectModel, 'renderedTitle'))
                        {{ $conversation->subjectModel->renderedTitle() }}
                    @endif
                    &mdash;
                @endif
                {{ $conversation->participants()->count() }} participante(s) &mdash;
                <span @class([
                    'font-medium',
                    'text-brand dark:text-blue-400' => $conversation->status === 'open',
                    'text-gray-400 dark:text-gray-500' => $conversation->status === 'closed',
                    'text-amber-600 dark:text-amber-400' => $conversation->status === 'archived',
                ])>
                    {{ match($conversation->status) {
                        'open'     => 'Abierto',
                        'closed'   => 'Cerrado',
                        'archived' => 'Archivado',
                        default    => $conversation->status,
                    } }}
                </span>
            </p>
        </div>

        {{-- Acciones del header (derecha) --}}
        <div class="ml-3 flex shrink-0 items-center gap-2">
            {{-- Roadmap #35 — acceso a la tarea del staff (evaluador/admin, no editor). --}}
            @php
                $taskUrl = $this->relatedTaskUrl();
            @endphp
            @if($taskUrl)
                <a href="{{ $taskUrl }}" target="_blank" rel="noopener"
                   class="flex items-center gap-1.5 rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                    {{ __('Ver tarea') }}
                </a>
            @endif
            @if(in_array($role, ['admin', 'evaluator'], true))
                @if($conversation->status === 'open')
                    <button wire:click="openCloseModal"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        {{ __('Cerrar') }}
                    </button>
                @elseif($conversation->status === 'closed')
                    <button wire:click="reopenConversation"
                            wire:confirm="¿Reabrir esta conversación?"
                            class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-brand hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400">
                        Reabrir
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('thread_error'))
        <div class="mx-4 mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/30 dark:text-red-300">
            {{ session('thread_error') }}
        </div>
    @endif
    @if(session('thread_info'))
        <div class="mx-4 mt-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-300">
            {{ session('thread_info') }}
        </div>
    @endif

    {{-- Mensajes --}}
    <div id="thread-messages-{{ $conversation->id }}"
         class="flex-1 overflow-y-auto space-y-4 p-4">

        @forelse($messages as $message)
            @php
                $isMine = $message->user_id === $authUser->id;
                $author = $message->user;
                $initials = collect(explode(' ', $author?->name ?? '??'))
                    ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                    ->take(2)
                    ->implode('');
                $timestamp = \App\Support\TimezoneHelper::format($message->created_at, $authUser);
            @endphp

            <div @class([
                'group flex items-end gap-2',
                'justify-end'  => $isMine,
                'justify-start' => !$isMine,
            ])>
                {{-- Avatar (izquierda, no mío) --}}
                @unless($isMine)
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-brand dark:bg-blue-900/50 dark:text-blue-300">
                        {{ $initials }}
                    </div>
                @endunless

                <div @class([
                    'max-w-[80%] min-w-0',
                    'items-end' => $isMine,
                ])>
                    {{-- Nombre + timestamp --}}
                    <p @class([
                        'mb-1 text-xs text-gray-500 dark:text-gray-400',
                        'text-right' => $isMine,
                    ])>
                        <span class="font-medium">{{ $author?->name ?? '—' }}</span>
                        &middot; {{ $timestamp }}
                    </p>

                    {{-- Burbuja del mensaje --}}
                    <div @class([
                        'rounded-2xl px-4 py-2.5 text-sm leading-relaxed break-words',
                        'rounded-br-sm bg-blue-100 text-blue-900 dark:bg-brand dark:text-white' => $isMine,
                        'rounded-bl-sm bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100'   => !$isMine,
                    ])>
                        {!! nl2br(e($message->body)) !!}

                        {{-- Sprint 3.7 #44: botón de pago embebido si aplica --}}
                        @if($message->payment_link_for_task_id && $message->paymentLinkTask)
                            @php
                                $linkTask = $message->paymentLinkTask;
                                $paymentUrl = $linkTask->paymentLinkUrl();
                                $linkProduct = $linkTask->title_params['product_id'] ?? null
                                    ? \App\Models\Product::find($linkTask->title_params['product_id'])
                                    : null;
                                $paidPayment = $linkTask->solicitedPayment;
                                $isPaid = $paidPayment && $paidPayment->status === 'completed';
                            @endphp

                            <div class="mt-3">
                                @if($isPaid)
                                    {{-- Pago completado: badge verde --}}
                                    <div class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        {{ __('Pago completado') }}
                                        @if($linkProduct)
                                            <span class="font-normal opacity-80">— {{ $linkProduct->getTranslationWithFallback('name') }}</span>
                                        @endif
                                    </div>
                                @elseif($paymentUrl && $linkProduct)
                                    {{-- Link vigente: botón clickeable --}}
                                    <a href="{{ $paymentUrl }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-brand shadow-sm ring-1 ring-blue-200 transition hover:bg-blue-50 hover:shadow dark:bg-gray-800 dark:text-blue-300 dark:ring-brand dark:hover:bg-blue-900/30">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span>
                                            {{ __('Pagar :name', ['name' => $linkProduct->getTranslationWithFallback('name')]) }}
                                            <span class="ml-1 font-bold">${{ number_format($linkProduct->price, 0) }} {{ $linkProduct->currency }}</span>
                                        </span>
                                        <svg class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @else
                                    {{-- Link expirado o producto eliminado --}}
                                    <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm italic text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        ⚠️ {{ __('Link de pago no disponible.') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Sprint 3.7 #44: badge si este mensaje generó tasks --}}
                    @if($message->derivedTasks->isNotEmpty())
                        <div @class([
                            'mt-1.5 flex flex-wrap gap-1.5 text-xs',
                            'justify-end' => $isMine,
                            'justify-start' => !$isMine,
                        ])>
                            @foreach($message->derivedTasks as $derivedTask)
                                <a href="{{ url('/admin/admin-tasks/'.$derivedTask->id) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 font-medium text-amber-800 transition hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60">
                                    📋 {{ __('Tarea #:id', ['id' => $derivedTask->id]) }}
                                    @if($derivedTask->status === 'completed')
                                        <span class="text-brand dark:text-blue-400">✓</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Sprint 3.7 #44: botón "Crear tarea" (solo admins, hover-reveal) --}}
                    @if($role === 'admin')
                        <div @class([
                            'mt-1 opacity-0 transition group-hover:opacity-100',
                            'text-right' => $isMine,
                            'text-left' => !$isMine,
                        ])>
                            <button type="button"
                                    wire:click="openCreateTaskModal({{ $message->id }})"
                                    class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs text-gray-500 transition hover:bg-amber-50 hover:text-amber-700 dark:text-gray-400 dark:hover:bg-amber-900/30 dark:hover:text-amber-300">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                {{ __('Crear tarea') }}
                            </button>
                        </div>
                    @endif

                    {{-- Adjuntos --}}
                    @if($message->attachments->isNotEmpty())
                        <div class="mt-2 space-y-1.5">
                            @foreach($message->attachments as $att)
                                @php
                                    $downloadUrl = URL::signedRoute('messages.attachment', $att);
                                @endphp

                                @if($att->isImage())
                                    {{-- Preview de imagen --}}
                                    <a href="{{ $downloadUrl }}" target="_blank"
                                       class="group block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600"
                                       title="{{ $att->original_name }}">
                                        <img src="{{ $downloadUrl }}"
                                             alt="{{ $att->original_name }}"
                                             class="max-h-48 w-full object-cover transition group-hover:opacity-90"
                                             loading="lazy">
                                        <div class="flex items-center justify-between bg-white/80 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800/80 dark:text-gray-400 backdrop-blur-sm">
                                            <span class="truncate">{{ $att->original_name }}</span>
                                            <span class="ml-2 shrink-0">{{ $att->humanSize() }}</span>
                                        </div>
                                    </a>
                                @else
                                    {{-- Card de archivo --}}
                                    <a href="{{ $downloadUrl }}"
                                       class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white/80 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800/60 dark:text-gray-300 dark:hover:bg-gray-700">
                                        {{-- Ícono según mime --}}
                                        @php
                                            $mimeIcon = match(true) {
                                                str_contains($att->mime_type, 'pdf')   => '📄',
                                                str_contains($att->mime_type, 'word')  => '📝',
                                                str_contains($att->mime_type, 'sheet') => '📊',
                                                str_contains($att->mime_type, 'presentation') => '📑',
                                                str_contains($att->mime_type, 'text') || str_contains($att->mime_type, 'csv') => '📃',
                                                default => '📎',
                                            };
                                        @endphp
                                        <span class="text-base" aria-hidden="true">{{ $mimeIcon }}</span>
                                        <span class="min-w-0 flex-1 truncate font-medium">{{ $att->original_name }}</span>
                                        <span class="shrink-0 text-gray-400">{{ $att->humanSize() }}</span>
                                        <svg class="h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Avatar (derecha, mío) --}}
                @if($isMine)
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white dark:bg-blue-500">
                        {{ $initials }}
                    </div>
                @endif
            </div>
        @empty
            <div class="flex h-full flex-col items-center justify-center py-16 text-center text-gray-400 dark:text-gray-600">
                <svg class="mb-3 h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                </svg>
                <p class="text-sm">Aún no hay mensajes en este hilo.</p>
                <p class="mt-1 text-xs">Sé el primero en escribir.</p>
            </div>
        @endforelse
    </div>

    {{-- Input de respuesta --}}
    @if($conversation->status === 'open')
        <div class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-900">

            {{-- Archivos pendientes --}}
            @if(!empty($pendingFiles))
                <div class="mb-3 flex flex-wrap gap-2">
                    @foreach($pendingFiles as $i => $file)
                        <div class="flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs text-brand dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            <span class="max-w-[120px] truncate">{{ $file->getClientOriginalName() }}</span>
                            <button type="button" wire:click="removePendingFile({{ $i }})"
                                    class="ml-1 text-blue-400 hover:text-brand dark:hover:text-blue-200">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            @error('newBody')
                <p class="mb-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @error('pendingFiles.*')
                <p class="mb-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex items-end gap-2 rounded-xl border bg-gray-50 p-2 transition dark:bg-gray-800"
                :class="dragOver
                    ? 'border-blue-400 ring-2 ring-blue-200 dark:ring-blue-800'
                    : 'border-gray-200 dark:border-gray-700'"
                @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false"
                @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; $wire.upload('pendingFiles', $refs.fileInput.files)">

                {{-- Textarea --}}
                <textarea wire:model="newBody"
                          rows="2"
                          placeholder="Escribí tu mensaje…"
                          class="flex-1 resize-none border-0 bg-transparent text-sm text-gray-900 placeholder-gray-400 focus:outline-none dark:text-gray-100 dark:placeholder-gray-500"
                          @keydown.ctrl.enter="$wire.send()"
                          @keydown.meta.enter="$wire.send()"></textarea>

                <div class="flex shrink-0 items-center gap-2">
                    {{-- Botón adjuntar --}}
                    <label class="cursor-pointer text-gray-400 hover:text-blue-500 dark:hover:text-blue-400" title="Adjuntar archivo">
                        <input type="file" multiple wire:model="pendingFiles" x-ref="fileInput" class="hidden"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.gif,.svg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                        </svg>
                    </label>

                    {{-- Botón enviar --}}
                    <button wire:click="send"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand text-white shadow transition hover:bg-blue-500 disabled:opacity-50"
                            title="Enviar (Ctrl+Enter)">
                        <svg wire:loading.remove class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        <svg wire:loading class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            <p class="mt-1.5 text-right text-xs text-gray-400 dark:text-gray-600">Ctrl+Enter para enviar · Max 10 MB por archivo</p>
        </div>
    @else
        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 text-center text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500">
            Esta conversación está cerrada y no acepta nuevos mensajes.
        </div>
    @endif

    {{-- ── Sprint 3.7 #44: Modal "Crear tarea desde mensaje" ─────────────── --}}
    {{-- ── Sprint 3.7 #45: Modal "Cerrar conversación" ─────────────── --}}
    @if(in_array($role, ['admin', 'evaluator'], true) && $showingCloseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4"
             wire:click.self="cancelCloseConversation">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800"
                 wire:click.stop>
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ __('Cerrar conversación') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Una vez cerrada, los participantes no podrán responder. Podés reabrirla después.') }}
                    </p>
                </div>

                <div class="space-y-4 p-6">
                    {{-- Toggle: enviar historial --}}
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="closeForm.send_history"
                               class="mt-0.5 rounded border-gray-300 text-brand focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                📧 {{ __('Enviar historial por email a los participantes') }}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Cada participante (editor + admin/evaluador) recibe un email con todos los mensajes cronológicamente y los adjuntos descargables por 30 días.') }}
                            </p>
                        </div>
                    </label>

                    {{-- Nota de cierre opcional, visible si toggle marcado --}}
                    @if($closeForm['send_history'])
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Nota de cierre (opcional)') }}
                            </label>
                            <textarea wire:model="closeForm.closing_note"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="{{ __('Comentario final del equipo que verá el editor al inicio del email…') }}"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <button wire:click="cancelCloseConversation"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ __('Cancelar') }}
                    </button>
                    <button wire:click="closeConversation"
                            wire:loading.attr="disabled"
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50">
                        <span wire:loading.remove wire:target="closeConversation">{{ __('Cerrar conversación') }}</span>
                        <span wire:loading wire:target="closeConversation">{{ __('Cerrando…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($role === 'admin' && $creatingTaskFromMessageId !== null)
        @php
            $sourceMessage = $conversation->messages->firstWhere('id', $creatingTaskFromMessageId)
                ?? $conversation->messages()->find($creatingTaskFromMessageId);
            $sourcePreview = $sourceMessage ? mb_substr(trim($sourceMessage->body), 0, 180) : '';
            if ($sourceMessage && mb_strlen($sourceMessage->body) > 180) $sourcePreview .= '…';
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4"
             wire:click.self="closeCreateTaskModal">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-800"
                 wire:click.stop>

                {{-- Header --}}
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        📋 {{ __('Crear tarea desde mensaje') }}
                    </h3>
                    <button type="button" wire:click="closeCreateTaskModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-5 p-6">
                    {{-- Mensaje origen --}}
                    @if($sourceMessage)
                        <div>
                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">💬 {{ __('Mensaje origen') }}</p>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $sourceMessage->user?->name ?? '—' }}</span>
                                    · {{ \App\Support\TimezoneHelper::format($sourceMessage->created_at, $authUser) }}
                                </p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $sourcePreview }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Tipo --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tipo de tarea') }}</label>
                        <select wire:model.live="taskForm.type"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="support">{{ __('Soporte (genérico)') }}</option>
                            <option value="evaluate_journal">{{ __('Evaluación de revista (cortesía)') }}</option>
                            <option value="reevaluate_journal">{{ __('Re-evaluación de revista (cortesía)') }}</option>
                            <option value="renewal_evaluation">{{ __('Renovación de sello (cortesía)') }}</option>
                            <option value="review_listing_journal">{{ __('Listado de revista') }}</option>
                            <option value="review_listing_book">{{ __('Listado de libro') }}</option>
                            <option value="consulting">{{ __('Consultoría') }}</option>
                        </select>
                        @error('taskForm.type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Warning si tipo != support --}}
                    @if($taskForm['type'] !== 'support')
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800/50 dark:bg-amber-900/20">
                            <p class="text-xs text-amber-800 dark:text-amber-300">
                                ⚠️ {{ __('Esta tarea normalmente se genera al confirmar un pago. Creala manualmente solo para cortesías institucionales o casos especiales.') }}
                            </p>
                            <textarea wire:model="taskForm.manual_reason"
                                      rows="2"
                                      placeholder="{{ __('Motivo de la creación manual (requerido)…') }}"
                                      class="mt-2 w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-amber-700 dark:bg-gray-900 dark:text-white"></textarea>
                            @error('taskForm.manual_reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    {{-- Título --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Título') }}</label>
                        <input type="text" wire:model="taskForm.title"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        @error('taskForm.title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Aplicar a (related) — solo si tipo requiere related --}}
                    @if(in_array($taskForm['type'], ['evaluate_journal','reevaluate_journal','renewal_evaluation','review_listing_journal','review_listing_book','consulting']))
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Aplicar a') }}</label>

                            @if(in_array($taskForm['type'], ['evaluate_journal','reevaluate_journal','renewal_evaluation','review_listing_journal']))
                                <select wire:model="taskForm.related_id"
                                        wire:change="$set('taskForm.related_type', '{{ \App\Models\Journal::class }}')"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    <option value="">— {{ __('Seleccionar revista del editor') }} —</option>
                                    @foreach($this->availableJournals as $journal)
                                        <option value="{{ $journal->id }}">{{ $journal->getTranslationWithFallback('title') }} (#{{ $journal->id }} · {{ $journal->status }})</option>
                                    @endforeach
                                </select>
                            @elseif($taskForm['type'] === 'review_listing_book')
                                <select wire:model="taskForm.related_id"
                                        wire:change="$set('taskForm.related_type', '{{ \App\Models\Book::class }}')"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    <option value="">— {{ __('Seleccionar libro del editor') }} —</option>
                                    @foreach($this->availableBooks as $book)
                                        <option value="{{ $book->id }}">{{ $book->getTranslationWithFallback('title') }} (#{{ $book->id }})</option>
                                    @endforeach
                                </select>
                            @elseif($taskForm['type'] === 'consulting')
                                <div class="space-y-2">
                                    <select wire:model.live="taskForm.related_type"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        <option value="">— {{ __('Tipo de consultoría') }} —</option>
                                        <option value="{{ \App\Models\Journal::class }}">{{ __('Sobre revista existente') }}</option>
                                        <option value="{{ \App\Models\User::class }}">{{ __('Pack Lanzamiento (nueva revista)') }}</option>
                                    </select>
                                    @if($taskForm['related_type'] === \App\Models\Journal::class)
                                        <select wire:model="taskForm.related_id"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                            <option value="">— {{ __('Seleccionar revista') }} —</option>
                                            @foreach($this->availableJournals as $journal)
                                                <option value="{{ $journal->id }}">{{ $journal->getTranslationWithFallback('title') }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($taskForm['related_type'] === \App\Models\User::class)
                                        @php $editor = $this->resolveEditorForCurrentContext ?? null; @endphp
                                        <p class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-brand dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ __('Se asociará al editor del hilo.') }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @error('taskForm.related_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('taskForm.related_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    {{-- Grid: Prioridad + Asignar + Vencimiento --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Prioridad') }}</label>
                            <select wire:model="taskForm.priority"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="low">{{ __('Baja') }}</option>
                                <option value="normal">{{ __('Normal') }}</option>
                                <option value="high">{{ __('Alta') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Asignar a') }}</label>
                            <select wire:model="taskForm.assigned_to"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                @foreach($this->adminUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->id === $authUser->id ? __('Yo').' ('.$u->name.')' : $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Vencimiento') }}</label>
                            <input type="date" wire:model="taskForm.due_at"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                    </div>

                    {{-- Notas internas opcionales --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Notas adicionales (opcional)') }}</label>
                        <textarea wire:model="taskForm.notes" rows="2"
                                  placeholder="{{ __('Información extra que ayude a procesar la tarea…') }}"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('El mensaje origen y el link al hilo se incluyen automáticamente.') }}</p>
                    </div>

                    {{-- Link de pago opcional --}}
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="taskForm.attach_payment_link"
                                   class="rounded border-gray-300 text-brand focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900">
                            💳 {{ __('Adjuntar link de pago') }}
                        </label>

                        @if($taskForm['attach_payment_link'])
                            <div class="mt-3 space-y-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Producto') }}</label>
                                    <select wire:model.live="taskForm.product_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        <option value="">— {{ __('Elegir producto') }} —</option>
                                        @foreach($this->activeProducts as $p)
                                            <option value="{{ $p->id }}">{{ $p->getTranslationWithFallback('name') }} — ${{ number_format($p->price, 0) }} {{ $p->currency }}</option>
                                        @endforeach
                                    </select>
                                    @error('taskForm.product_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                {{-- Sprint 3.7 #44: selector reactivo de payable según producto --}}
                                @if($taskForm['product_id'])
                                    @php
                                        $availablePayables = $this->paymentPayables;
                                        $payableLabel = $this->paymentPayableLabel;
                                    @endphp

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                            {{ __('Aplicar pago a (:resource)', ['resource' => $payableLabel]) }}
                                        </label>

                                        @if($availablePayables->count() === 0)
                                            {{-- Sin recursos disponibles → mensaje rojo + impide submit --}}
                                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                                                ⚠️ {{ __('El editor no tiene :resource registrado para este producto. Pedile que registre uno primero o elegí otro producto.', ['resource' => $payableLabel]) }}
                                            </div>
                                        @elseif($availablePayables->count() === 1)
                                            {{-- Solo 1 → auto-asignado, mostrar como confirmación --}}
                                            @php $only = $availablePayables->first(); @endphp
                                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                                ✓ {{ __('Se aplicará a:') }}
                                                <strong>
                                                    @if(method_exists($only, 'getTranslationWithFallback'))
                                                        {{ $only->getTranslationWithFallback('title') }}
                                                    @else
                                                        {{ $only->name ?? '#'.$only->id }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @else
                                            {{-- Varios → selector explícito --}}
                                            <select wire:model="taskForm.payment_payable_id"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                <option value="">— {{ __('Elegí cuál') }} —</option>
                                                @foreach($availablePayables as $pay)
                                                    <option value="{{ $pay->id }}">
                                                        @if(method_exists($pay, 'getTranslationWithFallback'))
                                                            {{ $pay->getTranslationWithFallback('title') }}
                                                        @else
                                                            {{ $pay->name ?? '#'.$pay->id }}
                                                        @endif
                                                        (#{{ $pay->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('taskForm.payment_payable_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        @endif
                                    </div>
                                @endif

                                <label class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" wire:model="taskForm.send_auto_message"
                                           class="mt-0.5 rounded border-gray-300 text-brand focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900">
                                    <span>{{ __('Enviar mensaje automático al editor con el link de pago') }}</span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="sticky bottom-0 flex justify-end gap-2 border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <button type="button" wire:click="closeCreateTaskModal"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ __('Cancelar') }}
                    </button>
                    @php
                        // Sprint 3.7 #44: disable submit si attach_payment_link y no hay payable disponible
                        $disableSubmit = ! empty($taskForm['attach_payment_link'])
                            && ! empty($taskForm['product_id'])
                            && $this->paymentPayables->count() === 0;
                    @endphp
                    <button type="button" wire:click="createTaskFromMessage"
                            wire:loading.attr="disabled"
                            @if($disableSubmit) disabled @endif
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="createTaskFromMessage">{{ __('Crear tarea') }}</span>
                        <span wire:loading wire:target="createTaskFromMessage">{{ __('Creando…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
