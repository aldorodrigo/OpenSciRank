<x-layouts.app title="{{ $book->title }} - Editorial Standards Platform" description="Ficha pública del libro académico {{ $book->title }} en el índice de Editorial Standards Platform.">
    <x-slot:jsonLd>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Book",
        "name": {{ Js::from($book->title) }},
        "url": "{{ url('/book/' . $book->slug) }}",
        @if($book->isbn_print)"isbn": {{ Js::from($book->isbn_print) }},@endif
        @if($book->isbn_digital)"isbn": {{ Js::from($book->isbn_digital) }},@endif
        @if($book->description)"description": {{ Js::from(strip_tags($book->description)) }},@endif
        @if($book->publisher)"publisher": {
            "@type": "Organization",
            "name": {{ Js::from($book->publisher) }}
        },@endif
        @if($book->publication_year)"datePublished": "{{ $book->publication_year }}",@endif
        "inLanguage": "es",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Editorial Standards Platform",
            "url": "{{ url('/') }}"
        }
    }
    </script>
    </x-slot:jsonLd>

    <x-slot:header>true</x-slot:header>

    {{-- Breadcrumb --}}
    <div class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="/" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Inicio</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="/search?type=books" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Libros Académicos</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate text-gray-900 dark:text-white">{{ $book->title }}</span>
            </nav>
        </div>
    </div>

    {{-- Header / Identity --}}
    <section class="border-b border-gray-200 bg-white py-10 dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-8 md:flex-row md:items-start">
                {{-- Cover / Icon --}}
                <div class="shrink-0">
                    @if($book->cover ?? null)
                        <img src="{{ Storage::url($book->cover) }}" alt="{{ $book->title }}" class="h-40 w-32 rounded-xl object-cover shadow-lg">
                    @else
                        <div class="flex h-40 w-32 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 shadow-lg dark:from-indigo-900/50 dark:to-purple-900/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="flex flex-wrap items-start gap-3">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $book->title }}</h1>
                        <span class="mt-1 inline-flex shrink-0 items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                            📚 Libro Indexado
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($book->authors ?? null)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $book->authors }}
                            </span>
                        @endif
                        @if($book->publisher ?? null)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $book->publisher }}
                            </span>
                        @endif
                        @if($book->isbn ?? null)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                ISBN: {{ $book->isbn }}
                            </span>
                        @endif
                        @if($book->publication_year ?? null)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $book->publication_year }}
                            </span>
                        @endif
                        @if($book->language ?? null)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                {{ $book->language }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex shrink-0 flex-col gap-3">
                    @if($book->url ?? null)
                        <a href="{{ $book->url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Ver libro
                        </a>
                    @endif
                    <a href="/register"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-600 px-5 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400 dark:text-indigo-400 dark:hover:bg-indigo-900/20">
                        Indexar mi libro →
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section class="bg-gray-50 py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-5xl space-y-6">
                {{-- Bibliographic Information --}}
                <div class="rounded-xl bg-white p-8 shadow-sm dark:bg-gray-900">
                    <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Información Bibliográfica</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @php $fields = [
                            'Título' => $book->title ?? null,
                            'Autores / Editores' => $book->authors ?? null,
                            'Editorial' => $book->publisher ?? null,
                            'ISBN' => $book->isbn ?? null,
                            'Año de publicación' => $book->publication_year ?? null,
                            'Idioma' => $book->language ?? null,
                            'País' => $book->country ?? null,
                            'Área temática' => $book->subject_area ?? null,
                            'DOI' => $book->doi ?? null,
                            'URL' => $book->url ?? null,
                        ]; @endphp
                        @foreach($fields as $label => $value)
                            @if($value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    @if($label === 'URL' || $label === 'DOI')
                                        <a href="{{ $value }}" target="_blank" rel="noopener" class="break-all text-indigo-600 hover:underline dark:text-indigo-400">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                            @endif
                        @endforeach
                    </dl>
                </div>

                @if($book->abstract ?? null)
                <div class="rounded-xl bg-white p-8 shadow-sm dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Descripción / Resumen</h2>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $book->abstract }}</p>
                </div>
                @endif

                {{-- Indexing Notice --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-800 dark:bg-indigo-900/20">
                    <p class="text-sm text-indigo-800 dark:text-indigo-300">
                        <strong>📚 Nota sobre indexación de libros:</strong> Este libro forma parte del índice de Editorial Standards Platform para facilitar su descubrimiento dentro del ecosistema de investigación. La inclusión de libros en el índice no implica evaluación editorial de la obra.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-16 overflow-hidden bg-gray-50 dark:bg-gray-950">
        <div class="container mx-auto px-4 text-center">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-xl md:p-12">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-2xl font-bold sm:text-3xl">¿Tienes un libro académico?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-indigo-100">Indexa tu libro en Editorial Standards Platform y facilita su descubrimiento en el ecosistema científico.</p>
                    <a href="/register" class="mt-8 inline-flex items-center rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                        Indexar mi Libro — Gratis
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
