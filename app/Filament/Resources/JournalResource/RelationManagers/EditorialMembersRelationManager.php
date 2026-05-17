<?php

namespace App\Filament\Resources\JournalResource\RelationManagers;

use App\Models\JournalEditorialMember;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EditorialMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'editorialMembers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.editorial_members.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.editorial_members.label');
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('admin.editorial_members.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('role')
                ->label(__('admin.editorial_members.role'))
                ->options(collect(JournalEditorialMember::ROLES)
                    ->mapWithKeys(fn (string $r) => [$r => __('admin.editorial_members.role_'.$r)])
                    ->toArray())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('orcid')
                ->label('ORCID')
                ->placeholder('0000-0002-1825-0097')
                ->helperText(__('admin.editorial_members.orcid_help'))
                ->regex(JournalEditorialMember::ORCID_REGEX)
                ->maxLength(19),
            Forms\Components\TextInput::make('affiliation')
                ->label(__('admin.editorial_members.affiliation'))
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->label(__('admin.editorial_members.email'))
                ->email()
                ->maxLength(255),
            Forms\Components\TextInput::make('display_order')
                ->label(__('admin.editorial_members.display_order'))
                ->numeric()
                ->default(0)
                ->minValue(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_order')
                    ->label('#')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.editorial_members.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('admin.editorial_members.role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('admin.editorial_members.role_'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        JournalEditorialMember::ROLE_EDITOR_IN_CHIEF => 'primary',
                        JournalEditorialMember::ROLE_ASSOCIATE => 'info',
                        JournalEditorialMember::ROLE_BOARD => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('orcid')
                    ->label('ORCID')
                    ->url(fn (JournalEditorialMember $record): ?string => $record->orcidUrl(), shouldOpenInNewTab: true)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('affiliation')
                    ->label(__('admin.editorial_members.affiliation'))
                    ->placeholder('—')
                    ->limit(40),
            ])
            ->defaultSort('display_order', 'asc')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
