<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-4xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-purple-600">{{ __('My Dashboard') }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">{{ $book ? __('Edit Book') : __('New Book') }}</span>
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
                <span class="w-16 text-center {{ $currentStep >= 1 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('General') }}</span>
                <span class="w-16 text-center {{ $currentStep >= 2 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('Content') }}</span>
                <span class="w-16 text-center {{ $currentStep >= 3 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('Access') }}</span>
                <span class="w-16 text-center {{ $currentStep >= 4 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('Evaluation') }}</span>
                <span class="w-16 text-center {{ $currentStep >= 5 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('Files') }}</span>
                <span class="w-16 text-center {{ $currentStep >= 6 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">{{ __('Confirm') }}</span>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">

            {{-- ============================================ --}}
            {{-- Step 1: General Information --}}
            {{-- ============================================ --}}
            @if($currentStep === 1)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('General Book Information') }}</h2>

                <div class="space-y-8">
                    {{-- Selector de idioma primario --}}
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Idioma principal del libro') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            @foreach(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'] as $code => $name)
                                <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border cursor-pointer {{ $primary_locale === $code ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="primary_locale" value="{{ $code }}" class="hidden">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('Este será el idioma obligatorio. Podrás añadir traducciones en cada campo.') }}</p>
                    </div>

                    {{-- Basic Identification --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Basic Identification') }}</h3>

                        <x-translatable-input
                            name="title"
                            :label="__('Book Title')"
                            model="title"
                            :primary="$primary_locale"
                            required
                            :placeholder="__('Enter the book title')" />

                        <x-translatable-input
                            name="subtitle"
                            :label="__('Subtitle')"
                            model="subtitle"
                            :primary="$primary_locale"
                            :placeholder="__('Book subtitle (optional)')" />

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="book_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Work Type') }} *</label>
                                <select id="book_type" wire:model="book_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($bookTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('book_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="primary_language" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Primary Language') }} *</label>
                                <select id="primary_language" wire:model="primary_language"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($languageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('primary_language') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="secondary_language" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Secondary Language') }}</label>
                                <select id="secondary_language" wire:model="secondary_language"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach($languageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="publication_year" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publication Year') }}</label>
                                <input type="number" id="publication_year" wire:model="publication_year"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="2024" min="1900" max="2100">
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <label for="edition" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Edition') }}</label>
                                <input type="text" id="edition" wire:model="edition"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="{{ __('1st, 2nd, revised...') }}">
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
                            <label for="landing_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Book URL / Landing Page') }}</label>
                            <input type="url" id="landing_url" wire:model="landing_url"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="https://example.com/book">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Book Cover') }}</label>
                            <input type="file" wire:model="cover_image" accept="image/*"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @if($cover_image)
                                <img src="{{ $cover_image->temporaryUrl() }}" class="mt-2 h-32 rounded">
                            @endif
                        </div>
                    </div>

                    {{-- Publisher and Format --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Publisher and Format') }}</h3>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="publisher" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publisher') }} *</label>
                                <input type="text" id="publisher" wire:model="publisher"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="{{ __('Publisher name') }}">
                                @error('publisher') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="publisher_country" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publisher Country') }} *</label>
                                <select id="publisher_country" wire:model="publisher_country"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('publisher_country') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="publisher_city" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('City') }}</label>
                                <input type="text" id="publisher_city" wire:model="publisher_city"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="{{ __('Publisher city') }}">
                            </div>

                            <div>
                                <x-translatable-input
                                    name="collection_series"
                                    :label="__('Collection / Series')"
                                    model="collection_series"
                                    :primary="$primary_locale"
                                    :placeholder="__('Collection or series name')" />
                            </div>
                        </div>

                        <div>
                            <x-translatable-input
                                name="sponsor_entity"
                                :label="__('Sponsoring Entity')"
                                model="sponsor_entity"
                                :primary="$primary_locale"
                                :placeholder="__('University, research center, etc.')" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="total_pages" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total Number of Pages') }}</label>
                                <input type="number" id="total_pages" wire:model="total_pages"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="350" min="1">
                            </div>

                            <div>
                                <label for="format" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Format') }}</label>
                                <select id="format" wire:model="format"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($formatOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="exact_publication_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Exact Publication Date') }}</label>
                            <input type="date" id="exact_publication_date" wire:model="exact_publication_date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 2: Academic Content and Authors --}}
            {{-- ============================================ --}}
            @if($currentStep === 2)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Academic Content and Authors') }}</h2>
                <div class="space-y-8">

                    {{-- Related Persons --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Related Persons') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Add all authors, academic editors, translators or coordinators of the book.') }}</p>

                        @foreach($authors as $index => $author)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Person') }} {{ $index + 1 }}</span>
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
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Role') }} *</label>
                                        <select wire:model="authors.{{ $index }}.role"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            @foreach($authorRoles as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Full Name') }} *</label>
                                        <input type="text" wire:model="authors.{{ $index }}.full_name"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="{{ __('First and last name') }}">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ORCID</label>
                                        <input type="text" wire:model="authors.{{ $index }}.orcid"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="0000-0000-0000-0000">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Institutional Affiliation') }}</label>
                                        <input type="text" wire:model="authors.{{ $index }}.affiliation"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="{{ __('University or institution') }}">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Country') }}</label>
                                        <select wire:model="authors.{{ $index }}.country_code"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="">{{ __('Select...') }}</option>
                                            @foreach($countries as $code => $name)
                                                <option value="{{ $code }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @error('authors') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        @error('authors.*.full_name') <p class="mt-1 text-sm text-red-500">{{ __('All authors must have a full name') }}</p> @enderror

                        <button type="button" wire:click="addAuthor" class="inline-flex items-center rounded-lg border border-purple-600 px-4 py-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add another person') }}
                        </button>
                    </div>

                    {{-- Summary and Areas --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Summary and Areas') }}</h3>
                        <x-translatable-textarea
                            name="abstract"
                            :label="__('Summary / Abstract')"
                            model="abstract"
                            :primary="$primary_locale"
                            :rows="6"
                            required
                            :placeholder="__('Write a detailed summary of the book (minimum 100 characters)')" />

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Keywords') }} * {{ __('(minimum 3)') }}</label>
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
                                placeholder="{{ __("Type a keyword and press Enter (e.g., 'Artificial Intelligence', ENTER)") }}"
                                @keydown.enter.prevent="$wire.set('keywords', [...$wire.keywords, $event.target.value]); $event.target.value = ''">
                            @error('keywords') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Areas of Knowledge') }} * {{ __('(select at least 1)') }}</label>
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
                                <label for="main_discipline" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Main Discipline') }}</label>
                                <input type="text" id="main_discipline" wire:model="main_discipline"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="{{ __('e.g.: Molecular Biology') }}">
                            </div>

                            <div>
                                <label for="secondary_discipline" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Secondary Discipline') }}</label>
                                <input type="text" id="secondary_discipline" wire:model="secondary_discipline"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="{{ __('e.g.: Genetics') }}">
                            </div>
                        </div>

                        <div>
                            <label for="academic_level" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Academic Level') }}</label>
                            <select id="academic_level" wire:model="academic_level"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach($academicLevelOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <x-translatable-textarea
                                    name="table_of_contents"
                                    :label="__('Table of Contents (Text)')"
                                    model="table_of_contents"
                                    :primary="$primary_locale"
                                    :rows="6"
                                    :placeholder="__('Write the table of contents here...')" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Table of Contents (File)') }}</label>
                                <div class="mt-1 flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 dark:border-gray-700">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="table_of_contents_file" class="relative cursor-pointer rounded-md bg-white font-semibold text-purple-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-purple-600 focus-within:ring-offset-2 hover:text-purple-500 dark:bg-gray-800">
                                                <span>{{ __('Upload file') }}</span>
                                                <input id="table_of_contents_file" wire:model="table_of_contents_file" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">{{ __('or drag and drop') }}</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">{{ __('PDF, DOC up to 10MB') }}</p>
                                    </div>
                                </div>
                                @if($table_of_contents_file)
                                    <p class="mt-2 text-sm text-green-600">{{ __('File selected:') }} {{ $table_of_contents_file->getClientOriginalName() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif



            {{-- ============================================ --}}
            {{-- Step 3: Open Access, Rights and Business Model --}}
            {{-- ============================================ --}}
            @if($currentStep === 3)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Open Access, Rights and Business Model') }}</h2>
                <div class="space-y-8">

                    {{-- Open Access and Rights --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Open Access and Rights') }}</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is it Open Access?') }} *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_open_access" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_open_access" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                </label>
                            </div>
                            @error('is_open_access') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($is_open_access)
                            <div>
                                <label for="access_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access Type') }}</label>
                                <select id="access_type" wire:model="access_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($accessTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="license_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('License') }} *</label>
                            <select id="license_type" wire:model="license_type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach($licenseTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('license_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-translatable-input
                                name="rights_holder"
                                :label="__('Rights Holder')"
                                model="rights_holder"
                                :primary="$primary_locale"
                                :placeholder="__('Name of the rights holder')" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Allows Reuse?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_reuse" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_reuse" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Allows Commercial Use?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_commercial_use" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="allows_commercial_use" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Business Model --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Business Model') }}</h3>
                        <div>
                            <label for="publication_model" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publication Model') }} *</label>
                            <select id="publication_model" wire:model.live="publication_model"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach($publicationModelOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('publication_model') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if(in_array($publication_model, ['pay_download', 'pay_print']))
                            <div>
                                <label for="access_cost" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access Cost (USD)') }}</label>
                                <input type="number" id="access_cost" wire:model="access_cost" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="29.99">
                            </div>
                        @endif

                        <div>
                            <label for="author_apc" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Author Publication Cost (APC in USD)') }}</label>
                            <input type="number" id="author_apc" wire:model="author_apc" step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="0.00">
                            <p class="mt-1 text-sm text-gray-500">{{ __('Leave blank or enter 0 if not applicable') }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Funded by') }}</label>
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
            {{-- Step 4: Editorial Quality, Evaluation and Indexing --}}
            {{-- ============================================ --}}
            @if($currentStep === 4)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Editorial Quality, Evaluation and Indexing') }}</h2>
                <div class="space-y-8">

                    {{-- Editorial Quality --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Editorial Quality and Evaluation') }}</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Peer Review?') }} *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="has_peer_review" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="has_peer_review" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                </label>
                            </div>
                            @error('has_peer_review') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($has_peer_review)
                            <div>
                                <label for="review_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Review Type') }}</label>
                                <select id="review_type" wire:model="review_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">{{ __('Select...') }}</option>
                                    @foreach($reviewTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Identified Editorial Committee?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_committee" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_committee" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Declared Editorial Standards?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_standards" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_editorial_standards" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Antiplagiarism Applied?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_antiplagiarism" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_antiplagiarism" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Editorial Ethics Code?') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_ethics_code" value="1"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" wire:model="has_ethics_code" value="0"
                                            class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Indexing and Visibility --}}
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2 dark:border-gray-700">{{ __('Indexing and Visibility') }}</h3>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is the book indexed?') }}</label>
                            <div class="flex gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_indexed" value="1"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model.live="is_indexed" value="0"
                                        class="border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>

                        @if($is_indexed)
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Indexes (Select all that apply)') }}</label>
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
                                    <label for="citation_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of Citations (Approx.)') }}</label>
                                    <input type="number" id="citation_count" wire:model="citation_count"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0">
                                </div>

                                <div>
                                    <x-translatable-input
                                        name="available_metrics"
                                        :label="__('Other Available Metrics')"
                                        model="available_metrics"
                                        :primary="$primary_locale"
                                        :placeholder="__('e.g.: Google Scholar h-index')" />
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 5: Files and Resources --}}
            {{-- ============================================ --}}
            @if($currentStep === 5)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Files and Resources') }}</h2>
                <div class="space-y-8">

                    {{-- Main File --}}
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                        <label class="mb-2 block text-lg font-medium text-gray-900 dark:text-white">{{ __('Main Book File (PDF/EPUB)') }} *</label>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Upload the complete file of the work.') }}</p>

                        <div class="mt-1 flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400 justify-center">
                                    <label for="main_file" class="relative cursor-pointer rounded-md bg-white font-semibold text-purple-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-purple-600 focus-within:ring-offset-2 hover:text-purple-500 dark:bg-gray-800">
                                        <span>{{ __('Upload main file') }}</span>
                                        <input id="main_file" wire:model="main_file" type="file" class="sr-only" accept=".pdf,.epub">
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">{{ __('PDF, EPUB up to 50MB') }}</p>
                            </div>
                        </div>
                        @if($main_file)
                            <p class="mt-2 text-sm text-green-600 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('File uploaded:') }} {{ $main_file->getClientOriginalName() }}
                            </p>
                        @elseif($book && $book->main_file)
                             <p class="mt-2 text-sm text-green-600 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('Current file available') }}
                            </p>
                        @endif
                        @error('main_file') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Chapters --}}
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Individual Chapters (Optional)') }}</h3>
                            <button type="button" wire:click="addChapterFile" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                + {{ __('Add Chapter') }}
                            </button>
                        </div>
                        <div class="space-y-4">
                            @foreach($chapter_files as $index => $chapter)
                                <div class="flex gap-4 items-start">
                                    <div class="flex-grow grid gap-4 sm:grid-cols-2">
                                        <input type="text" wire:model="chapter_files.{{ $index }}.chapter_name"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="{{ __('Chapter Title') }}">

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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Supplementary Material (Optional)') }}</h3>
                            <button type="button" wire:click="addSupplementaryFile" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                + {{ __('Add Material') }}
                            </button>
                        </div>
                        <div class="space-y-4">
                            @foreach($supplementary_files as $index => $file)
                                <div class="flex gap-4 items-start">
                                    <div class="flex-grow grid gap-4 sm:grid-cols-2">
                                        <input type="text" wire:model="supplementary_files.{{ $index }}.name"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="{{ __('Material Name') }}">

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
                            <label for="download_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('External Download URL (Optional)') }}</label>
                            <input type="url" id="download_url" wire:model="download_url"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="https://...">
                        </div>
                        <div>
                            <label for="file_size" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('File Size (Text)') }}</label>
                            <input type="text" id="file_size" wire:model="file_size"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="{{ __('e.g.: 5MB') }}">
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- Step 6: Confirm Data --}}
            {{-- ============================================ --}}
            @if($currentStep === 6)
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Confirm Data') }}</h2>
                <div class="space-y-6">
                    {{-- Book Identification --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Book Identification') }}</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $title[$primary_locale] ?? '' }}</dd>
                            </div>
                            @if(!empty($subtitle[$primary_locale] ?? ''))
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Subtitle') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $subtitle[$primary_locale] ?? '' }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $bookTypes[$book_type] ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">ISBN</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $isbn ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Authors --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Authors') }}</h3>
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

                    {{-- Publisher --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Publisher') }}</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Publisher') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $publisher }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Country') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $countries[$publisher_country] ?? $publisher_country }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Publication Date') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $exact_publication_date ?? $publication_year }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pages') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $total_pages ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Content and Indexing --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Content and Indexing') }}</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Table of Contents') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    @if($table_of_contents_file)
                                        {{ __('File uploaded') }}
                                    @elseif(!empty($table_of_contents[$primary_locale] ?? ''))
                                        {{ __('Entered (Text)') }}
                                    @else
                                        {{ __('Not available') }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Indexed') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $is_indexed ? __('Yes') : __('No') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Files --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Files') }}</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Main File') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    @if($main_file)
                                        {{ $main_file->getClientOriginalName() }}
                                    @elseif($book && $book->main_file)
                                        {{ __('Available') }}
                                    @else
                                        <span class="text-red-500">{{ __('Pending') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Chapters') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ count($chapter_files) }} {{ __('files') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Access and License --}}
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 font-medium text-gray-900 dark:text-white">{{ __('Access and License') }}</h3>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Open Access') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $is_open_access ? __('Yes') : __('No') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('License') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $licenseTypes[$license_type] ?? $license_type }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            <strong>{{ __('Note:') }}</strong> {{ __('By continuing you will be redirected to the payment page. Once the payment is completed, your book will be sent for review.') }}
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
                        {{ __('Previous') }}
                    </button>
                @else
                    <a href="{{ route('app.dashboard') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ __('Cancel') }}
                    </a>
                @endif

                <button wire:click="saveAndExit" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('Save Draft') }}
                </button>

                @if($currentStep < $totalSteps)
                    <button wire:click="nextStep" type="button" class="inline-flex items-center rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-500">
                        {{ __('Next') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <button wire:click="submit" type="button" class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('Proceed to Payment') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
