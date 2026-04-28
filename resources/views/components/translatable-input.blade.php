@props([
    'name',
    'label',
    'model',
    'locales' => ['es', 'en', 'pt'],
    'primary' => 'es',
    'required' => false,
    'type' => 'text',
    'placeholder' => null,
    'help' => null,
    'maxlength' => null,
])

<div x-data="{ tab: '{{ $primary }}' }" class="translatable-field">
    {{-- Label + tabs en la misma fila --}}
    <div class="mb-2 flex items-center justify-between gap-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>

        <div class="inline-flex items-center gap-0.5" role="tablist" aria-label="{{ __('Idioma') }}">
            @foreach($locales as $locale)
                <button
                    type="button"
                    @click="tab = '{{ $locale }}'"
                    :class="tab === '{{ $locale }}'
                        ? 'text-indigo-700 dark:text-indigo-300 font-semibold'
                        : 'text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="relative px-1.5 py-0.5 text-[11px] uppercase tracking-wide transition focus:outline-none"
                    title="{{ $locale === $primary ? __('Idioma principal') : strtoupper($locale) }}"
                >
                    {{ strtoupper($locale) }}
                    @if($locale === $primary)
                        <span class="text-indigo-500" aria-hidden="true">*</span>
                    @endif
                    <span
                        x-show="$wire.get('{{ $model }}.{{ $locale }}') && $wire.get('{{ $model }}.{{ $locale }}').toString().trim() !== ''"
                        x-cloak
                        class="ml-0.5 text-emerald-500 text-[10px]"
                        aria-label="{{ __('Has content') }}"
                    >&#10003;</span>
                </button>
                @if(!$loop->last)
                    <span class="text-gray-300 dark:text-gray-600 text-[10px]">|</span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Inputs (one per locale, all in DOM) --}}
    @foreach($locales as $locale)
        @php
            $isPrimary = $locale === $primary;
            $ph = is_array($placeholder) ? ($placeholder[$locale] ?? '') : ($placeholder ?? '');
        @endphp
        <div x-show="tab === '{{ $locale }}'" x-cloak style="display: none;">
            <input
                type="{{ $type }}"
                wire:model.live.debounce.500ms="{{ $model }}.{{ $locale }}"
                @if($isPrimary && $required) required @endif
                @if($maxlength) maxlength="{{ $maxlength }}" @endif
                @if($ph) placeholder="{{ $ph }}" @endif
                id="{{ $name }}_{{ $locale }}"
                name="{{ $name }}_{{ $locale }}"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >

            @if($isPrimary)
                @error($model.'.'.$primary)
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            @endif
        </div>
    @endforeach

    @if($help)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>
    @endif
</div>
