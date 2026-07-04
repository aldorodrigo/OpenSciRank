<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Models\EmailLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.email_log.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('recipient_email')
                    ->label(__('admin.email_log.recipient'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (EmailLog $record): ?string => $record->recipient_name),

                TextColumn::make('subject')
                    ->label(__('admin.email_log.subject'))
                    ->limit(50)
                    ->tooltip(fn (EmailLog $record): ?string => $record->subject)
                    ->placeholder('-'),

                TextColumn::make('notification_class')
                    ->label(__('admin.email_log.notification_class'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? class_basename($state)
                        : '-'
                    )
                    ->searchable(),

                TextColumn::make('mailer')
                    ->label(__('admin.email_log.mailer'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('admin.email_log.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EmailLog::STATUS_SENT => 'success',
                        EmailLog::STATUS_SENDING => 'info',
                        EmailLog::STATUS_QUEUED => 'gray',
                        EmailLog::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        EmailLog::STATUS_SENT => __('admin.email_log_status.sent'),
                        EmailLog::STATUS_SENDING => __('admin.email_log_status.sending'),
                        EmailLog::STATUS_QUEUED => __('admin.email_log_status.queued'),
                        EmailLog::STATUS_FAILED => __('admin.email_log_status.failed'),
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('ses_message_id')
                    ->label(__('admin.email_log.ses_message_id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.email_log.status'))
                    ->options([
                        EmailLog::STATUS_QUEUED => __('admin.email_log_status.queued'),
                        EmailLog::STATUS_SENDING => __('admin.email_log_status.sending'),
                        EmailLog::STATUS_SENT => __('admin.email_log_status.sent'),
                        EmailLog::STATUS_FAILED => __('admin.email_log_status.failed'),
                    ]),

                // Rango de fechas sobre created_at (mismo patrón que
                // el filtro payment_date de PaymentsTable).
                Filter::make('created_range')
                    ->label(__('Rango de fechas'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('Desde'))
                            ->native(false),
                        DatePicker::make('to')
                            ->label(__('Hasta'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['from'])) {
                            $indicators[] = __('Desde: :date', ['date' => \Carbon\Carbon::parse($data['from'])->format('d/m/Y')]);
                        }
                        if (! empty($data['to'])) {
                            $indicators[] = __('Hasta: :date', ['date' => \Carbon\Carbon::parse($data['to'])->format('d/m/Y')]);
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
