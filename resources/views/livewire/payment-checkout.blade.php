<x-slot:header>true</x-slot:header>

@php
    $products = $this->products;
    $addons = $this->addons;
    $renewalLadder = $this->renewalLadder;
    $hasRenewals = collect($renewalLadder)->isNotEmpty();
    $reevalProduct = $products->firstWhere('slug', 'journal-reevaluation');
    $selectedProduct = $products->firstWhere('id', $selectedPlan);
    $canOfferExpress = $this->canOfferExpress;
    $isAutoEarlyDiscount = $this->isAutoEarlyDiscountApplied;
    $discountAmount = $this->discountAmount;
    $earlyDeadline = $this->earlyDiscountDeadline;
@endphp

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

                {{-- RENOVACIONES: escalera 1y/2y/3y lado a lado --}}
                @if($hasRenewals)
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Renew your Editorial Seal') }}</h2>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Pick a renewal length. Longer terms unlock better per-year pricing.') }}</p>

                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach($renewalLadder as $row)
                                @php
                                    $rp = $row['product'];
                                    $years = $row['years'];
                                    $perYear = $row['per_year'];
                                    $savings = $row['savings_pct'];
                                    $isSelected = $selectedPlan === $rp->id;
                                @endphp
                                <div wire:click="selectPlan({{ $rp->id }})"
                                    class="relative cursor-pointer rounded-xl border-2 p-5 transition
                                        @if($isSelected) border-indigo-600 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20
                                        @else border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600
                                        @endif
                                    ">
                                    @if($savings !== null && $savings > 0)
                                        <span class="absolute -top-3 right-3 rounded-full bg-emerald-500 px-3 py-0.5 text-xs font-bold text-white shadow">
                                            {{ __('Save :pct%', ['pct' => $savings]) }}
                                        </span>
                                    @endif

                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ trans_choice('{1} :count Year|[2,*] :count Years', $years, ['count' => $years]) }}
                                        </h3>
                                        <div class="flex h-5 w-5 items-center justify-center rounded-full border-2
                                            @if($isSelected) border-indigo-600 bg-indigo-600
                                            @else border-gray-300 dark:border-gray-600
                                            @endif
                                        ">
                                            @if($isSelected)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($rp->price, 0) }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400"> {{ $rp->currency }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ${{ number_format($perYear, 2) }} / {{ __('year') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        @if($reevalProduct)
                            <div class="mt-6 border-t border-dashed border-gray-200 pt-6 dark:border-gray-700">
                                <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">{{ __('Or, if you only need a fresh score:') }}</p>
                                <div wire:click="selectPlan({{ $reevalProduct->id }})"
                                    class="flex cursor-pointer items-center justify-between rounded-xl border-2 p-4 transition
                                        @if($selectedPlan === $reevalProduct->id) border-indigo-600 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20
                                        @else border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600
                                        @endif
                                    ">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $reevalProduct->getTranslationWithFallback('name') }}</h3>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Re-evaluate without renewing the seal validity.') }}</p>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($reevalProduct->price, 0) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- EVALUACIÓN / RE-EVALUACIÓN estándar --}}
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Select your Plan') }}</h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($products as $product)
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
                @endif

                {{-- EXPRESS UPLIFT (sólo evaluación / re-evaluación) --}}
                @if($canOfferExpress)
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <label class="flex cursor-pointer items-start gap-4">
                            <input type="checkbox"
                                wire:model.live="expressUplift"
                                class="mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Express service +$:amount', ['amount' => number_format(\App\Livewire\PaymentCheckout::EXPRESS_UPLIFT_AMOUNT, 0)]) }}</h3>
                                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">+${{ number_format(\App\Livewire\PaymentCheckout::EXPRESS_UPLIFT_AMOUNT, 2) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Result in 5 business days instead of 15.') }}</p>
                            </div>
                        </label>
                    </div>
                @endif

                {{-- ADD-ONS (Plan de Acción + Consultoría) --}}
                @if($addons->isNotEmpty())
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Optional Add-ons') }}</h2>
                        @if($isRenewal)
                            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('checkout.renewal_addons_hint') }}</p>
                        @endif
                        <div class="space-y-3">
                            @foreach($addons as $addon)
                                @php $isAddonSelected = in_array($addon->id, $selectedAddons); @endphp
                                <label class="flex cursor-pointer items-start gap-4 rounded-xl border-2 p-4 transition
                                    @if($isAddonSelected) border-indigo-600 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20
                                    @else border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600
                                    @endif
                                ">
                                    <input type="checkbox"
                                        wire:click="toggleAddon({{ $addon->id }})"
                                        @checked($isAddonSelected)
                                        class="mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $addon->getTranslationWithFallback('name') }}</h3>
                                            <span class="whitespace-nowrap text-lg font-bold text-gray-900 dark:text-white">+${{ number_format($addon->price, 0) }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $addon->getTranslationWithFallback('description') }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Coupon code (manual; precedencia sobre auto-apply) --}}
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <label for="manualCouponCode" class="block text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('Discount code') }}
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Have a coupon? Enter it here. A manual code overrides any automatic discount.') }}
                    </p>
                    <input id="manualCouponCode"
                        type="text"
                        wire:model.live.debounce.500ms="manualCouponCode"
                        placeholder="{{ __('Coupon code') }}"
                        class="mt-3 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    @if($isAutoEarlyDiscount)
                        <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ __(':pct% OFF — early renewal', ['pct' => \App\Livewire\PaymentCheckout::EARLY_RENEWAL_DISCOUNT_PCT]) }}
                        </div>
                    @endif
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
                            <span class="text-gray-600 dark:text-gray-400">{{ $selectedProduct?->getTranslationWithFallback('name') ?? __('Select a plan') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">${{ number_format($selectedProduct?->price ?? 0, 2) }}</span>
                        </div>

                        @foreach($selectedAddons as $addonId)
                            @php $addon = $addons->firstWhere('id', $addonId); @endphp
                            @if($addon)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $addon->getTranslationWithFallback('name') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">+${{ number_format($addon->price, 2) }}</span>
                                </div>
                            @endif
                        @endforeach

                        @if($expressUplift && $canOfferExpress)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('Express service (+5 days turnaround)') }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">+${{ number_format(\App\Livewire\PaymentCheckout::EXPRESS_UPLIFT_AMOUNT, 2) }}</span>
                            </div>
                        @endif

                        @if($isAutoEarlyDiscount && $discountAmount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="inline-flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    {{ __('Early renewal discount (-:pct%)', ['pct' => \App\Livewire\PaymentCheckout::EARLY_RENEWAL_DISCOUNT_PCT]) }}
                                </span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">−${{ number_format($discountAmount, 2) }}</span>
                            </div>
                            @if($earlyDeadline)
                                <p class="text-xs text-emerald-700/80 dark:text-emerald-400/80">
                                    {{ __('Promo valid until :date', ['date' => $earlyDeadline->format('d/m/Y')]) }}
                                </p>
                            @endif
                        @endif

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Taxes') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">$0</span>
                        </div>
                    </div>

                    <div class="flex justify-between py-4">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($this->total, 2) }} {{ $selectedProduct?->currency }}</span>
                    </div>

                    <button wire:click="processPayment"
                        wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-emerald-600 py-3 text-center font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$selectedPlan) disabled @endif>
                        <span wire:loading.remove wire:target="processPayment">
                            {{ __('Pay') }} ${{ number_format($this->total, 2) }} {{ $selectedProduct?->currency }}
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
