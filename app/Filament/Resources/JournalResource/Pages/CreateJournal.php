<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use App\Models\Journal;
use Filament\Resources\Pages\CreateRecord;

class CreateJournal extends CreateRecord
{
    protected static string $resource = JournalResource::class;

    /**
     * Limpia traducciones vacías antes de crear el registro. Los campos
     * traducibles llegan como arrays asociativos (es/en/pt) gracias al
     * dot-path de Filament.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $translatable = (new Journal())->translatable ?? [];

        foreach ($translatable as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter(
                    $data[$field],
                    fn ($value) => filled($value)
                );
            }
        }

        return $data;
    }
}
