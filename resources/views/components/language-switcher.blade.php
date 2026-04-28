@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => ['label' => 'EN', 'flag' => '🇺🇸', 'name' => 'English'],
        'es' => ['label' => 'ES', 'flag' => '🇪🇸', 'name' => 'Español'],
        'pt' => ['label' => 'PT', 'flag' => '🇧🇷', 'name' => 'Português'],
    ];
    $currentPath = '/' . ltrim(request()->path(), '/');
    $existingQuery = collect(request()->query())->except('lang')->all();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.away="open = false"
        class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
        <span>{{ $locales[$currentLocale]['flag'] }}</span>
        <span>{{ $locales[$currentLocale]['label'] }}</span>
        <svg class="h-3.5 w-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-1 w-40 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
        style="display: none;">
        <div class="py-1">
            @foreach ($locales as $code => $info)
                @php
                    $query = array_merge($existingQuery, ['lang' => $code]);
                    $localizedPath = $currentPath . '?' . http_build_query($query);
                @endphp
                <a href="{{ $localizedPath }}"
                    class="flex items-center gap-2.5 px-3 py-2 text-sm {{ $code === $currentLocale ? 'bg-indigo-50 font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <span>{{ $info['flag'] }}</span>
                    <span>{{ $info['name'] }}</span>
                    @if ($code === $currentLocale)
                        <svg class="ml-auto h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
