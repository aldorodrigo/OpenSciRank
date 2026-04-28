<x-layouts.app :title="__('Payment Confirmed - Editorial Standards Platform')">
    <div class="flex min-h-[70vh] items-center justify-center bg-gray-50 py-12 dark:bg-gray-950">
        <div class="mx-auto max-w-lg px-4">
            @if($paid)
                <div class="rounded-2xl bg-white p-8 text-center shadow-xl dark:bg-gray-900">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Payment Confirmed') }}</h1>

                    @if(!empty($isRenewal))
                        <p class="mt-3 text-gray-600 dark:text-gray-400">
                            {{ __('The editorial seal of') }} <strong class="text-gray-900 dark:text-white">{{ $title }}</strong> {{ __('has been successfully renewed.') }}
                        </p>
                        <div class="mt-6 rounded-lg bg-emerald-50 p-4 dark:bg-emerald-900/20">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-lg">✅</span>
                                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Active Editorial Seal') }}</span>
                            </div>
                            @if(!empty($sealExpiresAt))
                                <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
                                    {{ __('New expiration:') }} <strong>{{ $sealExpiresAt->format('d/m/Y') }}</strong>
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="mt-3 text-gray-600 dark:text-gray-400">
                            {{ __('Your') }} {{ $type }} <strong class="text-gray-900 dark:text-white">{{ $title }}</strong> {{ __('has been submitted for evaluation.') }}
                        </p>
                        <div class="mt-6 rounded-lg bg-emerald-50 p-4 dark:bg-emerald-900/20">
                            <div class="flex items-center justify-center gap-2">
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-400">
                                    {{ __('Under Review') }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
                                {{ __('Our team will evaluate your') }} {{ $type }} {{ __('and notify you when the process is complete.') }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-8">
                        <a href="{{ route('app.dashboard') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('Go to My Dashboard') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="rounded-2xl bg-white p-8 text-center shadow-xl dark:bg-gray-900">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Payment Not Completed') }}</h1>

                    <p class="mt-3 text-gray-600 dark:text-gray-400">
                        {{ __('We could not confirm the payment for your') }} {{ $type }}. {{ __('Please try again.') }}
                    </p>

                    <div class="mt-8">
                        <a href="{{ route('app.dashboard') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            {{ __('Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
