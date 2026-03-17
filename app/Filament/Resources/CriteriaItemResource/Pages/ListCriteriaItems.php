<?php

namespace App\Filament\Resources\CriteriaItemResource\Pages;

use App\Filament\Resources\CriteriaItemResource;
use Filament\Resources\Pages\ListRecords;

class ListCriteriaItems extends ListRecords
{
    protected static string $resource = CriteriaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
