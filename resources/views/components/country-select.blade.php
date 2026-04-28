@props([
    'name' => 'country_code',
    'value' => '',
    'placeholder' => '',
    'wire' => null,
])

@php
    $selectedName = $value ? \App\Support\Countries::getName($value) : '';
    $placeholder = $placeholder ?: __('Search country...');
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: '{{ $value }}',
        selectedName: '{{ $selectedName }}',
        countries: {{ \App\Support\Countries::toJson() }},
        get filtered() {
            if (!this.search) return this.countries;
            const term = this.search.toLowerCase();
            return this.countries.filter(c => c.name.toLowerCase().includes(term) || c.code.toLowerCase().includes(term));
        },
        select(country) {
            this.selected = country.code;
            this.selectedName = country.name;
            this.search = '';
            this.open = false;
            @if($wire)
                $wire.set('{{ $wire }}', country.code);
            @endif
        },
        clear() {
            this.selected = '';
            this.selectedName = '';
            this.search = '';
            @if($wire)
                $wire.set('{{ $wire }}', '');
            @endif
        }
    }"
    @click.away="open = false"
    class="relative"
>
    {{-- Hidden input for form submission --}}
    <input type="hidden" name="{{ $name }}" :value="selected">

    {{-- Display button --}}
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-left transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
    >
        <span
            x-text="selectedName || '{{ $placeholder }}'"
            :class="selectedName ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
        ></span>
        <span class="flex items-center gap-2">
            <template x-if="selected">
                <button type="button" @click.stop="clear()" class="p-1 text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
        style="display: none;"
    >
        {{-- Search input --}}
        <div class="border-b border-gray-200 p-2 dark:border-gray-700">
            <input
                type="text"
                x-model="search"
                @keydown.escape="open = false"
                x-ref="searchInput"
                x-init="$watch('open', (value) => { if (value) $nextTick(() => $refs.searchInput.focus()) })"
                placeholder="{{ __('Type to search...') }}"
                class="w-full rounded-md border-0 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-900 dark:text-white"
            >
        </div>

        {{-- Options list --}}
        <ul class="max-h-60 overflow-y-auto py-1">
            <template x-for="country in filtered" :key="country.code">
                <li>
                    <button
                        type="button"
                        @click="select(country)"
                        class="flex w-full items-center justify-between px-4 py-2 text-left hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                        :class="selected === country.code ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''"
                    >
                        <span class="text-sm text-gray-900 dark:text-white" x-text="country.name"></span>
                        <span class="text-xs text-gray-400" x-text="country.code"></span>
                    </button>
                </li>
            </template>
            <template x-if="filtered.length === 0">
                <li class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No countries found') }}
                </li>
            </template>
        </ul>
    </div>
</div>
