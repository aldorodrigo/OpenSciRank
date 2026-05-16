<?php

namespace App\Filament\Resources\AdminTasks;

use App\Filament\Resources\AdminTasks\Pages\ListAdminTasks;
use App\Filament\Resources\AdminTasks\Pages\ViewAdminTask;
use App\Filament\Resources\AdminTasks\Schemas\AdminTaskInfolist;
use App\Filament\Resources\AdminTasks\Tables\AdminTasksTable;
use App\Models\AdminTask;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AdminTaskResource extends Resource
{
    protected static ?string $model = AdminTask::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.commercial');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tareas');
    }

    public static function getModelLabel(): string
    {
        return __('Tarea');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tareas');
    }

    public static function getNavigationBadge(): ?string
    {
        // Sprint 3.7 #44: el badge muestra tasks que requieren trabajo del admin.
        // No incluye awaiting_payment (espera del editor, no del admin).
        $count = AdminTask::whereIn('status', AdminTask::STATUSES_WORK_QUEUE)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Sprint 4 #34 — habilitado: super_admin puede crear tasks manualmente
     * desde Filament (casos ad-hoc, recordatorios internos, follow-ups).
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminTaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminTasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminTasks::route('/'),
            'create' => \App\Filament\Resources\AdminTasks\Pages\CreateAdminTask::route('/create'),
            'view' => ViewAdminTask::route('/{record}'),
        ];
    }
}
