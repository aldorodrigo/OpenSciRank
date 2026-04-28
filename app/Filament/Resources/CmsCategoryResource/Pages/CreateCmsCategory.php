<?php

namespace App\Filament\Resources\CmsCategoryResource\Pages;

use App\Filament\Resources\CmsCategoryResource;
use App\Models\CmsCategory;
use Filament\Resources\Pages\CreateRecord;

class CreateCmsCategory extends CreateRecord
{
    protected static string $resource = CmsCategoryResource::class;

    /**
     * Limpia traducciones vacías antes de crear.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $translatable = (new CmsCategory())->translatable ?? [];

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
