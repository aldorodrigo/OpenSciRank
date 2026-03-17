<div class="grid gap-8 lg:grid-cols-4">
    {{-- Filters Sidebar --}}
    <aside class="lg:col-span-1">
        <div class="sticky top-24 rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Filtros</h3>

            {{-- Search --}}
            <div class="mb-6">
                <label for="search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nombre de publicación..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
            </div>

            {{-- Type Filter --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                <select
                    wire:model.live="type"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">Todos</option>
                    <option value="journals">Solo Revistas</option>
                    <option value="books">Solo Libros</option>
                </select>
            </div>

            {{-- Level Filter --}}
            <div class="mb-6">
                <label for="level" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Calificación</label>
                <select
                    id="level"
                    wire:model.live="level"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Todas</option>
                    <option value="A">Nivel A</option>
                    <option value="B">Nivel B</option>
                    <option value="C">Nivel C</option>
                </select>
            </div>

            {{-- Country Filter --}}
            @if($countries->count() > 0)
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">País</label>
                <select
                    wire:model.live="country"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Todos los países</option>
                    @foreach($countries as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Sort --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por</label>
                <select
                    wire:model.live="sortBy"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="score">Mayor puntaje</option>
                    <option value="title">Nombre A-Z</option>
                    <option value="recent">Más recientes</option>
                </select>
            </div>

            {{-- Reset --}}
            <button
                wire:click="resetFilters"
                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                style="cursor:pointer;"
            >
                Limpiar Filtros
            </button>
        </div>
    </aside>

    {{-- Results Grid --}}
    <div class="lg:col-span-3">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Mostrando <span class="font-medium">{{ $results->count() }}</span> de <span class="font-medium">{{ $totalCount }}</span> publicaciones
            </p>
        </div>

        @if($results->isEmpty())
            <div class="rounded-xl bg-white p-12 text-center shadow-lg dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No se encontraron publicaciones</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Intenta ajustar tus filtros de búsqueda.</p>
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
                    @endphp
                    <article class="group overflow-hidden rounded-xl bg-white shadow-lg transition hover:shadow-xl dark:bg-gray-900">
                        <div class="p-6">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($item->current_level)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            @if($item->current_level === 'A') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif($item->current_level === 'B') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            Nivel {{ $item->current_level }}
                                        </span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $itemType === 'journal' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' }}
                                ">
                                    {{ $itemType === 'journal' ? '📖 Revista' : '📚 Libro' }}
                                </span>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                                <a href="{{ $link }}">{{ $item->title }}</a>
                            </h3>
                            @if($item->publishing_institution ?? $item->publisher ?? null)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item->publishing_institution ?? $item->publisher }}</p>
                            @endif
                            @if($item->country_code)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">📍 {{ $item->country_code }}</p>
                            @endif
                            @if($item->current_score)
                                <div class="mt-4 flex items-center gap-2">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ min($item->current_score, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $item->current_score }}%</span>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
