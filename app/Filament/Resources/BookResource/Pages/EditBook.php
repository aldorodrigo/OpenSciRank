<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    /**
     * Hidrata los campos traducibles como arrays para que los Tabs por
     * locale (campo.es / campo.en / campo.pt) reciban el valor correcto.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translatable = $this->record->translatable ?? [];

        foreach ($translatable as $field) {
            $data[$field] = $this->record->getTranslations($field);
        }

        return $data;
    }

    /**
     * Limpia traducciones vacías antes de guardar.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $translatable = $this->record->translatable ?? [];

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
