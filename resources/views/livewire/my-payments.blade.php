@if(!$asModal)
    <x-slot:header>true</x-slot:header>
@endif

<div class="{{ $asModal ? '' : 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8' }}">

    {{-- Header (solo modo pagina completa, en modal el header lo da el wrapper del modal) --}}
    @unless($asModal)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Mis pagos') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Historial de pagos realizados en la plataforma.') }}</p>
            </div>
            <a href="{{ route('app.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Volver al dashboard') }}
            </a>
        </div>
    @endunless

    {{-- Filtros --}}
    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <select wire:model.live="statusFilter"
                class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">{{ __('Todos los estados') }}</option>
            <option value="completed">{{ __('Completado') }}</option>
            <option value="pending">{{ __('Pendiente') }}</option>
            <option value="failed">{{ __('Fallido') }}</option>
            <option value="refunded">{{ __('Reembolsado') }}</option>
        </select>
        <select wire:model.live="productFilter"
                class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">{{ __('Todos los productos') }}</option>
            <option value="journal-evaluation">{{ __('Evaluación de revista') }}</option>
            <option value="journal-reevaluation">{{ __('Re-evaluación de revista') }}</option>
            <option value="seal-renewal-1y">{{ __('Renovación 1 año') }}</option>
            <option value="seal-renewal-2y">{{ __('Renovación 2 años') }}</option>
            <option value="seal-renewal-3y">{{ __('Renovación 3 años') }}</option>
            <option value="action-plan-consulting">{{ __('Plan de Acción + Consultoría') }}</option>
            <option value="book-listing">{{ __('Listing de libro') }}</option>
            <option value="book-listing-featured-1y">{{ __('Listing destacado') }}</option>
        </select>
    </div>

    {{-- Tabla --}}
    @if($payments->isEmpty())
        <div class="rounded-xl border-2 border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
            </svg>
            <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">{{ __('Sin pagos registrados') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Cuando realices un pago aparecerá acá con su detalle completo.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Fecha') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Producto') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Revista / Libro') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Monto') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Detalle') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $payment->product?->getTranslationWithFallback('name') ?? '—' }}
                                    </div>
                                    @if(($payment->metadata['is_express'] ?? false))
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046a1 1 0 0 1 .7 1.073l-.667 6 5.45-.001a1 1 0 0 1 .848 1.529L8.62 18.622a1 1 0 0 1-1.62-1.073l.667-6L2.217 11.55a1 1 0 0 1-.848-1.53L11.382.43a1 1 0 0 1-.082.616z"/></svg>
                                            Express
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($payment->payable)
                                        <a href="{{ $payment->payable_type === 'App\\Models\\Journal' ? '/journal/'.$payment->payable->slug : '/book/'.$payment->payable->slug }}"
                                           class="text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $payment->payable->getTranslationWithFallback('title') }}
                                        </a>
                                    @else
                                        <span class="text-gray-400 italic">{{ __('Recurso eliminado') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                                    ${{ number_format($payment->amount, 0) }} {{ $payment->currency }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php($svc = $payment->serviceStatus())
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        @if($svc === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                        @elseif($svc === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($svc === 'pending_work' || $svc === 'pending_payment') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                        @elseif($svc === 'partial') bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300
                                        @elseif($svc === 'failed') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                        @elseif($svc === 'refunded') bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                                        @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                                        @endif
                                    ">
                                        {{ match($svc) {
                                            'completed' => __('Servicio completado'),
                                            'in_progress' => __('En proceso'),
                                            'pending_work' => __('Pendiente de iniciar'),
                                            'pending_payment' => __('Pago pendiente'),
                                            'partial' => __('Parcialmente completado'),
                                            'failed' => __('Pago fallido'),
                                            'refunded' => __('Reembolsado'),
                                            default => ucfirst($svc),
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="showDetail({{ $payment->id }})"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60">
                                        {{ __('Ver') }}
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @endif

    {{-- Modal detalle --}}
    @if($showDetailModal && $selectedPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeDetail">
            <div class="relative mx-4 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" wire:click.stop>
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Detalle del pago') }} #{{ $selectedPayment->id }}</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $selectedPayment->created_at->format('d/m/Y H:i') }} ·
                            {{ __('Transacción') }}: <span class="font-mono">{{ $selectedPayment->transaction_id ?? '—' }}</span>
                        </p>
                    </div>
                    <button wire:click="closeDetail" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @php($b = $selectedPayment->breakdown())
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Concepto') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Monto') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $b['main']['name'] }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($b['main']['price'], 0) }}</td>
                            </tr>
                            @if($b['express'])
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046a1 1 0 0 1 .7 1.073l-.667 6 5.45-.001a1 1 0 0 1 .848 1.529L8.62 18.622a1 1 0 0 1-1.62-1.073l.667-6L2.217 11.55a1 1 0 0 1-.848-1.53L11.382.43a1 1 0 0 1-.082.616z"/></svg>
                                            {{ __('Servicio Express') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($b['express'], 0) }}</td>
                                </tr>
                            @endif
                            @foreach($b['addons'] as $addon)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $addon['name'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($addon['price'], 0) }}</td>
                                </tr>
                            @endforeach
                            @if($b['discount'] > 0)
                                <tr>
                                    <td class="px-4 py-2.5 italic text-emerald-700 dark:text-emerald-400">{{ __('Descuento aplicado') }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-emerald-700 dark:text-emerald-400">−${{ number_format($b['discount'], 0) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <td class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ __('Total cobrado') }}</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-gray-900 dark:text-gray-100">${{ number_format($b['amount'], 0) }} {{ $b['currency'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($selectedPayment->payable)
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Aplicado a') }}:
                        <a href="{{ $selectedPayment->payable_type === 'App\\Models\\Journal' ? '/journal/'.$selectedPayment->payable->slug : '/book/'.$selectedPayment->payable->slug }}"
                           class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            {{ $selectedPayment->payable->getTranslationWithFallback('title') }}
                        </a>
                    </p>
                @endif

                <div class="mt-6 flex justify-end">
                    <button wire:click="closeDetail" type="button"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        {{ __('Cerrar') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
