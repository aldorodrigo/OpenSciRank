<?php

namespace App\Filament\Resources\CriteriaItemResource\Pages;

use App\Filament\Resources\CriteriaItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCriteriaItem extends EditRecord
{
    protected static string $resource = CriteriaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
