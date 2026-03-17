<x-layouts.app title="Directorio - Editorial Standards Platform" description="Explora el directorio de revistas científicas y libros académicos en Editorial Standards Platform. Filtra por estado, país, disciplina y más.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-14 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">Directorio de Publicaciones</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">Explora revistas científicas y libros académicos. Evaluación editorial basada en criterios transparentes.</p>

            {{-- Stats --}}
            @php
                $journalCount = \App\Models\Journal::where('status', 'indexed')->count();
                $bookCount = \App\Models\Book::where('status', 'indexed')->count();
            @endphp
            <div class="mt-8 flex flex-wrap items-center justify-center gap-8">
                <div class="text-center">
                    <div class="text-3xl font-extrabold">{{ number_format($journalCount) }}</div>
                    <div class="mt-1 text-sm text-indigo-200">Revistas en el directorio</div>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <div class="text-3xl font-extrabold">{{ number_format($bookCount) }}</div>
                    <div class="mt-1 text-sm text-indigo-200">Libros indexados</div>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <div class="text-3xl font-extrabold">5</div>
                    <div class="mt-1 text-sm text-indigo-200">Áreas de evaluación</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Search & Results --}}
    <section class="bg-gray-50 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-10">
            <livewire:search-journals />
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-t border-gray-200 bg-white py-14 dark:border-gray-800 dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">¿No encuentras tu revista?</h2>
            <p class="mx-auto mt-3 max-w-lg text-gray-600 dark:text-gray-400">Si tu revista aún no está en nuestro directorio, regístrala gratis para formar parte de Editorial Standards Platform.</p>

            <a href="/register" class="mt-8 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar mi Revista — Gratis
            </a>
        </div>
    </section>

</x-layouts.app>
