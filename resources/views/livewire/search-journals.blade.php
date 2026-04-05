<div>
    {{-- Prominent Search Bar --}}
    <div class="mb-8">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar revistas científicas o libros académicos..."
                class="w-full rounded-xl border border-gray-300 bg-white py-4 pl-12 pr-4 text-lg shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
            >
            <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-4">
                <svg class="h-5 w-5 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Active Filters Chips --}}
    @if($search || $country || $type !== 'all' || $subjectArea || $frequency || $accessType || $apcRange)
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-500 dark:text-gray-400">Filtros activos:</span>
        @if($search)
        <button wire:click="$set('search', '')" class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700 transition hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 dark:hover:bg-indigo-900/60">
            "{{ $search }}" <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($type !== 'all')
        <button wire:click="$set('type', 'all')" class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700 transition hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-400 dark:hover:bg-purple-900/60">
            {{ $type === 'journals' ? 'Solo Revistas' : 'Solo Libros' }} <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($subjectArea)
        <button wire:click="$set('subjectArea', '')" class="inline-flex items-center gap-1 rounded-full bg-teal-100 px-3 py-1 text-sm font-medium text-teal-700 transition hover:bg-teal-200 dark:bg-teal-900/40 dark:text-teal-400 dark:hover:bg-teal-900/60">
            {{ \App\Livewire\SearchJournals::SUBJECT_AREAS[$subjectArea] ?? $subjectArea }} <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($frequency)
        <button wire:click="$set('frequency', '')" class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-3 py-1 text-sm font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-900/40 dark:text-sky-400 dark:hover:bg-sky-900/60">
            {{ \App\Livewire\SearchJournals::FREQUENCIES[$frequency] ?? $frequency }} <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($accessType)
        <button wire:click="$set('accessType', '')" class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-3 py-1 text-sm font-medium text-violet-700 transition hover:bg-violet-200 dark:bg-violet-900/40 dark:text-violet-400 dark:hover:bg-violet-900/60">
            {{ \App\Livewire\SearchJournals::ACCESS_TYPES[$accessType] ?? $accessType }} <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($apcRange)
        <button wire:click="$set('apcRange', '')" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700 transition hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:hover:bg-amber-900/60">
            {{ \App\Livewire\SearchJournals::APC_RANGES[$apcRange] ?? $apcRange }} <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        @if($country)
        <button wire:click="$set('country', '')" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700 transition hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60">
            {{ \App\Livewire\SearchJournals::countryFlag($country) }} {{ \App\Livewire\SearchJournals::countryName($country) }}
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
        <button wire:click="resetFilters" class="text-sm font-medium text-gray-500 underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            Limpiar todo
        </button>
    </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-4">
        {{-- Filters Sidebar --}}
        <aside class="lg:col-span-1">
            <div class="sticky top-24 rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Filtros</h3>

                {{-- Type Filter --}}
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                    <select
                        wire:model.live="type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="all">Todos</option>
                        <option value="journals">Solo Revistas</option>
                        <option value="books">Solo Libros</option>
                    </select>
                </div>

                {{-- Subject Area Filter --}}
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Área Temática</label>
                    <select
                        wire:model.live="subjectArea"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Todas las áreas</option>
                        @foreach(\App\Livewire\SearchJournals::SUBJECT_AREAS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Frequency Filter (journals only) --}}
                @if($type !== 'books')
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Frecuencia</label>
                    <select
                        wire:model.live="frequency"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Todas</option>
                        @foreach(\App\Livewire\SearchJournals::FREQUENCIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Access Type Filter --}}
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Acceso</label>
                    <select
                        wire:model.live="accessType"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Todos</option>
                        @foreach(\App\Livewire\SearchJournals::ACCESS_TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- APC Range Filter (journals only) --}}
                @if($type !== 'books')
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cobra APC</label>
                    <select
                        wire:model.live="apcRange"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Todos</option>
                        @foreach(\App\Livewire\SearchJournals::APC_RANGES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Country Filter --}}
                @if($countryCodes->count() > 0)
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">País</label>
                    <select
                        wire:model.live="country"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Todos los países</option>
                        @foreach($countryCodes as $c)
                            <option value="{{ $c }}">{{ \App\Livewire\SearchJournals::countryFlag($c) }} {{ \App\Livewire\SearchJournals::countryName($c) }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Sort --}}
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por</label>
                    <select
                        wire:model.live="sortBy"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="score">Mayor puntaje</option>
                        <option value="title">Nombre A-Z</option>
                        <option value="recent">Más recientes</option>
                    </select>
                </div>

                {{-- Reset --}}
                <button
                    wire:click="resetFilters"
                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    style="cursor:pointer;"
                >
                    Limpiar Filtros
                </button>
            </div>
        </aside>

        {{-- Results Grid --}}
        <div class="lg:col-span-3">
            {{-- Results count --}}
            <div class="mb-6 flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $totalCount }}</span> publicaciones
                    @if($type === 'all' && $journalTotal > 0 && $bookTotal > 0)
                        <span class="text-gray-400 dark:text-gray-500">·</span>
                        {{ $journalTotal }} {{ $journalTotal === 1 ? 'revista' : 'revistas' }}
                        <span class="text-gray-400 dark:text-gray-500">·</span>
                        {{ $bookTotal }} {{ $bookTotal === 1 ? 'libro' : 'libros' }}
                    @endif
                </p>
            </div>

            {{-- Loading overlay --}}
            <div class="relative">
                <div wire:loading.delay wire:target="search, type, country, sortBy, subjectArea, frequency, accessType, apcRange" class="absolute inset-0 z-10 flex items-start justify-center rounded-xl bg-white/60 pt-24 backdrop-blur-sm dark:bg-gray-950/60">
                    <div class="flex items-center gap-3 rounded-lg bg-white px-6 py-3 shadow-lg dark:bg-gray-900">
                        <svg class="h-5 w-5 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscando...</span>
                    </div>
                </div>

                @if($results->isEmpty())
                    <div class="rounded-xl bg-white p-12 text-center shadow-lg dark:bg-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No se encontraron publicaciones</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Intenta ajustar tus filtros o busca con otros términos.</p>
                        @if($search || $country || $type !== 'all' || $subjectArea || $frequency || $accessType || $apcRange)
                        <button wire:click="resetFilters" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Limpiar filtros
                        </button>
                        @endif
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($results as $result)
                            @php
                                $item = $result['item'];
                                $itemType = $result['type'];
                                $link = $itemType === 'journal'
                                    ? route('journal.show', $item->slug)
                                    : route('book.show', $item->slug);
                                $score = $item->current_score ?? 0;
                                $hasSeal = $itemType === 'journal' && $item->status === 'certified'
                                    && ($item->seal_expires_at === null || $item->seal_expires_at->isFuture());
                            @endphp
                            <a href="{{ $link }}" class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-md transition hover:shadow-xl hover:-translate-y-0.5 dark:bg-gray-900 {{ $hasSeal ? 'seal-ribbon-wrapper' : '' }}">
                                @if($hasSeal)
                                    <div class="seal-ribbon">SELLO ✓</div>
                                @endif
                                <div class="flex flex-1 flex-col p-5">
                                    {{-- Header: Logo + badges --}}
                                    <div class="mb-3 flex items-start gap-3">
                                        {{-- Logo / Icon --}}
                                        @if($itemType === 'journal' && $item->logo)
                                            <img src="{{ Storage::url($item->logo) }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-contain shadow-sm">
                                        @else
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $itemType === 'journal' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400' }}">
                                                @if($itemType === 'journal')
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                                @else
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                                </svg>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Badges --}}
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $itemType === 'journal' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}
                                            ">
                                                {{ $itemType === 'journal' ? 'Revista' : 'Libro' }}
                                            </span>
                                            @if($hasSeal)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                ✅ Sello
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Title --}}
                                    <h3 class="mb-1.5 text-base font-semibold leading-snug text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                                        {{ $item->title }}
                                    </h3>

                                    {{-- Institution --}}
                                    @if($item->publishing_institution ?? $item->publisher ?? null)
                                        <p class="mb-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ $item->publishing_institution ?? $item->publisher }}</p>
                                    @endif

                                    {{-- Country --}}
                                    @if($item->country_code)
                                        <p class="mb-3 text-xs text-gray-400 dark:text-gray-500">
                                            {{ \App\Livewire\SearchJournals::countryFlag($item->country_code) }}
                                            {{ \App\Livewire\SearchJournals::countryName($item->country_code) }}
                                        </p>
                                    @endif

                                    {{-- ISSN --}}
                                    @if($itemType === 'journal' && ($item->issn_print || $item->issn_online))
                                        <p class="mb-3 text-xs text-gray-400 dark:text-gray-500">
                                            ISSN: {{ $item->issn_online ?? $item->issn_print }}
                                        </p>
                                    @endif

                                    {{-- Spacer --}}
                                    <div class="flex-1"></div>

                                    {{-- Score bar --}}
                                    @if($score > 0)
                                    <div class="mt-3 flex items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                            <div class="h-full rounded-full transition-all {{ $score >= 75 ? 'bg-emerald-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-gray-400') }}"
                                                 style="width: {{ min($score, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold {{ $score >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400') }}">
                                            {{ number_format($score, 0) }}%
                                        </span>
                                    </div>
                                    @else
                                    <div class="mt-3">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Sin evaluar</span>
                                    </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $results->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
