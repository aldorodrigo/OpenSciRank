<x-layouts.app :title="__('Editorial Standards Platform - Editorial Evaluation for Scientific Journals')" :description="__('Global platform for technical evaluation and visibility for scientific journals. Transparent criteria, Editorial Standards Seal and academic publications directory.')">
    <x-slot:header>true</x-slot:header>

    {{-- Data queries --}}
    @php
        try {
            $publicStatuses = ['listed', 'evaluated', 'certified'];
            $journalCount = \App\Models\Journal::whereIn('status', $publicStatuses)->count();
            $certifiedCount = \App\Models\Journal::where('status', 'certified')->where('seal_status', 'active')->count();
            $bookCount = \App\Models\Book::where('status', 'listed')->count();
            $countryCount = \App\Models\Journal::whereIn('status', $publicStatuses)
                ->whereNotNull('country_code')->distinct('country_code')->count('country_code');
            $openAccessCount = \App\Models\Journal::whereIn('status', $publicStatuses)
                ->where('is_open_access', true)->count();
            $featuredJournals = \App\Models\Journal::whereIn('status', $publicStatuses)
                ->orderByRaw('current_score IS NULL, current_score DESC')
                ->take(8)
                ->get();
        } catch (\Exception $e) {
            $journalCount = 0;
            $certifiedCount = 0;
            $bookCount = 0;
            $countryCount = 0;
            $openAccessCount = 0;
            $featuredJournals = collect();
        }
    @endphp

    {{-- Hero Section --}}
    <section class="relative min-h-[80vh] flex items-center overflow-hidden bg-indigo-900 text-white">
        {{-- High-end background --}}
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600 opacity-90"></div>
            {{-- Decorative mesh/pattern --}}
            <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            {{-- Animated-like blurs --}}
            <div class="absolute -left-1/4 -top-1/4 h-[80%] w-[80%] rounded-full bg-indigo-500/30 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[80%] w-[80%] rounded-full bg-purple-500/20 blur-[120px]"></div>
        </div>

        <div class="container relative z-10 mx-auto px-4 py-24 text-center">
            <div class="mx-auto mb-8 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur-md border border-white/20">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ __('Global Editorial Quality Standards') }}
            </div>
            <h1 class="mx-auto max-w-4xl text-5xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl leading-tight">
                {{ __('Strengthening the editorial quality of science') }}
            </h1>
            <p class="mx-auto mt-8 max-w-2xl text-xl text-indigo-100/90 leading-relaxed">
                {{ __('Independent technical evaluation for scientific journals and global index of academic books. Transparency, rigor and international recognition.') }}
            </p>
            {{-- Data highlight badges --}}
            <div class="mx-auto mt-6 inline-flex flex-wrap items-center justify-center gap-3 text-sm font-medium text-indigo-200/80">
                <span class="rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm border border-white/10">{{ __('17 indicators') }}</span>
                <span class="text-indigo-300/50">&middot;</span>
                <span class="rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm border border-white/10">{{ __('5 evaluation areas') }}</span>
                <span class="text-indigo-300/50">&middot;</span>
                <span class="rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm border border-white/10">{{ __('Public criteria') }}</span>
            </div>
            <div class="mt-12 flex flex-col items-center justify-center gap-5 sm:flex-row">
                <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-2xl bg-white px-10 py-5 text-lg font-bold text-indigo-700 shadow-2xl transition-all hover:scale-105 hover:bg-indigo-50 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-6 w-6 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Register my Journal for Free') }}
                </a>
                <a href="/pricing" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10 px-10 py-5 text-lg font-bold text-white backdrop-blur-md transition-all hover:border-white hover:bg-white/20 active:scale-95">
                    {{ __('Request Evaluation') }} →
                </a>
            </div>
            <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-8 text-indigo-100/60 font-medium">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Public Criteria') }}
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Technical Independence') }}
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Verifiable Seal') }}
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section with count-up animation --}}
    <section class="border-b border-gray-200 bg-white py-16 dark:border-gray-800 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div
                x-data="{
                    shown: false,
                    animateCounter(el, target) {
                        if (target === 0) { el.textContent = '0'; return; }
                        let start = 0;
                        const duration = 1500;
                        const startTime = performance.now();
                        const step = (currentTime) => {
                            const elapsed = currentTime - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3);
                            const current = Math.round(eased * target);
                            el.textContent = current.toLocaleString();
                            if (progress < 1) requestAnimationFrame(step);
                        };
                        requestAnimationFrame(step);
                    },
                    startAnimation() {
                        if (this.shown) return;
                        this.shown = true;
                        this.$nextTick(() => {
                            this.$refs.statContainer.querySelectorAll('[data-target]').forEach(el => {
                                this.animateCounter(el, parseInt(el.dataset.target));
                            });
                        });
                    }
                }"
                x-init="
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) { startAnimation(); observer.disconnect(); }
                        });
                    }, { threshold: 0.2 });
                    observer.observe($refs.statContainer);
                "
                x-ref="statContainer"
            >
                <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:gap-8">
                    {{-- Revistas --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-indigo-50 to-white p-6 text-center transition hover:shadow-lg dark:from-indigo-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 lg:text-5xl" data-target="{{ $journalCount }}">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Journals in the directory') }}</div>
                    </div>

                    {{-- Certificadas --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-emerald-50 to-white p-6 text-center transition hover:shadow-lg dark:from-emerald-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400 lg:text-5xl" data-target="{{ $certifiedCount }}">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Journals with active seal') }}</div>
                    </div>

                    {{-- Países --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-purple-50 to-white p-6 text-center transition hover:shadow-lg dark:from-purple-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 003 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-purple-600 dark:text-purple-400 lg:text-5xl" data-target="{{ $countryCount }}">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Countries represented') }}</div>
                    </div>

                    {{-- Libros --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-amber-50 to-white p-6 text-center transition hover:shadow-lg dark:from-amber-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-amber-600 dark:text-amber-400 lg:text-5xl" data-target="{{ $bookCount }}">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Academic books') }}</div>
                    </div>

                    {{-- Indicadores --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-sky-50 to-white p-6 text-center transition hover:shadow-lg dark:from-sky-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-sky-600 dark:text-sky-400 lg:text-5xl" data-target="17">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Indicators evaluated') }}</div>
                    </div>

                    {{-- Open Access --}}
                    <div class="group rounded-2xl bg-gradient-to-br from-teal-50 to-white p-6 text-center transition hover:shadow-lg dark:from-teal-950/50 dark:to-gray-900 lg:p-8">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        </div>
                        <div class="text-4xl font-extrabold text-teal-600 dark:text-teal-400 lg:text-5xl" data-target="{{ $openAccessCount }}">0</div>
                        <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Open Access Journals') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Journals (moved up) --}}
    @if($featuredJournals->count() > 0)
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ __('Journals in our directory') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">{{ __('Publications participating in the editorial evaluation system.') }}</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredJournals as $fj)
                @php
                    $countryFlags = [
                        'US' => "\u{1F1FA}\u{1F1F8}", 'GB' => "\u{1F1EC}\u{1F1E7}", 'DE' => "\u{1F1E9}\u{1F1EA}",
                        'FR' => "\u{1F1EB}\u{1F1F7}", 'IT' => "\u{1F1EE}\u{1F1F9}", 'BR' => "\u{1F1E7}\u{1F1F7}",
                        'CH' => "\u{1F1E8}\u{1F1ED}", 'NL' => "\u{1F1F3}\u{1F1F1}", 'ES' => "\u{1F1EA}\u{1F1F8}",
                        'MX' => "\u{1F1F2}\u{1F1FD}", 'AR' => "\u{1F1E6}\u{1F1F7}", 'CO' => "\u{1F1E8}\u{1F1F4}",
                        'CL' => "\u{1F1E8}\u{1F1F1}", 'PE' => "\u{1F1F5}\u{1F1EA}", 'JP' => "\u{1F1EF}\u{1F1F5}",
                        'CN' => "\u{1F1E8}\u{1F1F3}", 'IN' => "\u{1F1EE}\u{1F1F3}", 'AU' => "\u{1F1E6}\u{1F1FA}",
                        'CA' => "\u{1F1E8}\u{1F1E6}", 'KR' => "\u{1F1F0}\u{1F1F7}", 'SE' => "\u{1F1F8}\u{1F1EA}",
                        'NO' => "\u{1F1F3}\u{1F1F4}", 'DK' => "\u{1F1E9}\u{1F1F0}", 'FI' => "\u{1F1EB}\u{1F1EE}",
                        'AT' => "\u{1F1E6}\u{1F1F9}", 'PT' => "\u{1F1F5}\u{1F1F9}", 'PL' => "\u{1F1F5}\u{1F1F1}",
                        'CU' => "\u{1F1E8}\u{1F1FA}", 'EC' => "\u{1F1EA}\u{1F1E8}", 'VE' => "\u{1F1FB}\u{1F1EA}",
                    ];
                    $flag = $countryFlags[$fj->country_code] ?? null;
                @endphp
                <a href="{{ route('journal.show', $fj->slug) }}" class="group rounded-xl border border-gray-200 bg-white p-6 transition hover:border-indigo-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600" style="text-decoration:none;">
                    <div class="mb-4 flex items-center gap-4">
                        @if($fj->logo)
                            <img src="{{ Storage::url($fj->logo) }}" alt="{{ $fj->getTranslationWithFallback('title') }}" class="h-12 w-12 rounded-lg object-cover">
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $fj->getTranslationWithFallback('title') }}</h3>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                @if($flag)<span class="mr-1">{{ $flag }}</span>@endif
                                {{ $fj->publishing_institution ? $fj->getTranslationWithFallback('publishing_institution') : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($fj->current_score && $fj->current_score >= 75)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                                Certified
                            </span>
                        @elseif($fj->current_score)
                            <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-800 dark:bg-purple-900/50 dark:text-purple-400">
                                Evaluated
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Listed
                            </span>
                        @endif
                        @if($fj->is_open_access)
                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-100 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-900/50 dark:text-teal-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75" /></svg>
                                OA
                            </span>
                        @endif
                        @if($fj->current_score)
                            <div class="ml-auto flex items-center gap-2">
                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full {{ $fj->current_score >= 75 ? 'bg-emerald-500' : ($fj->current_score >= 50 ? 'bg-amber-500' : 'bg-gray-400') }}" style="width: {{ min($fj->current_score, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $fj->current_score }}%</span>
                            </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="/search" class="inline-flex items-center font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400" style="cursor:pointer;">
                    {{ __('View full directory') }} →
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- How It Works --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ __('How does it work?') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">{{ __('A transparent process of registration, evaluation and editorial recognition.') }}</p>
            </div>
            <div class="relative mx-auto max-w-5xl">
                <div class="absolute left-1/2 top-12 hidden h-0.5 w-2/3 -translate-x-1/2 bg-gradient-to-r from-indigo-200 via-purple-200 to-emerald-200 dark:from-indigo-800 dark:via-purple-800 dark:to-emerald-800 md:block"></div>
                <div class="grid gap-8 md:grid-cols-3">
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-indigo-100 text-4xl dark:bg-indigo-900/50">📝</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Step 1') }}</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ __('Registration and Listing') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Register your journal for free. Get a public profile with basic editorial information in our directory.') }}</p>
                    </div>
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-purple-100 text-4xl dark:bg-purple-900/50">🔍</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-purple-600 dark:text-purple-400">{{ __('Step 2') }}</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ __('Editorial Evaluation') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Request a technical editorial evaluation. Our team reviews 5 areas with verifiable indicators and assigns an Editorial Standards Score.') }}</p>
                    </div>
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-emerald-100 text-4xl dark:bg-emerald-900/50">✅</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ __('Step 3') }}</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ __('Report and Seal') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Receive a detailed technical report. If you reach ≥75 points and meet the critical indicators, get the Editorial Standards Seal.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ __('Project principles') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">{{ __('A system based on transparency, rigor and independence.') }}</p>
            </div>
            <div class="grid gap-8 md:grid-cols-3 lg:grid-cols-5">
                @php
                    $principles = [
                        ['🔍', __('Transparency'), __('Editorial evaluation criteria are public and accessible.')],
                        ['🧪', __('Technical Rigor'), __('Evaluations are based on verifiable indicators.')],
                        ['⚖️', __('Independence'), __('Results do not depend on payment for the evaluation process.')],
                        ['🔄', __('Continuous Improvement'), __('Evaluations seek to strengthen the editorial quality of journals.')],
                        ['🌐', __('Access to Knowledge'), __('We promote the visibility of scientific publications.')],
                    ];
                @endphp
                @foreach($principles as [$icon, $title, $desc])
                <div class="rounded-xl bg-white p-6 text-center shadow-lg transition hover:shadow-xl dark:bg-gray-900">
                    <div class="mb-3 text-3xl">{{ $icon }}</div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Participation Model --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ __('Participation model') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">{{ __('Journals can be listed, evaluated and, if they meet the standards, obtain the editorial seal.') }}</p>
            </div>
            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-3">
                <div class="rounded-xl border-2 border-gray-200 bg-white p-8 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 text-3xl">📋</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Listed Journal</h3>
                    <p class="mb-4 text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('Listed Journal') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Public journal profile') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Visibility in the directory') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Basic editorial information') }}</li>
                        <li class="flex items-start gap-2"><span class="text-gray-400">—</span> <span class="text-gray-400">{{ __('Does not imply evaluation') }}</span></li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Free') }}</p>
                </div>

                <div class="rounded-xl border-2 border-purple-200 bg-white p-8 shadow-lg dark:border-purple-700 dark:bg-gray-800">
                    <div class="mb-4 text-3xl">📊</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Evaluated Journal</h3>
                    <p class="mb-4 text-sm font-medium text-purple-600 dark:text-purple-400">{{ __('Evaluated Journal') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Complete technical report') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Results by criteria') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Editorial Standards Score</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Improvement recommendations') }}</li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-purple-600 dark:text-purple-400">{{ __('Evaluation process') }}</p>
                </div>

                <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-8 shadow-lg dark:border-emerald-700 dark:bg-emerald-900/20">
                    <div class="mb-4 text-3xl">🏅</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Certified Journal</h3>
                    <p class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">Editorial Standards Seal</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Score ≥ 75 points') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Meets critical indicators') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Verifiable editorial seal') }}</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> {{ __('Downloadable certificate') }}</li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Based on compliance') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="relative py-20 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>
                <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-purple-400/20 blur-3xl"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">{{ __('Want to strengthen the editorial quality of your journal?') }}</h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-indigo-100 sm:text-xl">
                        {{ __('Register your journal for free. Request a technical evaluation based on transparent criteria and get the Editorial Standards Seal if you meet the standards.') }}
                    </p>

                    <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-10 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            <span class="relative">{{ __('Register my Journal — Free') }}</span>
                        </a>
                        <a href="/contact" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-10 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            {{ __('Contact the team') }}
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-4 text-sm font-medium text-white/70">
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             {{ __('Free registration') }}
                        </span>
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             {{ __('Public technical criteria') }}
                        </span>
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             {{ __('Guaranteed independence') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
