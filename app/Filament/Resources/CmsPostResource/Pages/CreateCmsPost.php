<?php

namespace App\Filament\Resources\CmsPostResource\Pages;

use App\Filament\Resources\CmsPostResource;
use App\Models\CmsPost;
use Filament\Resources\Pages\CreateRecord;

class CreateCmsPost extends CreateRecord
{
    protected static string $resource = CmsPostResource::class;

    /**
     * Inyecta el user_id del autor y limpia traducciones vacías antes de crear.
     * Los campos traducibles llegan como arrays asociativos (es/en/pt) gracias
     * al dot-path de Filament.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $translatable = (new CmsPost())->translatable ?? [];

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
