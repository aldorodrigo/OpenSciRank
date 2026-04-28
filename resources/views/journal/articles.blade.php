<x-layouts.app
    :title="__('Articles of') . ' ' . $journal->getTranslationWithFallback('title') . ' - Editorial Standards Platform'"
    :description="__('Complete list of articles published in') . ' ' . $journal->getTranslationWithFallback('title')"
>
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-8 dark:bg-gray-950">
        <div class="mx-auto max-w-5xl px-4">
            {{-- Breadcrumbs --}}
            <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                <a href="/" class="hover:text-indigo-600">{{ __('Home') }}</a>
                <span class="mx-2">/</span>
                <a href="/search" class="hover:text-indigo-600">{{ __('Search') }}</a>
                <span class="mx-2">/</span>
                <a href="{{ route('journal.show', $journal->slug) }}" class="hover:text-indigo-600">{{ Illuminate\Support\Str::limit($journal->getTranslationWithFallback('title'), 40) }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-white">{{ __('Articles') }}</span>
            </nav>

            {{-- Header Card --}}
            <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📄 {{ __('Published Articles') }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $articles->total() }} {{ __('articles in') }}
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $journal->getTranslationWithFallback('title') }}</span>
                        </p>
                    </div>
                    <a href="{{ route('journal.show', $journal->slug) }}"
                        class="inline-flex items-center gap-2 self-start rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to journal') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-8">
        @if($articles->isEmpty())
            <div class="rounded-xl bg-white p-12 text-center shadow-lg dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('No articles found.') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($articles as $article)
                    <div class="rounded-xl bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:bg-gray-900">
                        <h3 class="text-base font-semibold leading-snug text-gray-900 dark:text-white">
                            @if($article->url)
                                <a href="{{ $article->url }}" target="_blank" rel="noopener" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $article->title }}
                                </a>
                            @else
                                {{ $article->title }}
                            @endif
                        </h3>

                        @if($article->authors_json)
                            <div class="mt-2 flex flex-col gap-1 text-sm text-gray-500 dark:text-gray-400">
                                @foreach($article->authors_json as $author)
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900 dark:text-white">👤 {{ $author['name'] }}</span>
                                        @if(!empty($author['affiliation']))
                                            <span class="text-xs text-gray-400 dark:text-gray-500" title="{{ $author['affiliation'] }}">
                                                ({{ Illuminate\Support\Str::limit($author['affiliation'], 50) }})
                                            </span>
                                        @endif
                                        @if(!empty($author['orcid']))
                                            <a href="https://orcid.org/{{ $author['orcid'] }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center text-[#A6CE39] hover:text-[#8dae30]"
                                                title="ORCID: {{ $author['orcid'] }}">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                                    <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zM7.369 4.378c.525 0 .947.431.947.947s-.422.947-.947.947a.95.95 0 0 1-.947-.947c0-.525.422-.947.947-.947zm-.722 3.038h1.444v10.041H6.647V7.416zm3.562 0h3.9c3.712 0 5.344 2.653 5.344 5.025 0 2.578-2.016 5.025-5.325 5.025h-3.919V7.416zm1.444 1.306v7.444h2.297c3.272 0 4.022-2.484 4.022-3.722 0-2.016-1.212-3.722-4.097-3.722h-2.222z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($article->authors)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                👤 {{ Illuminate\Support\Str::limit($article->authors, 120) }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-4">
                            @if($article->date)
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    📅 {{ $article->date->format('d/m/Y') }}
                                </span>
                            @endif
                            @if($article->language)
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ strtoupper($article->language) }}</span>
                            @endif
                            @if($article->url)
                                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    {{ __('View article') }}
                                </a>
                            @endif
                            @if($article->pdf_url)
                                <a href="{{ $article->pdf_url }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    PDF
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
