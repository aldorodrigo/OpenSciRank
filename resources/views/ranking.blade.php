<x-layouts.app :title="__('Editorial Standards Ranking') . ' - Editorial Standards Platform'" :description="__('Ranking of journals with an active Editorial Standards Seal, sorted by their compliance score against transparent editorial criteria.')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-brand-deep py-14 text-white">
        <div class="container mx-auto px-4 text-center">
            <div class="mb-4 inline-flex items-center rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                {{ __('Live · ranked by editorial compliance, not by payments') }}
            </div>
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Editorial Standards Ranking') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-blue-200">{{ __('Scientific journals with an active Editorial Standards Seal, sorted by their compliance score against 18 transparent criteria across 5 evaluation areas.') }}</p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-sm">
                <a href="{{ route('search') }}" class="rounded-lg border border-white/30 px-4 py-2 text-white transition hover:bg-white/10">
                    {{ __('Browse full directory') }}
                </a>
                <a href="{{ route('methodology') }}" class="rounded-lg border border-white/30 px-4 py-2 text-white transition hover:bg-white/10">
                    {{ __('How we evaluate') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Ranking Table --}}
    <section class="bg-gray-50 py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            @php
                $journals = \App\Models\Journal::where('status', 'certified')
                    ->whereNotNull('seal_expires_at')
                    ->where('seal_expires_at', '>', now())
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
                            <img src="{{ Storage::url($j->logo) }}" alt="{{ $j->getTranslationWithFallback('title') }}" class="h-10 w-10 rounded-lg object-cover">
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        @endif
                        <p class="mt-2 px-4 text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ $j->getTranslationWithFallback('title') }}</p>
                        <span class="mt-2 text-xl font-bold text-brand dark:text-blue-400">{{ $j->current_score }}%</span>
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
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Journal') }}</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Seal') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Score') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Country') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($journals as $position => $j)
                            @php $rank = ($journals->currentPage() - 1) * $journals->perPage() + $position + 1; @endphp
                            <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition">
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
                                            <img src="{{ Storage::url($j->logo) }}" alt="{{ $j->getTranslationWithFallback('title') }}" class="h-9 w-9 rounded-lg object-cover">
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-gray-900 group-hover:text-brand dark:text-white dark:group-hover:text-blue-400">{{ $j->getTranslationWithFallback('title') }}</p>
                                            @if($j->publishing_institution)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $j->getTranslationWithFallback('publishing_institution') }}</p>
                                            @endif
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-brand dark:bg-blue-900/50 dark:text-blue-400" title="{{ __('Seal valid until :date', ['date' => $j->seal_expires_at?->format('Y-m-d')]) }}">
                                        ✓ {{ __('Seal') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full rounded-full bg-brand" style="width: {{ min($j->current_score, 100) }}%"></div>
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
                <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-3xl dark:bg-blue-900/30">✓</div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('No certified journals yet') }}</h2>
                <p class="mx-auto mt-3 max-w-md text-gray-600 dark:text-gray-400">{{ __('Be the first to earn the Editorial Standards Seal and lead the ranking.') }}</p>
                <a href="/register" class="mt-8 inline-flex rounded-lg bg-brand px-8 py-3 font-semibold text-white transition hover:bg-blue-500">
                    {{ __('Register my Journal — Free') }}
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Do you want to appear in the ranking?') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-600 dark:text-gray-400">{{ __('Register your journal or book and get a professional evaluation based on +50 international criteria.') }}</p>
            <a href="/register" class="mt-8 inline-flex items-center rounded-lg bg-brand px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-500">
                {{ __('Index my Publication — Free') }}
            </a>
        </div>
    </section>

</x-layouts.app>
