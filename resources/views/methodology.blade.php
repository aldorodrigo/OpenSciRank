<x-layouts.app :title="__('Methodology - Editorial Standards Platform')" :description="__('Learn about the editorial evaluation methodology used by Editorial Standards Platform to evaluate scientific journals.')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-emerald-600 to-teal-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">{{ __('Editorial Evaluation Methodology') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-emerald-100">{{ __('Transparent evaluation based on five fundamental areas of editorial standards.') }}</p>
        </div>
    </section>

    {{-- Methodology Content --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                {{-- Introduction --}}
                <div class="prose prose-lg max-w-none dark:prose-invert">
                    <h2>{{ __('Evaluation Principles') }}</h2>
                    <p>{{ __('Editorial Standards Platform evaluates scientific journals through a structured and transparent process. The evaluation is based on verifiable technical criteria, organized into five fundamental areas covering the most important aspects of editorial practice.') }}</p>
                    <p>{{ __('The evaluation does not constitute an assessment of the scientific content published by the journal, but rather the compliance with criteria related to transparency and editorial practices.') }}</p>
                </div>

                {{-- 5 Evaluation Areas --}}
                <div class="mt-12">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Evaluation Areas') }}</h2>
                    <div class="space-y-6">
                        {{-- Area 1 --}}
                        <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                    <span class="text-xl font-bold">1</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Editorial Identity') }}</h3>
                                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400">{{ __('20 points') }}</span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• {{ __('Visible and valid ISSN') }}</li>
                                        <li>• {{ __('Publishing institution clearly identified') }}</li>
                                        <li>• {{ __('Visible editorial board') }}</li>
                                        <li>• {{ __('Visible institutional contact information') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Area 2 --}}
                        <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                                    <span class="text-xl font-bold">2</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Transparency of the Editorial Process') }}</h3>
                                        <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-700 dark:bg-purple-900/50 dark:text-purple-400">{{ __('25 points') }}</span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• {{ __('Visible peer review policy') }}</li>
                                        <li>• {{ __('Clear author guidelines') }}</li>
                                        <li>• {{ __('Editorial process clearly described') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Area 3 --}}
                        <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-pink-100 text-pink-600 dark:bg-pink-900/50 dark:text-pink-400">
                                    <span class="text-xl font-bold">3</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Editorial Ethics') }}</h3>
                                        <span class="rounded-full bg-pink-100 px-3 py-1 text-xs font-bold text-pink-700 dark:bg-pink-900/50 dark:text-pink-400">{{ __('20 points') }}</span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• {{ __('Editorial ethics policy') }}</li>
                                        <li>• {{ __('Anti-plagiarism policy') }}</li>
                                        <li>• {{ __('Retraction or correction policy') }}</li>
                                        <li>• {{ __('Conflict of interest declaration') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Area 4 --}}
                        <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                                    <span class="text-xl font-bold">4</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Access and Rights') }}</h3>
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">{{ __('15 points') }}</span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• {{ __('Access model clearly informed') }}</li>
                                        <li>• {{ __('Visible license or terms of use') }}</li>
                                        <li>• {{ __('Copyright clearly informed') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Area 5 --}}
                        <div class="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                                    <span class="text-xl font-bold">5</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Technical Infrastructure') }}</h3>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">{{ __('20 points') }}</span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• {{ __('Functional website') }}</li>
                                        <li>• {{ __('Accessible archive of issues or publications') }}</li>
                                        <li>• {{ __('Persistent identifiers (DOI, ORCID)') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Scoring System --}}
                <div class="mt-16">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Scoring System') }}</h2>
                    <div class="rounded-xl bg-gray-50 p-8 dark:bg-gray-800">
                        <p class="mb-6 text-gray-600 dark:text-gray-400">{{ __('Each indicator is evaluated using a simple scale:') }}</p>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg bg-emerald-50 p-4 text-center dark:bg-emerald-900/20">
                                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ __('Meets') }}</div>
                                <div class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">{{ __('100% of score') }}</div>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-4 text-center dark:bg-amber-900/20">
                                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ __('Partial') }}</div>
                                <div class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ __('50% of score') }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-100 p-4 text-center dark:bg-gray-700">
                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ __('Does not meet') }}</div>
                                <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ __('0% of score') }}</div>
                            </div>
                        </div>
                        <div class="mt-8 rounded-lg border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-800 dark:bg-indigo-900/20">
                            <p class="font-semibold text-indigo-900 dark:text-indigo-200">{{ __('Maximum total score: 100 points') }}</p>
                            <p class="mt-2 text-sm text-indigo-700 dark:text-indigo-300">{{ __('The Editorial Standards Score represents the level of editorial compliance of the journal according to the defined criteria.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Editorial Standards Seal --}}
                <div class="mt-16">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Obtaining the Editorial Standards Seal') }}</h2>
                    <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-8 dark:border-emerald-700 dark:bg-emerald-900/20">
                        <p class="mb-6 text-gray-700 dark:text-gray-300">{{ __('To obtain the editorial seal the journal must meet two conditions:') }}</p>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <h4 class="mb-3 font-semibold text-emerald-800 dark:text-emerald-300">{{ __('1. Reach a minimum of 75 points out of 100') }}</h4>
                                <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: 75%"></div>
                                </div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Minimum editorial compliance threshold.') }}</p>
                            </div>
                            <div>
                                <h4 class="mb-3 font-semibold text-emerald-800 dark:text-emerald-300">{{ __('2. Meet the critical indicators') }}</h4>
                                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>✓ {{ __('Visible and valid ISSN') }}</li>
                                    <li>✓ {{ __('Peer review policy') }}</li>
                                    <li>✓ {{ __('Editorial ethics policy') }}</li>
                                    <li>✓ {{ __('Rights or license information') }}</li>
                                    <li>✓ {{ __('Functional website') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Process Timeline --}}
                <div class="mt-16">
                    <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Evaluation Process') }}</h2>
                    <p class="mb-10 text-gray-600 dark:text-gray-400">{{ __('From registration to obtaining the editorial seal.') }}</p>
                    <div class="relative">
                        <div class="absolute left-6 top-0 h-full w-0.5 bg-gradient-to-b from-indigo-500 via-purple-500 to-emerald-500 sm:left-1/2 sm:-translate-x-px"></div>
                        @php
                            $steps = [
                                ['📝', __('Journal Registration'), __('The editor registers their journal on the platform and completes basic editorial information. The journal is listed in the directory for free.'), 'indigo'],
                                ['📋', __('Evaluation Request'), __('The journal requests a formal technical editorial evaluation process. This request has an associated cost covering the audit process.'), 'purple'],
                                ['🔍', __('Criteria Review'), __('The evaluation team reviews the editorial indicators, analyzes available evidence and assigns scores according to the defined criteria.'), 'pink'],
                                ['📊', __('Technical Report'), __('A detailed technical report is generated with the Editorial Standards Score, results by criterion and editorial recommendations.'), 'amber'],
                                ['✅', __('Result'), __('If the journal reaches ≥75 points and meets the critical indicators, it obtains the Editorial Standards Seal. If not, it receives the report with improvement recommendations.'), 'emerald'],
                            ];
                        @endphp
                        @foreach($steps as $i => [$icon, $title, $desc, $color])
                        <div class="relative mb-10 flex items-start gap-8 pl-16 sm:pl-0 {{ $i % 2 === 0 ? 'sm:flex-row' : 'sm:flex-row-reverse' }}">
                            <div class="absolute left-0 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white shadow-md dark:bg-gray-900 sm:relative sm:left-auto">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $color }}-100 text-xl dark:bg-{{ $color }}-900/50">{{ $icon }}</div>
                            </div>
                            <div class="flex-1 rounded-xl bg-gray-50 p-5 dark:bg-gray-800 {{ $i % 2 === 0 ? 'sm:text-left' : 'sm:text-right' }}">
                                <div class="text-xs font-bold uppercase tracking-wider text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ __('Step') }} {{ $i + 1 }}</div>
                                <h3 class="mt-1 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Independence Notice --}}
                <div class="mt-16 rounded-2xl bg-amber-50 p-8 dark:bg-amber-900/20">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">⚖️ {{ __('Independence Principle') }}</h2>
                    <p class="text-gray-700 dark:text-gray-300">{{ __('Payment for the evaluation process does not guarantee obtaining the editorial seal. The result depends exclusively on compliance with the technical criteria defined by the platform. Evaluators do not receive incentives based on results.') }}</p>
                </div>

                {{-- CTA --}}
                <div class="relative mt-20 overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-12">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>
                    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-purple-400/20 blur-3xl"></div>

                    <div class="relative z-10">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Ready to evaluate your journal?') }}</h2>
                        <p class="mx-auto mt-4 max-w-xl text-lg text-indigo-100">{{ __('Register your journal for free and request the editorial evaluation to get your quality seal.') }}</p>

                        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                                <span class="relative">{{ __('Register my Journal') }}</span>
                            </a>
                            <a href="/contact" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                                {{ __('Contact the team') }}
                            </a>
                        </div>

                        <p class="mt-8 text-sm font-medium text-white/60">{{ __('Join over 500 global publications that trust our standards.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
