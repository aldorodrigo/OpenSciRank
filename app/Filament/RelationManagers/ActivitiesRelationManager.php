<?php

namespace App\Filament\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activitiesAsSubject';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clock';

    /**
     * Roadmap #35 — el historial de actividad (activity log) es información
     * interna del staff. El evaluador no lo ve.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.activity.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.activity.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.activity.plural');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.activity.date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Model $record): string => $record->created_at->locale(app()->getLocale())->diffForHumans()),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('admin.activity.user'))
                    ->placeholder(__('admin.common.system'))
                    ->description(fn (Model $record): ?string => $record->causer?->email)
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.activity.event'))
                    ->badge()
                    ->color(fn (Model $record): string => match ($record->event) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'restored' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('changes')
                    ->label(__('admin.activity.changes'))
                    ->getStateUsing(function (Model $record): string {
                        // En Spatie v5 los diffs automáticos viven en attribute_changes.
                        // En manuales (withProperties) viven en properties.
                        $changes = $record->attribute_changes ?? collect();
                        $old = $changes->get('old', []);
                        $new = $changes->get('attributes', []);

                        $lines = [];

                        foreach ($new as $field => $value) {
                            $oldValue = data_get($old, $field, '∅');
                            $newValue = is_scalar($value) || $value === null ? ($value ?? '∅') : json_encode($value);
                            $oldValue = is_scalar($oldValue) || $oldValue === null ? ($oldValue ?? '∅') : json_encode($oldValue);

                            if ((string) $oldValue === (string) $newValue) {
                                continue;
                            }

                            $lines[] = "{$field}: {$oldValue} → {$newValue}";
                        }

                        // Para logs manuales: mostrar propiedades personalizadas
                        $properties = $record->properties ?? collect();
                        foreach ($properties as $key => $value) {
                            $rendered = is_scalar($value) || $value === null ? ($value ?? '∅') : json_encode($value);
                            $lines[] = "{$key}: {$rendered}";
                        }

                        return empty($lines) ? '—' : implode(' · ', $lines);
                    })
                    ->wrap()
                    ->html(false)
                    ->extraAttributes(['class' => 'font-mono text-xs']),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('admin.activity.event_type'))
                    ->options([
                        'created' => __('admin.activity.event_created'),
                        'updated' => __('admin.activity.event_updated'),
                        'deleted' => __('admin.activity.event_deleted'),
                        'restored' => __('admin.activity.event_restored'),
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
