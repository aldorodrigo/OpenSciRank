{{-- Modal con el detalle completo del pago asociado a la admin_task.
     Visible sólo para super_admin desde el botón "Ver pago" en el header
     de ViewAdminTask. Reemplaza la sección inline previa del infolist. --}}

@php
    /** @var \App\Models\AdminTask $task */
    $task = $task;
    // Sprint 3.7 #46: usar effective_payment para cubrir el flujo nuevo #44
    // (link de pago → payment.solicited_by_admin_task_id) además del viejo
    // (admin_task.payment_id seteado por webhook).
    $payment = $task->effective_payment;
    $b = $payment?->breakdown();
    $coupon = $payment?->metadata['coupon_code'] ?? null;
@endphp

@if($payment === null)
    <p class="text-sm italic text-gray-500 dark:text-gray-400">{{ __('Esta tarea no tiene pago asociado.') }}</p>
@else
    <div class="space-y-5">
        {{-- Bloque 1: Metadatos del pago --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('ID de pago') }}</div>
                <div class="mt-1">
                    <a href="{{ \App\Filament\Resources\Payments\PaymentResource::getUrl('view', ['record' => $payment->id]) }}"
                       target="_blank"
                       class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                        #{{ $payment->id }}
                    </a>
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Fecha de pago') }}</div>
                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $payment->created_at?->format('d/m/Y H:i') ?? '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Estado del pago') }}</div>
                <div class="mt-1">
                    @php
                        $statusColor = match ($payment->status) {
                            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            'refunded' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                        {{ __('admin.payment_status.' . $payment->status) }}
                    </span>
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Comprador') }}</div>
                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $payment->user?->name ?? '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Email del comprador') }}</div>
                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($payment->user?->email)
                        <a href="mailto:{{ $payment->user->email }}" class="text-indigo-600 hover:underline dark:text-indigo-400">
                            {{ $payment->user->email }}
                        </a>
                    @else
                        —
                    @endif
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Proveedor') }}</div>
                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $payment->provider ? ucfirst($payment->provider) : '—' }}
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('ID de transacción') }}</div>
                <div class="mt-1 font-mono text-xs text-gray-700 dark:text-gray-300 break-all">
                    {{ $payment->transaction_id ?? '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Cupón aplicado') }}</div>
                <div class="mt-1">
                    @if($coupon)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            {{ $coupon }}
                        </span>
                    @else
                        <span class="text-sm italic text-gray-400">{{ __('Sin cupón') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bloque 2: tabla de desglose con highlight de filas que pertenecen a esta tarea --}}
        @if($b)
            @php
                $taskSlugs = match($task->type) {
                    \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL,
                    \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL,
                    \App\Models\AdminTask::TYPE_RENEWAL_EVALUATION => array_filter([
                        $b['main']['slug'],
                        $b['express'] ? 'express' : null,
                    ]),
                    \App\Models\AdminTask::TYPE_CONSULTING => ['action-plan-consulting', 'new-journal-consulting'],
                    \App\Models\AdminTask::TYPE_REVIEW_LISTING_BOOK => [$b['main']['slug']],
                    // Sprint 3.7 #46: support task → producto support-credit
                    \App\Models\AdminTask::TYPE_SUPPORT => ['support-credit'],
                    default => [],
                };
            @endphp

            <div>
                <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Desglose del cobro') }}</h4>
                <p class="mb-2 text-xs italic text-gray-500 dark:text-gray-400">
                    {{ __('Filas resaltadas pertenecen a esta tarea.') }}
                </p>

                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Concepto') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Slug') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Monto') }}</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Pertenece a') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            {{-- Producto principal --}}
                            @php($isMainTaskRow = in_array($b['main']['slug'], $taskSlugs, true))
                            <tr class="{{ $isMainTaskRow ? 'bg-indigo-50 dark:bg-indigo-950/30' : '' }}">
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $b['main']['name'] }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $b['main']['slug'] ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($b['main']['price'], 0) }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($isMainTaskRow)
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{{ __('Esta tarea') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Express uplift --}}
                            @if($b['express'])
                                @php($isExpressTaskRow = in_array('express', $taskSlugs, true))
                                <tr class="{{ $isExpressTaskRow ? 'bg-indigo-50 dark:bg-indigo-950/30' : '' }}">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046a1 1 0 0 1 .7 1.073l-.667 6 5.45-.001a1 1 0 0 1 .848 1.529L8.62 18.622a1 1 0 0 1-1.62-1.073l.667-6L2.217 11.55a1 1 0 0 1-.848-1.53L11.382.43a1 1 0 0 1-.082.616z"/></svg>
                                            {{ __('Servicio Express') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ __('uplift') }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($b['express'], 0) }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($isExpressTaskRow)
                                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{{ __('Esta tarea') }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Addons --}}
                            @foreach($b['addons'] as $addon)
                                @php($isAddonTaskRow = in_array($addon['slug'], $taskSlugs, true))
                                <tr class="{{ $isAddonTaskRow ? 'bg-indigo-50 dark:bg-indigo-950/30' : '' }}">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $addon['name'] }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $addon['slug'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($addon['price'], 0) }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($isAddonTaskRow)
                                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{{ __('Esta tarea') }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ __('Otra tarea') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Descuento (cupón) --}}
                            @if($b['discount'] > 0)
                                <tr>
                                    <td class="px-4 py-2.5 italic text-emerald-700 dark:text-emerald-400">{{ __('Descuento aplicado') }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">—</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-emerald-700 dark:text-emerald-400">−${{ number_format($b['discount'], 0) }}</td>
                                    <td class="px-4 py-2.5"></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ __('Total cobrado') }}</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-gray-900 dark:text-gray-100">${{ number_format($b['amount'], 0) }} {{ $b['currency'] }}</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endif
