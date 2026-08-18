{{-- Modal de detalle del pago para /admin/payments.
     Muestra TODOS los datos del pago + listado de admin_tasks relacionadas
     con links a cada una. Visible sólo para super_admin (gate aplicado por
     la acción que abre este modal). --}}

@php
    /** @var \App\Models\Payment $payment */
    $payment = $payment;
    $b = $payment->breakdown();
    $coupon = $payment->metadata['coupon_code'] ?? null;
    $isExpress = (bool) ($payment->metadata['is_express'] ?? false);
    $tasks = $payment->adminTasks()->with('assignee')->orderBy('id')->get();

    // Cortesía (#69): el motivo es obligatorio al otorgarla y queda en el
    // metadata; sin esto el desglose muestra un descuento del 100% sin decir
    // por qué ni quién lo autorizó.
    $isCourtesy = $payment->isCourtesy();
    $courtesyReason = $isCourtesy ? ($payment->metadata['reason'] ?? null) : null;
    $courtesyGrantedBy = $isCourtesy ? ($payment->metadata['granted_by_name'] ?? null) : null;

    // El proveedor se etiqueta igual que en la tabla y el infolist. `courtesy`
    // no es una pasarela: sin traducir se leía "Courtesy" en el panel en español.
    $providerLabel = match (true) {
        $isCourtesy => __('admin.payment.provider_courtesy'),
        filled($payment->provider) => ucfirst($payment->provider),
        default => '—',
    };

    $payableLabel = $payment->payable
        ? match ($payment->payable_type) {
            \App\Models\Journal::class => '[' . __('Revista') . '] ' . $payment->payable->getTranslationWithFallback('title'),
            \App\Models\Book::class    => '[' . __('Libro') . '] ' . $payment->payable->getTranslationWithFallback('title'),
            default => $payment->payable_type,
        }
        : __('admin.payment.orphan_label');

    $payableUrl = match (true) {
        $payment->payable === null => null,
        $payment->payable_type === \App\Models\Journal::class => \App\Filament\Resources\JournalResource::getUrl('edit', ['record' => $payment->payable_id]),
        $payment->payable_type === \App\Models\Book::class    => \App\Filament\Resources\BookResource::getUrl('edit', ['record' => $payment->payable_id]),
        default => null,
    };
@endphp

