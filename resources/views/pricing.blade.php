<x-layouts.app :title="__('Pricing - Editorial Standards Platform')" :description="__('Learn about our plans and prices for editorial evaluation of scientific journals and academic book listing.')">
    <x-slot:header>true</x-slot:header>

    {{-- Precio de un producto por slug, con fallback si la fila no existe en la BD
         (ej. ProductSeeder no corrido) o si el producto está inactivo. --}}
    @php
        $price = fn (string $slug, string $fallback) => ($products[$slug] ?? null)?->price
            ? number_format($products[$slug]->price, 0)
            : $fallback;
    @endphp

    {{-- Hero --}}
    <section class="bg-brand-deep py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Plans and Pricing') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-blue-200">{{ __('Choose the plan that best suits your needs. Registration in the directory is free.') }}</p>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">{{ __('How does the model work?') }}</h2>
                <div class="rounded-xl bg-blue-50 p-8 dark:bg-blue-900/20">
                    <p class="text-gray-700 dark:text-gray-300">{{ __('The platform uses a hybrid model that combines open access to the directory with editorial technical evaluation processes. Payment is made for the technical evaluation process, not for obtaining the editorial seal.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Plans --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Scientific Journals') }}</h2>

            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-2">
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
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Public journal profile') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Visibility in the search') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Basic editorial information') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block w-full rounded-xl border-2 border-gray-300 py-3 text-center text-sm font-bold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-800">
                        {{ __('Register my Journal') }}
                    </a>
                </div>

                {{-- Evaluation --}}
                <div class="relative flex flex-col rounded-2xl bg-brand p-8 text-white shadow-2xl ring-2 ring-blue-500">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-blue-400 px-4 py-1 text-xs font-bold text-blue-900 shadow-md">
                        {{ __('MOST POPULAR') }}
                    </div>
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold">{{ __('Editorial Evaluation') }}</h3>
                    <p class="mt-2 text-sm text-blue-200">{{ __('Complete technical evaluation + possibility of Editorial Seal.') }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold">${{ $price('journal-evaluation', '99') }}</span>
                        <span class="text-sm text-blue-200"> USD</span>
                    </div>
                    <p class="mt-1 text-xs text-blue-300">{{ __('Standard timeframe: :days business days', ['days' => \App\Models\Setting::get('sla_evaluation_business_days', 15)]) }}</p>
                    <ul class="mt-8 flex-1 space-y-3">
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Everything in the free listing') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Review of 18 editorial indicators') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Editorial Standards Score') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Detailed technical report') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Editorial Seal (if meets 75%+)') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Improvement recommendations') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block w-full rounded-xl bg-white py-3 text-center text-sm font-bold text-brand shadow-lg transition hover:bg-blue-50">
                        {{ __('Request Evaluation') }}
                    </a>
                </div>

                {{-- Bloque institucional desactivado 2026-05-10, ver roadmap #22 --}}
            </div>

            {{-- Seal Renewal (Sprint 3 #13) — bloque informativo al final de Scientific Journals --}}
            <div class="mx-auto mt-16 max-w-5xl">
                <div class="rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                    <div class="mb-8 text-center">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Renewal of the Editorial Seal') }}</h2>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">{{ __('Keep your certification active') }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        {{-- 1 año --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('1 Year') }}</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">
                                ${{ $price('seal-renewal-1y', '89') }}
                                <span class="text-xs font-normal text-gray-500">USD</span>
                            </p>
                        </div>
                        {{-- 2 años (highlight) --}}
                        <div class="rounded-xl bg-blue-50 p-6 text-center ring-1 ring-blue-200 dark:bg-blue-900/30 dark:ring-brand">
                            <p class="text-xs uppercase tracking-wide text-brand dark:text-blue-300">{{ __('2 Years') }} · {{ __('most picked') }}</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">
                                ${{ $price('seal-renewal-2y', '149') }}
                                <span class="text-xs font-normal text-gray-500">USD</span>
                            </p>
                        </div>
                        {{-- 3 años --}}
                        <div class="rounded-xl bg-blue-50 p-6 text-center ring-1 ring-blue-200 dark:bg-blue-900/20 dark:ring-blue-700">
                            <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">{{ __('3 Years') }} · {{ __('best value') }}</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">
                                ${{ $price('seal-renewal-3y', '199') }}
                                <span class="text-xs font-normal text-gray-500">USD</span>
                            </p>
                        </div>
                    </div>

                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Seal validity: up to 3 years depending on plan') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Early renewal (60-30 days before expiration): 10% discount') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('What is included: new evaluation + badge refresh + recommendations') }}
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ __("If your seal expires: the journal reverts to 'Evaluated' status; you keep your score, but lose the public badge until you renew.") }}
                        </li>
                    </ul>

                    @if(page_enabled('seal_renewal'))
                        <div class="mt-8 text-center">
                            <a href="{{ locale_path('/seal-renewal') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ __('Seal renewal information') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Book Listing --}}
            <div class="mx-auto mt-12 max-w-5xl">
                <h2 class="mb-8 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Academic Books') }}</h2>
                <div class="mx-auto max-w-lg rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/50">
                            <svg class="h-6 w-6 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Academic Book Listing') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Permanent inclusion in the academic publications index.') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ $price('book-listing', '49') }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400"> USD</span>
                        </div>
                    </div>
                    <ul class="mt-6 grid grid-cols-2 gap-3">
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Public profile with metadata') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Visibility in the search') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('ISBN, authors, publisher') }}
                        </li>
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ __('Permanent presence') }}
                        </li>
                    </ul>
                    <a href="/register" class="mt-6 block w-full rounded-xl bg-blue-500 py-3 text-center text-sm font-bold text-white shadow-md transition hover:bg-blue-400">
                        {{ __('Register Book') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Sprint 3.7 #38 — Pack Lanzamiento Editorial (standalone) --}}
    @php
        $launchPack = $products['new-journal-consulting'] ?? null;
    @endphp
    @if($launchPack && $launchPack->is_active)
    <section class="bg-slate-50 py-20 ">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-5xl">
                <div class="mb-12 text-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                        🚀 {{ __('Launch your journal') }}
                    </span>
                    <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $launchPack->getTranslationWithFallback('name') }}
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-gray-600 dark:text-gray-400">
                        {{ __('Launch your scientific journal from scratch: 3 one-on-one sessions with expert editors, domain and OJS hosting for 12 months, and full editorial and technical guidance.') }}
                    </p>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-blue-200 dark:bg-gray-800 dark:ring-blue-800">
                    <div class="grid gap-0 lg:grid-cols-5">
                        {{-- Lado izquierdo: features --}}
                        <div class="lg:col-span-3 p-8 lg:p-10">
                            <div class="grid gap-6 sm:grid-cols-2">
                                {{-- Editorial consulting --}}
                                <div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Editorial consulting') }}</h3>
                                    </div>
                                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <li>{{ __('3 sessions of 60 min with expert editors') }}</li>
                                        <li>{{ __('Scope and editorial policy') }}</li>
                                        <li>{{ __('Peer review process design') }}</li>
                                        <li>{{ __('COPE, anti-plagiarism, conflicts of interest') }}</li>
                                        <li>{{ __('Editorial board structure') }}</li>
                                    </ul>
                                </div>

                                {{-- Technical consulting --}}
                                <div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Technical consulting') }}</h3>
                                    </div>
                                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <li>{{ __('Initial OJS/PKP setup') }}</li>
                                        <li>{{ __('Submission → review → publication workflow') }}</li>
                                        <li>{{ __('DOI assignment and indexer registration') }}</li>
                                        <li>{{ __('OAI-PMH configuration') }}</li>
                                    </ul>
                                </div>

                                {{-- Infrastructure included --}}
                                <div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Infrastructure (12 months)') }}</h3>
                                    </div>
                                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <li>{{ __('Custom .org or .com domain') }}</li>
                                        <li>{{ __('Managed OJS hosting with daily backups') }}</li>
                                        <li>{{ __('SSL certificate') }}</li>
                                        <li>{{ __('Technical support 12 months') }}</li>
                                    </ul>
                                </div>

                                {{-- Launch accompaniment --}}
                                <div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-brand dark:bg-blue-900/50 dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Launch support') }}</h3>
                                    </div>
                                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <li>{{ __('Final site review before go-live') }}</li>
                                        <li>{{ __('First call-for-papers support') }}</li>
                                        <li>{{ __('Recommendations for initial indexers (DOAJ, Latindex, etc.)') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Lado derecho: precio + CTA --}}
                        <div class="lg:col-span-2 bg-brand-deep p-8 text-white lg:p-10">
                            <div class="flex h-full flex-col">
                                <p class="text-sm uppercase tracking-wide text-blue-200">{{ __('All-inclusive package') }}</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-5xl font-extrabold">${{ number_format($launchPack->price, 0) }}</span>
                                    <span class="text-xl font-medium text-blue-200">USD</span>
                                </div>
                                <p class="mt-2 text-sm text-blue-100">{{ __('One-time payment · No recurring fees') }}</p>

                                <ul class="mt-6 space-y-2 text-sm">
                                    <li class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('3 consulting sessions') }}
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Domain + hosting for 12 months') }}
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Editorial + technical guidance') }}
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Launch accompaniment') }}
                                    </li>
                                </ul>

                                <a href="{{ locale_path('/checkout/new-journal-consulting') }}"
                                   class="mt-auto block w-full rounded-xl bg-white py-3.5 text-center text-base font-bold text-brand shadow-lg transition hover:bg-blue-50">
                                    {{ __('Start launching your journal →') }}
                                </a>
                                <p class="mt-3 text-center text-xs text-blue-200">
                                    {{ __('Secure checkout via Stripe') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Add-ons --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-5xl">
                <h2 class="mb-4 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Add-ons') }}</h2>
                <p class="mb-12 text-center text-gray-500 dark:text-gray-400">{{ __('Additional services to enhance your editorial evaluation.') }}</p>

                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- Action Plan + Consulting (antes Premium Report — roadmap #17) --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-blue-200 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Action Plan + Consulting') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Specific recommendations per criterion, 30-min consulting session with the evaluator and a prioritized roadmap.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $price('action-plan-consulting', '215') }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Re-evaluation --}}
                    <div class="flex items-start gap-4 rounded-xl border border-gray-200 p-6 transition hover:border-blue-200 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <svg class="h-5 w-5 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Editorial Re-evaluation') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Full new evaluation to improve your score or achieve the seal.') }}</p>
                                </div>
                                <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">${{ $price('journal-reevaluation', '99') }} <span class="text-xs font-normal text-gray-500">USD</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Seal Renewal — escalera (roadmap #14) --}}
                    <div class="sm:col-span-2 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                                <svg class="h-5 w-5 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Seal Renewal') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Extends the Editorial Standards Seal validity. Longer terms unlock better per-year pricing.') }}</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg bg-gray-50 p-3 text-center dark:bg-gray-800">
                                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('1 Year') }}</p>
                                        <p class="text-xl font-extrabold text-gray-900 dark:text-white">${{ $price('seal-renewal-1y', '89') }} <span class="text-xs font-normal text-gray-500">USD</span></p>
                                    </div>
                                    <div class="rounded-lg bg-blue-50 p-3 text-center ring-1 ring-blue-200 dark:bg-blue-900/30 dark:ring-brand">
                                        <p class="text-xs uppercase tracking-wide text-brand dark:text-blue-300">{{ __('2 Years') }} · {{ __('most picked') }}</p>
                                        <p class="text-xl font-extrabold text-gray-900 dark:text-white">${{ $price('seal-renewal-2y', '149') }} <span class="text-xs font-normal text-gray-500">USD</span></p>
                                    </div>
                                    <div class="rounded-lg bg-blue-50 p-3 text-center ring-1 ring-blue-200 dark:bg-blue-900/20 dark:ring-blue-700">
                                        <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">{{ __('3 Years') }} · {{ __('best value') }}</p>
                                        <p class="text-xl font-extrabold text-gray-900 dark:text-white">${{ $price('seal-renewal-3y', '199') }} <span class="text-xs font-normal text-gray-500">USD</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Express se ofrece como uplift en el checkout (roadmap #15) --}}
                <p class="mx-auto mt-8 max-w-3xl text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Express service is available as a +$:amount upgrade during evaluation or re-evaluation checkout (:express business days instead of :standard).', [
                        'amount' => 50,
                        'express' => \App\Models\Setting::get('sla_evaluation_express_business_days', 5),
                        'standard' => \App\Models\Setting::get('sla_evaluation_business_days', 15),
                    ]) }}
                </p>
            </div>
        </div>
    </section>

    {{-- Independence Notice --}}
    <section class="bg-gray-50 py-8 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl rounded-xl border-2 border-blue-200 bg-blue-50 p-6 text-center dark:border-blue-700 dark:bg-blue-900/20">
                <p class="font-semibold text-blue-800 dark:text-blue-300">{{ __('Independence principle') }}</p>
                <p class="mt-2 text-sm text-blue-700 dark:text-blue-400">{{ __('Payment for the evaluation process does not guarantee obtaining the Editorial Standards Seal. The result depends exclusively on meeting the defined technical criteria.') }}</p>
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
                        [__('Do you offer evaluation for institutions with multiple journals?'), __('Yes. For institutions managing several journals, please contact our team to receive a personalized proposal.')],
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
            <div class="relative overflow-hidden rounded-3xl bg-brand p-8 text-center text-white shadow-2xl md:p-16">
                <div class="absolute inset-0 bg-brand-deep"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Want to evaluate your journal?') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-blue-100">{{ __('Register your journal for free and start the path to editorial excellence.') }}</p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-brand shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
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
