<x-layouts.app :title="__('About Us - Editorial Standards Platform')" :description="__('Learn about the purpose, principles and scope of Editorial Standards Platform, global editorial evaluation platform for scientific journals.')">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-24 text-white">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
        <div class="container relative mx-auto px-4 text-center">
            <div class="mb-4 inline-flex items-center rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                🌍 {{ __('Global reach · Transparent editorial evaluation') }}
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Editorial Standards Platform
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-indigo-100 sm:text-xl">
                {{ __('Global platform for technical evaluation and visibility for scientific journals based on transparent criteria.') }}
            </p>
        </div>
    </section>

    {{-- Purpose --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-2">
                <div class="rounded-2xl bg-indigo-50 p-8 dark:bg-indigo-900/20">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Purpose') }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('To contribute to transparency and trust in scientific communication through structured editorial evaluation for scientific journals, public visibility of results and academic book indexing.') }}</p>
                </div>
                <div class="rounded-2xl bg-purple-50 p-8 dark:bg-purple-900/20">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    </div>
                    <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Scope') }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('The project has a global scope and seeks to contribute to the strengthening of international scientific communication, working with scientific journals and academic books from around the world.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">{{ __('Problem we address') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $problems = [
                            ['🔎', __('Difficulty identifying journals with good editorial practices')],
                            ['🔒', __('Lack of transparency in some editorial processes')],
                            ['📈', __('Growth of journals with unclear standards')],
                            ['📖', __('Low visibility of academic and scientific books')],
                            ['🗂️', __('Dispersion of information about editorial policies')],
                        ];
                    @endphp
                    @foreach($problems as [$icon, $desc])
                    <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                        <span class="text-2xl">{{ $icon }}</span>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Principles --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ __('Project Principles') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">{{ __('Five fundamental principles that guide the editorial evaluation system.') }}</p>
            </div>
            <div class="mx-auto grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @php
                    $principles = [
                        ['🔍', __('Transparency'), __('Editorial evaluation criteria are public.')],
                        ['🧪', __('Technical Rigor'), __('Evaluations are based on verifiable indicators.')],
                        ['⚖️', __('Independence'), __('Results do not depend on payment for the process.')],
                        ['🔄', __('Continuous Improvement'), __('Evaluations seek to strengthen editorial quality.')],
                        ['🌐', __('Access to Knowledge'), __('We promote the visibility of scientific publications.')],
                    ];
                @endphp
                @foreach($principles as [$icon, $title, $desc])
                <div class="rounded-xl bg-gray-50 p-6 text-center shadow-sm transition hover:shadow-md dark:bg-gray-800">
                    <div class="mb-4 text-4xl">{{ $icon }}</div>
                    <h3 class="mb-2 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- What it is / What it is not --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-2">
                <div class="rounded-2xl bg-emerald-50 p-8 dark:bg-emerald-900/20">
                    <h3 class="mb-4 text-xl font-bold text-emerald-800 dark:text-emerald-300">✅ {{ __('What the platform is') }}</h3>
                    <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <li>• {{ __('An editorial evaluation system for scientific journals') }}</li>
                        <li>• {{ __('A structured directory of academic journals') }}</li>
                        <li>• {{ __('An index of scientific and academic books') }}</li>
                        <li>• {{ __('An editorial transparency tool') }}</li>
                        <li>• {{ __('A visibility space for scientific publications') }}</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-red-50 p-8 dark:bg-red-900/20">
                    <h3 class="mb-4 text-xl font-bold text-red-800 dark:text-red-300">❌ {{ __('What the platform is not') }}</h3>
                    <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <li>• {{ __('It is not a scientific publisher') }}</li>
                        <li>• {{ __('It is not an article publishing system') }}</li>
                        <li>• {{ __('It does not guarantee journal approval') }}</li>
                        <li>• {{ __('It is not a book evaluation system') }}</li>
                        <li>• {{ __('It is not a ranking based on payments') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Target Audience --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Target Audience') }}</h2>
            </div>
            <div class="mx-auto grid max-w-4xl gap-8 sm:grid-cols-2 md:grid-cols-4">
                @php
                    $audiences = [
                        ['📰', __('Journal Editors'), __('Who want to evaluate and strengthen their editorial standards.')],
                        ['✍️', __('Authors and Researchers'), __('Who seek to identify reliable journals and academic publications.')],
                        ['🏛️', __('Academic Institutions'), __('That need structured information about scientific publications.')],
                        ['📚', __('Libraries'), __('That seek academic publication discovery tools.')],
                    ];
                @endphp
                @foreach($audiences as [$icon, $title, $desc])
                <div class="text-center">
                    <div class="mb-3 text-4xl">{{ $icon }}</div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Institutional Formula --}}
    <section class="bg-gray-50 py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl rounded-2xl bg-indigo-50 p-8 text-center dark:bg-indigo-900/20">
                <p class="text-lg font-medium text-gray-800 dark:text-gray-200">
                    "{{ __('Journals can be listed on the platform, evaluated through transparent technical criteria and, if they reach the required level of compliance, obtain the editorial seal of the platform.') }}"
                </p>
                <p class="mt-4 text-sm text-indigo-600 dark:text-indigo-400">— {{ __('Institutional formula of the project') }}</p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-20 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Join the scientific community') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-indigo-100 italic">"{{ __('Independent technical evaluation to strengthen global scientific communication.') }}"</p>
                    <p class="mx-auto mt-4 max-w-xl text-indigo-100">{{ __('We help publications gain visibility and credibility through international standards.') }}</p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            {{ __('Register my Journal — Free') }}
                        </a>
                        <a href="/contact" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            {{ __('Contact the team') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
