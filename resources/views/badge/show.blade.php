<x-layouts.app title="Sello Editorial Embebible - {{ $journal->title }} - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-8 dark:bg-gray-950">
        <div class="container mx-auto max-w-2xl px-4">

            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('app.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Volver al Panel
                </a>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">Sello Editorial Embebible</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ $journal->title }}</p>
            </div>

            {{-- Preview --}}
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Vista Previa</h2>
                <div class="flex justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-800">
                    <img src="{{ route('badge.svg', $journal->slug) }}" alt="Editorial Standards Seal - {{ $journal->title }}" class="h-auto w-full">
                </div>
                <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                    Sello vigente hasta {{ $journal->seal_expires_at->format('d/m/Y') }}
                </p>
            </div>

            {{-- Embed Codes --}}
            <div class="mt-6 space-y-6">

                {{-- HTML --}}
                <div x-data="{ copied: false }" class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Codigo HTML</h2>
                        <button
                            @click="navigator.clipboard.writeText($refs.htmlCode.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                        </button>
                    </div>
                    <pre x-ref="htmlCode" class="overflow-x-auto rounded-lg bg-gray-100 p-4 text-sm text-gray-800 dark:bg-gray-800 dark:text-gray-200"><code>&lt;a href="{{ url('/journal/' . $journal->slug) }}" target="_blank" rel="noopener"&gt;
  &lt;img src="{{ route('badge.svg', $journal->slug) }}" alt="Editorial Standards Seal - {{ $journal->title }}" style="height:80px"&gt;
&lt;/a&gt;</code></pre>
                </div>

                {{-- Markdown --}}
                <div x-data="{ copied: false }" class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Codigo Markdown</h2>
                        <button
                            @click="navigator.clipboard.writeText($refs.mdCode.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                        </button>
                    </div>
                    <pre x-ref="mdCode" class="overflow-x-auto rounded-lg bg-gray-100 p-4 text-sm text-gray-800 dark:bg-gray-800 dark:text-gray-200"><code>[![Editorial Standards Seal - {{ $journal->title }}]({{ route('badge.svg', $journal->slug) }})]({{ url('/journal/' . $journal->slug) }})</code></pre>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="mt-6 rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <h2 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Instrucciones</h2>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        Copia el codigo HTML o Markdown y pegalo en tu sitio web (homepage, pagina "Acerca de", o footer).
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        El sello se actualiza automaticamente. Si vence, la imagen cambiara a "Sello Expirado".
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        Al hacer clic en el sello, los visitantes seran redirigidos a la ficha publica de tu revista donde podran verificar su vigencia.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>
