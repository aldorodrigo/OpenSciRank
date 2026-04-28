<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-5xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-indigo-600">{{ __('My Dashboard') }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">{{ __('Payment') }}</span>
        </nav>

        <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">{{ __('Complete Payment') }}</h1>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Plan Selection --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Select your Plan') }}</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($this->products as $product)
                            <div wire:click="selectPlan({{ $product->id }})"
                                class="relative cursor-pointer rounded-xl border-2 p-6 transition
                                    @if($selectedPlan === $product->id) border-indigo-600 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20
                                    @else border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600
                                    @endif
                                ">

                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $product->getTranslationWithFallback('name') }}</h3>
                                    <div class="flex h-5 w-5 items-center justify-center rounded-full border-2
                                        @if($selectedPlan === $product->id) border-indigo-600 bg-indigo-600
                                        @else border-gray-300 dark:border-gray-600
                                        @endif
                                    ">
                                        @if($selectedPlan === $product->id)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/ {{ __('one-time payment') }}</span>
                                </div>

                                <div class="prose prose-sm dark:prose-invert">
                                    {!! $product->getTranslationWithFallback('description') !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Payment Method') }}</h2>

                    <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ __('Secure payment with Stripe') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Credit card, debit and more payment methods available.') }}</p>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Summary') }}</h2>

                    <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $journal->getTranslationWithFallback('title') }}</p>
                        @if($journal->issn)
                            <p class="text-sm text-gray-500 dark:text-gray-400">ISSN: {{ $journal->issn }}</p>
                        @endif
                    </div>

                    <div class="space-y-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $this->products->firstWhere('id', $selectedPlan)?->getTranslationWithFallback('name') ?? __('Select a plan') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Taxes') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">$0</span>
                        </div>
                    </div>

                    <div class="flex justify-between py-4">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }} {{ $this->products->firstWhere('id', $selectedPlan)?->currency }}</span>
                    </div>

                    <button wire:click="processPayment"
                        wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-emerald-600 py-3 text-center font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$selectedPlan) disabled @endif>
                        <span wire:loading.remove wire:target="processPayment">
                            {{ __('Pay') }} ${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }} {{ $this->products->firstWhere('id', $selectedPlan)?->currency }}
                        </span>
                        <span wire:loading wire:target="processPayment" class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ __('Redirecting to Stripe...') }}
                        </span>
                    </button>

                    <p class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                        {{ __('By making the payment you accept our') }} <a href="/terms" class="text-indigo-600 hover:underline">{{ __('Terms of Service') }}</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
