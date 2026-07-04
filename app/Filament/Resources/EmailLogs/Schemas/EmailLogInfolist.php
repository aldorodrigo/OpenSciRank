<?php

namespace App\Filament\Resources\EmailLogs\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\EmailLog;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.email_log.section_details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('recipient_email')
                            ->label(__('admin.email_log.recipient')),

                        TextEntry::make('recipient_name')
                            ->label(__('admin.email_log.recipient_name'))
                            ->placeholder('-'),

                        TextEntry::make('subject')
                            ->label(__('admin.email_log.subject'))
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notification_class')
                            ->label(__('admin.email_log.notification_class'))
                            ->formatStateUsing(fn (?string $state): string => $state !== null ? class_basename($state) : '-')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('mailer')
                            ->label(__('admin.email_log.mailer')),

                        TextEntry::make('status')
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
                            }),

                        // Vínculo al notifiable cuando es un usuario del sistema.
                        TextEntry::make('notifiable_id')
                            ->label(__('admin.email_log.notifiable'))
                            ->formatStateUsing(function (EmailLog $record): string {
                                if ($record->notifiable_type === User::class && $record->notifiable !== null) {
                                    return $record->notifiable->name;
                                }

                                return $record->notifiable_type !== null
                                    ? class_basename($record->notifiable_type).' #'.$record->notifiable_id
                                    : '-';
                            })
                            ->url(fn (EmailLog $record): ?string => $record->notifiable_type === User::class && $record->notifiable !== null
                                ? UserResource::getUrl('edit', ['record' => $record->notifiable_id])
                                : null
                            )
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                    ]),

                Section::make(__('admin.email_log.section_delivery'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('correlation_uuid')
                            ->label(__('admin.email_log.correlation_uuid'))
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('ses_message_id')
                            ->label(__('admin.email_log.ses_message_id'))
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label(__('admin.email_log.created_at'))
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),

                        TextEntry::make('sending_at')
                            ->label(__('admin.email_log.sending_at'))
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),

                        TextEntry::make('sent_at')
                            ->label(__('admin.email_log.sent_at'))
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),

                        TextEntry::make('failed_at')
                            ->label(__('admin.email_log.failed_at'))
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),
                    ]),

                Section::make(__('admin.email_log.section_error'))
                    ->visible(fn (EmailLog $record): bool => filled($record->error_message))
                    ->schema([
                        TextEntry::make('error_message')
                            ->label(__('admin.email_log.error_message'))
                            ->color('danger')
                            ->columnSpanFull(),
                    ]),

                // meta suele traer contexto técnico adicional (headers, intentos, etc.)
                Section::make(__('admin.email_log.section_meta'))
                    ->visible(fn (EmailLog $record): bool => filled($record->meta))
                    ->collapsed()
                    ->schema([
                        TextEntry::make('meta')
                            ->label(__('admin.email_log.meta'))
                            ->formatStateUsing(fn (?array $state): string => $state !== null
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : '-'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
