<x-layouts.app :title="__('Seal Renewal — Editorial Standards Platform')" :description="__('Renewal of the Editorial Seal')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-brand-deep py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Keep your seal active') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-blue-200">{{ __('Renewal of the Editorial Seal') }} — {{ __('Keep your certification active') }}</p>
            <div class="mt-8">
                @auth
                    <a href="{{ route('app.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-3 font-bold text-brand shadow-sm transition hover:bg-blue-50">
                        {{ __('Renew my seal') }}
                    </a>
                @else
                    <a href="/register" class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-3 font-bold text-brand shadow-sm transition hover:bg-blue-50">
                        {{ __('Renew my seal') }}
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- Benefits --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-5xl">
                <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Benefits of an active seal') }}</h2>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                            <svg class="h-6 w-6 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Visibility in the directory and search') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('A live editorial seal puts your journal at the top of search results and unlocks the certified filter for readers and authors.') }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                            <svg class="h-6 w-6 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Public badge on your editorial site') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __("Embed the live SVG badge on your journal's website. The badge updates automatically if you renew on time.") }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                            <svg class="h-6 w-6 text-brand dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Presence in our reports') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Certified journals are included in our annual editorial standards reports, distributed to institutions and indexers worldwide.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Early renewal discount --}}
    <section class="bg-amber-50 py-16 dark:bg-amber-900/10">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl rounded-2xl border-2 border-amber-300 bg-white p-8 text-center shadow-lg dark:border-amber-700 dark:bg-gray-900">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40">
                    <svg class="h-7 w-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Early renewal discount') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-700 dark:text-gray-300">{{ __('Renew between 60 and 30 days before your seal expires and we apply an automatic :pct% discount on any renewal plan (1, 2 or 3 years).', ['pct' => 10]) }}</p>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('What editors are saying') }}</h2>
            <div class="mx-auto grid max-w-5xl gap-6 md:grid-cols-3">
                <figure class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                    <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 7H6a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2H6v2h1a4 4 0 004-4V9a2 2 0 00-2-2zm10 0h-3a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2h-1v2h1a4 4 0 004-4V9a2 2 0 00-2-2z"/></svg>
                    <blockquote class="mt-4 flex-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Renewing the seal was straightforward — the new evaluation pointed out exactly what we had to improve and we are now in the 80% range.') }}
                    </blockquote>
                    <figcaption class="mt-4 border-t border-gray-200 pt-4 text-xs dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">Dra. María Lozano</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Editor-in-chief') }} · Revista Latinoamericana de Biociencias</p>
                    </figcaption>
                </figure>

                <figure class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                    <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 7H6a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2H6v2h1a4 4 0 004-4V9a2 2 0 00-2-2zm10 0h-3a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2h-1v2h1a4 4 0 004-4V9a2 2 0 00-2-2z"/></svg>
                    <blockquote class="mt-4 flex-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('The early renewal discount was a welcome surprise; we always plan our editorial budget months in advance.') }}
                    </blockquote>
                    <figcaption class="mt-4 border-t border-gray-200 pt-4 text-xs dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">Prof. João Almeida</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Managing editor') }} · Cadernos de Pesquisa Aplicada</p>
                    </figcaption>
                </figure>

                <figure class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                    <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 7H6a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2H6v2h1a4 4 0 004-4V9a2 2 0 00-2-2zm10 0h-3a2 2 0 00-2 2v3a2 2 0 002 2h3v3a2 2 0 01-2 2h-1v2h1a4 4 0 004-4V9a2 2 0 00-2-2z"/></svg>
                    <blockquote class="mt-4 flex-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Keeping the seal active gives our authors and readers a clear signal of editorial commitment.') }}
                    </blockquote>
                    <figcaption class="mt-4 border-t border-gray-200 pt-4 text-xs dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">Sara Kennedy</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Publisher director') }} · Journal of Editorial Methodology</p>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- FAQs --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">{{ __('Frequently asked questions about renewal') }}</h2>
            <div class="mx-auto max-w-3xl space-y-4">
                @php
                    $faqs = [
                        [__('When can I renew my seal?'), __('You can start the renewal any time from your dashboard, but the best window is 60 to 30 days before expiration — that is when the automatic 10% discount applies.')],
                        [__('Does the renewal include a new evaluation?'), __('Yes. Every renewal triggers a fresh editorial evaluation against current criteria. If you score 75% or higher with all critical indicators met, the seal is extended automatically.')],
                        [__('What happens if my seal expires?'), __("Your journal reverts to 'Evaluated' status. You keep your last score but lose the public badge and the certified filter spot until you renew.")],
                        [__('Can I pick a longer renewal term?'), __('Yes. We offer 1, 2 and 3 year renewals. Longer terms unlock better per-year pricing.')],
                        [__('Does the discount stack with manual coupons?'), __('No. A manual coupon takes precedence: if you enter a code at checkout, the automatic early-renewal discount is not applied.')],
                        [__('What if I miss the early window?'), __('You can still renew at the standard price between 30 days before and after expiration. The seal remains valid until the expiration date.')],
                        [__('Will I lose my previous evaluation history?'), __('Never. Every evaluation, score and observation is preserved in your dashboard for transparency, even after renewals.')],
                    ];
                @endphp
                @foreach($faqs as $faq)
                    <details class="group rounded-xl border border-gray-200 bg-white p-4 transition-colors hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-gray-600">
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
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-brand p-8 text-center text-white shadow-2xl md:p-16">
                <div class="absolute inset-0 bg-brand-deep"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Keep your seal active') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-blue-100">{{ __('Renew between 60 and 30 days before your seal expires and we apply an automatic :pct% discount on any renewal plan (1, 2 or 3 years).', ['pct' => 10]) }}</p>
                    <div class="mt-10 flex items-center justify-center">
                        @auth
                            <a href="{{ route('app.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 font-bold text-brand shadow-lg transition hover:scale-105 hover:bg-blue-50">
                                {{ __('Renew my seal') }}
                            </a>
                        @else
                            <a href="/register" class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 font-bold text-brand shadow-lg transition hover:scale-105 hover:bg-blue-50">
                                {{ __('Renew my seal') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
