<x-slot:header>true</x-slot:header>

<div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-950"
     x-data="{
         rejectModal: false,
         historyExpanded: false,
         hireModal: @entangle('showHireModal'),
         rejectModalOpen: @entangle('rejectTaskId').live
     }">
    <div class="container mx-auto px-4">

        {{-- ── Volver al dashboard ────────────────────────────────── --}}
        <a href="{{ route('app.dashboard') }}"
           wire:navigate
           class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al dashboard
        </a>

        {{-- ── Encabezado ─────────────────────────────────────────── --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mis Consultorías</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestiona tus sesiones de asesoría editorial.
                </p>
            </div>
            <button
                type="button"
                wire:click="$set('showHireModal', true)"
                class="inline-flex items-center gap-2 rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-brand shadow-sm transition hover:bg-blue-50 dark:border-brand dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-blue-950/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Contratar nueva consultoría
            </button>
        </div>

        {{-- ── Toast flash ────────────────────────────────────────── --}}
        @if(session('toast'))
        @php $toast = session('toast'); @endphp
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 6000)"
            x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm shadow
                {{ $toast['type'] === 'success' ? 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-200' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200' }}">
            @if($toast['type'] === 'success')
                <svg class="h-5 w-5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @else
                <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            @endif
            <span>{{ $toast['message'] }}</span>
            <button @click="show = false" class="ml-auto text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
        @endif

        {{-- ── Empty state ─────────────────────────────────────────── --}}
        @if($tasks->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-900">
            <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Todavía no tenés consultorías</h3>
            <p class="mt-2 max-w-xs text-sm text-gray-500 dark:text-gray-400">
                Contratá una sesión de asesoría editorial personalizada para llevar tu revista o proyecto al siguiente nivel.
            </p>
            <button
                type="button"
                wire:click="$set('showHireModal', true)"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Contratar consultoría
            </button>
        </div>
        @else

        {{-- ══════════════════════════════════════════════════════════
             SECCIÓN A — Acción requerida
        ══════════════════════════════════════════════════════════════ --}}
        @if($actionTasks->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">{{ $actionCount }}</span>
                Acción requerida
            </h2>

            <div class="space-y-4">
                @foreach($actionTasks as $task)
                <div class="rounded-2xl border-2 border-blue-300 bg-blue-50 p-6 shadow-sm dark:border-brand dark:bg-blue-950/30">
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center rounded-full bg-brand px-2.5 py-0.5 text-xs font-semibold text-white">Acción requerida</span>
                            <h3 class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $task->renderedTitle() }}</h3>
                            @if($task->payment?->product)
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $task->payment->product->getTranslationWithFallback('name') }}</p>
                            @endif
                        </div>
                        @if($task->activeProposals->isNotEmpty() && $task->activeProposals->first()?->expires_at)
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            Expira el {{ $this->formatDate($task->activeProposals->first()->expires_at, 'd/m/Y') }}
                        </p>
                        @endif
                    </div>

                    <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tu evaluador propuso los siguientes horarios. Elegí el que más te convenga:
                    </p>

                    @if($task->activeProposals->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay propuestas activas disponibles en este momento.</p>
                    @else
                    <div class="space-y-2">
                        @foreach($task->activeProposals as $proposal)
                        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-blue-200 bg-white px-4 py-3 dark:border-blue-800 dark:bg-gray-900">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $this->formatDate($proposal->proposed_slot) }}
                                </p>
                                @if($proposal->notes)
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $proposal->notes }}</p>
                                @endif
                            </div>
                            <button
                                type="button"
                                wire:click="acceptProposal({{ $task->id }}, {{ $proposal->id }})"
                                wire:confirm="¿Confirmar esta sesión para {{ $this->formatDate($proposal->proposed_slot) }}?"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Acepto este
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="mt-4 border-t border-blue-200 pt-4 dark:border-blue-800">
                        <button
                            type="button"
                            wire:click="openRejectModal({{ $task->id }})"
                            class="text-sm text-gray-500 underline decoration-dotted hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Ninguno me sirve — pedir otras fechas
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             SECCIÓN B — Activas
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTasks->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Activas</h2>

            <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2">
                @foreach($activeTasks as $task)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    {{-- Badge de estado --}}
                    <div class="mb-3 flex items-center justify-between gap-2">
                        @php
                            $badgeClass = match($task->status) {
                                'pending'     => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
                                'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'scheduled'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                'in_session'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                default       => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
                            };
                            $badgeLabel = match($task->status) {
                                'pending'     => 'Pendiente',
                                'in_progress' => 'En proceso',
                                'scheduled'   => 'Agendada',
                                'in_session'  => 'En sesión',
                                default       => $task->status,
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        @if($task->assignee)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $task->assignee->name }}</span>
                        @endif
                    </div>

                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $task->renderedTitle() }}</h3>
                    @if($task->payment?->product)
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $task->payment->product->getTranslationWithFallback('name') }}</p>
                    @endif

                    {{-- Mensaje según estado --}}
                    @if(in_array($task->status, ['pending', 'in_progress']))
                    <p class="mt-3 rounded-lg bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        Esperando que tu evaluador proponga fechas. Tiempo estimado: 3 días hábiles.
                    </p>
                    @endif

                    {{-- Detalles cuando está agendada --}}
                    @if($task->status === 'scheduled' && $task->scheduled_for)
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="font-medium">{{ $this->formatDate($task->scheduled_for) }}</span>
                        </div>

                        {{-- Acciones --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            {{-- Descargar .ics --}}
                            <a
                                href="{{ route('app.consulting.ics', $task) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Descargar .ics
                            </a>

                            {{-- Mensajes --}}
                            @if($task->conversation)
                            <a
                                href="{{ route('app.messages', ['conversation' => $task->conversation->id]) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Mensajes
                            </a>
                            @endif

                            {{-- Unirse a la sesión --}}
                            @if($task->consulting_meet_url)
                            <a
                                href="{{ $task->consulting_meet_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-500">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.89L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Unirse a la sesión
                            </a>
                            @endif

                            {{-- Reagendar --}}
                            @php
                                $canReschedule = $this->canReschedule($task);
                                $tooltip = $this->rescheduleTooltip($task);
                            @endphp
                            <button
                                type="button"
                                @if(!$canReschedule) disabled title="{{ $tooltip }}" @endif
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition
                                    {{ $canReschedule
                                        ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-300'
                                        : 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-600' }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reagendar
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- En sesión --}}
                    @if($task->status === 'in_session' && $task->consulting_meet_url)
                    <div class="mt-3">
                        <a
                            href="{{ $task->consulting_meet_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.89L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Unirse a la sesión en curso
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             SECCIÓN C — Historial
        ══════════════════════════════════════════════════════════════ --}}
        @if($historyTasks->isNotEmpty())
        <section>
            <button
                type="button"
                @click="historyExpanded = !historyExpanded"
                class="mb-4 flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="h-4 w-4 transition-transform" :class="historyExpanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                Historial ({{ $historyTasks->count() }})
            </button>

            <div x-show="historyExpanded" x-transition class="space-y-3">
                @foreach($historyTasks as $task)
                <div
                    x-data="{ notesOpen: false }"
                    class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $task->renderedTitle() }}</h3>
                            @if($task->scheduled_for)
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Sesión: {{ $this->formatDate($task->scheduled_for) }}
                            </p>
                            @endif
                            @if($task->assignee)
                            <p class="text-xs text-gray-500 dark:text-gray-400">Evaluador: {{ $task->assignee->name }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $historyBadge = $task->status === 'completed'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
                                $historyLabel = $task->status === 'completed' ? 'Completada' : 'Cancelada';
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $historyBadge }}">{{ $historyLabel }}</span>

                            @if($task->client_visible_notes)
                            <button
                                type="button"
                                @click="notesOpen = true"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Ver resumen
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Modal resumen del evaluador --}}
                    @if($task->client_visible_notes)
                    <div
                        x-show="notesOpen"
                        x-transition
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        style="display:none;">
                        <div class="absolute inset-0 bg-black/50" @click="notesOpen = false"></div>
                        <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">Resumen del evaluador</h4>
                                <button @click="notesOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="prose prose-sm max-w-none text-gray-700 dark:prose-invert dark:text-gray-300">
                                {!! nl2br(e($task->client_visible_notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @endif

        @endif {{-- fin de @else (tasks no vacías) --}}
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         MODAL — Contratar nueva consultoría
    ═══════════════════════════════════════════════════════════════ --}}
    @if($showHireModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-data
        x-show="$wire.showHireModal"
        x-transition>
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showHireModal', false)"></div>
        <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Contratar nueva consultoría</h3>
                <button wire:click="$set('showHireModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">

                {{-- Opción 1: Action Plan + Consultoría --}}
                @php
                    $hasEvaluatedJournal = \App\Models\Journal::where('user_id', auth()->id())
                        ->whereIn('status', ['evaluated', 'certified'])
                        ->exists();
                    $actionPlanProduct = \App\Models\Product::where('slug', 'action-plan-consulting')->where('is_active', true)->first();
                @endphp

                <div class="flex flex-col rounded-xl border-2 {{ $hasEvaluatedJournal ? 'border-blue-300 bg-blue-50 dark:border-brand dark:bg-blue-950/20' : 'border-gray-200 bg-gray-50 opacity-60 dark:border-gray-700 dark:bg-gray-800' }} p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Plan de Acción + Consultoría</h4>
                        <span class="text-lg font-bold text-brand dark:text-blue-400">
                            ${{ $actionPlanProduct ? number_format($actionPlanProduct->price, 0) : '215' }} USD
                        </span>
                    </div>
                    <p class="mb-4 flex-1 text-sm text-gray-600 dark:text-gray-400">
                        Sesión personalizada de consultoría editorial junto al plan de acción de tu revista. Requiere tener una revista evaluada o certificada.
                    </p>
                    @if($hasEvaluatedJournal)
                        @php
                            $evaluatedJournal = \App\Models\Journal::where('user_id', auth()->id())
                                ->whereIn('status', ['evaluated', 'certified'])
                                ->first();
                        @endphp
                        <a
                            href="{{ route('app.checkout', $evaluatedJournal) }}"
                            class="mt-auto inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">
                            Comprar
                        </a>
                    @else
                        <span class="mt-auto inline-flex items-center justify-center rounded-lg bg-gray-300 px-4 py-2 text-sm font-semibold text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400">
                            Requiere revista evaluada
                        </span>
                    @endif
                </div>

                {{-- Opción 2: Pack Lanzamiento Editorial --}}
                @php
                    $newJournalProduct = \App\Models\Product::where('slug', 'new-journal-consulting')->where('is_active', true)->first();
                @endphp
                <div class="flex flex-col rounded-xl border-2 border-blue-300 bg-blue-50 p-5 dark:border-brand dark:bg-blue-950/20">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Pack Lanzamiento Editorial</h4>
                        <span class="text-lg font-bold text-brand dark:text-blue-400">
                            ${{ $newJournalProduct ? number_format($newJournalProduct->price, 0) : '1500' }} USD
                        </span>
                    </div>
                    <p class="mb-4 flex-1 text-sm text-gray-600 dark:text-gray-400">
                        Consultoría completa para lanzar o relanzar tu proyecto editorial. Disponible sin necesitar una revista activa.
                    </p>
                    @if($newJournalProduct)
                    <a
                        href="{{ route('app.consulting.checkout.new') }}"
                        class="mt-auto inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">
                        Comprar
                    </a>
                    @else
                    <span class="mt-auto inline-flex items-center justify-center rounded-lg bg-gray-300 px-4 py-2 text-sm font-semibold text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400">
                        No disponible
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         MODAL — Pedir otras fechas (rechazo de propuestas)
    ═══════════════════════════════════════════════════════════════ --}}
    @if($rejectTaskId)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-show="true"
        x-transition>
        <div class="absolute inset-0 bg-black/50" wire:click="closeRejectModal"></div>
        <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pedir otras fechas</h3>
                <button wire:click="closeRejectModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Podés dejar un mensaje opcional al evaluador indicando tus preferencias de horario o disponibilidad.
            </p>
            <textarea
                wire:model="rejectReason"
                rows="4"
                placeholder="Ej: Prefiero las tardes después de las 15:00 (UTC-3). Disponible lunes y miércoles."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500"></textarea>
            <div class="mt-4 flex justify-end gap-3">
                <button
                    type="button"
                    wire:click="closeRejectModal"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="rejectAllProposals"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="rejectAllProposals">Enviar solicitud</span>
                    <span wire:loading wire:target="rejectAllProposals">Enviando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
