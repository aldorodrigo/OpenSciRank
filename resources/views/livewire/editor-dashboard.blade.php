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
                                <th class="px-6 py-3">Calificación</th>
                                <th class="px-6 py-3">Puntaje</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($journals as $journal)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
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
                                            @if($journal->status === 'indexed') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif($journal->status === 'submitted') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif($journal->status === 'requires_changes') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($journal->status) { 'draft' => 'Borrador', 'submitted' => 'Enviado', 'requires_changes' => 'Requiere correcciones', 'indexed' => 'Indexado', default => ucfirst($journal->status) } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($journal->current_level)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                @if($journal->current_level === 'A') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                                @elseif($journal->current_level === 'B') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                                @endif
                                            ">
                                                Nivel {{ $journal->current_level }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
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
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($journal->status === 'draft' || $journal->status === 'requires_changes')
                                                <a href="{{ route('app.submit.edit', $journal) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                    Editar
                                                </a>
                                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                                <a href="{{ route('app.checkout', $journal) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">
                                                    {{ $journal->status === 'requires_changes' ? 'Corregir y Reenviar' : 'Pagar' }}
                                                </a>
                                            @else
                                                <a href="{{ route('journal.show', $journal->slug) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                    Ver Ficha
                                                </a>
                                            @endif
                                            @if($journal->status === 'indexed' && $journal->oai_base_url)
                                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                                <button wire:click="harvestOai({{ $journal->id }})" wire:loading.attr="disabled"
                                                    class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400" style="cursor:pointer;">
                                                    <span wire:loading.remove wire:target="harvestOai({{ $journal->id }})">📡 Cosechar</span>
                                                    <span wire:loading wire:target="harvestOai({{ $journal->id }})">⏳ Cosechando...</span>
                                                </button>
                                            @endif
                                            @if($journal->status !== 'indexed')
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button wire:click="deleteJournal({{ $journal->id }})" wire:confirm="¿Estás seguro de eliminar esta revista?" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400" style="cursor:pointer;">
                                                Eliminar
                                            </button>
                                            @endif
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
                                <th class="px-6 py-3">Calificación</th>
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
                                            @if($book->status === 'indexed') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif($book->status === 'submitted') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif($book->status === 'requires_changes') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($book->status) { 'draft' => 'Borrador', 'submitted' => 'Enviado', 'requires_changes' => 'Requiere correcciones', 'indexed' => 'Indexado', default => ucfirst($book->status) } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($book->current_level)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                @if($book->current_level === 'A') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                                @elseif($book->current_level === 'B') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                                @endif
                                            ">
                                                Nivel {{ $book->current_level }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
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
                                        <div class="flex items-center gap-2">
                                            @if($book->status === 'draft' || $book->status === 'requires_changes')
                                                <a href="{{ route('app.book.submit.edit', $book) }}" class="text-sm font-medium text-purple-600 hover:text-purple-500 dark:text-purple-400">
                                                    Editar
                                                </a>
                                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                                <a href="{{ route('app.book.checkout', $book) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">
                                                    {{ $book->status === 'requires_changes' ? 'Corregir y Reenviar' : 'Pagar' }}
                                                </a>
                                            @else
                                                <a href="{{ route('book.show', $book->slug) }}" class="text-sm font-medium text-purple-600 hover:text-purple-500 dark:text-purple-400">
                                                    Ver Ficha
                                                </a>
                                            @endif
                                            @if($book->status !== 'indexed')
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button wire:click="deleteBook({{ $book->id }})" wire:confirm="¿Estás seguro de eliminar este libro?" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400" style="cursor:pointer;">
                                                Eliminar
                                            </button>
                                            @endif
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
</div>
