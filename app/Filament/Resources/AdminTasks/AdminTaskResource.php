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
        $count = AdminTask::open()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * AdminTask has no create form — tasks are system-generated.
     */
    public static function canCreate(): bool
    {
        return false;
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
            'view' => ViewAdminTask::route('/{record}'),
        ];
    }
}
