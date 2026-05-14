{{-- Tabla de detalle del pago asociado a la admin_task.
     Renderiza TODOS los items pagados, resaltando los que corresponden
     a esta tarea específica (vs los que pertenecen a otras tasks
     generadas por el mismo payment, ej. consulting addon). --}}

@php
    $b = $getRecord()->payment?->breakdown();
    $task = $getRecord();
@endphp

@if($b)
    @php
        // Determinar qué slugs corresponden a esta task para resaltar la fila.
        $taskSlugs = match($task->type) {
            \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL,
            \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL,
            \App\Models\AdminTask::TYPE_RENEWAL_EVALUATION => array_filter([
                $b['main']['slug'],
                $b['express'] ? 'express' : null,
            ]),
            \App\Models\AdminTask::TYPE_CONSULTING => ['action-plan-consulting'],
            \App\Models\AdminTask::TYPE_REVIEW_LISTING_BOOK => [$b['main']['slug']],
            default => [],
        };
    @endphp

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
@else
    <p class="text-sm italic text-gray-500 dark:text-gray-400">{{ __('Sin información de pago.') }}</p>
@endif
