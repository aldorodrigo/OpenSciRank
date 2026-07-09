<?php

namespace App\Models;

use App\Models\AdminTask;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payment extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'amount',
                'currency',
                'stripe_session_id',
                'product_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Pago registrado',
                'updated' => 'Pago actualizado',
                default => $eventName,
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Sprint 3.7 #44 — task que solicitó este pago (link de pago).
     * Null para pagos iniciados directamente desde checkout público.
     */
    public function solicitingTask()
    {
        return $this->belongsTo(AdminTask::class, 'solicited_by_admin_task_id');
    }

    public function adminTasks()
    {
        return $this->hasMany(AdminTask::class);
    }

    /**
     * Estado del SERVICIO desde la perspectiva del editor. Diferente del
     * `status` raw del payment (que es financiero — completed/refunded/etc).
     *
     * Resuelve mirando las admin_tasks asociadas:
     *  - failed/refunded → refleja el status del payment
     *  - pending (payment no completed) → 'pending_payment'
     *  - completed payment + sin tasks asociadas → 'completed' (instantáneo, ej. featured)
     *  - completed payment + tasks abiertas pending sin iniciar → 'pending_work'
     *  - completed payment + alguna task in_progress → 'in_progress'
     *  - completed payment + tasks abiertas + cerradas → 'partial'
     *  - completed payment + todas las tasks cerradas (completed o cancelled) → 'completed'
     */
    public function serviceStatus(): string
    {
        return match ($this->status) {
            'failed' => 'failed',
            'refunded' => 'refunded',
            'pending' => 'pending_payment',
            'completed' => $this->resolveCompletedServiceStatus(),
            default => $this->status,
        };
    }

    protected function resolveCompletedServiceStatus(): string
    {
        $tasks = $this->adminTasks;

        if ($tasks->isEmpty()) {
            return 'completed';
        }

        $openCount = $tasks->whereIn('status', AdminTask::STATUSES_OPEN)->count();
        $closedCount = $tasks->whereIn('status', AdminTask::STATUSES_TERMINAL)->count();
        $totalCount = $tasks->count();

        if ($openCount === 0) {
            return 'completed';
        }

        // Sprint 4 #61: changes_requested (revisor pidió cambios) y resubmitted
        // (editor reenvió) son etapas activas de la entrega del servicio, no
        // "trabajo pendiente sin empezar" → cuentan como en progreso.
        $inProgress = $tasks->whereIn('status', [
            AdminTask::STATUS_IN_PROGRESS,
            AdminTask::STATUS_CHANGES_REQUESTED,
            AdminTask::STATUS_RESUBMITTED,
            AdminTask::STATUS_SCHEDULED,
            AdminTask::STATUS_IN_SESSION,
        ])->count();

        if ($inProgress > 0 && $closedCount > 0) {
            return 'partial';
        }

        if ($inProgress > 0) {
            return 'in_progress';
        }

        // openCount > 0, ninguno in_progress, ninguno cerrado → todo pendiente
        if ($closedCount === 0) {
            return 'pending_work';
        }

        return 'partial';
    }

    /**
     * Itemiza el pago en sus componentes: producto principal, Express si aplica,
     * addons (resueltos vía addon_slugs en metadata), y el total efectivamente
     * cobrado (que puede no coincidir con la suma si hubo cupón aplicado).
     *
     * Estructura:
     *  [
     *    'main'    => ['name' => 'Evaluación Editorial', 'price' => 99.0],
     *    'express' => 50.0 | null,
     *    'addons'  => [['name' => 'Plan de Acción', 'slug' => '...', 'price' => 215.0], ...],
     *    'subtotal' => 364.0,        // suma de los items arriba
     *    'amount'   => 364.0,        // lo que efectivamente cobró Stripe (post-cupón)
     *    'discount' => 0.0,          // subtotal - amount (positivo si hubo cupón)
     *  ]
     */
    public function breakdown(): array
    {
        $main = [
            'name' => $this->product?->getTranslationWithFallback('name') ?? '—',
            'slug' => $this->product?->slug ?? null,
            'price' => (float) ($this->product?->price ?? 0),
        ];

        $isExpress = (bool) ($this->metadata['is_express'] ?? false);
        $expressAmount = $isExpress ? \App\Livewire\PaymentCheckout::EXPRESS_UPLIFT_AMOUNT : null;

        $addonSlugsCsv = $this->metadata['addon_slugs'] ?? '';
        $addonSlugs = is_array($addonSlugsCsv)
            ? array_filter($addonSlugsCsv)
            : array_filter(array_map('trim', explode(',', (string) $addonSlugsCsv)));

        $addons = [];
        if (! empty($addonSlugs)) {
            $addons = Product::whereIn('slug', $addonSlugs)
                ->get()
                ->map(fn (Product $p) => [
                    'name' => $p->getTranslationWithFallback('name'),
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                ])
                ->all();
        }

        $subtotal = $main['price']
            + ($expressAmount ?? 0)
            + array_sum(array_column($addons, 'price'));

        $amount = (float) $this->amount;
        $discount = max(0.0, $subtotal - $amount);

        return [
            'main' => $main,
            'express' => $expressAmount,
            'addons' => $addons,
            'subtotal' => $subtotal,
            'amount' => $amount,
            'discount' => $discount,
            'currency' => $this->currency ?? 'USD',
        ];
    }
}
