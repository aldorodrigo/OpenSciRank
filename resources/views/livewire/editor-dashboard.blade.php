<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mi Panel</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Gestiona tus revistas y libros</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('app.submit') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Revista
                </a>
                <a href="{{ route('app.book.submit') }}" class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Libro
                </a>
            </div>
        </div>

        {{-- Stats Overview --}}
        <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $journals->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Revistas</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $books->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Libros</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $journals->where('status', 'indexed')->count() + $books->where('status', 'indexed')->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Indexados</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $journals->where('status', 'submitted')->count() + $books->where('status', 'submitted')->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">En Revisión</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Journals Table --}}
        <div class="mb-8 rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mis Revistas</h2>
            </div>

            @if($journals->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No tienes revistas registradas</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Comienza registrando tu primera revista.</p>
                    <a href="{{ route('app.submit') }}" class="mt-6 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Registrar Revista
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Revista</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Puntaje</th>
                                <th class="px-6 py-3">Sello</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($journals as $journal)
                                <tr class="
                                    @if($journal->seal_expires_at && $journal->seal_expires_at->isPast())
                                        bg-red-50 dark:bg-red-950/30 border-l-4 border-red-500
                                    @else
                                        hover:bg-gray-50 dark:hover:bg-gray-800/50
                                    @endif
                                ">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $journal->title }}</p>
                                                @if($journal->issn_print)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">ISSN: {{ $journal->issn_print }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            @if($journal->status === 'certified' && $journal->seal_expires_at?->isPast()) bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 line-through
                                            @elseif(in_array($journal->status, ['indexed', 'listed', 'certified'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif(in_array($journal->status, ['submitted', 'pending_listing'])) bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif(str_starts_with($journal->status, 'requires_changes')) bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @elseif($journal->status === 'evaluated') bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($journal->status) {
                                                'draft' => 'Borrador',
                                                'submitted' => 'En Evaluacion',
                                                'pending_listing' => 'Pendiente de Listado',
                                                'requires_changes_listing' => 'Correcciones (Listado)',
                                                'requires_changes_evaluation' => 'Correcciones (Evaluacion)',
                                                'listed' => 'Listada',
                                                'evaluated' => 'Evaluada',
                                                'certified' => 'Certificada',
                                                'indexed' => 'Indexada',
                                                default => ucfirst($journal->status)
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($journal->current_score)
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-indigo-600" style="width: {{ min($journal->current_score, 100) }}%"></div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $journal->current_score }}%</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    {{-- Columna Sello --}}
                                    <td class="px-6 py-4">
                                        @if($journal->seal_expires_at)
                                            @if($journal->seal_expires_at->isPast())
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-400">
                                                    🔴 Vencido
                                                </span>
                                                <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                            @elseif(now()->diffInDays($journal->seal_expires_at) <= 60)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                                    ⚠️ Por vencer
                                                </span>
                                                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                                    ✅ Vigente
                                                </span>
                                                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                                <a href="{{ route('app.badge', $journal) }}" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    Obtener Sello
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-600">—</span>
                                        @endif
                                    </td>

                                    {{-- Columna Acciones --}}
                                    <td class="px-6 py-4">
                                        <div x-data="{ open: false, top: '0px', left: '0px' }" @click.outside="open = false">
                                            <button @click.stop="const r = $el.getBoundingClientRect(); top = (r.bottom + 4) + 'px'; left = Math.max(0, r.right - 208) + 'px'; open = !open;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                Acciones
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-transition
                                                class="fixed z-50 w-52 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                                :style="`top: ${top}; left: ${left}`">
                                                <div class="py-1">

                                                    {{-- Editar (solo borrador y correcciones) --}}
                                                    @if(in_array($journal->status, ['draft', 'requires_changes_listing', 'requires_changes_evaluation']))
                                                        <a href="{{ route('app.submit.edit', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            Editar
                                                        </a>
                                                    @endif

                                                    {{-- Ver Ficha --}}
                                                    @if($journal->status !== 'draft')
                                                        <a href="{{ route('journal.show', $journal->slug) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            Ver Ficha
                                                        </a>
                                                    @endif

                                                    {{-- Pagar Evaluación (borrador o listada) --}}
                                                    @if(in_array($journal->status, ['draft', 'listed']))
                                                        <a href="{{ route('app.checkout', $journal) }}"
                                                            onclick="return confirm('{{ $journal->status === 'listed' ? 'Tu revista ya está listada. Al pagar la evaluación será analizada por un experto. ¿Deseas continuar?' : '¿Deseas iniciar el proceso de evaluación editorial?' }}')"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                            Pagar Evaluación
                                                        </a>
                                                    @endif

                                                    {{-- Reenviar para Listar --}}
                                                    @if($journal->status === 'requires_changes_listing')
                                                        <button wire:click="showObservations({{ $journal->id }}, 'journal')" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 dark:text-amber-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            Reenviar para Listar
                                                        </button>
                                                    @endif

                                                    {{-- Corregir y Reenviar (evaluación) --}}
                                                    @if($journal->status === 'requires_changes_evaluation')
                                                        <a href="{{ route('app.checkout', $journal) }}"
                                                            onclick="return confirm('Confirma que ya realizaste las correcciones indicadas. Se te redirigirá al pago para una nueva evaluación. ¿Deseas continuar?')"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Corregir y Reenviar
                                                        </a>
                                                    @endif

                                                    {{-- Renovar Sello (vencido o próximo a vencer) --}}
                                                    @if($journal->seal_expires_at && ($journal->seal_expires_at->isPast() || now()->diffInDays($journal->seal_expires_at) <= 60))
                                                        <a href="{{ route('app.renew', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm {{ $journal->seal_expires_at->isPast() ? 'font-semibold text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }} hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            Renovar Sello
                                                        </a>
                                                    @endif

                                                    {{-- Obtener Sello (certificada con sello vigente) --}}
                                                    @if($journal->status === 'certified' && $journal->seal_expires_at?->isFuture())
                                                        <a href="{{ route('app.badge', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                            Obtener Sello
                                                        </a>
                                                    @endif

                                                    {{-- Cosechar OAI --}}
                                                    @if($journal->status === 'indexed' && $journal->oai_base_url)
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="harvestOai({{ $journal->id }})" wire:loading.attr="disabled" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            📡 Cosechar OAI
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar (solo borrador) --}}
                                                    @if($journal->status === 'draft')
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="deleteJournal({{ $journal->id }})"
                                                            wire:confirm="¿Estás seguro de eliminar esta revista? Esta acción no se puede deshacer."
                                                            @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            Eliminar
                                                        </button>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Books Table --}}
        <div class="rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mis Libros</h2>
            </div>

            @if($books->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No tienes libros registrados</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Comienza registrando tu primer libro.</p>
                    <a href="{{ route('app.book.submit') }}" class="mt-6 inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500">
                        Registrar Libro
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Libro</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Puntaje</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($books as $book)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $book->title }}</p>
                                                @if($book->isbn_print)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">ISBN: {{ $book->isbn_print }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            @if(in_array($book->status, ['indexed', 'listed'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif(in_array($book->status, ['submitted', 'pending_listing'])) bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif(str_starts_with($book->status, 'requires_changes')) bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($book->status) {
                                                'draft' => 'Borrador',
                                                'submitted' => 'Enviado',
                                                'pending_listing' => 'Pendiente de Listado',
                                                'requires_changes_listing' => 'Correcciones (Listado)',
                                                'listed' => 'Listado',
                                                'indexed' => 'Indexado',
                                                default => ucfirst($book->status)
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($book->current_score)
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-purple-600" style="width: {{ min($book->current_score, 100) }}%"></div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $book->current_score }}%</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div x-data="{ open: false, top: '0px', left: '0px' }" @click.outside="open = false">
                                            <button @click.stop="const r = $el.getBoundingClientRect(); top = (r.bottom + 4) + 'px'; left = Math.max(0, r.right - 208) + 'px'; open = !open;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                Acciones
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-transition
                                                class="fixed z-50 w-52 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                                :style="`top: ${top}; left: ${left}`">
                                                <div class="py-1">

                                                    {{-- Editar (borrador o correcciones) --}}
                                                    @if(in_array($book->status, ['draft', 'requires_changes_listing']))
                                                        <a href="{{ route('app.book.submit.edit', $book) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            Editar
                                                        </a>
                                                    @endif

                                                    {{-- Ver Ficha --}}
                                                    @if($book->status !== 'draft')
                                                        <a href="{{ route('book.show', $book->slug) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            Ver Ficha
                                                        </a>
                                                    @endif

                                                    {{-- Pagar Listado (borrador) --}}
                                                    @if($book->status === 'draft')
                                                        <a href="{{ route('app.book.checkout', $book) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                            Pagar Listado
                                                        </a>
                                                    @endif

                                                    {{-- Reenviar para Listar --}}
                                                    @if($book->status === 'requires_changes_listing')
                                                        <button wire:click="showObservations({{ $book->id }}, 'book')" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 dark:text-amber-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            Reenviar para Listar
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar (solo borrador) --}}
                                                    @if($book->status === 'draft')
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="deleteBook({{ $book->id }})"
                                                            wire:confirm="¿Estás seguro de eliminar este libro? Esta acción no se puede deshacer."
                                                            @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            Eliminar
                                                        </button>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Observaciones --}}
    @if($showObservationsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="background-color: rgba(0,0,0,0.5);">
        <div class="relative mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Observaciones del revisor</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $observationsTitle }}</p>
                </div>
                <button wire:click="closeObservationsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Observaciones --}}
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="mb-2 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <span class="text-sm font-semibold text-red-700 dark:text-red-400">Correcciones solicitadas:</span>
                </div>
                <div class="prose prose-sm max-w-none text-red-800 dark:text-red-300">
                    {!! nl2br(e($observationsNotes)) !!}
                </div>
            </div>

            {{-- Advertencia --}}
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                <p class="text-sm text-amber-800 dark:text-amber-300">
                    Al reenviar, tu solicitud volverá a ser revisada por el equipo. Asegúrate de haber corregido todas las observaciones antes de continuar.
                </p>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <button wire:click="closeObservationsModal"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cerrar
                </button>
                <button wire:click="confirmResubmitForListing"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                    Ya corregí, reenviar solicitud
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
