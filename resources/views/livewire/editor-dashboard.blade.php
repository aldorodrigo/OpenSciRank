<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950"
     x-data="{
        confirmModal: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmUrl: '',
        confirmAction: '',
        openConfirm(title, message, url, action) {
            this.confirmTitle = title;
            this.confirmMessage = message;
            this.confirmUrl = url;
            this.confirmAction = action;
            this.confirmModal = true;
        }
     }">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('My Dashboard') }}</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ __('Manage your journals and books') }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('app.submit') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('New Journal') }}
                </a>
                <a href="{{ route('app.book.submit') }}" class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('New Book') }}
                </a>
            </div>
        </div>

        {{-- Banner Contextual --}}
        @if($bannerType !== 'none')
        <div x-data="{ dismissed: localStorage.getItem('dashboard-banner-{{ $bannerType }}') === 'true' }" x-show="!dismissed" x-transition class="mb-8">
            @if($bannerType === 'welcome')
                <div class="relative rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-white p-6 shadow-lg dark:border-indigo-800 dark:from-indigo-950/50 dark:to-gray-900">
                    <button @click="dismissed = true; localStorage.setItem('dashboard-banner-welcome', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/50">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Welcome to Editorial Standards Platform') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Register your journal and choose your path: list it for free in our directory or request an expert editorial evaluation to obtain the Quality Seal.') }}</p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ route('app.submit') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">{{ __('Register Journal') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($bannerType === 'drafts_only')
                <div class="relative rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-6 shadow-lg dark:border-amber-800 dark:from-amber-950/50 dark:to-gray-900">
                    <button @click="dismissed = true; localStorage.setItem('dashboard-banner-drafts_only', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('You have journals in draft') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Complete your journal registration to list it in the directory or request an editorial evaluation with the option of a Quality Seal.') }}</p>
                        </div>
                    </div>
                </div>
            @elseif($bannerType === 'listed_no_evaluation' && $listedJournal)
                <div class="relative rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-white p-6 shadow-lg dark:border-emerald-800 dark:from-emerald-950/50 dark:to-gray-900">
                    <button @click="dismissed = true; localStorage.setItem('dashboard-banner-listed_no_evaluation', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Take the next step: get the Editorial Quality Seal') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Your journal is already visible in the directory. An expert can evaluate it against international quality criteria. If it passes, you get a verifiable seal for 1 year.') }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="flex items-center gap-1"><svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('International credibility') }}</span>
                                <span class="flex items-center gap-1"><svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('Greater visibility') }}</span>
                                <span class="flex items-center gap-1"><svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('Badge for your website') }}</span>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('app.checkout', $listedJournal) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ __('Request Evaluation — $99 USD') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($bannerType === 'evaluated_not_certified' && $evaluatedJournal)
                <div class="relative rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-white p-6 shadow-lg dark:border-blue-800 dark:from-blue-950/50 dark:to-gray-900">
                    <button @click="dismissed = true; localStorage.setItem('dashboard-banner-evaluated_not_certified', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your journal was evaluated — you are close to the seal') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $evaluatedJournal->getTranslationWithFallback('title') }} {{ __('scored') }} <strong>{{ $evaluatedJournal->current_score }}%</strong>. {{ __('75% is required to obtain the seal. Review the evaluator\'s observations and request a re-evaluation.') }}
                            </p>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2.5 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="relative h-full rounded-full {{ $evaluatedJournal->current_score >= 75 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ min($evaluatedJournal->current_score, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $evaluatedJournal->current_score }}% / 75%</span>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('app.checkout', $evaluatedJournal) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                    {{ __('Request Re-evaluation — $99 USD') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($bannerType === 'seal_expired' && $sealJournal)
                <div class="relative rounded-xl border border-red-200 bg-gradient-to-r from-red-50 to-white p-6 shadow-lg dark:border-red-800 dark:from-red-950/50 dark:to-gray-900">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ __('Your Quality Seal has expired') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('The seal of') }} <strong>{{ $sealJournal->getTranslationWithFallback('title') }}</strong> {{ __('expired on') }} {{ $sealJournal->seal_expires_at->format('d/m/Y') }}. {{ __('Renew now to keep the certification active.') }}</p>
                            <div class="mt-4">
                                <a href="{{ route('app.renew', $sealJournal) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500">{{ __('Renew Seal — $129 USD') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($bannerType === 'seal_expiring' && $sealJournal)
                <div class="relative rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-6 shadow-lg dark:border-amber-800 dark:from-amber-950/50 dark:to-gray-900">
                    <button @click="dismissed = true; localStorage.setItem('dashboard-banner-seal_expiring', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-300">{{ __('Your seal is about to expire') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('The seal of') }} <strong>{{ $sealJournal->getTranslationWithFallback('title') }}</strong> {{ __('expires on') }} {{ $sealJournal->seal_expires_at->format('d/m/Y') }}. {{ __('Renew in advance to avoid losing the certification.') }}</p>
                            <div class="mt-4">
                                <a href="{{ route('app.renew', $sealJournal) }}" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500">{{ __('Renew Seal — $129 USD') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- Stats Overview (clickable as quick filters) --}}
        @php
            $underEvaluationCount = $allJournals->where('status', 'submitted')->count() + $allBooks->where('status', 'submitted')->count();
            $isAllActive = $journalStatusFilter === '' && $bookStatusFilter === '' && $journalSearch === '' && $journalScoreFilter === '' && $journalSealFilter === '' && $bookSearch === '' && $bookScoreFilter === '';
            $isCertActive = $journalStatusFilter === 'certified';
            $isActionActive = $journalStatusFilter === 'action_needed';
            $isUnderEvalActive = $journalStatusFilter === 'submitted' && $bookStatusFilter === 'submitted';
        @endphp
        <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <button type="button" wire:click="applyQuickFilter('all')"
                class="group rounded-xl bg-white p-6 text-left shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-900 {{ $isAllActive ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $allJournals->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Journals') }}</p>
                        <p class="mt-0.5 text-xs text-indigo-600 opacity-0 transition group-hover:opacity-100 dark:text-indigo-400">{{ __('Show all') }} →</p>
                    </div>
                </div>
            </button>
            <button type="button" wire:click="applyQuickFilter('certified')" @disabled($certifiedCount === 0)
                class="group rounded-xl bg-white p-6 text-left shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-lg dark:bg-gray-900 {{ $isCertActive ? 'ring-2 ring-emerald-500' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $certifiedCount }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Certified') }}</p>
                        @if($certifiedCount === 0 && $allJournals->isNotEmpty())
                            <p class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">{{ __('Get your first seal') }}</p>
                        @elseif($certifiedCount > 0)
                            <p class="mt-0.5 text-xs text-emerald-600 opacity-0 transition group-hover:opacity-100 dark:text-emerald-400">{{ __('Filter') }} →</p>
                        @endif
                    </div>
                </div>
            </button>
            <button type="button" wire:click="applyQuickFilter('action_needed')" @disabled($actionNeededCount === 0)
                class="group rounded-xl bg-white p-6 text-left shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-amber-500 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-lg dark:bg-gray-900 {{ $isActionActive ? 'ring-2 ring-amber-500' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $actionNeededCount > 0 ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $actionNeededCount }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Action Required') }}</p>
                        @if($actionNeededCount > 0)
                            <p class="mt-0.5 text-xs text-amber-600 opacity-0 transition group-hover:opacity-100 dark:text-amber-400">{{ __('Filter') }} →</p>
                        @endif
                    </div>
                </div>
            </button>
            <button type="button" wire:click="applyQuickFilter('submitted')" @disabled($underEvaluationCount === 0)
                class="group rounded-xl bg-white p-6 text-left shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-lg dark:bg-gray-900 {{ $isUnderEvalActive ? 'ring-2 ring-purple-500' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $underEvaluationCount }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Under Evaluation') }}</p>
                        @if($underEvaluationCount > 0)
                            <p class="mt-0.5 text-xs text-purple-600 opacity-0 transition group-hover:opacity-100 dark:text-purple-400">{{ __('Filter') }} →</p>
                        @endif
                    </div>
                </div>
            </button>
        </div>

        {{-- Propuesta de valor --}}
        @if($certifiedCount === 0 && $journals->isNotEmpty())
        <div x-data="{ show: localStorage.getItem('dashboard-value-dismissed') !== 'true' }" x-show="show" x-transition class="mb-8">
            <div class="relative rounded-xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                <button @click="show = false; localStorage.setItem('dashboard-value-dismissed', 'true')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Why get the Editorial Quality Seal?') }}</h3>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Credibility') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Certifies that your journal meets international editorial standards.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                            <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Visibility') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Certified journals appear highlighted in the directory.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/50">
                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Verifiable badge') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('You get a seal to display on your journal\'s website.') }}</p>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-right text-xs text-gray-500 dark:text-gray-400">{{ __('From') }} <strong class="text-gray-900 dark:text-white">$99 USD</strong> — {{ __('one-time payment') }}</p>
            </div>
        </div>
        @endif

        {{-- Journals Table --}}
        <div class="mb-8 rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('My Journals') }}</h2>
            </div>

            @if($allJournals->isEmpty())
                <div class="p-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Register your first journal') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Choose how you want to start:') }}</p>
                    </div>
                    <div class="mx-auto mt-6 grid max-w-2xl gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border-2 border-gray-200 p-5 text-center dark:border-gray-700">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            </div>
                            <h4 class="mt-3 font-semibold text-gray-900 dark:text-white">{{ __('Free Listing') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Your journal appears in the public directory at no cost.') }}</p>
                            <a href="{{ route('app.submit') }}" class="mt-4 inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                {{ __('Register Free') }}
                            </a>
                        </div>
                        <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50/50 p-5 text-center dark:border-emerald-800 dark:bg-emerald-950/20">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h4 class="mt-3 font-semibold text-gray-900 dark:text-white">{{ __('Evaluation with Seal') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Expert evaluation + Editorial Quality Seal for 1 year.') }}</p>
                            <a href="{{ route('app.submit') }}" class="mt-4 inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                {{ __('Register and Evaluate — $99') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- Filtros de revistas --}}
                <div x-data="{ open: false }" class="border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3 px-6 py-3">
                        {{-- Buscador siempre visible --}}
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                            <input type="text" wire:model.live.debounce.300ms="journalSearch" placeholder="{{ __('Search by title...') }}" class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:placeholder-gray-500">
                        </div>
                        {{-- Botón filtros --}}
                        <button @click="open = !open" class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            {{ __('Filters') }}
                            @if($journalStatusFilter || $journalScoreFilter || $journalSealFilter)
                                <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">
                                    {{ (int)($journalStatusFilter !== '') + (int)($journalScoreFilter !== '') + (int)($journalSealFilter !== '') }}
                                </span>
                            @endif
                            <svg class="h-3 w-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        {{-- Limpiar filtros --}}
                        @if($journalSearch || $journalStatusFilter || $journalScoreFilter || $journalSealFilter)
                            <button wire:click="$set('journalSearch', ''); $set('journalStatusFilter', ''); $set('journalScoreFilter', ''); $set('journalSealFilter', '')" class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400">
                                {{ __('Clear') }}
                            </button>
                        @endif
                    </div>
                    {{-- Panel desplegable --}}
                    <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-800">
                        <div class="grid grid-cols-1 gap-3 px-6 py-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</label>
                                <select wire:model.live="journalStatusFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="action_needed">{{ __('Action Required') }}</option>
                                    <option value="draft">{{ __('Draft') }}</option>
                                    <option value="submitted">{{ __('Submitted') }}</option>
                                    <option value="pending_listing">{{ __('Pending listing') }}</option>
                                    <option value="listed">{{ __('Listed') }}</option>
                                    <option value="evaluated">{{ __('Evaluated') }}</option>
                                    <option value="certified">{{ __('Certified') }}</option>
                                    <option value="requires_changes_listing">{{ __('Requires changes (listing)') }}</option>
                                    <option value="requires_changes_evaluation">{{ __('Requires changes (evaluation)') }}</option>
                                    <option value="rejected">{{ __('Rejected') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Score') }}</label>
                                <select wire:model.live="journalScoreFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="high">{{ __('75% or more') }}</option>
                                    <option value="medium">{{ __('50% - 74%') }}</option>
                                    <option value="low">{{ __('1% - 49%') }}</option>
                                    <option value="none">{{ __('No score') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Seal') }}</label>
                                <select wire:model.live="journalSealFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="expiring">{{ __('Expiring soon') }}</option>
                                    <option value="expired">{{ __('Expired') }}</option>
                                    <option value="none">{{ __('No seal') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortJournals('title')">
                                    {{ __('Journal') }}
                                    @if($journalSortField === 'title') <span>{{ $journalSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortJournals('status')">
                                    {{ __('Status') }}
                                    @if($journalSortField === 'status') <span>{{ $journalSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortJournals('score')">
                                    {{ __('Score') }}
                                    @if($journalSortField === 'score') <span>{{ $journalSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortJournals('seal_status')">
                                    {{ __('Seal') }}
                                    @if($journalSortField === 'seal_status') <span>{{ $journalSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="px-6 py-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($journals as $journal)
                                <tr class="
                                    @if($journal->seal_expires_at && $journal->seal_expires_at->isPast())
                                        bg-red-50 dark:bg-red-950/30 border-l-4 border-red-500
                                    @else
                                        hover:bg-gray-50 dark:hover:bg-gray-800/50
                                    @endif
                                ">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $journal->getTranslationWithFallback('title') }}</p>
                                                @if($journal->issn_print)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">ISSN: {{ $journal->issn_print }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            @if($journal->status === 'certified' && $journal->seal_expires_at?->isPast()) bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 line-through
                                            @elseif(in_array($journal->status, ['indexed', 'listed', 'certified'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif(in_array($journal->status, ['submitted', 'pending_listing'])) bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif(str_starts_with($journal->status, 'requires_changes')) bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @elseif($journal->status === 'evaluated') bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($journal->status) {
                                                'draft' => __('Draft'),
                                                'submitted' => __('Under Evaluation'),
                                                'pending_listing' => __('Pending Listing'),
                                                'requires_changes_listing' => __('Changes (Listing)'),
                                                'requires_changes_evaluation' => __('Changes (Evaluation)'),
                                                'listed' => __('Listed'),
                                                'evaluated' => __('Evaluated'),
                                                'certified' => __('Certified'),
                                                'indexed' => __('Indexed'),
                                                default => ucfirst($journal->status)
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($journal->current_score)
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <div class="relative h-2 w-20 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                        <div class="h-full rounded-full {{ $journal->current_score >= 75 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($journal->current_score, 100) }}%"></div>
                                                        {{-- Marcador 75% --}}
                                                        <div class="absolute top-0 h-full w-px bg-gray-400 dark:bg-gray-500" style="left: 75%"></div>
                                                    </div>
                                                    <span class="text-sm font-medium {{ $journal->current_score >= 75 ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $journal->current_score }}%</span>
                                                </div>
                                                @if($journal->status === 'evaluated' && $journal->current_score < 75)
                                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ __(':points pts needed to certify', ['points' => 75 - $journal->current_score]) }}</p>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    {{-- Columna Sello --}}
                                    <td class="px-6 py-4">
                                        @if($journal->seal_expires_at)
                                            @if($journal->seal_expires_at->isPast())
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-400">
                                                    🔴 {{ __('Expired') }}
                                                </span>
                                                <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                            @elseif(now()->diffInDays($journal->seal_expires_at) <= 60)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                                    ⚠️ {{ __('Expiring soon') }}
                                                </span>
                                                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                                    ✅ {{ __('Active') }}
                                                </span>
                                                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ $journal->seal_expires_at->format('d/m/Y') }}</p>
                                                <a href="{{ route('app.badge', $journal) }}" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    {{ __('View Seal') }}
                                                </a>
                                            @endif
                                        @elseif($journal->status === 'listed')
                                            <button @click="openConfirm('{{ __('Request Editorial Evaluation') }}', '{{ __('Your journal is already listed in the directory. By requesting the evaluation, an expert will analyze your journal against editorial quality criteria. If it passes with 75% or more, you will get the Quality Seal for 1 year.') }}', '{{ route('app.checkout', $journal) }}', '{{ __('Continue to Payment — $99') }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-500 cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                {{ __('Get Seal') }}
                                            </button>
                                        @elseif($journal->status === 'evaluated')
                                            <div>
                                                <p class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $journal->current_score }}% / 75%</p>
                                                <a href="{{ route('app.checkout', $journal) }}" class="mt-1 inline-flex text-xs font-medium text-blue-600 hover:text-blue-500 hover:underline dark:text-blue-400">{{ __('Re-evaluate') }}</a>
                                            </div>
                                        @elseif($journal->status === 'submitted')
                                            <span class="inline-flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                                                <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span></span>
                                                {{ __('In progress') }}
                                            </span>
                                        @elseif($journal->status === 'draft')
                                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('Complete registration') }}</span>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-600">—</span>
                                        @endif
                                    </td>

                                    {{-- Columna Acciones --}}
                                    <td class="px-6 py-4">
                                        <div x-data="{ open: false, top: '0px', left: '0px' }" @click.outside="open = false">
                                            <button @click.stop="const r = $el.getBoundingClientRect(); top = (r.bottom + 4) + 'px'; left = Math.max(0, r.right - 208) + 'px'; open = !open;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                {{ __('Actions') }}
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-transition
                                                class="fixed z-50 w-52 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                                :style="`top: ${top}; left: ${left}`">
                                                <div class="py-1">

                                                    {{-- Editar (solo borrador y correcciones) --}}
                                                    @if(in_array($journal->status, ['draft', 'requires_changes_listing', 'requires_changes_evaluation']))
                                                        <a href="{{ route('app.submit.edit', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            {{ __('Edit') }}
                                                        </a>
                                                    @endif

                                                    {{-- Ver Ficha --}}
                                                    @if($journal->status !== 'draft')
                                                        <a href="{{ route('journal.show', $journal->slug) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            {{ __('View Profile') }}
                                                        </a>
                                                    @endif

                                                    {{-- Pagar Evaluación (borrador o listada) --}}
                                                    @if(in_array($journal->status, ['draft', 'listed']))
                                                        <button @click="open = false; openConfirm(
                                                            '{{ __('Request Editorial Evaluation') }}',
                                                            '{{ $journal->status === 'listed' ? __('Your journal is already listed in the directory. By requesting the evaluation, an expert will analyze your journal against editorial quality criteria. If it passes with 75% or more, you will get the Quality Seal for 1 year.') : __('By requesting the editorial evaluation, an expert will analyze your journal against quality criteria. If it passes with 75% or more, you will get the Quality Seal for 1 year.') }}',
                                                            '{{ route('app.checkout', $journal) }}',
                                                            '{{ __('Continue to Payment — $99') }}'
                                                        )"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                            {{ __('Pay for Evaluation') }}
                                                        </button>
                                                    @endif

                                                    {{-- Reenviar para Listar --}}
                                                    @if($journal->status === 'requires_changes_listing')
                                                        <button wire:click="showObservations({{ $journal->id }}, 'journal')" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 dark:text-amber-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            {{ __('Resubmit for Listing') }}
                                                        </button>
                                                    @endif

                                                    {{-- Corregir y Reenviar (evaluación) --}}
                                                    @if($journal->status === 'requires_changes_evaluation')
                                                        <button @click="open = false; openConfirm(
                                                            '{{ __('Request Re-evaluation') }}',
                                                            '{{ __('Confirm that you have made the corrections indicated by the evaluator. You will be redirected to payment for a new editorial evaluation.') }}',
                                                            '{{ route('app.checkout', $journal) }}',
                                                            '{{ __('Continue to Payment — $99') }}'
                                                        )"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            {{ __('Fix and Resubmit') }}
                                                        </button>
                                                    @endif

                                                    {{-- Renovar Sello (vencido o próximo a vencer) --}}
                                                    @if($journal->seal_expires_at && ($journal->seal_expires_at->isPast() || now()->diffInDays($journal->seal_expires_at) <= 60))
                                                        <a href="{{ route('app.renew', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm {{ $journal->seal_expires_at->isPast() ? 'font-semibold text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }} hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            {{ __('Renew Seal') }}
                                                        </a>
                                                    @endif

                                                    {{-- Obtener Sello (certificada con sello vigente) --}}
                                                    @if($journal->status === 'certified' && $journal->seal_expires_at?->isFuture())
                                                        <a href="{{ route('app.badge', $journal) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                            {{ __('View Seal') }}
                                                        </a>
                                                    @endif

                                                    {{-- Cosechar OAI --}}
                                                    @if($journal->status === 'indexed' && $journal->oai_base_url)
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="harvestOai({{ $journal->id }})" wire:loading.attr="disabled" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            📡 {{ __('Harvest OAI') }}
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar (solo borrador) --}}
                                                    @if($journal->status === 'draft')
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="deleteJournal({{ $journal->id }})"
                                                            wire:confirm="{{ __('Are you sure you want to delete this journal? This action cannot be undone.') }}"
                                                            @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No journals found with the applied filters.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php $journalTotalPages = (int) ceil($journalTotal / $perPage); @endphp
                @if($journalTotal > 0)
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-6 py-3 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Showing') }} {{ ($journalPage - 1) * $perPage + 1 }}–{{ min($journalPage * $perPage, $journalTotal) }} {{ __('of') }} {{ $journalTotal }}
                            </p>
                            <select wire:change="changePerPage($event.target.value)" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                @foreach([5, 10, 25, 50] as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} {{ __('per page') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($journalTotalPages > 1)
                            @php
                                $jPages = collect();
                                $jPages = $jPages->merge(range(1, min(3, $journalTotalPages)));
                                $jPages = $jPages->merge(range(max(1, $journalPage - 1), min($journalTotalPages, $journalPage + 1)));
                                $jPages = $jPages->merge(range(max(1, $journalTotalPages - 2), $journalTotalPages));
                                $jPages = $jPages->unique()->sort()->values();
                            @endphp
                            <div class="flex items-center gap-1">
                                <button wire:click="$set('journalPage', 1)" @if($journalPage <= 1) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &laquo;&laquo;
                                </button>
                                <button wire:click="$set('journalPage', {{ $journalPage - 1 }})" @if($journalPage <= 1) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &laquo;
                                </button>
                                @foreach($jPages as $idx => $i)
                                    @if($idx > 0 && $i - $jPages[$idx - 1] > 1)
                                        <span class="px-1 text-sm text-gray-400">...</span>
                                    @endif
                                    <button wire:click="$set('journalPage', {{ $i }})"
                                        class="rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $journalPage === $i ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                                        {{ $i }}
                                    </button>
                                @endforeach
                                <button wire:click="$set('journalPage', {{ $journalPage + 1 }})" @if($journalPage >= $journalTotalPages) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &raquo;
                                </button>
                                <button wire:click="$set('journalPage', {{ $journalTotalPages }})" @if($journalPage >= $journalTotalPages) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &raquo;&raquo;
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        {{-- Books Table --}}
        <div class="rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('My Books') }}</h2>
            </div>

            @if($allBooks->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('You have no books registered') }}</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Start by registering your first book.') }}</p>
                    <a href="{{ route('app.book.submit') }}" class="mt-6 inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500">
                        {{ __('Register Book') }}
                    </a>
                </div>
            @else
                {{-- Filtros de libros --}}
                <div x-data="{ open: false }" class="border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3 px-6 py-3">
                        {{-- Buscador siempre visible --}}
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                            <input type="text" wire:model.live.debounce.300ms="bookSearch" placeholder="{{ __('Search by title...') }}" class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:placeholder-gray-500">
                        </div>
                        {{-- Botón filtros --}}
                        <button @click="open = !open" class="relative inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            {{ __('Filters') }}
                            @if($bookStatusFilter || $bookScoreFilter)
                                <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-purple-500 text-[10px] font-bold text-white">
                                    {{ (int)($bookStatusFilter !== '') + (int)($bookScoreFilter !== '') }}
                                </span>
                            @endif
                            <svg class="h-3 w-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        {{-- Limpiar filtros --}}
                        @if($bookSearch || $bookStatusFilter || $bookScoreFilter)
                            <button wire:click="$set('bookSearch', ''); $set('bookStatusFilter', ''); $set('bookScoreFilter', '')" class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400">
                                {{ __('Clear') }}
                            </button>
                        @endif
                    </div>
                    {{-- Panel desplegable --}}
                    <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-800">
                        <div class="grid grid-cols-1 gap-3 px-6 py-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</label>
                                <select wire:model.live="bookStatusFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="draft">{{ __('Draft') }}</option>
                                    <option value="submitted">{{ __('Submitted') }}</option>
                                    <option value="pending_listing">{{ __('Pending listing') }}</option>
                                    <option value="listed">{{ __('Listed') }}</option>
                                    <option value="requires_changes_listing">{{ __('Requires changes') }}</option>
                                    <option value="rejected">{{ __('Rejected') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Score') }}</label>
                                <select wire:model.live="bookScoreFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="high">{{ __('75% or more') }}</option>
                                    <option value="medium">{{ __('50% - 74%') }}</option>
                                    <option value="low">{{ __('1% - 49%') }}</option>
                                    <option value="none">{{ __('No score') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortBooks('title')">
                                    {{ __('Book') }}
                                    @if($bookSortField === 'title') <span>{{ $bookSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortBooks('status')">
                                    {{ __('Status') }}
                                    @if($bookSortField === 'status') <span>{{ $bookSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="cursor-pointer px-6 py-3 select-none" wire:click="sortBooks('score')">
                                    {{ __('Score') }}
                                    @if($bookSortField === 'score') <span>{{ $bookSortDir === 'asc' ? '▲' : '▼' }}</span> @endif
                                </th>
                                <th class="px-6 py-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($books as $book)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $book->getTranslationWithFallback('title') }}</p>
                                                @if($book->isbn_print)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">ISBN: {{ $book->isbn_print }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            @if(in_array($book->status, ['indexed', 'listed'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                            @elseif(in_array($book->status, ['submitted', 'pending_listing'])) bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                            @elseif(str_starts_with($book->status, 'requires_changes')) bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ match($book->status) {
                                                'draft' => __('Draft'),
                                                'submitted' => __('Submitted'),
                                                'pending_listing' => __('Pending Listing'),
                                                'requires_changes_listing' => __('Changes (Listing)'),
                                                'listed' => __('Listed'),
                                                'indexed' => __('Indexed'),
                                                default => ucfirst($book->status)
                                            } }}
                                        </span>
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
                                        <div x-data="{ open: false, top: '0px', left: '0px' }" @click.outside="open = false">
                                            <button @click.stop="const r = $el.getBoundingClientRect(); top = (r.bottom + 4) + 'px'; left = Math.max(0, r.right - 208) + 'px'; open = !open;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                {{ __('Actions') }}
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-transition
                                                class="fixed z-50 w-52 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                                :style="`top: ${top}; left: ${left}`">
                                                <div class="py-1">

                                                    {{-- Editar (borrador o correcciones) --}}
                                                    @if(in_array($book->status, ['draft', 'requires_changes_listing']))
                                                        <a href="{{ route('app.book.submit.edit', $book) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            {{ __('Edit') }}
                                                        </a>
                                                    @endif

                                                    {{-- Ver Ficha --}}
                                                    @if($book->status !== 'draft')
                                                        <a href="{{ route('book.show', $book->slug) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            {{ __('View Profile') }}
                                                        </a>
                                                    @endif

                                                    {{-- Pagar Listado (borrador) --}}
                                                    @if($book->status === 'draft')
                                                        <a href="{{ route('app.book.checkout', $book) }}"
                                                            class="flex items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50 dark:text-emerald-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                            {{ __('Pay for Listing') }}
                                                        </a>
                                                    @endif

                                                    {{-- Reenviar para Listar --}}
                                                    @if($book->status === 'requires_changes_listing')
                                                        <button wire:click="showObservations({{ $book->id }}, 'book')" @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 dark:text-amber-400 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            {{ __('Resubmit for Listing') }}
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar (solo borrador) --}}
                                                    @if($book->status === 'draft')
                                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                        <button wire:click="deleteBook({{ $book->id }})"
                                                            wire:confirm="{{ __('Are you sure you want to delete this book? This action cannot be undone.') }}"
                                                            @click="open = false"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No books found with the applied filters.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php $bookTotalPages = (int) ceil($bookTotal / $perPage); @endphp
                @if($bookTotal > 0)
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-6 py-3 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Showing :from–:to of :total', ['from' => ($bookPage - 1) * $perPage + 1, 'to' => min($bookPage * $perPage, $bookTotal), 'total' => $bookTotal]) }}
                            </p>
                            <select wire:change="changePerPage($event.target.value)" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                @foreach([5, 10, 25, 50] as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} {{ __('per page') }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($bookTotalPages > 1)
                            @php
                                $bPages = collect();
                                $bPages = $bPages->merge(range(1, min(3, $bookTotalPages)));
                                $bPages = $bPages->merge(range(max(1, $bookPage - 1), min($bookTotalPages, $bookPage + 1)));
                                $bPages = $bPages->merge(range(max(1, $bookTotalPages - 2), $bookTotalPages));
                                $bPages = $bPages->unique()->sort()->values();
                            @endphp
                            <div class="flex items-center gap-1">
                                <button wire:click="$set('bookPage', 1)" @if($bookPage <= 1) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &laquo;&laquo;
                                </button>
                                <button wire:click="$set('bookPage', {{ $bookPage - 1 }})" @if($bookPage <= 1) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &laquo;
                                </button>
                                @foreach($bPages as $idx => $i)
                                    @if($idx > 0 && $i - $bPages[$idx - 1] > 1)
                                        <span class="px-1 text-sm text-gray-400">...</span>
                                    @endif
                                    <button wire:click="$set('bookPage', {{ $i }})"
                                        class="rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $bookPage === $i ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                                        {{ $i }}
                                    </button>
                                @endforeach
                                <button wire:click="$set('bookPage', {{ $bookPage + 1 }})" @if($bookPage >= $bookTotalPages) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &raquo;
                                </button>
                                <button wire:click="$set('bookPage', {{ $bookTotalPages }})" @if($bookPage >= $bookTotalPages) disabled @endif
                                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    &raquo;&raquo;
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de Observaciones --}}
    @if($showObservationsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="background-color: rgba(0,0,0,0.5);">
        <div class="relative mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __("Reviewer's Observations") }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $observationsTitle }}</p>
                </div>
                <button wire:click="closeObservationsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Observaciones --}}
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="mb-2 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <span class="text-sm font-semibold text-red-700 dark:text-red-400">{{ __('Requested corrections:') }}</span>
                </div>
                <div class="prose prose-sm max-w-none text-red-800 dark:text-red-300">
                    {!! nl2br(e($observationsNotes)) !!}
                </div>
            </div>

            {{-- Advertencia --}}
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                <p class="text-sm text-amber-800 dark:text-amber-300">
                    {{ __('When resubmitting, your request will be reviewed by the team again. Make sure you have corrected all observations before continuing.') }}
                </p>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <button wire:click="closeObservationsModal"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Close') }}
                </button>
                <button wire:click="confirmResubmitForListing"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                    {{ __("I've corrected it, resubmit request") }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Confirmacion de Pago --}}
    <div x-show="confirmModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="background-color: rgba(0,0,0,0.5);" x-cloak>
        <div x-show="confirmModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            @click.outside="confirmModal = false"
            class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">

            {{-- Icono --}}
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>

            {{-- Titulo --}}
            <h3 class="mb-2 text-center text-lg font-semibold text-gray-900 dark:text-white" x-text="confirmTitle"></h3>

            {{-- Mensaje --}}
            <p class="mb-6 text-center text-sm text-gray-600 dark:text-gray-400" x-text="confirmMessage"></p>

            {{-- Beneficios --}}
            <div class="mb-6 space-y-2 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ __('Evaluation by an expert in editorial standards') }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ __('Detailed report with recommendations') }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ __('Verifiable Quality Seal for 1 year (if approved)') }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ __('Secure payment with Stripe') }}
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3">
                <button @click="confirmModal = false"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <a :href="confirmUrl"
                    class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                    <span x-text="confirmAction"></span>
                </a>
            </div>
        </div>
    </div>
</div>
