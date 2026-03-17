<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-5xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-purple-600">Mi Panel</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">Pago de Libro</span>
        </nav>

        <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">Completar Pago</h1>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Plan Selection --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Selecciona tu Plan</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($this->products as $product)
                            <div wire:click="selectPlan({{ $product->id }})"
                                class="relative cursor-pointer rounded-xl border-2 p-6 transition
                                    @if($selectedPlan === $product->id) border-purple-600 bg-purple-50 dark:border-purple-500 dark:bg-purple-900/20
                                    @else border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600
                                    @endif
                                ">

                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $product->name }}</h3>
                                    <div class="flex h-5 w-5 items-center justify-center rounded-full border-2
                                        @if($selectedPlan === $product->id) border-purple-600 bg-purple-600
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
                                    <span class="text-gray-500 dark:text-gray-400">/ pago único</span>
                                </div>

                                <div class="prose prose-sm dark:prose-invert">
                                    {!! $product->description !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Método de Pago</h2>

                    <div class="space-y-4">
                        <label class="flex cursor-pointer items-center rounded-lg border-2 p-4 transition
                            @if($paymentMethod === 'card') border-purple-600 bg-purple-50 dark:border-purple-500 dark:bg-purple-900/20
                            @else border-gray-200 hover:border-gray-300 dark:border-gray-700
                            @endif
                        ">
                            <input type="radio" wire:model.live="paymentMethod" value="card" class="sr-only">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full border-2
                                @if($paymentMethod === 'card') border-purple-600 bg-purple-600
                                @else border-gray-300 dark:border-gray-600
                                @endif
                            ">
                                @if($paymentMethod === 'card')
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                @endif
                            </div>
                            <div class="ml-3 flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">Tarjeta de Crédito/Débito</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center rounded-lg border-2 p-4 transition
                            @if($paymentMethod === 'paypal') border-purple-600 bg-purple-50 dark:border-purple-500 dark:bg-purple-900/20
                            @else border-gray-200 hover:border-gray-300 dark:border-gray-700
                            @endif
                        ">
                            <input type="radio" wire:model.live="paymentMethod" value="paypal" class="sr-only">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full border-2
                                @if($paymentMethod === 'paypal') border-purple-600 bg-purple-600
                                @else border-gray-300 dark:border-gray-600
                                @endif
                            ">
                                @if($paymentMethod === 'paypal')
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                @endif
                            </div>
                            <div class="ml-3 flex items-center gap-3">
                                <span class="text-2xl font-bold text-[#003087]">Pay</span><span class="text-2xl font-bold text-[#009CDE]">Pal</span>
                            </div>
                        </label>
                    </div>

                    @if($paymentMethod === 'card')
                        <div class="mt-6 space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Tarjeta</label>
                                <input type="text" placeholder="4242 4242 4242 4242"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Expiración</label>
                                    <input type="text" placeholder="MM/AA"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">CVC</label>
                                    <input type="text" placeholder="123"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Resumen</h2>

                    <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $book->title }}</p>
                        @if($book->isbn_print)
                            <p class="text-sm text-gray-500 dark:text-gray-400">ISBN: {{ $book->isbn_print }}</p>
                        @endif
                    </div>

                    <div class="space-y-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $this->products->firstWhere('id', $selectedPlan)?->name ?? 'Seleccione un plan' }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Impuestos</span>
                            <span class="font-medium text-gray-900 dark:text-white">$0</span>
                        </div>
                    </div>

                    <div class="flex justify-between py-4">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }} {{ $this->products->firstWhere('id', $selectedPlan)?->currency }}</span>
                    </div>

                    <button wire:click="processPayment"
                        class="w-full rounded-lg bg-emerald-600 py-3 text-center font-semibold text-white shadow-sm transition hover:bg-emerald-500"
                        @if(!$selectedPlan) disabled @endif>
                        Pagar ${{ number_format($this->products->firstWhere('id', $selectedPlan)?->price ?? 0, 2) }} {{ $this->products->firstWhere('id', $selectedPlan)?->currency }}
                    </button>

                    <p class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                        Al realizar el pago aceptas nuestros <a href="/terms" class="text-purple-600 hover:underline">Términos de Servicio</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
