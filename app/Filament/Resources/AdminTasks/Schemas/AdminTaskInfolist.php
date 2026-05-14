<?php

namespace App\Filament\Resources\AdminTasks\Schemas;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\AdminTask;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminTaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── 1. Resumen (info principal + recurso relacionado en una vista) ──
                Section::make(__('Resumen de la tarea'))
                    ->icon('heroicon-o-clipboard-document')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('type')
                            ->label(__('Tipo'))
                            ->badge()
                            ->size(\Filament\Support\Enums\TextSize::Medium)
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

                        TextEntry::make('related_id')
                            ->label(__('Recurso'))
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
                            ->openUrlInNewTab()
                            ->placeholder('—'),

                        TextEntry::make('title_key')
                            ->label(__('Tarea'))
                            ->formatStateUsing(fn (AdminTask $record): string => $record->renderedTitle())
                            ->columnSpanFull()
                            ->weight('semibold'),
                    ]),

                // ── 2. Estado y prioridad ─────────────────────────────────────
                Section::make(__('Estado y prioridad'))
                    ->icon('heroicon-o-signal')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('Estado'))
                            ->badge()
                            ->size(\Filament\Support\Enums\TextSize::Medium)
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

                        TextEntry::make('priority')
                            ->label(__('Prioridad'))
                            ->badge()
                            ->size(\Filament\Support\Enums\TextSize::Medium)
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

                        TextEntry::make('due_at')
                            ->label(__('Fecha límite'))
                            ->placeholder('—')
                            ->formatStateUsing(function (AdminTask $record): string {
                                if (! $record->due_at) {
                                    return '—';
                                }

                                $formatted = $record->due_at->format('d/m/Y');
                                $days = $record->daysUntilDue();

                                if ($record->isOverdue()) {
                                    return $formatted.' · '.__('Venció hace :days días', ['days' => abs($days)]);
                                }

                                if ($days === 0) {
                                    return $formatted.' · '.__('Vence hoy');
                                }

                                return $formatted.' · '.__('Vence en :days días', ['days' => $days]);
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
                            })
                            ->weight('semibold'),
                    ]),

                // ── 3. Asignación ─────────────────────────────────────────────
                Section::make(__('Asignación'))
                    ->icon('heroicon-o-user-circle')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextEntry::make('assignee.name')
                            ->label(__('Asignado a'))
                            ->placeholder(__('Sin asignar'))
                            ->color(fn (AdminTask $record): string => $record->assignee ? 'gray' : 'warning'),

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
                            ->visible(fn (AdminTask $record): bool => $record->status === AdminTask::STATUS_CANCELLED)
                            ->color('danger')
                            ->columnSpanFull(),
                    ]),

                // ── 4. Detalle de esta tarea (visible para todos, sin total del pago) ──
                Section::make(__('Detalle de esta tarea'))
                    ->icon('heroicon-o-document-check')
                    ->visible(fn (AdminTask $record): bool => $record->payment !== null)
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('task_amount')
                            ->label(__('Monto correspondiente a esta tarea'))
                            ->getStateUsing(fn (AdminTask $record): string => $record->taskAmount() !== null
                                ? '$'.number_format($record->taskAmount(), 0).' '.($record->payment?->currency ?? 'USD')
                                : '—'
                            )
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->weight('bold'),

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

                // ── 5. Pago completo (sólo super_admin) — TABLA con todos los items ──
                Section::make(__('Información del pago'))
                    ->description(__('Detalle de la transacción completa. Filas resaltadas pertenecen a esta tarea.'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (AdminTask $record): bool => $record->payment !== null
                        && (auth()->user()?->hasRole('super_admin') ?? false)
                    )
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('payment.id')
                            ->label(__('ID de pago'))
                            ->prefix('#')
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

                        ViewEntry::make('payment_breakdown_table')
                            ->view('filament.admin-tasks.payment-breakdown-table')
                            ->columnSpanFull(),
                    ]),

                // ── 6. Notas internas ─────────────────────────────────────────
                Section::make(__('Notas internas'))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (AdminTask $record): bool => filled($record->notes))
                    ->schema([
                        TextEntry::make('notes')
                            ->hiddenLabel()
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // ── 7. Detalles de consultoría (solo type=consulting) ─────────
                Section::make(__('Detalles de consultoría'))
                    ->icon('heroicon-o-calendar')
                    ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING)
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('scheduled_for')
                            ->label(__('Sesión agendada para'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder(__('Sin agendar')),

                        TextEntry::make('consulting_status_label')
                            ->label(__('Etapa actual'))
                            ->badge()
                            ->color(fn (AdminTask $record): string => match ($record->status) {
                                AdminTask::STATUS_PENDING => 'gray',
                                AdminTask::STATUS_SCHEDULED => 'indigo',
                                AdminTask::STATUS_IN_SESSION => 'warning',
                                AdminTask::STATUS_COMPLETED => 'success',
                                AdminTask::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            })
                            ->getStateUsing(fn (AdminTask $record): string => match ($record->status) {
                                AdminTask::STATUS_PENDING => __('Sin agendar'),
                                AdminTask::STATUS_IN_PROGRESS => __('En preparación'),
                                AdminTask::STATUS_SCHEDULED => __('Agendada — esperando sesión'),
                                AdminTask::STATUS_IN_SESSION => __('Sesión en curso'),
                                AdminTask::STATUS_COMPLETED => __('Sesión completada'),
                                AdminTask::STATUS_CANCELLED => __('Cancelada'),
                                default => $record->status,
                            }),
                    ]),
            ]);
    }
}
