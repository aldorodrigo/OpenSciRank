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

    protected static ?string $title = 'Historial';

    protected static ?string $modelLabel = 'Evento';

    protected static ?string $pluralModelLabel = 'Eventos';

    protected static string | BackedEnum | null $icon = 'heroicon-o-clock';

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
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Model $record): string => $record->created_at->locale('es')->diffForHumans()),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->placeholder('Sistema')
                    ->description(fn (Model $record): ?string => $record->causer?->email)
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Evento')
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
                    ->label('Cambios')
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
                    ->label('Tipo de evento')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
