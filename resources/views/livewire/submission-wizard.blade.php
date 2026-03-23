<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-4xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-indigo-600">Mi Panel</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">{{ $journal ? 'Editar Revista' : 'Nueva Revista' }}</span>
        </nav>

        {{-- Progress Steps --}}
        <div class="mb-8 overflow-x-auto">
            <div class="flex items-center justify-between min-w-max">
                @php
                    $stepNames = ['Acerca de', 'Acceso Abierto', 'Licencias', 'Editorial', 'Modelo', 'Prácticas', 'Confirmar'];
                @endphp
                @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center">
                        <button wire:click="goToStep({{ $i }})" 
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $currentStep >= $i ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }} font-semibold transition {{ $i <= $currentStep ? 'cursor-pointer hover:ring-2 hover:ring-indigo-300' : 'cursor-not-allowed' }}">
                            @if($currentStep > $i)
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            @else
                                {{ $i }}
                            @endif
                        </button>
                        @if($i < $totalSteps)
                            <div class="mx-2 h-1 w-8 {{ $currentStep > $i ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }} rounded-full"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="mt-3 flex justify-between min-w-max">
                @foreach($stepNames as $index => $name)
                    <span class="w-10 text-center text-xs {{ $currentStep >= $index + 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500' }}">{{ $name }}</span>
                    @if($index < count($stepNames) - 1)
                        <span class="w-8"></span>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Form Card --}}
        <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
            {{-- Step 1: About --}}
            @if($currentStep === 1)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">1️⃣ Acerca de la Revista</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Identidad y alcance editorial</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Título oficial * <x-field-tooltip text="Nombre completo y oficial de la revista tal como aparece en su portada." /></label>
                        <input type="text" wire:model="title" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre abreviado <x-field-tooltip text="Abreviatura estándar de la revista según ISO 4, si existe." /></label>
                        <input type="text" wire:model="abbreviated_name" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción / Alcance * <span class="text-xs text-gray-400">(mín. 50 caracteres)</span> <x-field-tooltip text="Describe el enfoque temático, la misión y el alcance editorial de la revista." /></label>
                        <textarea wire:model="description" rows="4" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Área(s) temática(s) * <x-field-tooltip text="Selecciona las disciplinas principales que cubre la revista." /></label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($subjectAreaOptions as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="subject_areas" value="{{ $key }}" class="h-4 w-4 rounded text-indigo-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('subject_areas') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Público objetivo <x-field-tooltip text="¿A quién está dirigida principalmente la revista?" /></label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($audienceOptions as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="target_audience" value="{{ $key }}" class="h-4 w-4 rounded text-indigo-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Idioma(s) de publicación * <x-field-tooltip text="Idiomas en los que se aceptan y publican artículos." /></label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($languageOptions as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="publication_languages" value="{{ $key }}" class="h-4 w-4 rounded text-indigo-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('publication_languages') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Año de inicio <x-field-tooltip text="Año en que la revista comenzó a publicarse." /></label>
                            <input type="number" wire:model="start_year" min="1900" max="{{ date('Y') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sitio web oficial * <x-field-tooltip text="Dirección web principal de la revista." /></label>
                            <input type="url" wire:model="url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Logo upload --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo de la Revista <x-field-tooltip text="Sube el logo oficial de la revista. Formatos aceptados: JPG, PNG, SVG, WEBP. Máximo 2 MB." /></label>
                        @if($existing_logo && !$logo)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ Storage::url($existing_logo) }}" alt="Logo actual" class="max-h-32 w-auto rounded-lg object-contain shadow">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Logo actual</span>
                            </div>
                        @endif
                        @if($logo)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="max-h-32 w-auto rounded-lg object-contain shadow">
                                <span class="text-sm text-emerald-600">Nuevo logo seleccionado</span>
                            </div>
                        @endif
                        <label class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-6 transition hover:border-indigo-400 dark:border-gray-600 dark:hover:border-indigo-500">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Clic para seleccionar imagen</p>
                            </div>
                            <input type="file" wire:model="logo" accept="image/*" class="hidden">
                        </label>
                        @error('logo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 2: Open Access --}}
            @if($currentStep === 2)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">2️⃣ Cumplimiento del Acceso Abierto</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Verificar si la revista cumple con principios de Open Access</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿La revista es de acceso abierto? * <x-field-tooltip text="Indica si todos los artículos están disponibles gratuitamente para el público." /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="is_open_access" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="is_open_access" value="0" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('is_open_access') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de acceso * <x-field-tooltip text="Completo: todos los artículos son gratuitos. Híbrido: solo algunos. Restringido: requiere suscripción." /></label>
                        <select wire:model="access_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Seleccionar...</option>
                            @foreach($accessTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('access_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Los artículos son accesibles sin registro? * <x-field-tooltip text="¿Se pueden leer los artículos sin necesidad de crear una cuenta en el sitio?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="articles_accessible_without_registration" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="articles_accessible_without_registration" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('articles_accessible_without_registration') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Se permite el autoarchivo? <x-field-tooltip text="¿Los autores pueden depositar sus artículos en repositorios institucionales o personales?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_self_archiving" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_self_archiving" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Política de acceso abierto (URL) <x-field-tooltip text="Enlace a la página donde se describe la política de acceso abierto de la revista." /></label>
                        <input type="url" wire:model="open_access_policy_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Existe embargo? <x-field-tooltip text="¿Los artículos tienen un período de restricción antes de estar disponibles en acceso abierto?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="has_embargo" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="has_embargo" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    @if($has_embargo)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Duración del embargo (meses) <x-field-tooltip text="Número de meses que dura el periodo de restricción." /></label>
                        <input type="number" wire:model="embargo_months" min="1" max="60" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    @endif
                </div>
            @endif

            {{-- Step 3: Licenses --}}
            @if($currentStep === 3)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">3️⃣ Derechos de Autor y Licencias</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Transparencia legal y reutilización</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de licencia principal * <x-field-tooltip text="Licencia bajo la cual se publican los artículos (ej. Creative Commons)." /></label>
                        <select wire:model="license_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Seleccionar...</option>
                            @foreach($licenseTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('license_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL de la licencia <x-field-tooltip text="Enlace directo al texto completo de la licencia utilizada." /></label>
                        <input type="url" wire:model="license_url" placeholder="https://creativecommons.org/..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Los autores conservan derechos? * <x-field-tooltip text="¿Los autores mantienen el copyright de sus artículos tras la publicación?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="authors_retain_copyright" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="authors_retain_copyright" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('authors_retain_copyright') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Se permite reutilización comercial? <x-field-tooltip text="¿Se permite usar el contenido con fines comerciales?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_commercial_reuse" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_commercial_reuse" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Política de copyright <x-field-tooltip text="Texto o URL donde se describe la política de derechos de autor de la revista." /></label>
                        <textarea wire:model="copyright_policy" rows="3" placeholder="Texto o URL de la política..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Licencias visibles en los artículos? <x-field-tooltip text="¿Cada artículo muestra de forma clara la licencia bajo la que se publica?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="licenses_visible_in_articles" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="licenses_visible_in_articles" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 4: Editorial --}}
            @if($currentStep === 4)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">4️⃣ Editorial</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Gestión editorial de la revista</p>
                <div class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Institución editora * <x-field-tooltip text="Universidad, centro de investigación u organización que publica la revista." /></label>
                            <input type="text" wire:model="publishing_institution" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('publishing_institution') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">País de la editorial * <x-field-tooltip text="País donde tiene sede la institución editora." /></label>
                            <x-country-select wire="country_code" :value="$country_code" name="country_code" />
                            @error('country_code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Editor/a responsable * <x-field-tooltip text="Nombre del director/a o editor/a jefe de la revista." /></label>
                            <input type="text" wire:model="editor_name" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('editor_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email institucional * <span class="text-xs text-gray-400">(no Gmail/Yahoo)</span> <x-field-tooltip text="Correo electrónico oficial de la revista con dominio institucional." /></label>
                            <input type="email" wire:model="institutional_email" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('institutional_email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Comité editorial visible? * <x-field-tooltip text="¿El sitio web muestra los nombres y afiliaciones de los miembros del comité editorial?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="editorial_board_visible" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="editorial_board_visible" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('editorial_board_visible') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL del comité editorial <x-field-tooltip text="Enlace a la página donde se lista el comité editorial." /></label>
                        <input type="url" wire:model="editorial_board_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de revisión por pares * <x-field-tooltip text="Método de evaluación de los artículos por expertos antes de su publicación." /></label>
                            <select wire:model="peer_review_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($peerReviewTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('peer_review_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Periodicidad * <x-field-tooltip text="Frecuencia con la que se publican nuevos números o artículos." /></label>
                            <select wire:model="publication_frequency" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($frequencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('publication_frequency') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 5: Business Model --}}
            @if($currentStep === 5)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">5️⃣ Modelo de Negocio</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Barreras económicas y sostenibilidad</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿La revista cobra APC (Article Processing Charges)? * <x-field-tooltip text="¿Los autores deben pagar para que su artículo sea publicado?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="charges_apc" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="charges_apc" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('charges_apc') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($charges_apc)
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto del APC <x-field-tooltip text="Costo que deben pagar los autores por artículo publicado." /></label>
                            <input type="number" wire:model="apc_amount" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
                            <select wire:model="apc_currency" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Existen exenciones? <x-field-tooltip text="¿Se ofrecen descuentos o exenciones de APC para autores de países en desarrollo u otras condiciones?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_apc_waivers" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_apc_waivers" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fuente de financiamiento <x-field-tooltip text="¿Cómo se financia la publicación de la revista?" /></label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($fundingOptions as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="funding_sources" value="{{ $key }}" class="h-4 w-4 rounded text-indigo-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Publicidad en el sitio? <x-field-tooltip text="¿El sitio web de la revista muestra anuncios publicitarios?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_advertising" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_advertising" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Transparencia del modelo económico? <x-field-tooltip text="¿La revista publica información clara sobre cómo se financia y cuáles son sus costos?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="business_model_transparent" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="business_model_transparent" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 6: Best Practices --}}
            @if($currentStep === 6)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">6️⃣ Mejores Prácticas</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Calidad editorial y ética</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Política de ética editorial? * <x-field-tooltip text="¿La revista tiene publicada una política de ética para autores, editores y revisores?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_ethics_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_ethics_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('has_ethics_policy') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Adhiere a COPE? <x-field-tooltip text="¿La revista sigue las directrices del Committee on Publication Ethics (COPE)?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="adheres_to_cope" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="adheres_to_cope" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Política antiplagio? * <x-field-tooltip text="¿La revista verifica la originalidad de los artículos con herramientas antiplagio?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_antiplagiarism_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_antiplagiarism_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                        @error('has_antiplagiarism_policy') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Herramienta antiplagio <x-field-tooltip text="Software específico que se utiliza para verificar la originalidad." /></label>
                        <input type="text" wire:model="antiplagiarism_tool" placeholder="ej: Turnitin, iThenticate" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Declaración de conflicto de intereses? <x-field-tooltip text="¿Se exige a autores y revisores declarar posibles conflictos de interés?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_conflict_of_interest_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_conflict_of_interest_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Uso declarado de IA editorial? <x-field-tooltip text="¿La revista tiene política sobre el uso de inteligencia artificial en la producción de artículos?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="declares_ai_use" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="declares_ai_use" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿DOI asignado a los artículos? <x-field-tooltip text="¿Cada artículo tiene un identificador único DOI (Digital Object Identifier)?" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="assigns_doi" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">Sí</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="assigns_doi" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">No</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ISSN Impreso <x-field-tooltip text="Número de serie internacional para la versión impresa (formato: 1234-5678)." /></label>
                            <input type="text" wire:model="issn_print" placeholder="1234-5678" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ISSN Online <x-field-tooltip text="Número de serie internacional para la versión electrónica." /></label>
                            <input type="text" wire:model="issn_online" placeholder="1234-5679" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Editorial / Publisher <x-field-tooltip text="Nombre de la editorial o casa publicadora, si difiere de la institución editora." /></label>
                        <input type="text" wire:model="publisher" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Configuración OAI-PMH (Opcional)</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Si la revista cuenta con un servidor OAI-PMH, configúralo aquí para la cosecha automática de artículos.</p>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL Base OAI-PMH <x-field-tooltip text="URL base del servidor OAI-PMH (ej. https://revista.edu/oai/request)." /></label>
                        <input type="url" wire:model="oai_base_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('oai_base_url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Set Spec <x-field-tooltip text="Identificador del SET para cosechar solo una colección específica (opcional)." /></label>
                            <input type="text" wire:model="oai_set_spec" placeholder="ej: col_123456789_1" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prefijo de Metadatos <x-field-tooltip text="Formato de metadatos preferido para la cosecha." /></label>
                            <select wire:model="oai_metadata_prefix" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="oai_dc">Dublin Core (oai_dc)</option>
                                <option value="marcxml">MARCXML</option>
                                <option value="oai_datacite">DataCite</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 7: Confirmation --}}
            @if($currentStep === 7)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">✅ Confirmar Datos</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Revisa la información antes de continuar</p>
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-3 font-medium text-gray-900 dark:text-white">Información General</h3>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-gray-500">Título:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $title }}</dd></div>
                            <div><dt class="text-gray-500">URL:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $url }}</dd></div>
                            <div><dt class="text-gray-500">Acceso Abierto:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $is_open_access ? 'Sí' : 'No' }}</dd></div>
                            <div><dt class="text-gray-500">Licencia:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $licenseTypes[$license_type] ?? '—' }}</dd></div>
                        </dl>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-3 font-medium text-gray-900 dark:text-white">Editorial</h3>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-gray-500">Institución:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $publishing_institution }}</dd></div>
                            <div><dt class="text-gray-500">Editor:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $editor_name }}</dd></div>
                            <div><dt class="text-gray-500">Email:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $institutional_email }}</dd></div>
                            <div><dt class="text-gray-500">Revisión:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $peerReviewTypes[$peer_review_type] ?? '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="mt-8">
                        <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Elige cómo continuar</h3>
                        <div class="grid gap-6 md:grid-cols-2">
                            {{-- Option 1: Evaluate (Recommended) --}}
                            <div class="relative flex flex-col rounded-2xl border-2 border-indigo-500 bg-indigo-50/50 p-6 dark:border-indigo-400 dark:bg-indigo-900/20">
                                <div class="absolute -top-3 left-6 inline-flex rounded-full bg-indigo-500 px-3 py-1 text-xs font-bold text-white shadow-sm">
                                    Recomendado
                                </div>
                                <h4 class="mb-2 flex items-center gap-2 text-lg font-bold text-indigo-900 dark:text-indigo-300">
                                    <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
                                    Evaluar Revista
                                </h4>
                                <p class="mb-6 flex-1 text-sm leading-relaxed text-indigo-800/80 dark:text-indigo-200/80">
                                    Obtén el Sello de Calidad Editorial Standards Platform. Tu revista será evaluada en detalle según nuestros criterios metodológicos. Una calificación alta aumentará significativamente la visibilidad, prestigio y confianza en tus publicaciones, destacándote en la comunidad científica.
                                </p>
                                <button wire:click="submit" type="button" class="wizard-btn wizard-btn-success flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                                    Pagar y Evaluar
                                </button>
                            </div>
                            
                            {{-- Option 2: List --}}
                            <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                                <h4 class="mb-2 flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    Listar Revista (Gratis)
                                </h4>
                                <p class="mb-6 flex-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    Registra tu revista en nuestra base de datos de acceso público de forma gratuita. Tu revista aparecerá en los resultados de búsqueda tras ser aprobada por el equipo, pero no contará con el Sello de Calidad ni con una calificación detallada de nuestra plataforma.
                                </p>
                                <button wire:click="listJournal" type="button" class="wizard-btn flex w-full items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-6 py-3.5 font-bold text-slate-700 transition-all hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    Solicitar Listado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Navigation --}}
            <div class="mt-8 flex justify-between items-center">
                @if($currentStep > 1)
                    <button wire:click="previousStep" type="button" class="wizard-btn wizard-btn-outline inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        Anterior
                    </button>
                @else
                    <a href="{{ route('app.dashboard') }}" class="wizard-btn wizard-btn-outline inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Cancelar</a>
                @endif

                <button wire:click="saveAsDraft" type="button" class="wizard-btn wizard-btn-draft inline-flex items-center rounded-lg border border-amber-300 bg-amber-50 px-5 py-3 font-semibold text-amber-700 dark:border-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                    Guardar Borrador
                </button>

                @if($currentStep < $totalSteps)
                    <button wire:click="nextStep" type="button" class="wizard-btn wizard-btn-primary inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white">
                        Siguiente
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                @endif
            </div>

            <style>
                .wizard-btn {
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .wizard-btn:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                .wizard-btn:active {
                    transform: translateY(0);
                    box-shadow: none;
                }
                .wizard-btn-outline:hover {
                    background-color: #f3f4f6;
                }
                .wizard-btn-draft:hover {
                    background-color: #fef3c7;
                    border-color: #f59e0b;
                }
                .wizard-btn-primary:hover {
                    background-color: #4f46e5;
                }
                .wizard-btn-success:hover {
                    background-color: #059669;
                }
            </style>
        </div>
    </div>
</div>
