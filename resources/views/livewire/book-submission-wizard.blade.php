<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-4xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-purple-600">Mi Panel</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">{{ $book ? 'Editar Libro' : 'Nuevo Libro' }}</span>
        </nav>

        {{-- Progress Steps --}}
        <div class="mb-8 overflow-x-auto">
            <div class="flex items-center justify-between min-w-max">
                @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center">
                        <div wire:click="goToStep({{ $i }})"
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $currentStep >= $i ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }} font-semibold {{ $i <= $currentStep ? 'cursor-pointer' : '' }}">
                            @if($currentStep > $i)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        @if($i < $totalSteps)
                            <div class="mx-2 h-1 w-8 sm:w-12 {{ $currentStep > $i ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700' }} rounded-full"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="mt-4 flex justify-between text-xs min-w-max gap-2">
                <span class="w-16 text-center {{ $currentStep >= 1 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">General</span>
                <span class="w-16 text-center {{ $currentStep >= 2 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">Contenido</span>
                <span class="w-16 text-center {{ $currentStep >= 3 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">Acceso</span>
                <span class="w-16 text-center {{ $currentStep >= 4 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">Evaluación</span>
                <span class="w-16 text-center {{ $currentStep >= 5 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">Archivos</span>
                <span class="w-16 text-center {{ $currentStep >= 6 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">Confirmar</span>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
            
            {{-- ============================================ --}}
            {{-- Step 1: Información General (1 & 3) --}}
            {{-- ============================================ --}}
            @if($currentStep === 1)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Información General del Libro</h2>
                
                <div class="space-y-8">
                    {{-- Identificación Básica --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Identificación Básica</h3>
                        <div>
                            <label for="title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Título del Libro *</label>
                            <input type="text" id="title" wire:model="title" 
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Ingresa el título del libro">
                            @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="subtitle" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Subtítulo</label>
                            <input type="text" id="subtitle" wire:model="subtitle"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Subtítulo del libro (opcional)">
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="book_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Obra *</label>
                                <select id="book_type" wire:model="book_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($bookTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('book_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="primary_language" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Idioma Principal *</label>
                                <select id="primary_language" wire:model="primary_language"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($languageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('primary_language') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="secondary_language" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Idioma Secundario</label>
                                <select id="secondary_language" wire:model="secondary_language"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Ninguno</option>
                                    @foreach($languageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="publication_year" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Año de Publicación</label>
                                <input type="number" id="publication_year" wire:model="publication_year"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="2024" min="1900" max="2100">
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <label for="edition" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Edición</label>
                                <input type="text" id="edition" wire:model="edition"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="1ª, 2ª, revisada...">
                            </div>

                            <div>
                                <label for="isbn" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ISBN</label>
                                <input type="text" id="isbn" wire:model="isbn"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="978-3-16-148410-0">
                            </div>

                            <div>
                                <label for="doi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">DOI</label>
                                <input type="text" id="doi" wire:model="doi"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="10.1000/xyz123">
                            </div>
                        </div>

                        <div>
                            <label for="landing_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL del Libro / Landing Page</label>
                            <input type="url" id="landing_url" wire:model="landing_url"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="https://ejemplo.com/libro">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Portada del Libro</label>
                            <input type="file" wire:model="cover_image" accept="image/*"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @if($cover_image)
                                <img src="{{ $cover_image->temporaryUrl() }}" class="mt-2 h-32 rounded">
                            @endif
                        </div>
                    </div>

                    {{-- Editorial y Publicación --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Editorial y Formato</h3>
                        
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="publisher" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Editorial *</label>
                                <input type="text" id="publisher" wire:model="publisher"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Nombre de la editorial">
                                @error('publisher') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="publisher_country" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">País de la Editorial *</label>
                                <select id="publisher_country" wire:model="publisher_country"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('publisher_country') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="publisher_city" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                                <input type="text" id="publisher_city" wire:model="publisher_city"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Ciudad de la editorial">
                            </div>

                            <div>
                                <label for="collection_series" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Colección / Serie</label>
                                <input type="text" id="collection_series" wire:model="collection_series"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Nombre de la colección o serie">
                            </div>
                        </div>

                        <div>
                            <label for="sponsor_entity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Entidad Patrocinadora</label>
                            <input type="text" id="sponsor_entity" wire:model="sponsor_entity"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Universidad, centro de investigación, etc.">
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="total_pages" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Número Total de Páginas</label>
                                <input type="number" id="total_pages" wire:model="total_pages"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="350" min="1">
                            </div>

                            <div>
                                <label for="format" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Formato</label>
                                <select id="format" wire:model="format"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($formatOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="exact_publication_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Exacta de Publicación</label>
                            <input type="date" id="exact_publication_date" wire:model="exact_publication_date" 
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 2: Autores y Contenido Académico (2 & 4) --}}
            {{-- ============================================ --}}
            @if($currentStep === 2)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Contenido Académico y Autores</h2>
                <div class="space-y-8">

                    {{-- Autores y Responsabilidades --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Personas Relacionadas</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Agregue todos los autores, editores académicos, traductores o coordinadores del libro.</p>
                        
                        @foreach($authors as $index => $author)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white">Persona {{ $index + 1 }}</span>
                                    @if(count($authors) > 1)
                                        <button type="button" wire:click="removeAuthor({{ $index }})" class="text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rol *</label>
                                        <select wire:model="authors.{{ $index }}.role"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            @foreach($authorRoles as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo *</label>
                                        <input type="text" wire:model="authors.{{ $index }}.full_name"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="Nombre y apellidos">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ORCID</label>
                                        <input type="text" wire:model="authors.{{ $index }}.orcid"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="0000-0000-0000-0000">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Afiliación Institucional</label>
                                        <input type="text" wire:model="authors.{{ $index }}.affiliation"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="Universidad o institución">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">País</label>
                                        <select wire:model="authors.{{ $index }}.country_code"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="">Seleccionar...</option>
                                            @foreach($countries as $code => $name)
                                                <option value="{{ $code }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @error('authors') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        @error('authors.*.full_name') <p class="mt-1 text-sm text-red-500">Todos los autores deben tener nombre completo</p> @enderror

                        <button type="button" wire:click="addAuthor" class="inline-flex items-center rounded-lg border border-purple-600 px-4 py-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar otra persona
                        </button>
                    </div>

                    {{-- Contenido Académico --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Resumen y Áreas</h3>
                        <div>
                            <label for="abstract" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Resumen / Abstract *</label>
                            <textarea id="abstract" wire:model="abstract" rows="6"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Escriba un resumen detallado del libro (mínimo 100 caracteres)"></textarea>
                            @error('abstract') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Palabras Clave * (mínimo 3)</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($keywords as $keyword)
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-sm text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ $keyword }}
                                        <button type="button" wire:click="$set('keywords', {{ json_encode(array_values(array_diff($keywords, [$keyword]))) }})" class="ml-1 text-purple-500 hover:text-purple-700">&times;</button>
                                    </span>
                                @endforeach
                            </div>
                            <input type="text" 
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Escriba una palabra clave y presione Enter (Ej: 'Inteligencia Artificial', ENTER)"
                                @keydown.enter.prevent="$wire.set('keywords', [...$wire.keywords, $event.target.value]); $event.target.value = ''">
                            @error('keywords') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Áreas del Conocimiento * (seleccione al menos 1)</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($knowledgeAreaOptions as $value => $label)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" wire:model="knowledge_areas" value="{{ $value }}"
                                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('knowledge_areas') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="main_discipline" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Disciplina Principal</label>
                                <input type="text" id="main_discipline" wire:model="main_discipline"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Ej: Biología Molecular">
                            </div>

                            <div>
                                <label for="secondary_discipline" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Disciplina Secundaria</label>
                                <input type="text" id="secondary_discipline" wire:model="secondary_discipline"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Ej: Genética">
                            </div>
                        </div>

                        <div>
                            <label for="academic_level" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nivel Académico</label>
                            <select id="academic_level" wire:model="academic_level"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($academicLevelOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="table_of_contents" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tabla de Contenidos (Texto)</label>
                                <textarea id="table_of_contents" wire:model="table_of_contents" rows="6"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Escriba la tabla de contenidos aquí..."></textarea>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tabla de Contenidos (Archivo)</label>
                                <div class="mt-1 flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 dark:border-gray-700">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="table_of_contents_file" class="relative cursor-pointer rounded-md bg-white font-semibold text-purple-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-purple-600 focus-within:ring-offset-2 hover:text-purple-500 dark:bg-gray-800">
                                                <span>Subir archivo</span>
                                                <input id="table_of_contents_file" wire:model="table_of_contents_file" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">o arrastrar y soltar</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PDF, DOC hasta 10MB</p>
                                    </div>
                                </div>
                                @if($table_of_contents_file)
                                    <p class="mt-2 text-sm text-green-600">Archivo seleccionado: {{ $table_of_contents_file->getClientOriginalName() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif



            {{-- ============================================ --}}
            {{-- Step 3: Acceso Abierto, Derechos y Modelo de Negocio (5 & 6) --}}
            {{-- ============================================ --}}
            @if($currentStep === 3)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Acceso Abierto, Derechos y Modelo de Negocio</h2>
                <div class="space-y-8">
                    
                    {{-- Acceso Abierto y Derechos --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Acceso Abierto y Derechos</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Es de Acceso Abierto? *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_open_access" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_open_access" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">No</span>
                                </label>
                            </div>
                            @error('is_open_access') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($is_open_access)
                            <div>
                                <label for="access_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Acceso</label>
                                <select id="access_type" wire:model="access_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($accessTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="license_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Licencia *</label>
                            <select id="license_type" wire:model="license_type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($licenseTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('license_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="rights_holder" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Titular de Derechos</label>
                            <input type="text" id="rights_holder" wire:model="rights_holder"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Nombre del titular de los derechos">
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Permite Reutilización?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_reuse" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_reuse" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Permite Uso Comercial?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_commercial_use" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_commercial_use" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modelo de Negocio --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Modelo de Negocio</h3>
                        <div>
                            <label for="publication_model" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo de Publicación *</label>
                            <select id="publication_model" wire:model.live="publication_model"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($publicationModelOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('publication_model') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if(in_array($publication_model, ['pay_download', 'pay_print']))
                            <div>
                                <label for="access_cost" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Costo de Acceso (USD)</label>
                                <input type="number" id="access_cost" wire:model="access_cost" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="29.99">
                            </div>
                        @endif

                        <div>
                            <label for="author_apc" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Costo de Publicación para Autores (APC en USD)</label>
                            <input type="number" id="author_apc" wire:model="author_apc" step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="0.00">
                            <p class="mt-1 text-sm text-gray-500">Deje en blanco o ingrese 0 si no aplica</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Financiado por</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($fundingOptions as $value => $label)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" wire:model="funded_by" value="{{ $value }}"
                                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 4: Calidad Editorial, Evaluación e Indexación (7 & 8) --}}
            {{-- ============================================ --}}
            @if($currentStep === 4)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Calidad Editorial, Evaluación e Indexación</h2>
                <div class="space-y-8">
                    
                    {{-- Calidad Editorial --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Calidad y Evaluación Editorial</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Revisión por Pares? *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="has_peer_review" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="has_peer_review" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">No</span>
                                </label>
                            </div>
                            @error('has_peer_review') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($has_peer_review)
                            <div>
                                <label for="review_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Revisión</label>
                                <select id="review_type" wire:model="review_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($reviewTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Comité Editorial Identificado?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_committee" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_committee" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Normas Editoriales Declaradas?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_standards" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_standards" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Antiplagio Aplicado?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_antiplagiarism" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_antiplagiarism" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Código de Ética Editorial?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_ethics_code" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_ethics_code" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

            {{-- ============================================ --}}
            {{-- Step 8: Indexación y Visibilidad --}}
            {{-- ============================================ --}}
                    {{-- Indexación y Visibilidad --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">Indexación y Visibilidad</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿El libro está indexado?</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_indexed" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">Sí</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_indexed" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">No</span>
                                </label>
                            </div>
                        </div>

                        @if($is_indexed)
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Índices (Seleccione todos los que apliquen)</label>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach($indexOptions as $value => $label)
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" wire:model="indexes" value="{{ $value }}"
                                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="citation_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Citas (Aprox.)</label>
                                    <input type="number" id="citation_count" wire:model="citation_count"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0">
                                </div>

                                <div>
                                    <label for="available_metrics" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Otras Métricas Disponibles</label>
                                    <input type="text" id="available_metrics" wire:model="available_metrics"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="Ej: Google Scholar h-index">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 5: Archivos y Recursos (9) --}}
            {{-- ============================================ --}}
            @if($currentStep === 5)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Archivos y Recursos</h2>
                <div class="space-y-8">
                    
                    {{-- Main File --}}
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                        <label class="mb-2 block text-lg font-medium text-gray-900 dark:text-white">Archivo Principal del Libro (PDF/EPUB) *</label>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Suba el archivo completo de la obra.</p>
                        
                        <div class="mt-1 flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400 justify-center">
                                    <label for="main_file" class="relative cursor-pointer rounded-md bg-white font-semibold text-purple-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-purple-600 focus-within:ring-offset-2 hover:text-purple-500 dark:bg-gray-800">
                                        <span>Subir archivo principal</span>
                                        <input id="main_file" wire:model="main_file" type="file" class="sr-only" accept=".pdf,.epub">
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PDF, EPUB hasta 50MB</p>
                            </div>
                        </div>
                        @if($main_file)
                            <p class="mt-2 text-sm text-green-600 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Archivo cargado: {{ $main_file->getClientOriginalName() }}
                            </p>
                        @elseif($book && $book->main_file)
                             <p class="mt-2 text-sm text-green-600 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Archivo actual disponible
                            </p>
                        @endif
                        @error('main_file') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Chapters --}}
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Capítulos Individuales (Opcional)</h3>
                            <button type="button" wire:click="addChapterFile" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                + Agregar Capítulo
                            </button>
                        </div>
                        <div class="space-y-4">
                            @foreach($chapter_files as $index => $chapter)
                                <div class="flex gap-4 items-start">
                                    <div class="flex-grow grid gap-4 sm:grid-cols-2">
                                        <input type="text" wire:model="chapter_files.{{ $index }}.chapter_name" 
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="Título del Capítulo">
                                        
                                        <input type="file" wire:model="chapter_files.{{ $index }}.file" 
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-purple-900/20 dark:file:text-purple-300">
                                    </div>
                                    <button type="button" wire:click="removeChapterFile({{ $index }})" class="text-red-500 hover:text-red-700 mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Supplementary Files --}}
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Material Complementario (Opcional)</h3>
                            <button type="button" wire:click="addSupplementaryFile" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                + Agregar Material
                            </button>
                        </div>
                        <div class="space-y-4">
                            @foreach($supplementary_files as $index => $file)
                                <div class="flex gap-4 items-start">
                                    <div class="flex-grow grid gap-4 sm:grid-cols-2">
                                        <input type="text" wire:model="supplementary_files.{{ $index }}.name" 
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="Nombre del Material">
                                        
                                        <input type="file" wire:model="supplementary_files.{{ $index }}.file" 
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-purple-900/20 dark:file:text-purple-300">
                                    </div>
                                    <button type="button" wire:click="removeSupplementaryFile({{ $index }})" class="text-red-500 hover:text-red-700 mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="download_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL de Descarga Externa (Opcional)</label>
                            <input type="url" id="download_url" wire:model="download_url"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="https://...">
                        </div>
                        <div>
                            <label for="file_size" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tamaño del Archivo (Texto)</label>
                            <input type="text" id="file_size" wire:model="file_size"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Ej: 5MB">
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 6: Confirmación (10) --}}
            {{-- ============================================ --}}
            @if($currentStep === 6)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Confirmar Datos</h2>
                <div class="space-y-6">
                    {{-- Identificación --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Identificación del Libro</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Título</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $title }}</dd>
                            </div>
                            @if($subtitle)
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Subtítulo</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $subtitle }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $bookTypes[$book_type] ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">ISBN</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $isbn ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Autores --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Autores</h3>
                        <ul class="space-y-2">
                            @foreach($authors as $author)
                                @if(!empty($author['full_name']))
                                <li class="text-gray-700 dark:text-gray-300">
                                    <span class="font-medium">{{ $author['full_name'] }}</span>
                                    <span class="text-sm text-gray-500">({{ $authorRoles[$author['role']] ?? $author['role'] }})</span>
                                    @if(!empty($author['affiliation']))
                                        <span class="text-sm text-gray-500">- {{ $author['affiliation'] }}</span>
                                    @endif
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    {{-- Editorial --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Editorial</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Editorial</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $publisher }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">País</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $countries[$publisher_country] ?? $publisher_country }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Fecha Publicación</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $exact_publication_date ?? $publication_year }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Páginas</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $total_pages ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Contenido e Indexación --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Contenido e Indexación</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Tabla de Contenidos</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    @if($table_of_contents_file)
                                        Archivo cargado
                                    @elseif($table_of_contents)
                                        Ingresada (Texto)
                                    @else
                                        No disponible
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Indexado</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $is_indexed ? 'Sí' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Archivos --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Archivos</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Archivo Principal</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    @if($main_file)
                                        {{ $main_file->getClientOriginalName() }}
                                    @elseif($book && $book->main_file)
                                        Disponible
                                    @else
                                        <span class="text-red-500">Pendiente</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Capítulos</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ count($chapter_files) }} archivos</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Acceso --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Acceso y Licencia</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Acceso Abierto</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $is_open_access ? 'Sí' : 'No' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Licencia</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $licenseTypes[$license_type] ?? $license_type }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            <strong>Nota:</strong> Al continuar serás redirigido a la página de pago. Una vez completado el pago, tu libro será enviado a revisión.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Navigation --}}
            <div class="mt-8 flex justify-between">
                @if($currentStep > 1)
                    <button wire:click="previousStep" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Anterior
                    </button>
                @else
                    <a href="{{ route('app.dashboard') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancelar
                    </a>
                @endif

                <button wire:click="saveAndExit" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Guardar Borrador
                </button>

                @if($currentStep < $totalSteps)
                    <button wire:click="nextStep" type="button" class="inline-flex items-center rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-500">
                        Siguiente
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <button wire:click="submit" type="button" class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Continuar al Pago
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
