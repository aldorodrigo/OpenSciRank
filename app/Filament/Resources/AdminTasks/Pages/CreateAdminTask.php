<?php

namespace App\Filament\Resources\AdminTasks\Pages;

use App\Filament\Resources\AdminTasks\AdminTaskResource;
use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Journal;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Sprint 4 #34 — crear admin_task manualmente desde Filament.
 *
 * Casos de uso: tareas ad-hoc no atadas a pago, recordatorios internos,
 * revisión de un editor problemático, follow-ups administrativos.
 *
 * Restringido a super_admin (gate por policy de AdminTaskResource::canCreate).
 * Bloquea tipos que sólo nacen automático: orphan_payment.
 */
class CreateAdminTask extends CreateRecord
{
    protected static string $resource = AdminTaskResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Información de la tarea'))
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label(__('Tipo'))
                            ->options([
                                AdminTask::TYPE_SUPPORT => __('Soporte (genérico)'),
                                AdminTask::TYPE_EVALUATE_JOURNAL => __('Evaluación de revista (cortesía)'),
                                AdminTask::TYPE_REEVALUATE_JOURNAL => __('Re-evaluación de revista (cortesía)'),
                                AdminTask::TYPE_RENEWAL_EVALUATION => __('Renovación de sello (cortesía)'),
                                AdminTask::TYPE_REVIEW_LISTING_JOURNAL => __('Listado de revista'),
                                AdminTask::TYPE_REVIEW_LISTING_BOOK => __('Listado de libro'),
                                AdminTask::TYPE_CONSULTING => __('Consultoría'),
                            ])
                            ->required()
                            ->default(AdminTask::TYPE_SUPPORT)
                            ->live(),

                        TextInput::make('title')
                            ->label(__('Título'))
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->helperText(__('Texto visible en la cola de tareas.')),
                    ]),

                Section::make(__('Recurso asociado (opcional)'))
                    ->description(__('Vincular la tarea a una revista, libro o usuario. Para tipos de evaluación/listing es obligatorio.'))
                    ->columns(2)
                    ->schema([
                        Select::make('related_type')
                            ->label(__('Tipo de recurso'))
                            ->options([
                                '' => __('Sin recurso (general)'),
                                Journal::class => __('Revista'),
                                Book::class => __('Libro'),
                                User::class => __('Usuario'),
                            ])
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('related_id', null)),

                        Select::make('related_id')
                            ->label(__('Recurso'))
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search, $get) {
                                return match ($get('related_type')) {
                                    Journal::class => Journal::query()
                                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.*')) LIKE ?", ["%{$search}%"])
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($j) => [$j->id => $j->getTranslationWithFallback('title').' (#'.$j->id.')'])
                                        ->all(),
                                    Book::class => Book::query()
                                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.*')) LIKE ?", ["%{$search}%"])
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($b) => [$b->id => $b->getTranslationWithFallback('title').' (#'.$b->id.')'])
                                        ->all(),
                                    User::class => User::query()
                                        ->where(fn ($q) => $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->email.')'])
                                        ->all(),
                                    default => [],
                                };
                            })
                            ->getOptionLabelUsing(fn ($value, $get) => match ($get('related_type')) {
                                Journal::class => optional(Journal::find($value))->getTranslationWithFallback('title'),
                                Book::class => optional(Book::find($value))->getTranslationWithFallback('title'),
                                User::class => optional(User::find($value))?->name,
                                default => null,
                            })
                            ->visible(fn ($get) => filled($get('related_type'))),
                    ]),

                Section::make(__('Asignación y prioridad'))
                    ->columns(3)
                    ->schema([
                        Select::make('assigned_to')
                            ->label(__('Asignar a'))
                            ->options(fn () => User::role('super_admin')->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => auth()->id())
                            ->helperText(__('Si no se asigna, queda en la cola general.')),

                        Select::make('priority')
                            ->label(__('Prioridad'))
                            ->options([
                                AdminTask::PRIORITY_LOW => __('Baja'),
                                AdminTask::PRIORITY_NORMAL => __('Normal'),
                                AdminTask::PRIORITY_HIGH => __('Alta'),
                            ])
                            ->required()
                            ->default(AdminTask::PRIORITY_NORMAL),

                        DateTimePicker::make('due_at')
                            ->label(__('Vencimiento'))
                            ->native(false)
                            ->minDate(now())
                            ->default(fn ($get) => AdminTask::calculateDueAt(
                                $get('type') ?: AdminTask::TYPE_SUPPORT
                            )?->toDateTimeString()),
                    ]),

                Section::make(__('Notas internas'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('')
                            ->rows(4)
                            ->maxLength(5000)
                            ->placeholder(__('Contexto, motivo, info adicional para quien tome la tarea…')),
                    ]),
            ]);
    }

    /**
     * Override del save para mapear los campos del form a la estructura real
     * del modelo (title → title_key + title_params, related_type/id pueden ser null).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convertimos el título a title_key + title_params usando key 'tasks.support'
        // como fallback genérico para tareas manuales (preview = el título completo).
        $title = $data['title'] ?? '—';

        // Limpiar related_type/id vacíos
        if (empty($data['related_type'])) {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        $data['title_key'] = 'tasks.support'; // genérico — el título real va en title_params
        $data['title_params'] = ['preview' => $title];
        $data['status'] = AdminTask::STATUS_PENDING;

        unset($data['title']); // no es columna

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
