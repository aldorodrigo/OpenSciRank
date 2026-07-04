<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    // Resource de solo lectura: sin CreateAction en el header.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
