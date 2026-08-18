<x-layouts.app :title="$book->getTranslationWithFallback('title') . ' - Editorial Standards Platform'" :description="__('Public profile of the academic book') . ' ' . $book->getTranslationWithFallback('title') . ' ' . __('in the Editorial Standards Platform index.')">
    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => $book->getTranslationWithFallback('title'),
            'url' => url('/book/' . $book->slug),
            'isbn' => $book->isbn_print ?? $book->isbn_online ?? $book->isbn ?? null,
            'description' => $book->getTranslationWithFallback('abstract')
                ? strip_tags($book->getTranslationWithFallback('abstract'))
                : null,
            'publisher' => $book->publisher
                ? ['@type' => 'Organization', 'name' => $book->publisher]
                : null,
            'datePublished' => $book->publication_year ? (string) $book->publication_year : null,
            'inLanguage' => $book->primary_language ?? 'es',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Editorial Standards Platform',
                'url' => url('/'),
            ],
        ]);
    @endphp
    <x-slot:jsonLd>
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:jsonLd>

    @php
        // `authors` es un HasMany a BookAuthor: interpolarlo directamente
        // imprimía el JSON de la relación en la ficha pública (issue #74).
        $bookAuthors = $book->authors;

        $authorNames = $bookAuthors->pluck('full_name')->filter()->implode(', ');

        $authorDetails = $bookAuthors
            ->map(function ($author) {
                $line = $author->full_name;

                if ($role = \App\Support\BookVocabulary::label('author_role', $author->role)) {
                    $line .= ' ('.$role.')';
                }

                if (filled($author->affiliation)) {
                    $line .= ' — '.$author->affiliation;
                }

                return $line;
            })
            ->filter()
            ->all();

        $publisherCountry = $book->publisher_country
            ? (\App\Support\Countries::getName($book->publisher_country) ?? $book->publisher_country)
            : null;

        $primaryLanguage = \App\Support\BookVocabulary::label('language', $book->primary_language);
    @endphp

    <x-slot:header>true</x-slot:header>

    {{-- Breadcrumb --}}
    <div class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="/" class="transition hover:text-brand dark:hover:text-blue-400">{{ __('Home') }}</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="/search?type=books" class="transition hover:text-brand dark:hover:text-blue-400">{{ __('Academic Books') }}</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate text-gray-900 dark:text-white">{{ $book->getTranslationWithFallback('title') }}</span>
            </nav>
        </div>
    </div>

    {{-- Header / Identity --}}
    <section class="border-b border-gray-200 bg-white py-10 dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-8 md:flex-row md:items-start">
                {{-- Cover / Icon --}}
                <div class="shrink-0">
                    @if($book->cover_image ?? null)
                        <img src="{{ Storage::disk('public')->url($book->cover_image) }}" alt="{{ $book->getTranslationWithFallback('title') }}" class="h-40 w-32 rounded-xl object-cover shadow-lg">
                    @else
                        <div class="flex h-40 w-32 items-center justify-center rounded-xl bg-blue-100 shadow-lg dark:from-blue-900/50 dark:to-blue-900/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="flex flex-wrap items-start gap-3">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $book->getTranslationWithFallback('title') }}</h1>
                        <span class="mt-1 inline-flex shrink-0 items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-brand dark:bg-blue-900/50 dark:text-blue-300">
                            📚 {{ __('Indexed Book') }}
                        </span>
                        {{-- Sprint 3 #20: badge prominente para libros destacados aún vigentes. --}}
                        @if($book->is_featured && $book->featured_until && $book->featured_until->toDateString() >= now()->toDateString())
                            <span class="mt-1 inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                ★ {{ __('Featured') }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($authorNames)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $authorNames }}
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
                        @if($primaryLanguage)
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                {{ $primaryLanguage }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex shrink-0 flex-col gap-3">
                    @if($book->landing_url ?? null)
                        <a href="{{ $book->landing_url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            {{ __('View book') }}
                        </a>
                    @endif
                    <a href="/register"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-brand px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20">
                        {{ __('Index my Book →') }}
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
                    <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('Bibliographic Information') }}</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        {{-- Los nombres de columna son los reales del modelo: la versión
                             anterior pedía `language`/`country`/`subject_area`/`url`, que no
                             existen, y la ficha quedaba casi vacía (issue #74). --}}
                        @php $fields = [
                            'Title'              => $book->getTranslationWithFallback('title') ?: null,
                            'Authors / Editors'  => $authorDetails ?: null,
                            'Publisher'          => $book->publisher ?? null,
                            'ISBN'               => $book->isbn ?? null,
                            'Publication year'   => $book->publication_year ?? null,
                            'Language'           => $primaryLanguage,
                            'Country'            => $publisherCountry,
                            'Subject area'       => $book->main_discipline ?? null,
                            'DOI'                => $book->doi ?? null,
                            'URL'                => $book->landing_url ?? null,
                        ]; @endphp
                        @foreach($fields as $label => $value)
                            @if($value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __($label) }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    @if(is_array($value))
                                        <ul class="space-y-0.5">
                                            @foreach($value as $line)
                                                <li>{{ $line }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif($label === 'URL' || $label === 'DOI')
                                        <a href="{{ $value }}" target="_blank" rel="noopener" class="break-all text-brand hover:underline dark:text-blue-400">{{ $value }}</a>
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
                    <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">{{ __('Description / Abstract') }}</h2>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $book->getTranslationWithFallback('abstract') }}</p>
                </div>
                @endif

                {{-- Indexing Notice --}}
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/20">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>📚 {{ __('Note on book indexing:') }}</strong> {{ __('This book is part of the Editorial Standards Platform index to facilitate its discovery within the research ecosystem. The inclusion of books in the index does not imply editorial evaluation of the work.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-16 overflow-hidden bg-gray-50 dark:bg-gray-950">
        <div class="container mx-auto px-4 text-center">
            <div class="relative overflow-hidden rounded-3xl bg-brand p-8 text-center text-white shadow-xl md:p-12">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-brand-deep"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-2xl font-bold sm:text-3xl">{{ __('Do you have an academic book?') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-blue-100">{{ __('Index your book in Editorial Standards Platform and facilitate its discovery in the scientific ecosystem.') }}</p>
                    <a href="/register" class="mt-8 inline-flex items-center rounded-xl bg-white px-8 py-4 font-bold text-brand shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                        {{ __('Index my Book — Free') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
