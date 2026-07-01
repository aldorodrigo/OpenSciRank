<?php

namespace App\Filament\Resources\JournalResource\Pages;

use App\Filament\Resources\JournalResource;
use Filament\Resources\Pages\EditRecord;

class EditJournal extends EditRecord
{
    protected static string $resource = JournalResource::class;

    /**
     * Roadmap #35 — el evaluator conserva Update:Journal (lo exige el guardado
     * de la evaluación), lo que técnicamente le abriría el form de edición admin
     * completo (link "Relacionado a" de la Tarea apunta a 'edit'). Lo mandamos a
     * su página de evaluación, la única superficie de Journal que le corresponde.
     */
    public function mount(int | string $record): void
    {
        parent::mount($record);

        $user = auth()->user();

        if ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin')) {
            $this->redirect(JournalResource::getUrl('evaluate', ['record' => $this->record]));
        }
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
     * Limpia traducciones vacías antes de guardar; spatie persiste el array
     * tal cual, por lo que evitamos almacenar locales sin contenido.
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
