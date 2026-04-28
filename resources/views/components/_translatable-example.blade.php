{{--
    Ejemplo de uso de los componentes <x-translatable-input> y <x-translatable-textarea>.

    Este archivo NO se renderiza en runtime — sirve solo como referencia
    para los wizards Livewire (SubmissionWizard, BookSubmissionWizard).

    Pre-requisitos en el componente Livewire:

        public string $primary_locale = 'es';
        public array $title       = ['es' => '', 'en' => '', 'pt' => ''];
        public array $description = ['es' => '', 'en' => '', 'pt' => ''];

    Reglas de validación (solo el locale primario es required):

        protected function rules(): array
        {
            return [
                "title.{$this->primary_locale}"       => 'required|string|max:255',
                "description.{$this->primary_locale}" => 'required|string|min:50',
            ];
        }

    Uso en blade:

    <x-translatable-input
        name="title"
        label="Título de la revista"
        model="title"
        :primary="$primary_locale"
        required
        help="Nombre oficial de la revista" />

    <x-translatable-textarea
        name="description"
        label="Descripción / Alcance"
        model="description"
        :primary="$primary_locale"
        :rows="6"
        required />

    Props soportadas:

        translatable-input:
          name (string, req)         — id/name html base
          label (string, req)        — etiqueta visible
          model (string, req)        — nombre Livewire base; se bindea a model.es / model.en / model.pt
          locales (array)            — default ['es','en','pt']
          primary (string)           — locale primario; default 'es'
          required (bool)            — default false; aplica required HTML solo al input primario
          type (string)              — text | email | url; default 'text'
          placeholder (array|string) — string global o array por locale
          help (string|null)         — texto de ayuda bajo el input
          maxlength (int|null)

        translatable-textarea: idem + rows (int, default 4).

    Notas técnicas:

    - Los inputs de TODOS los locales están siempre presentes en el DOM
      (ocultos con x-show + x-cloak), para que wire:model siga sincronizando
      el estado Livewire aun cuando una pestaña no esté activa.

    - El check verde aparece automáticamente en pestañas con valor no vacío,
      consultado vía $wire.get() (reactivo).

    - El texto del badge "Principal" y la línea de ayuda están traducidos
      con __() para respetar el locale de la UI.

    - Solo se muestra @error() bajo el input del locale primario; los
      campos secundarios no se validan (son opcionales).
--}}
