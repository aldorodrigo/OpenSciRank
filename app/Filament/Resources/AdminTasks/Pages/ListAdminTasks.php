<?php

namespace App\Filament\Resources\AdminTasks\Pages;

use App\Filament\Resources\AdminTasks\AdminTaskResource;
use App\Models\AdminTask;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAdminTasks extends ListRecords
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Roadmap #35 — conteo scopeado para los badges de los tabs. Un evaluator
     * solo cuenta SUS tareas (assigned_to), igual que su tabla y el badge del
     * nav; así los números de los tabs no contradicen lo que ve en la grilla.
     */
    protected function tabCount(array|string $statuses): int
    {
        $query = AdminTask::whereIn('status', (array) $statuses);

        $user = auth()->user();
        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            $query->where('assigned_to', $user->id);
        }

        return $query->count();
    }

    /**
     * Tabs de estado en el header de la tabla.
     * El primer tab (Por hacer) es el default y filtra solo tareas abiertas.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            // Sprint 3.7 #44: "Por hacer" excluye awaiting_payment (queue separada).
            // Cortesías (sin link de pago) y tareas pagadas activadas → quedan acá.
            'open' => Tab::make(__('Por hacer'))
                ->badge(fn () => $this->tabCount(AdminTask::STATUSES_WORK_QUEUE))
                ->badgeColor('warning')
                ->query(fn (Builder $query) => $query->whereIn('status', AdminTask::STATUSES_WORK_QUEUE)),

            // Sprint 3.7 #44: tab dedicado para tasks con pago pendiente.
            // No están en "Por hacer" porque todavía no se cobró.
            'awaiting_payment' => Tab::make(__('Pago pendiente'))
                ->badge(fn () => $this->tabCount(AdminTask::STATUS_AWAITING_PAYMENT))
                ->badgeColor('warning')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_AWAITING_PAYMENT)),

            'completed' => Tab::make(__('Completadas'))
                ->badge(fn () => $this->tabCount(AdminTask::STATUS_COMPLETED))
                ->badgeColor('success')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_COMPLETED)),

            'cancelled' => Tab::make(__('Canceladas'))
                ->badge(fn () => $this->tabCount(AdminTask::STATUS_CANCELLED))
                ->badgeColor('gray')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_CANCELLED)),

            'all' => Tab::make(__('Todas'))
                ->query(fn (Builder $query) => $query),
        ];

        // Roadmap #35 — el evaluador no ve el tab de pagos ("Pago pendiente"):
        // sus tareas nunca están en awaiting_payment y es info comercial.
        $user = auth()->user();
        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            unset($tabs['awaiting_payment']);
        }

        return $tabs;
    }
}
