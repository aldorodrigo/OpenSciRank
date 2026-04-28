<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translatable = $this->record->translatable ?? [];

        foreach ($translatable as $field) {
            $data[$field] = $this->record->getTranslations($field);
        }

        return $data;
    }

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
