{{--
    Sprint 3.7 #40 — Bandeja de mensajes del editor (/app/messages).
    Sidebar izquierdo con lista de hilos + panel derecho con MessageThread.
--}}
<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 dark:bg-gray-950" style="height: calc(100vh - 4rem); display: flex; overflow: hidden;">

    {{-- ── Sidebar: lista de conversaciones ───────────────────────── --}}
    <div @class([
        'flex flex-col border-r border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900',
        'w-full md:w-80 lg:w-96 shrink-0',
        'hidden md:flex' => $activeConversationId,
        'flex' => !$activeConversationId,
    ])>

        {{-- Header sidebar --}}
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                        Mensajes
                        @if($this->totalUnread > 0)
                            <span class="ml-1.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-brand px-1.5 text-xs font-semibold text-white">
                                {{ $this->totalUnread }}
                            </span>
                        @endif
                    </h1>
                </div>
                <button wire:click="openNewModal"
                        class="flex items-center gap-1.5 rounded-lg bg-brand px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nueva consulta
                </button>
            </div>

            {{-- Búsqueda --}}
            <div class="mt-3">
                <input wire:model.live.debounce.300ms="search"
                       type="search"
                       placeholder="Buscar conversación…"
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500">
            </div>

            {{-- Filtros --}}
            <div class="mt-2 flex gap-1">
                @foreach(['all' => 'Todos', 'unread' => 'Sin leer', 'closed' => 'Cerrados'] as $key => $label)
                    <button wire:click="$set('filter', '{{ $key }}')"
                            @class([
                                'rounded-full px-3 py-1 text-xs font-medium transition',
                                'bg-blue-100 text-brand dark:bg-blue-900/40 dark:text-blue-300' => $filter === $key,
                                'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' => $filter !== $key,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Lista de conversaciones --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($this->conversations as $conv)
                @php
                    $unread = $conv->unreadCountFor(auth()->user());
                    $lastMsg = $conv->messages->first();
                    $snippet = $lastMsg ? mb_strimwidth(strip_tags($lastMsg->body), 0, 80, '…') : 'Sin mensajes aún';
                    $relTime = $conv->last_message_at
                        ? $conv->last_message_at->diffForHumans()
                        : '—';
                @endphp
                <button wire:click="selectConversation({{ $conv->id }})"
                        @class([
                            'w-full text-left px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-800',
                            'bg-blue-50 dark:bg-blue-900/20' => $activeConversationId === $conv->id,
                        ])>
                    <div class="flex items-start justify-between gap-2">
                        <p @class([
                            'truncate text-sm font-semibold',
                            'text-gray-900 dark:text-white' => $unread > 0,
                            'text-gray-600 dark:text-gray-300' => $unread === 0,
                        ])>
                            {{ $conv->subject }}
                        </p>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ $relTime }}</span>
                            @if($unread > 0)
                                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-brand px-1 text-xs font-bold text-white">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ $snippet }}</p>
                    @if($conv->status !== 'open')
                        <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            {{ $conv->status === 'closed' ? 'Cerrado' : 'Archivado' }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <svg class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No tenés conversaciones todavía.</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">¿Tenés alguna duda?</p>
                    <button wire:click="openNewModal"
                            class="mt-4 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">
                        + Nueva consulta
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ── Panel derecho: hilo activo ──────────────────────────────── --}}
    <div @class([
        'flex flex-1 flex-col min-w-0',
        'hidden md:flex' => !$activeConversationId,
        'flex' => $activeConversationId,
    ])>
        @if($this->activeConversation)
            {{-- Botón "Volver" en mobile --}}
            <div class="flex items-center border-b border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900 md:hidden">
                <button wire:click="$set('activeConversationId', null)"
                        class="flex items-center gap-1.5 text-sm font-medium text-brand dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Volver
                </button>
            </div>

            {{-- Roadmap #35 — acceso al recurso del hilo (revista / libro / consultoría). --}}
            @if($this->activeResourceLink)
                <a href="{{ $this->activeResourceLink['url'] }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-brand transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-blue-400 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                    {{ $this->activeResourceLink['label'] }}
                </a>
            @endif

            <div class="flex flex-1 flex-col overflow-hidden">
                @livewire('message-thread', [
                    'conversation' => $this->activeConversation,
                    'role'         => $this->threadRole,
                ], key('thread-'.$this->activeConversation->id))
            </div>
        @else
            {{-- Empty state panel derecho --}}
            <div class="hidden flex-1 flex-col items-center justify-center md:flex">
                <svg class="mb-3 h-12 w-12 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Seleccioná una conversación</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">o creá una nueva consulta</p>
            </div>
        @endif
    </div>

    {{-- ── Modal: Nueva consulta ────────────────────────────────────── --}}
    @if($showNewModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
             x-data x-on:keydown.escape.window="$wire.closeNewModal()">
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeNewModal"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-lg rounded-t-2xl bg-white shadow-2xl dark:bg-gray-900 sm:rounded-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nueva consulta</h3>
                    <button wire:click="closeNewModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Formulario --}}
                <div class="space-y-4 px-5 py-4">

                    {{-- Asunto --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Asunto <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="newSubject" type="text" maxlength="200"
                               placeholder="Breve descripción de tu consulta"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @error('newSubject')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sobre qué --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sobre qué
                        </label>
                        <select wire:model.live="newAboutType"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="general">Pregunta general</option>
                            <option value="journal">Una de mis revistas</option>
                            <option value="book">Uno de mis libros</option>
                            <option value="task">Una de mis consultorías</option>
                        </select>
                    </div>

                    {{-- Sub-select según tipo --}}
                    @if($newAboutType === 'journal')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Revista</label>
                            <select wire:model="newAboutId"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">Seleccioná una revista</option>
                                @foreach($this->myJournals as $j)
                                    <option value="{{ $j->id }}">{{ $j->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($newAboutType === 'book')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Libro</label>
                            <select wire:model="newAboutId"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">Seleccioná un libro</option>
                                @foreach($this->myBooks as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($newAboutType === 'task')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultoría</label>
                            <select wire:model="newAboutId"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">Seleccioná una consultoría</option>
                                @foreach($this->myTasks as $t)
                                    <option value="{{ $t->id }}">{{ $t->renderedTitle() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Primer mensaje --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mensaje <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="newBody" rows="4" maxlength="10000"
                                  placeholder="Describí tu consulta con el mayor detalle posible…"
                                  class="w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
                        @error('newBody')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Adjuntos opcionales --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Adjuntos (opcional)
                        </label>
                        <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-4 text-center transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-brand">
                            <input type="file" multiple wire:model="newFiles" class="hidden"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.gif,.svg">
                            <svg class="mb-1.5 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32"/>
                            </svg>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Arrastrá archivos o hacé clic para seleccionar</p>
                            <p class="mt-0.5 text-xs text-gray-300 dark:text-gray-600">PDF, Word, Excel, imágenes — máx. 10 MB c/u</p>
                        </label>
                        @if(!empty($newFiles))
                            <ul class="mt-2 space-y-1">
                                @foreach($newFiles as $f)
                                    <li class="text-xs text-gray-600 dark:text-gray-400">{{ $f->getClientOriginalName() }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @error('newFiles.*')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                    <button wire:click="closeNewModal"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="createConversation"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            class="flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                        <span wire:loading.remove wire:target="createConversation">Enviar consulta</span>
                        <span wire:loading wire:target="createConversation">Enviando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
