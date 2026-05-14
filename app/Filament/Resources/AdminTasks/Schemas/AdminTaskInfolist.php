<?php

namespace App\Filament\Resources\AdminTasks\Schemas;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\AdminTask;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminTaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── 1. Información principal ──────────────────────────────────
                Section::make(__('Información principal'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->label(__('Tipo'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                AdminTask::TYPE_EVALUATE_JOURNAL,
                                AdminTask::TYPE_REEVALUATE_JOURNAL => 'indigo',
                                AdminTask::TYPE_RENEWAL_EVALUATION => 'purple',
                                AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
                                AdminTask::TYPE_REVIEW_LISTING_BOOK => 'info',
                                AdminTask::TYPE_CONSULTING => 'success',
                                AdminTask::TYPE_ORPHAN_PAYMENT => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación de revista'),
                                AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación de revista'),
                                AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación de sello'),
                                AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado de revista'),
                                AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado de libro'),
                                AdminTask::TYPE_CONSULTING => __('Consultoría'),
                                AdminTask::TYPE_ORPHAN_PAYMENT => __('Pago huérfano'),
                                default => $state,
                            }),

                        TextEntry::make('title_key')
                            ->label(__('Tarea'))
                            ->formatStateUsing(fn (AdminTask $record): string => $record->renderedTitle())
                            ->columnSpanFull(),

                        TextEntry::make('priority')
                            ->label(__('Prioridad'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                AdminTask::PRIORITY_HIGH => 'danger',
                                AdminTask::PRIORITY_NORMAL => 'gray',
                                AdminTask::PRIORITY_LOW => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                AdminTask::PRIORITY_HIGH => __('Alta'),
                                AdminTask::PRIORITY_NORMAL => __('Normal'),
                                AdminTask::PRIORITY_LOW => __('Baja'),
                                default => $state,
                            }),

                        TextEntry::make('status')
                            ->label(__('Estado'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_PENDING => 'gray',
                                AdminTask::STATUS_IN_PROGRESS => 'info',
                                AdminTask::STATUS_SCHEDULED => 'indigo',
                                AdminTask::STATUS_IN_SESSION => 'warning',
                                AdminTask::STATUS_COMPLETED => 'success',
                                AdminTask::STATUS_CANCELLED => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_PENDING => __('Pendiente'),
                                AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                                AdminTask::STATUS_SCHEDULED => __('Agendada'),
                                AdminTask::STATUS_IN_SESSION => __('En sesión'),
                                AdminTask::STATUS_COMPLETED => __('Completada'),
                                AdminTask::STATUS_CANCELLED => __('Cancelada'),
                                default => $state,
                            }),

                        TextEntry::make('due_at')
                            ->label(__('Fecha límite'))
                            ->placeholder('—')
                            ->formatStateUsing(function (AdminTask $record): string {
                                if (! $record->due_at) {
                                    return '—';
                                }

                                $formatted = $record->due_at->format('d/m/Y H:i');
                                $days = $record->daysUntilDue();

                                if ($record->isOverdue()) {
                                    return "{$formatted} (".__('Expired :days days ago', ['days' => abs($days)]).')';
                                }

                                return "{$formatted} (".__('Expires in :days days', ['days' => $days]).')';
                            })
                            ->color(function (AdminTask $record): string {
                                if (! $record->due_at) {
                                    return 'gray';
                                }

                                if ($record->isOverdue()) {
                                    return 'danger';
                                }

                                $days = $record->daysUntilDue();

                                return $days < 3 ? 'danger' : ($days < 7 ? 'warning' : 'success');
                            }),
                    ]),

                // ── 2. Asignación ─────────────────────────────────────────────
                Section::make(__('Asignación'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('assignee.name')
                            ->label(__('Asignado a'))
                            ->placeholder(__('Sin asignar')),

                        TextEntry::make('started_at')
                            ->label(__('Iniciada el'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('completed_at')
                            ->label(__('Cerrada el'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('cancelled_reason')
                            ->label(__('Razón de cancelación'))
                            ->placeholder('—')
                            ->visible(fn (AdminTask $record): bool => $record->status === AdminTask::STATUS_CANCELLED)
                            ->columnSpanFull(),
                    ]),

                // ── 3a. Detalle de esta tarea (visible para todos, sin total del pago) ──
                Section::make(__('Detalle'))
                    ->visible(fn (AdminTask $record): bool => $record->payment !== null)
                    ->schema([
                        TextEntry::make('task_amount')
                            ->label(__('Monto correspondiente a esta tarea'))
                            ->getStateUsing(fn (AdminTask $record): string => $record->taskAmount() !== null
                                ? '$'.number_format($record->taskAmount(), 2).' '.($record->payment?->currency ?? 'USD')
                                : '—'
                            ),

                        // Servicio Express si aplica (sin monto)
                        TextEntry::make('express_indicator')
                            ->label(__('Servicio Express'))
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-bolt')
                            ->getStateUsing(function (AdminTask $record): ?string {
                                $isExpress = $record->payment?->metadata['is_express'] ?? false;
                                return $isExpress ? __('Sí') : null;
                            })
                            ->placeholder(__('No')),
                    ]),

                // ── 3b. Pago completo (sólo super_admin) ──────────────────────
                // Muestra el desglose del pago: cada producto + addons + total.
                // Los evaluadores no ven esta sección — sólo necesitan saber
                // qué hacer, no cuánto pagó el editor por todo el bundle.
                Section::make(__('Información del pago (admin)'))
                    ->columns(2)
                    ->visible(fn (AdminTask $record): bool => $record->payment !== null
                        && (auth()->user()?->hasRole('super_admin') ?? false)
                    )
                    ->schema([
                        TextEntry::make('payment.id')
                            ->label(__('ID de pago'))
                            ->url(fn (AdminTask $record): ?string => $record->payment
                                ? PaymentResource::getUrl('view', ['record' => $record->payment_id])
                                : null
                            )
                            ->openUrlInNewTab()
                            ->placeholder('—'),

                        TextEntry::make('payment.created_at')
                            ->label(__('Fecha de pago'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        // Breakdown del pago completo
                        TextEntry::make('payment_breakdown')
                            ->label(__('Desglose del pago'))
                            ->getStateUsing(function (AdminTask $record): string {
                                $b = $record->payment?->breakdown();
                                if (! $b) {
                                    return '—';
                                }

                                $currency = $b['currency'];
                                $lines = [];
                                $lines[] = $b['main']['name'].': $'.number_format($b['main']['price'], 0);
                                if ($b['express']) {
                                    $lines[] = __('Express').': $'.number_format($b['express'], 0);
                                }
                                foreach ($b['addons'] as $addon) {
                                    $lines[] = $addon['name'].': $'.number_format($addon['price'], 0);
                                }
                                if ($b['discount'] > 0) {
                                    $lines[] = __('Descuento').': −$'.number_format($b['discount'], 0);
                                }
                                $lines[] = '**'.__('Total').': $'.number_format($b['amount'], 0).' '.$currency.'**';

                                return implode("\n", $lines);
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // ── 4. Recurso relacionado ────────────────────────────────────
                Section::make(__('Recurso relacionado'))
                    ->visible(fn (AdminTask $record): bool => $record->related !== null)
                    ->schema([
                        TextEntry::make('related_id')
                            ->label(__('Nombre'))
                            ->formatStateUsing(fn (AdminTask $record): string => $record->related
                                ? $record->related->getTranslationWithFallback('title')
                                : '—'
                            )
                            ->url(fn (AdminTask $record): ?string => match (true) {
                                $record->related === null => null,
                                $record->related_type === 'App\\Models\\Journal' => JournalResource::getUrl('edit', ['record' => $record->related_id]),
                                $record->related_type === 'App\\Models\\Book' => BookResource::getUrl('edit', ['record' => $record->related_id]),
                                default => null,
                            })
                            ->openUrlInNewTab(),

                        TextEntry::make('related_type')
                            ->label(__('Tipo de recurso'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'App\\Models\\Journal' => __('Revista'),
                                'App\\Models\\Book' => __('Libro'),
                                default => $state,
                            }),
                    ]),

                // ── 5. Notas internas ─────────────────────────────────────────
                Section::make(__('Notas internas'))
                    ->visible(fn (AdminTask $record): bool => filled($record->notes))
                    ->schema([
                        TextEntry::make('notes')
                            ->label(__('Notas'))
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // ── 6. Detalles de consultoría ────────────────────────────────
                Section::make(__('Detalles de consultoría'))
                    ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('scheduled_for')
                            ->label(__('Sesión agendada para'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder(__('Sin agendar')),

                        TextEntry::make('status')
                            ->label(__('Estado de la sesión'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_SCHEDULED => 'indigo',
                                AdminTask::STATUS_IN_SESSION => 'warning',
                                AdminTask::STATUS_COMPLETED => 'success',
                                AdminTask::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_PENDING => __('Sin agendar'),
                                AdminTask::STATUS_IN_PROGRESS => __('En preparación'),
                                AdminTask::STATUS_SCHEDULED => __('Agendada — esperando sesión'),
                                AdminTask::STATUS_IN_SESSION => __('Sesión en curso'),
                                AdminTask::STATUS_COMPLETED => __('Sesión completada'),
                                AdminTask::STATUS_CANCELLED => __('Cancelada'),
                                default => $state,
                            }),
                    ]),
            ]);
    }
}
