<x-layouts.app title="Ranking Global - Editorial Standards Platform" description="Ranking de las mejores revistas científicas y libros académicos indexados en Editorial Standards Platform, ordenados por puntaje de evaluación.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-amber-500 via-orange-500 to-red-500 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <div class="mb-4 inline-flex items-center rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                🏆 Actualizado en tiempo real
            </div>
            <h1 class="text-4xl font-bold sm:text-5xl">Ranking Global</h1>
            <p class="mx-auto mt-4 max-w-2xl text-orange-100">Las publicaciones científicas con mayor calidad editorial según la evaluación de Editorial Standards Platform.</p>
        </div>
    </section>

    {{-- Filters --}}
    <section class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 self-center">Tipo:</span>
                    <button class="rounded-full bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white">Revistas</button>
                    <button class="rounded-full bg-gray-100 px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300">Libros</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 self-center">Nivel:</span>
                    @foreach(['Todos', 'A', 'B', 'C'] as $i => $level)
                    <button class="rounded-full {{ $i === 0 ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }} px-4 py-1.5 text-sm font-medium transition">
                        {{ $level }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Ranking Table --}}
    <section class="bg-gray-50 py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            @php
                $journals = \App\Models\Journal::where('status', 'indexed')
                    ->whereNotNull('current_score')
                    ->orderByDesc('current_score')
                    ->paginate(20);
            @endphp

            @if($journals->count() > 0)
            <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-gray-900">
                {{-- Top 3 Podium --}}
                @if($journals->currentPage() === 1)
                <div class="grid grid-cols-3 gap-px border-b border-gray-100 bg-gray-100 dark:border-gray-800 dark:bg-gray-800 sm:hidden md:grid">
                    @foreach($journals->take(3) as $pos => $j)
                    <div class="flex flex-col items-center bg-white py-6 text-center dark:bg-gray-900 {{ $pos === 0 ? 'bg-amber-50 dark:bg-amber-900/10' : '' }}">
                        <div class="mb-2 text-3xl">{{ $pos === 0 ? '🥇' : ($pos === 1 ? '🥈' : '🥉') }}</div>
                        @if($j->logo)
                            <img src="{{ Storage::url($j->logo) }}" alt="{{ $j->title }}" class="h-10 w-10 rounded-lg object-cover">
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        @endif
                        <p class="mt-2 px-4 text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ $j->title }}</p>
                        <span class="mt-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $j->current_score }}%</span>
                        @if($j->current_level)
                        <span class="mt-1 rounded-full px-2 py-0.5 text-xs font-bold
                            @if($j->current_level === 'A') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                            @elseif($j->current_level === 'B') bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400
                            @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                            @endif">Nivel {{ $j->current_level }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Full Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Publicación</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nivel</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Puntaje</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">País</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($journals as $position => $j)
                            @php $rank = ($journals->currentPage() - 1) * $journals->perPage() + $position + 1; @endphp
                            <tr class="group hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-400 dark:text-gray-500 {{ $rank <= 3 ? 'text-lg' : 'text-sm' }}">
                                        @if($rank === 1) 🥇
                                        @elseif($rank === 2) 🥈
                                        @elseif($rank === 3) 🥉
                                        @else #{{ $rank }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('journal.show', $j->slug) }}" class="flex items-center gap-3">
                                        @if($j->logo)
                                            <img src="{{ Storage::url($j->logo) }}" alt="{{ $j->title }}" class="h-9 w-9 rounded-lg object-cover">
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $j->title }}</p>
                                            @if($j->publishing_institution)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $j->publishing_institution }}</p>
                                            @endif
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($j->current_level)
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                        @if($j->current_level === 'A') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                        @elseif($j->current_level === 'B') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                        @endif">{{ $j->current_level }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ min($j->current_score, 100) }}%"></div>
                                        </div>
                                        <span class="min-w-[3.5rem] text-right text-sm font-bold text-gray-900 dark:text-white">{{ $j->current_score }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $j->country_code ? strtoupper($j->country_code) : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($journals->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $journals->links() }}
                </div>
                @endif
            </div>
            @else
            {{-- Empty state --}}
            <div class="py-24 text-center">
                <div class="mx-auto mb-6 text-6xl">🏆</div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">El ranking está en construcción</h2>
                <p class="mx-auto mt-3 max-w-md text-gray-600 dark:text-gray-400">Aún no hay publicaciones indexadas. Sé el primero en registrar tu revista.</p>
                <a href="/register" class="mt-8 inline-flex rounded-lg bg-indigo-600 px-8 py-3 font-semibold text-white transition hover:bg-indigo-500">
                    Registrar mi Revista
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">¿Quieres aparecer en el ranking?</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-600 dark:text-gray-400">Registra tu revista o libro y obtén una evaluación profesional basada en +50 criterios internacionales.</p>
            <a href="/register" class="mt-8 inline-flex items-center rounded-lg bg-indigo-600 px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Indexar mi Publicación — Gratis
            </a>
        </div>
    </section>

</x-layouts.app>
