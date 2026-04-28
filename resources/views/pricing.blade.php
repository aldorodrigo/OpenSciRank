<x-layouts.app :title="__('Pricing - Editorial Standards Platform')" :description="__('Learn about our plans and prices for editorial evaluation of scientific journals and academic book listing.')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Plans and Pricing') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">{{ __('Choose the plan that best suits your needs. Registration in the directory is free.') }}</p>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">{{ __('How does the model work?') }}</h2>
                <div class="rounded-xl bg-indigo-50 p-8 dark:bg-indigo-900/20">
                    <p class="text-gray-700 dark:text-gray-300">{{ __('The platform uses a hybrid model that combines open access to the directory with editorial technical evaluation processes. Payment is made for the technical evaluation process, not for obtaining the editorial seal.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Plans --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Scientific Journals') }}</h2>

            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-3">
                {{-- Free Listing --}}
                <div class="flex flex-col rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Free Listing') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Your journal in the public directory at no cost.') }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold text-gray-900 dark:text-white">$0</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400"> USD</span>
                    </div>
                    <ul class="mt-8 flex-1 space-y-3">
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Public journal profile') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Visibility in the search') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Basic editorial information') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block w-full rounded-xl border-2 border-gray-300 py-3 text-center text-sm font-bold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-800">
                        {{ __('Register my Journal') }}
                    </a>
                </div>

                {{-- Evaluation --}}
                <div class="relative flex flex-col rounded-2xl bg-indigo-600 p-8 text-white shadow-2xl ring-2 ring-indigo-500">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-amber-400 px-4 py-1 text-xs font-bold text-amber-900 shadow-md">
                        {{ __('MOST POPULAR') }}
                    </div>
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold">{{ __('Editorial Evaluation') }}</h3>
                    <p class="mt-2 text-sm text-indigo-200">{{ __('Complete technical evaluation + possibility of Editorial Seal.') }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold">${{ $products['journal-evaluation']?->price ? number_format($products['journal-evaluation']->price, 0) : '99' }}</span>
                        <span class="text-sm text-indigo-200"> USD</span>
                    </div>
                    <p class="mt-1 text-xs text-indigo-300">{{ __('Standard timeframe: 15 business days') }}</p>
                    <ul class="mt-8 flex-1 space-y-3">
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Everything in the free listing') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Review of 18 editorial indicators') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Editorial Standards Score') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Detailed technical report') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Editorial Seal (if meets 75%+)') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Improvement recommendations') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block w-full rounded-xl bg-white py-3 text-center text-sm font-bold text-indigo-600 shadow-lg transition hover:bg-indigo-50">
                        {{ __('Request Evaluation') }}
                    </a>
                </div>

                {{-- Institutional --}}
                <div class="flex flex-col rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/50">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Institutional Package') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Evaluation of 3 journals for universities and publishers.') }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold text-gray-900 dark:text-white">${{ $products['institutional-pack']?->price ? number_format($products['institutional-pack']->price, 0) : '199' }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400"> USD</span>
                    </div>
                    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ __('Save $:amount vs. 3 individual evaluations', ['amount' => $products['journal-evaluation']?->price ? number_format($products['journal-evaluation']->price * 3 - $products['institutional-pack']->price, 0) : '98']) }}</p>
                    <ul class="mt-8 flex-1 space-y-3">
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('3 complete editorial evaluations') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Individual technical report per journal') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Institutional billing') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Institutional reports') }}
                        </li>
                    </ul>
                    <a href="/contact" class="mt-8 block w-full rounded-xl border-2 border-purple-300 py-3 text-center text-sm font-bold text-purple-600 transition hover:border-purple-400 hover:bg-purple-50 dark:border-purple-600 dark:text-purple-400 dark:hover:border-purple-500 dark:hover:bg-purple-900/20">
                        {{ __('Contact') }}
                    </a>
                </div>
            </div>

            {{-- Book Listing --}}
            <div class="mx-auto mt-12 max-w-5xl">
                <h2 class="mb-8 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Academic Books') }}</h2>
                <div class="mx-auto max-w-lg rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/50">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Academic Book Listing') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Permanent inclusion in the academic publications index.') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ $products['book-listing']?->price ? number_format($products['book-listing']->price, 0) : '49' }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400"> USD</span>
                        </div>
                    </div>
                    <ul class="mt-6 grid grid-cols-2 gap-3">
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Public profile with metadata') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Visibility in the search') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('ISBN, authors, publisher') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Permanent presence') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-6 block w-full rounded-xl bg-amber-500 py-3 text-center text-sm font-bold text-white shadow-md transition hover:bg-amber-400">
                        {{ __('Register Book') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Add-ons --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-5xl">
                <h2 class="mb-4 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Add-ons') }}</h2>
                <p class="mb-12 text-center text-gray-500 dark:text-gray-400">{{ __('Additional services to enhance your editorial evaluation.') }}</p>

                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- Express Evaluation --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:hover:border-indigo-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/50">
                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Express Evaluation') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Result in 5 business days instead of 15. Acquired together with an evaluation.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $products['express-evaluation']?->price ? number_format($products['express-evaluation']->price, 0) : '149' }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Premium Report --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:hover:border-indigo-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Premium Report') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Specific recommendations, best practice examples and prioritized action plan.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $products['premium-report']?->price ? number_format($products['premium-report']->price, 0) : '30' }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Re-evaluation --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:hover:border-indigo-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Editorial Re-evaluation') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Full new evaluation to improve your score or achieve the seal.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $products['journal-reevaluation']?->price ? number_format($products['journal-reevaluation']->price, 0) : '99' }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Seal Renewal --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:hover:border-indigo-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Seal Renewal — 2 Years') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Extends the Editorial Standards Seal validity for 24 months.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $products['seal-renewal-2y']?->price ? number_format($products['seal-renewal-2y']->price, 0) : '129' }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Independence Notice --}}
    <section class="bg-gray-50 py-8 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl rounded-xl border-2 border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-700 dark:bg-amber-900/20">
                <p class="font-semibold text-amber-800 dark:text-amber-300">{{ __('Independence principle') }}</p>
                <p class="mt-2 text-sm text-amber-700 dark:text-amber-400">{{ __('Payment for the evaluation process does not guarantee obtaining the Editorial Standards Seal. The result depends exclusively on meeting the defined technical criteria.') }}</p>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Frequently Asked Questions') }}</h2>
            <div class="mx-auto max-w-3xl space-y-4">
                @php
                    $faqs = [
                        [__('What does the evaluation include?'), __('The evaluation covers five fundamental areas: editorial identity, transparency of the editorial process, editorial ethics, access and rights, and technical infrastructure. Each indicator is evaluated as Meets, Partial or Does not meet.')],
                        [__('Does payment guarantee obtaining the seal?'), __('No. Payment covers the technical evaluation process. The result depends exclusively on meeting the criteria. This independence principle ensures the credibility of the system.')],
                        [__('What happens if my journal does not get the seal?'), __('The journal remains as "Evaluated Journal" and receives a technical report with observations and recommendations for improvement. You can request a new evaluation after implementing improvements.')],
                        [__('Are books also evaluated?'), __('No. The platform includes an academic book index to facilitate their discovery, but does not perform editorial evaluation of books.')],
                        [__('Does the seal have a validity period?'), __('Yes. The Editorial Standards Seal has a validity of 1 year. Once the period ends, the journal can request a renewal or a new evaluation.')],
                        [__('Can I request a review of the result?'), __('Yes. Evaluated journals can request a review when they consider there is an error. The request must include evidence supporting the claim.')],
                        [__('Do you offer evaluation for institutions with multiple journals?'), __('Yes. The Institutional Package includes evaluation of 3 journals with a discount. For more journals, contact our team for a personalized proposal.')],
                    ];
                @endphp
                @foreach($faqs as $faq)
                <details class="group rounded-xl border border-gray-200 p-4 transition-colors hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-gray-900 dark:text-white">
                        {{ $faq[0] }}
                        <svg class="h-5 w-5 shrink-0 text-gray-500 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $faq[1] }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative overflow-hidden bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Want to evaluate your journal?') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-indigo-100">{{ __('Register your journal for free and start the path to editorial excellence.') }}</p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            {{ __('Request Evaluation') }}
                        </a>
                        <a href="/register" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            {{ __('Register for Free') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
