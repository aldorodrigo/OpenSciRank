<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-4xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-indigo-600">{{ __('My Dashboard') }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">{{ $journal ? __('Edit Journal') : __('New Journal') }}</span>
        </nav>

        {{-- Progress Steps --}}
        <div class="mb-8 overflow-x-auto">
            <div class="flex items-center justify-between min-w-max">
                @php
                    $stepNames = [
                        __('About'),
                        __('Open Access'),
                        __('Licenses'),
                        __('Editorial'),
                        __('Model'),
                        __('Practices'),
                        __('Confirm'),
                    ];
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
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">1️⃣ {{ __('About the Journal') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Identity and editorial scope') }}</p>
                <div class="space-y-6">
                    {{-- Selector de idioma primario --}}
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Idioma principal de la revista') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            @foreach(['es' => 'Español', 'en' => 'English', 'pt' => 'Português'] as $code => $name)
                                <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border cursor-pointer {{ $primary_locale === $code ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="primary_locale" value="{{ $code }}" class="hidden">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('Este será el idioma obligatorio. Podrás añadir traducciones en cada campo.') }}</p>
                    </div>

                    <x-translatable-input
                        name="title"
                        :label="__('Official title')"
                        model="title"
                        :primary="$primary_locale"
                        required
                        :help="__('Full and official name of the journal as it appears on its cover.')" />

                    <x-translatable-input
                        name="abbreviated_name"
                        :label="__('Abbreviated name')"
                        model="abbreviated_name"
                        :primary="$primary_locale"
                        :help="__('Standard abbreviation of the journal according to ISO 4, if it exists.')" />

                    <x-translatable-textarea
                        name="description"
                        :label="__('Description / Scope') . ' (' . __('min. 50 characters') . ')'"
                        model="description"
                        :primary="$primary_locale"
                        :rows="4"
                        required
                        :help="__('Describe the thematic focus, mission and editorial scope of the journal.')" />

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Subject area(s)') }} * <x-field-tooltip :text="__('Select the main disciplines covered by the journal.')" /></label>
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
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Target audience') }} <x-field-tooltip :text="__('Who is the journal primarily aimed at?')" /></label>
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
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publication language(s)') }} * <x-field-tooltip :text="__('Languages in which articles are accepted and published.')" /></label>
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
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Year') }} <x-field-tooltip :text="__('Year in which the journal began publishing.')" /></label>
                            <input type="number" wire:model="start_year" min="1900" max="{{ date('Y') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Official Website') }} * <x-field-tooltip :text="__('Main website address of the journal.')" /></label>
                            <input type="url" wire:model="url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Logo upload --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Journal Logo') }} <x-field-tooltip :text="__('Upload the official journal logo. Accepted formats: JPG, PNG, SVG, WEBP. Maximum 2 MB.')" /></label>
                        @if($existing_logo && !$logo)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ Storage::url($existing_logo) }}" alt="{{ __('Current logo') }}" class="max-h-32 w-auto rounded-lg object-contain shadow">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Current logo') }}</span>
                            </div>
                        @endif
                        @if($logo)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="max-h-32 w-auto rounded-lg object-contain shadow">
                                <span class="text-sm text-emerald-600">{{ __('New logo selected') }}</span>
                            </div>
                        @endif
                        <label class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-6 transition hover:border-indigo-400 dark:border-gray-600 dark:hover:border-indigo-500">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Click to select image') }}</p>
                            </div>
                            <input type="file" wire:model="logo" accept="image/*" class="hidden">
                        </label>
                        @error('logo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 2: Open Access --}}
            @if($currentStep === 2)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">2️⃣ {{ __('Open Access Compliance') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Verify whether the journal meets Open Access principles') }}</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is the journal open access?') }} * <x-field-tooltip :text="__('Indicates whether all articles are freely available to the public.')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="is_open_access" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="is_open_access" value="0" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('is_open_access') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access type') }} * <x-field-tooltip :text="__('Full: all articles are free. Hybrid: only some. Restricted: requires subscription.')" /></label>
                        <select wire:model="access_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach($accessTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('access_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Are articles accessible without registration?') }} * <x-field-tooltip :text="__('Can articles be read without needing to create an account on the site?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="articles_accessible_without_registration" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="articles_accessible_without_registration" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('articles_accessible_without_registration') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is self-archiving allowed?') }} <x-field-tooltip :text="__('Can authors deposit their articles in institutional or personal repositories?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_self_archiving" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_self_archiving" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Open access policy (URL)') }} <x-field-tooltip :text="__("Link to the page describing the journal's open access policy.")" /></label>
                        <input type="url" wire:model="open_access_policy_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is there an embargo?') }} <x-field-tooltip :text="__('Do articles have a restriction period before being available in open access?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="has_embargo" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="has_embargo" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    @if($has_embargo)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Embargo duration (months)') }} <x-field-tooltip :text="__('Number of months the restriction period lasts.')" /></label>
                        <input type="number" wire:model="embargo_months" min="1" max="60" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    @endif
                </div>
            @endif

            {{-- Step 3: Licenses --}}
            @if($currentStep === 3)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">3️⃣ {{ __('Copyright and Licenses') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Legal transparency and reuse') }}</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Main license type') }} * <x-field-tooltip :text="__('License under which articles are published (e.g. Creative Commons).')" /></label>
                        <select wire:model="license_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach($licenseTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('license_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('License URL') }} <x-field-tooltip :text="__('Direct link to the full text of the license used.')" /></label>
                        <input type="url" wire:model="license_url" placeholder="https://creativecommons.org/..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Do authors retain rights?') }} * <x-field-tooltip :text="__('Do authors retain the copyright of their articles after publication?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="authors_retain_copyright" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="authors_retain_copyright" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('authors_retain_copyright') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Is commercial reuse allowed?') }} <x-field-tooltip :text="__('Is it permitted to use the content for commercial purposes?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_commercial_reuse" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="allows_commercial_reuse" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <x-translatable-textarea
                        name="copyright_policy"
                        :label="__('Copyright policy')"
                        model="copyright_policy"
                        :primary="$primary_locale"
                        :rows="3"
                        :placeholder="__('Text or URL of the policy...')"
                        :help="__('Text or URL describing the journal\'s copyright policy.')" />

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Licenses visible in articles?') }} <x-field-tooltip :text="__('Does each article clearly display the license under which it is published?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="licenses_visible_in_articles" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="licenses_visible_in_articles" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 4: Editorial --}}
            @if($currentStep === 4)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">4️⃣ {{ __('Editorial') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Editorial management of the journal') }}</p>
                <div class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-translatable-input
                                name="publishing_institution"
                                :label="__('Publishing institution')"
                                model="publishing_institution"
                                :primary="$primary_locale"
                                required
                                :help="__('University, research center or organization that publishes the journal.')" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Publisher's country") }} * <x-field-tooltip :text="__('Country where the publishing institution is based.')" /></label>
                            <x-country-select wire="country_code" :value="$country_code" name="country_code" />
                            @error('country_code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-translatable-input
                                name="editor_name"
                                :label="__('Responsible editor')"
                                model="editor_name"
                                :primary="$primary_locale"
                                required
                                :help="__('Name of the director or editor-in-chief of the journal.')" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Institutional email') }} * <span class="text-xs text-gray-400">({{ __('not Gmail/Yahoo') }})</span> <x-field-tooltip :text="__('Official journal email with institutional domain.')" /></label>
                            <input type="email" wire:model="institutional_email" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('institutional_email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Editorial board visible?') }} * <x-field-tooltip :text="__('Does the website show the names and affiliations of the editorial board members?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="editorial_board_visible" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="editorial_board_visible" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('editorial_board_visible') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Editorial board URL') }} <x-field-tooltip :text="__('Link to the page listing the editorial board.')" /></label>
                        <input type="url" wire:model="editorial_board_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Peer review type') }} * <x-field-tooltip :text="__('Method of evaluating articles by experts before publication.')" /></label>
                            <select wire:model="peer_review_type" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach($peerReviewTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('peer_review_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Frequency') }} * <x-field-tooltip :text="__('How often new issues or articles are published.')" /></label>
                            <select wire:model="publication_frequency" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">{{ __('Select...') }}</option>
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
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">5️⃣ {{ __('Business Model') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Economic barriers and sustainability') }}</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Does the journal charge APC (Article Processing Charges)?') }} * <x-field-tooltip :text="__('Do authors have to pay for their article to be published?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="charges_apc" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="charges_apc" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('charges_apc') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($charges_apc)
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('APC amount') }} <x-field-tooltip :text="__('Cost that authors must pay per published article.')" /></label>
                            <input type="number" wire:model="apc_amount" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Currency') }}</label>
                            <select wire:model="apc_currency" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Are there waivers?') }} <x-field-tooltip :text="__('Are APC discounts or waivers offered for authors from developing countries or other conditions?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_apc_waivers" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_apc_waivers" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Funding source') }} <x-field-tooltip :text="__('How is the publication of the journal funded?')" /></label>
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
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Advertising on the site?') }} <x-field-tooltip :text="__("Does the journal's website display advertising?")" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_advertising" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_advertising" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Business model transparency?') }} <x-field-tooltip :text="__('Does the journal publish clear information about how it is funded and what its costs are?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="business_model_transparent" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="business_model_transparent" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 6: Best Practices --}}
            @if($currentStep === 6)
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">6️⃣ {{ __('Best Practices') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Editorial quality and ethics') }}</p>
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Editorial ethics policy?') }} * <x-field-tooltip :text="__('Does the journal have a published ethics policy for authors, editors and reviewers?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_ethics_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_ethics_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('has_ethics_policy') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Adheres to COPE?') }} <x-field-tooltip :text="__('Does the journal follow the guidelines of the Committee on Publication Ethics (COPE)?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="adheres_to_cope" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="adheres_to_cope" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Anti-plagiarism policy?') }} * <x-field-tooltip :text="__('Does the journal verify the originality of articles with anti-plagiarism tools?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_antiplagiarism_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_antiplagiarism_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                        @error('has_antiplagiarism_policy') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Anti-plagiarism tool') }} <x-field-tooltip :text="__('Specific software used to verify originality.')" /></label>
                        <input type="text" wire:model="antiplagiarism_tool" placeholder="{{ __('e.g.: Turnitin, iThenticate') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Conflict of interest declaration?') }} <x-field-tooltip :text="__('Are authors and reviewers required to declare potential conflicts of interest?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_conflict_of_interest_policy" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="has_conflict_of_interest_policy" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Declared AI use in editorial process?') }} <x-field-tooltip :text="__('Does the journal have a policy on the use of artificial intelligence in article production?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="declares_ai_use" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="declares_ai_use" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('DOI assigned to articles?') }} <x-field-tooltip :text="__('Does each article have a unique DOI (Digital Object Identifier)?')" /></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="assigns_doi" value="1" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('Yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="assigns_doi" value="0" class="h-4 w-4 text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Print ISSN') }} <x-field-tooltip :text="__('International serial number for the print version (format: 1234-5678).')" /></label>
                            <input type="text" wire:model="issn_print" placeholder="1234-5678" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Online ISSN') }} <x-field-tooltip :text="__('International serial number for the electronic version.')" /></label>
                            <input type="text" wire:model="issn_online" placeholder="1234-5679" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Publisher') }} <x-field-tooltip :text="__('Name of the publisher, if it differs from the publishing institution.')" /></label>
                        <input type="text" wire:model="publisher" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('OAI-PMH Configuration (Optional)') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('If the journal has an OAI-PMH server, configure it here for automatic article harvesting.') }}</p>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('OAI-PMH Base URL') }} <x-field-tooltip :text="__('Base URL of the OAI-PMH server (e.g. https://journal.edu/oai/request).')" /></label>
                        <input type="url" wire:model="oai_base_url" placeholder="https://..." class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('oai_base_url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Set Spec') }} <x-field-tooltip :text="__('SET identifier to harvest only a specific collection (optional).')" /></label>
                            <input type="text" wire:model="oai_set_spec" placeholder="{{ __('e.g.: col_123456789_1') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Metadata Prefix') }} <x-field-tooltip :text="__('Preferred metadata format for harvesting.')" /></label>
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
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">✅ {{ __('Confirm Data') }}</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Review the information before continuing') }}</p>
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-3 font-medium text-gray-900 dark:text-white">{{ __('General Information') }}</h3>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-gray-500">{{ __('Title') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $title[$primary_locale] ?? '' }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('Official Website') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $url }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('Open Access') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $is_open_access ? __('Yes') : __('No') }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('License') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $licenseTypes[$license_type] ?? '—' }}</dd></div>
                        </dl>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-3 font-medium text-gray-900 dark:text-white">{{ __('Editorial') }}</h3>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-gray-500">{{ __('Institution') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $publishing_institution[$primary_locale] ?? '' }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('Editor') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $editor_name[$primary_locale] ?? '' }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('Email') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $institutional_email }}</dd></div>
                            <div><dt class="text-gray-500">{{ __('Peer Review') }}:</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $peerReviewTypes[$peer_review_type] ?? '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="mt-8">
                        <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">{{ __('Choose how to continue') }}</h3>
                        <div class="grid gap-6 md:grid-cols-2">
                            {{-- Option 1: Evaluate (Recommended) --}}
                            <div class="relative flex flex-col rounded-2xl border-2 border-indigo-500 bg-indigo-50/50 p-6 dark:border-indigo-400 dark:bg-indigo-900/20">
                                <div class="absolute -top-3 left-6 inline-flex rounded-full bg-indigo-500 px-3 py-1 text-xs font-bold text-white shadow-sm">
                                    {{ __('Recommended') }}
                                </div>
                                <h4 class="mb-2 flex items-center gap-2 text-lg font-bold text-indigo-900 dark:text-indigo-300">
                                    <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
                                    {{ __('Evaluate Journal') }}
                                </h4>
                                <p class="mb-6 flex-1 text-sm leading-relaxed text-indigo-800/80 dark:text-indigo-200/80">
                                    {{ __('Get the Editorial Standards Platform Quality Seal. Your journal will be evaluated in detail according to our methodological criteria. A high score will significantly increase the visibility, prestige and trust in your publications, distinguishing you in the scientific community.') }}
                                </p>
                                <button wire:click="submit" type="button" class="wizard-btn wizard-btn-success flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                                    {{ __('Pay and Evaluate') }}
                                </button>
                            </div>

                            {{-- Option 2: List --}}
                            <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                                <h4 class="mb-2 flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    {{ __('List Journal (Free)') }}
                                </h4>
                                <p class="mb-6 flex-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ __('Register your journal in our public access database for free. Your journal will appear in search results after being approved by the team, but will not have the Quality Seal nor a detailed rating from our platform.') }}
                                </p>
                                <button wire:click="listJournal" type="button" class="wizard-btn flex w-full items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-6 py-3.5 font-bold text-slate-700 transition-all hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    {{ __('Request Listing') }}
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
                        {{ __('Previous') }}
                    </button>
                @else
                    <a href="{{ route('app.dashboard') }}" class="wizard-btn wizard-btn-outline inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ __('Cancel') }}</a>
                @endif

                <button wire:click="saveAsDraft" type="button" class="wizard-btn wizard-btn-draft inline-flex items-center rounded-lg border border-amber-300 bg-amber-50 px-5 py-3 font-semibold text-amber-700 dark:border-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                    {{ __('Save Draft') }}
                </button>

                @if($currentStep < $totalSteps)
                    <button wire:click="nextStep" type="button" class="wizard-btn wizard-btn-primary inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white">
                        {{ __('Next') }}
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
