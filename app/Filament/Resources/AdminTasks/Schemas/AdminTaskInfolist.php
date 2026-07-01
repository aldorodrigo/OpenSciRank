<?php

namespace App\Filament\Resources\AdminTasks\Schemas;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\JournalResource;
use App\Models\AdminTask;
use App\Models\ConsultingProposal;
use Filament\Infolists\Components\TextEntry;
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
                                AdminTask::TYPE_REEVALUATE_JOURNAL => 'primary',
                                AdminTask::TYPE_RENEWAL_EVALUATION => 'info',
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

                        // Sprint 3.7: recurso relacionado con doble acceso:
                        //   • "Editar (admin)" → Filament resource (interno)
                        //   • "Ver ficha pública" → vista pública /journal/{slug} o /book/{slug}
                        // Para User payable (support-credit, new-journal-consulting)
                        // solo aparece el botón admin (no hay ficha pública de usuario).
                        TextEntry::make('related_id')
                            ->label(__('Recurso'))
                            ->html()
                            ->getStateUsing(function (AdminTask $record): string {
                                if (! $record->related) {
                                    return '<span class="text-sm italic text-gray-400">—</span>';
                                }

                                $name = e(\App\Support\PaymentPayableResolver::payableDisplayName($record->related));

                                $adminUrl = match (true) {
                                    $record->related_type === 'App\\Models\\Journal' => JournalResource::getUrl('edit', ['record' => $record->related_id]),
                                    $record->related_type === 'App\\Models\\Book' => BookResource::getUrl('edit', ['record' => $record->related_id]),
                                    $record->related_type === 'App\\Models\\User' => \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $record->related_id]),
                                    default => null,
                                };

                                $publicUrl = null;
                                $resourceTypeLabel = __('recurso');
                                if ($record->related_type === 'App\\Models\\Journal' && filled($record->related->slug ?? null)) {
                                    $publicUrl = route('journal.show', ['slug' => $record->related->slug]);
                                    $resourceTypeLabel = __('revista');
                                } elseif ($record->related_type === 'App\\Models\\Book' && filled($record->related->slug ?? null)) {
                                    $publicUrl = route('book.show', ['slug' => $record->related->slug]);
                                    $resourceTypeLabel = __('libro');
                                } elseif ($record->related_type === 'App\\Models\\User') {
                                    $resourceTypeLabel = __('editor');
                                }

                                $editLabel = e(__('Editar (admin)'));
                                $publicLabel = e(__('Ver ficha pública'));
                                $typeLabel = e(__(':type', ['type' => ucfirst($resourceTypeLabel)]));

                                $html = '<div class="space-y-2">';
                                $html .= '<div class="flex items-baseline gap-2"><span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">'.$typeLabel.'</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100">'.$name.'</span></div>';
                                $html .= '<div class="flex flex-wrap items-center gap-2">';

                                // Roadmap #35 — "Editar (admin)" abre el form de
                                // edición del journal (desde donde se puede tocar la
                                // evaluación). Solo super_admin; el evaluador se queda
                                // con "Ver ficha pública".
                                $canEditAdmin = auth()->user()?->hasRole('super_admin') ?? false;

                                if ($adminUrl && $canEditAdmin) {
                                    $editIcon = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
                                    $html .= '<a href="'.e($adminUrl).'" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-800 dark:hover:bg-blue-900/60">'.$editIcon.$editLabel.'</a>';
                                }

                                if ($publicUrl) {
                                    $eyeIcon = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
                                    $html .= '<a href="'.e($publicUrl).'" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200 transition hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-800 dark:hover:bg-emerald-900/60">'.$eyeIcon.$publicLabel.'</a>';
                                }

                                $html .= '</div></div>';
                                return $html;
                            }),

                        TextEntry::make('title_key')
                            ->label(__('Tarea'))
                            ->formatStateUsing(fn (AdminTask $record): string => $record->renderedTitle())
                            ->columnSpanFull()
                            ->weight('semibold'),

                        // Sprint 3.7: badge "Cortesía" para tareas sin pago.
                        // Solo visible en admin (este infolist) — el editor no
                        // tiene acceso a /admin/admin-tasks.
                        TextEntry::make('is_complimentary')
                            ->label('')
                            // Roadmap #35 — cortesía/pago es info comercial del super_admin.
                            ->visible(fn (AdminTask $record): bool => $record->isComplimentary()
                                && (auth()->user()?->hasRole('super_admin') ?? false)
                            )
                            ->getStateUsing(fn (): string => __('Cortesía — sin pago'))
                            ->badge()
                            ->color('emerald')
                            ->icon('heroicon-o-gift')
                            ->columnSpanFull(),
                    ]),

                // ── 2. Estado, prioridad y asignación (unificado) ─────────────
                Section::make(__('Estado, prioridad y asignación'))
                    ->icon('heroicon-o-signal')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('Estado'))
                            ->badge()
                            ->size(\Filament\Support\Enums\TextSize::Medium)
                            ->color(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_AWAITING_PAYMENT => 'warning',
                                AdminTask::STATUS_PENDING => 'gray',
                                AdminTask::STATUS_IN_PROGRESS => 'info',
                                AdminTask::STATUS_PROPOSAL_SENT => 'primary',
                                AdminTask::STATUS_SCHEDULED => 'primary',
                                AdminTask::STATUS_IN_SESSION => 'warning',
                                AdminTask::STATUS_COMPLETED => 'success',
                                AdminTask::STATUS_CANCELLED => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                AdminTask::STATUS_AWAITING_PAYMENT => __('Pago pendiente'),
                                AdminTask::STATUS_PENDING => __('Pendiente'),
                                AdminTask::STATUS_IN_PROGRESS => __('En progreso'),
                                AdminTask::STATUS_PROPOSAL_SENT => __('Propuesta enviada'),
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

                        TextEntry::make('assignee.name')
                            ->label(__('Asignado a'))
                            ->icon('heroicon-o-user-circle')
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

                // ── 3. Notas internas ─────────────────────────────────────────
                Section::make(__('Notas internas'))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (AdminTask $record): bool => filled($record->notes))
                    ->schema([
                        TextEntry::make('notes')
                            ->hiddenLabel()
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // ── 4. Detalles de consultoría (solo type=consulting) ─────────
                Section::make(__('Detalles de consultoría'))
                    ->icon('heroicon-o-calendar')
                    ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING)
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextEntry::make('scheduled_for')
                            ->label(__('Sesión agendada para'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder(__('Sin agendar')),

                        TextEntry::make('consulting_meet_url')
                            ->label(__('URL de la reunión'))
                            ->placeholder(__('Sin URL'))
                            ->url(fn (AdminTask $record): ?string => $record->consulting_meet_url)
                            ->openUrlInNewTab(),

                        TextEntry::make('proposal_count_sent')
                            ->label(__('Rondas de propuesta enviadas'))
                            ->placeholder('0')
                            ->formatStateUsing(fn ($state): string => (string) ($state ?? 0)),

                        TextEntry::make('consulting_status_label')
                            ->label(__('Etapa actual'))
                            ->badge()
                            ->color(fn (AdminTask $record): string => match ($record->status) {
                                AdminTask::STATUS_PENDING => 'gray',
                                AdminTask::STATUS_PROPOSAL_SENT => 'primary',
                                AdminTask::STATUS_SCHEDULED => 'primary',
                                AdminTask::STATUS_IN_SESSION => 'warning',
                                AdminTask::STATUS_COMPLETED => 'success',
                                AdminTask::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            })
                            ->getStateUsing(fn (AdminTask $record): string => match ($record->status) {
                                AdminTask::STATUS_PENDING => __('Sin agendar'),
                                AdminTask::STATUS_IN_PROGRESS => __('En preparación'),
                                AdminTask::STATUS_PROPOSAL_SENT => __('Propuesta enviada — esperando respuesta del editor'),
                                AdminTask::STATUS_SCHEDULED => __('Agendada — esperando sesión'),
                                AdminTask::STATUS_IN_SESSION => __('Sesión en curso'),
                                AdminTask::STATUS_COMPLETED => __('Sesión completada'),
                                AdminTask::STATUS_CANCELLED => __('Cancelada'),
                                default => $record->status,
                            }),

                        TextEntry::make('client_visible_notes')
                            ->label(__('Resumen visible al cliente'))
                            ->placeholder(__('Sin resumen'))
                            ->columnSpanFull()
                            ->visible(fn (AdminTask $record): bool => filled($record->client_visible_notes)),
                    ]),

                // ── 5. Propuestas de fecha (solo type=consulting) ─────────────
                Section::make(__('Propuestas de fecha'))
                    ->icon('heroicon-o-clock')
                    ->visible(fn (AdminTask $record): bool => $record->type === AdminTask::TYPE_CONSULTING
                        && $record->proposals()->exists()
                    )
                    ->schema([
                        TextEntry::make('proposals_summary')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->getStateUsing(fn (AdminTask $record): string => '')
                            ->formatStateUsing(function (AdminTask $record): string {
                                $proposals = $record->proposals()->with(['proposedBy', 'acceptedBy'])->get();

                                if ($proposals->isEmpty()) {
                                    return __('Sin propuestas registradas.');
                                }

                                $lines = $proposals->map(function (ConsultingProposal $p): string {
                                    $slot = $p->proposed_slot
                                        ? $p->proposed_slot->format('d/m/Y H:i').' UTC'
                                        : '—';

                                    $statusLabel = match ($p->status) {
                                        ConsultingProposal::STATUS_ACTIVE => __('Activa'),
                                        ConsultingProposal::STATUS_ACCEPTED => __('Aceptada'),
                                        ConsultingProposal::STATUS_REJECTED => __('Rechazada'),
                                        ConsultingProposal::STATUS_EXPIRED => __('Expirada'),
                                        ConsultingProposal::STATUS_SUPERSEDED => __('Superada'),
                                        default => $p->status,
                                    };

                                    $proposer = $p->proposedBy?->name ?? '—';
                                    $notes = filled($p->notes) ? ' · '.$p->notes : '';
                                    $accepted = '';

                                    if ($p->status === ConsultingProposal::STATUS_ACCEPTED && $p->acceptedBy) {
                                        $accepted = ' · '.__('Aceptada por: ').$p->acceptedBy->name;
                                        if ($p->accepted_at) {
                                            $accepted .= ' ('.$p->accepted_at->format('d/m/Y H:i').')';
                                        }
                                    }

                                    $expires = '';
                                    if ($p->status === ConsultingProposal::STATUS_ACTIVE && $p->expires_at) {
                                        $expires = ' · '.__('Expira: ').$p->expires_at->format('d/m/Y');
                                    }

                                    return "**{$slot}** [{$statusLabel}] · ".__('Propuesta por: ').$proposer.$notes.$accepted.$expires;
                                })->implode("\n\n");

                                return $lines;
                            })
                            ->markdown(),
                    ]),
            ]);
    }
}
