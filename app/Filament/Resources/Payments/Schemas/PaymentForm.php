<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Group::make([
                    \Filament\Forms\Components\Section::make(__('admin.payment.section_details'))
                        ->schema([
                            TextInput::make('transaction_id')
                                ->label(__('admin.payment.transaction_id')),
                            TextInput::make('provider')
                                ->label(__('admin.payment.provider')),
                            TextInput::make('amount')
                                ->label(__('admin.payment.amount'))
                                ->prefix('$'),
                            TextInput::make('currency')
                                ->label(__('admin.payment.currency')),
                            TextInput::make('status')
                                ->label(__('admin.payment.status')),
                        ])->columns(2),

                    \Filament\Forms\Components\Section::make(__('admin.payment.section_relations'))
                        ->schema([
                            \Filament\Forms\Components\Select::make('user_id')
                                ->label(__('admin.payment.user'))
                                ->relationship('user', 'name'),
                            \Filament\Forms\Components\Select::make('product_id')
                                ->label(__('admin.payment.product'))
                                ->relationship('product', 'name'),
                            TextInput::make('payable_type')
                                ->label(__('admin.payment.payable_type')),
                            TextInput::make('payable_id')
                                ->label(__('admin.payment.payable_id')),
                        ])->columns(2),

                    \Filament\Forms\Components\Section::make(__('admin.payment.section_metadata'))
                        ->schema([
                            \Filament\Forms\Components\KeyValue::make('metadata')
                                ->label(__('admin.payment.metadata')),
                        ]),
                ])->disabled(),
            ]);
    }
}