<div class="space-y-6">
    {{-- ── Sección 1: metadatos del pago ─────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('ID de pago') }}</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">#{{ $payment->id }}</div>
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
                    <a href="mailto:{{ $payment->user->email }}" class="text-blue-700 hover:underline dark:text-blue-400">
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
                {{ $providerLabel }}
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

        <div class="md:col-span-3">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Recurso asociado') }}</div>
            <div class="mt-1 text-sm">
                @if($payableUrl)
                    <a href="{{ $payableUrl }}" target="_blank" class="font-medium text-blue-700 hover:underline dark:text-blue-400">
                        {{ $payableLabel }}
                    </a>
                @elseif($payment->payable === null)
                    <span class="font-medium text-red-600 dark:text-red-400">{{ $payableLabel }}</span>
                @else
                    <span>{{ $payableLabel }}</span>
                @endif
            </div>
        </div>

        @if($isCourtesy)
            <div class="md:col-span-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                    {{ __('admin.payment.courtesy_reason') }}
                </div>
                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $courtesyReason ?: '—' }}
                </div>
                @if($courtesyGrantedBy)
                    <div class="mt-2 text-xs text-emerald-800 dark:text-emerald-300">
                        {{ __('admin.payment.courtesy_granted_by', ['name' => $courtesyGrantedBy]) }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ── Sección 2: desglose del cobro ─────────────────────────────── --}}
    <div>
        <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Desglose del cobro') }}</h4>

        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Concepto') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Slug') }}</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Monto') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $b['main']['name'] }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $b['main']['slug'] ?? '—' }}</td>
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
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ __('uplift') }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($b['express'], 0) }}</td>
                        </tr>
                    @endif

                    @foreach($b['addons'] as $addon)
                        <tr>
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $addon['name'] }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $addon['slug'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-gray-100">${{ number_format($addon['price'], 0) }}</td>
                        </tr>
                    @endforeach

                    @if($b['discount'] > 0)
                        <tr>
                            <td class="px-4 py-2.5 italic text-emerald-700 dark:text-emerald-400">
                                {{ $isCourtesy ? __('admin.payment.courtesy_waiver') : __('Descuento aplicado') }}
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $isCourtesy ? __('admin.payment.provider_courtesy') : '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-emerald-700 dark:text-emerald-400">−${{ number_format($b['discount'], 0) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ __('Total cobrado') }}</td>
                        <td class="px-4 py-3 text-right text-base font-bold text-gray-900 dark:text-gray-100">${{ number_format($b['amount'], 0) }} {{ $b['currency'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Sección 3: tareas relacionadas ────────────────────────────── --}}
    <div>
        <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
            {{ __('Tareas relacionadas') }}
            <span class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">({{ $tasks->count() }})</span>
        </h4>

        @if($tasks->isEmpty())
            <p class="rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm italic text-gray-500 dark:border-gray-700 dark:text-gray-400">
                {{ __('Este pago no generó tareas administrativas.') }}
            </p>
        @else
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tipo') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Estado') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Asignado a') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Vence') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Acción') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach($tasks as $t)
                            @php
                                $typeLabel = match ($t->type) {
                                    \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación de revista'),
                                    \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación de revista'),
                                    \App\Models\AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación de sello'),
                                    \App\Models\AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado de revista'),
                                    \App\Models\AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado de libro'),
                                    \App\Models\AdminTask::TYPE_CONSULTING => __('Consultoría'),
                                    \App\Models\AdminTask::TYPE_ORPHAN_PAYMENT => __('Pago huérfano'),
                                    default => $t->type,
                                };

                                $typeColor = match ($t->type) {
                                    \App\Models\AdminTask::TYPE_EVALUATE_JOURNAL,
                                    \App\Models\AdminTask::TYPE_REEVALUATE_JOURNAL => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    \App\Models\AdminTask::TYPE_RENEWAL_EVALUATION => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    \App\Models\AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                                    \App\Models\AdminTask::TYPE_REVIEW_LISTING_BOOK => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
                                    \App\Models\AdminTask::TYPE_CONSULTING => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    \App\Models\AdminTask::TYPE_ORPHAN_PAYMENT => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                };

                                $statusLabel = match ($t->status) {
                                    \App\Models\AdminTask::STATUS_PENDING => __('Pendiente'),
                                    \App\Models\AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                                    \App\Models\AdminTask::STATUS_CHANGES_REQUESTED => __('Cambios solicitados'),
                                    \App\Models\AdminTask::STATUS_RESUBMITTED => __('Reenviada'),
                                    \App\Models\AdminTask::STATUS_SCHEDULED => __('Agendada'),
                                    \App\Models\AdminTask::STATUS_IN_SESSION => __('En sesión'),
                                    \App\Models\AdminTask::STATUS_COMPLETED => __('Completada'),
                                    \App\Models\AdminTask::STATUS_CANCELLED => __('Cancelada'),
                                    default => $t->status,
                                };

                                $statusColor = match ($t->status) {
                                    \App\Models\AdminTask::STATUS_PENDING => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    \App\Models\AdminTask::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
                                    \App\Models\AdminTask::STATUS_CHANGES_REQUESTED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    \App\Models\AdminTask::STATUS_RESUBMITTED => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    \App\Models\AdminTask::STATUS_SCHEDULED => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    \App\Models\AdminTask::STATUS_IN_SESSION => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    \App\Models\AdminTask::STATUS_COMPLETED => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    \App\Models\AdminTask::STATUS_CANCELLED => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                };

                                $dueColor = $t->due_at
                                    ? ($t->isOverdue() ? 'text-red-600 dark:text-red-400 font-semibold' : ($t->daysUntilDue() < 3 ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-gray-700 dark:text-gray-300'))
                                    : 'text-gray-400';
                            @endphp
                            <tr>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">#{{ $t->id }}</div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-sm">
                                    @if($t->assignee)
                                        <span class="text-gray-900 dark:text-gray-100">{{ $t->assignee->name }}</span>
                                    @else
                                        <span class="italic text-amber-600 dark:text-amber-400">{{ __('Sin asignar') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs {{ $dueColor }}">
                                    @if($t->due_at)
                                        {{ $t->due_at->format('d/m/Y') }}
                                        @if($t->isOverdue())
                                            · {{ __('vencida') }}
                                        @elseif($t->daysUntilDue() === 0)
                                            · {{ __('hoy') }}
                                        @else
                                            · {{ __(':d días', ['d' => $t->daysUntilDue()]) }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="{{ \App\Filament\Resources\AdminTasks\AdminTaskResource::getUrl('view', ['record' => $t->id]) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50">
                                        {{ __('Abrir') }}
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
