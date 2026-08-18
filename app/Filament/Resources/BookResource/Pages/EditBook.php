<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Actions\BookCourtesyActions;
use App\Filament\Actions\BookListingActions;
use App\Filament\Resources\BookResource;
use App\Models\Book;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    /**
     * Acciones sobre la ficha abierta, que es donde aterriza el admin cuando
     * arranca la tarea de revisión (`AdminTask::workUrl()`).
     *
     * Issue #75: la resolución del listado se hace desde acá, después de mirar
     * los datos del libro, sin tocar el select de "Estado Interno".
     * "Publicar de cortesía" sigue disponible para el caso previo: el libro
     * todavía en borrador al que se le exonera el cobro.
     */
    protected function getHeaderActions(): array
    {
        return [
            BookListingActions::preview(fn (): ?Book => $this->getRecord()),
            BookListingActions::approve(fn (): ?Book => $this->getRecord()),
            BookListingActions::requestChanges(fn (): ?Book => $this->getRecord()),
            BookListingActions::reject(fn (): ?Book => $this->getRecord()),
            BookCourtesyActions::listing(fn (): ?Book => $this->getRecord()),
        ];
    }

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
