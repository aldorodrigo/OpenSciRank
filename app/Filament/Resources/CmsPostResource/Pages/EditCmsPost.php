<?php

namespace App\Filament\Resources\CmsPostResource\Pages;

use App\Filament\Resources\CmsPostResource;
use Filament\Resources\Pages\EditRecord;

class EditCmsPost extends EditRecord
{
    protected static string $resource = CmsPostResource::class;

    /**
     * Hidrata los campos traducibles como arrays para los Tabs por locale
     * (campo.es / campo.en / campo.pt).
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
