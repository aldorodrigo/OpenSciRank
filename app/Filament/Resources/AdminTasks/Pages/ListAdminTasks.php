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
     * Tabs de estado en el header de la tabla.
     * El primer tab (Por hacer) es el default y filtra solo tareas abiertas.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            // Sprint 3.7 #44: "Por hacer" excluye awaiting_payment (queue separada).
            // Cortesías (sin link de pago) y tareas pagadas activadas → quedan acá.
            'open' => Tab::make(__('Por hacer'))
                ->badge(fn () => AdminTask::whereIn('status', AdminTask::STATUSES_WORK_QUEUE)->count())
                ->badgeColor('warning')
                ->query(fn (Builder $query) => $query->whereIn('status', AdminTask::STATUSES_WORK_QUEUE)),

            // Sprint 3.7 #44: tab dedicado para tasks con pago pendiente.
            // No están en "Por hacer" porque todavía no se cobró.
            'awaiting_payment' => Tab::make(__('Pago pendiente'))
                ->badge(fn () => AdminTask::where('status', AdminTask::STATUS_AWAITING_PAYMENT)->count())
                ->badgeColor('warning')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_AWAITING_PAYMENT)),

            'completed' => Tab::make(__('Completadas'))
                ->badge(fn () => AdminTask::where('status', AdminTask::STATUS_COMPLETED)->count())
                ->badgeColor('success')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_COMPLETED)),

            'cancelled' => Tab::make(__('Canceladas'))
                ->badge(fn () => AdminTask::where('status', AdminTask::STATUS_CANCELLED)->count())
                ->badgeColor('gray')
                ->query(fn (Builder $query) => $query->where('status', AdminTask::STATUS_CANCELLED)),

            'all' => Tab::make(__('Todas'))
                ->query(fn (Builder $query) => $query),
        ];
    }
}
