<x-layouts.app :title="__('Directory - Editorial Standards Platform')" :description="__('Explore the directory of scientific journals and academic books on Editorial Standards Platform. Filter by status, country, discipline and more.')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-14 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Publication Directory') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">{{ __('Explore scientific journals and academic books. Editorial evaluation based on transparent criteria.') }}</p>

            {{-- Stats --}}
            @php
                $journalCount = \App\Models\Journal::whereIn('status', ['listed', 'evaluated', 'certified'])->count();
                $bookCount = \App\Models\Book::where('status', 'listed')->count();
                $certifiedCount = \App\Models\Journal::where('status', 'certified')
                    ->where(fn($q) => $q->whereNull('seal_expires_at')->orWhere('seal_expires_at', '>', now()))
                    ->count();
            @endphp
            <div class="mt-8 flex flex-wrap items-center justify-center gap-8">
                <div class="text-center">
                    <div class="text-3xl font-extrabold">{{ number_format($journalCount) }}</div>
                    <div class="mt-1 text-sm text-indigo-200">{{ __('Journals in the directory') }}</div>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <div class="text-3xl font-extrabold">{{ number_format($bookCount) }}</div>
                    <div class="mt-1 text-sm text-indigo-200">{{ __('Indexed books') }}</div>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <div class="text-3xl font-extrabold">{{ number_format($certifiedCount) }}</div>
                    <div class="mt-1 text-sm text-indigo-200">{{ __('Certified Journals') }}</div>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <div class="text-3xl font-extrabold">5</div>
                    <div class="mt-1 text-sm text-indigo-200">{{ __('Evaluation areas') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Search & Results --}}
    <section class="bg-gray-50 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-10">
            <livewire:search-journals />
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-t border-gray-200 bg-white py-14 dark:border-gray-800 dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __("Can't find your journal?") }}</h2>
            <p class="mx-auto mt-3 max-w-lg text-gray-600 dark:text-gray-400">{{ __('If your journal is not yet in our directory, register it for free to become part of Editorial Standards Platform.') }}</p>

            <a href="/register" class="mt-8 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Register my Journal — Free') }}
            </a>
        </div>
    </section>

</x-layouts.app>
