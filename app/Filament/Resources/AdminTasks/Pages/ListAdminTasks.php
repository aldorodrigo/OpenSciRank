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
            'open' => Tab::make(__('Por hacer'))
                ->badge(fn () => AdminTask::open()->count())
                ->badgeColor('warning')
                ->query(fn (Builder $query) => $query->whereIn('status', AdminTask::STATUSES_OPEN)),

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
