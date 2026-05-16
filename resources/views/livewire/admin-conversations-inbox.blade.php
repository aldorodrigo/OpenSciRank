{{--
    Sprint 3.7 #40 — Bandeja de conversaciones admin (/admin/conversations).
    Todas las conversaciones del sistema. Filtros avanzados.
    Layout standalone con site-header (fuera de Filament).
--}}
<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 dark:bg-gray-950" style="height: calc(100vh - 4rem); display: flex; overflow: hidden;">

    {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
    <div @class([
        'flex flex-col border-r border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 shrink-0',
        'w-full md:w-80 lg:w-96',
        'hidden md:flex' => $activeConversationId,
        'flex' => !$activeConversationId,
    ])>

        {{-- Header --}}
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                    Conversaciones
                    @if($this->totalUnread > 0)
                        <span class="ml-1.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white">
                            {{ $this->totalUnread }}
                        </span>
                    @endif
                </h1>
                <a href="/admin" class="text-xs text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                    Volver al admin
                </a>
            </div>

            {{-- Búsqueda --}}
            <div class="mt-3">
                <input wire:model.live.debounce.300ms="search"
                       type="search"
                       placeholder="Buscar por asunto…"
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500">
            </div>

            {{-- Filtro estado --}}
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach(['all' => 'Todos', 'open' => 'Abiertos', 'closed' => 'Cerrados', 'archived' => 'Archivados'] as $key => $label)
                    <button wire:click="$set('statusFilter', '{{ $key }}')"
                            @class([
                                'rounded-full px-2.5 py-1 text-xs font-medium transition',
                                'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' => $statusFilter === $key,
                                'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' => $statusFilter !== $key,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Filtro tipo de subject --}}
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach(['all' => 'Todos', 'general' => 'General', 'journal' => 'Revista', 'book' => 'Libro', 'task' => 'Tarea'] as $key => $label)
                    <button wire:click="$set('subjectTypeFilter', '{{ $key }}')"
                            @class([
                                'rounded-full px-2.5 py-1 text-xs font-medium transition',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $subjectTypeFilter === $key,
                                'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' => $subjectTypeFilter !== $key,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Lista --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($this->conversations as $conv)
                @php
                    $unread = $conv->unreadCountFor(auth()->user());
                    $lastMsg = $conv->messages->first();
                    $snippet = $lastMsg ? mb_strimwidth(strip_tags($lastMsg->body), 0, 80, '…') : 'Sin mensajes';
                    $relTime = $conv->last_message_at?->diffForHumans() ?? '—';
                    $starter = $conv->startedBy;
                @endphp
                <button wire:click="selectConversation({{ $conv->id }})"
                        @class([
                            'w-full text-left px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-800',
                            'bg-indigo-50 dark:bg-indigo-900/20' => $activeConversationId === $conv->id,
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
                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ $relTime }}</span>
                            @if($unread > 0)
                                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Iniciado por: {{ $starter?->name ?? '—' }}</p>
                    <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ $snippet }}</p>
                    @if($conv->status !== 'open')
                        <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            {{ match($conv->status) { 'closed' => 'Cerrado', 'archived' => 'Archivado', default => $conv->status } }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <p class="text-sm text-gray-400">No hay conversaciones con estos filtros.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ── Panel derecho ────────────────────────────────────────────── --}}
    <div @class([
        'flex flex-1 flex-col min-w-0',
        'hidden md:flex' => !$activeConversationId,
        'flex' => $activeConversationId,
    ])>
        @if($this->activeConversation)
            {{-- Botón volver mobile --}}
            <div class="flex items-center gap-3 border-b border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900 md:hidden">
                <button wire:click="$set('activeConversationId', null)"
                        class="flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Volver
                </button>
            </div>

            {{-- Acciones de admin encima del hilo --}}
            <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
                <span class="text-xs text-gray-400 dark:text-gray-500">Acciones:</span>
                @if($this->activeConversation->status === 'open')
                    <button wire:click="closeConversation({{ $this->activeConversation->id }})"
                            wire:confirm="¿Cerrar esta conversación?"
                            class="rounded-md bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                        Cerrar
                    </button>
                    <button wire:click="archiveConversation({{ $this->activeConversation->id }})"
                            wire:confirm="¿Archivar esta conversación? Quedará oculta por defecto."
                            class="rounded-md bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400">
                        Archivar
                    </button>
                @elseif($this->activeConversation->status === 'closed')
                    <button wire:click="reopenConversation({{ $this->activeConversation->id }})"
                            wire:confirm="¿Reabrir esta conversación?"
                            class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400">
                        Reabrir
                    </button>
                @endif
            </div>

            <div class="flex flex-1 flex-col overflow-hidden">
                @livewire('message-thread', [
                    'conversation' => $this->activeConversation,
                    'role'         => 'admin',
                ], key('admin-thread-'.$this->activeConversation->id))
            </div>
        @else
            <div class="hidden flex-1 flex-col items-center justify-center md:flex">
                <svg class="mb-3 h-12 w-12 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Seleccioná una conversación</p>
            </div>
        @endif
    </div>

</div>
