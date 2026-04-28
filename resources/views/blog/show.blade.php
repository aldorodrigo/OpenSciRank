<x-layouts.app title="{{ $post->getTranslationWithFallback('title') }} - Blog" description="{{ $post->getTranslationWithFallback('excerpt') }}">
    <x-slot:header>true</x-slot:header>

    {{-- Breadcrumb --}}
    <section class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('blog.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Blog</a>
                <span>/</span>
                <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $post->cat_label }}</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">{{ Str::limit($post->getTranslationWithFallback('title'), 50) }}</span>
            </nav>
        </div>
    </section>

    {{-- Article --}}
    <article class="bg-white py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl">

                {{-- Header --}}
                <header class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 px-3 py-1 text-xs font-semibold">{{ $post->cat_label }}</span>
                        @if($post->is_featured)
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">⭐ {{ __('Featured') }}</span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl lg:text-5xl leading-tight">{{ $post->getTranslationWithFallback('title') }}</h1>

                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ $post->getTranslationWithFallback('excerpt') }}</p>

                    <div class="mt-6 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>📅 {{ $post->published_at->translatedFormat('d \d\e F, Y') }}</span>
                        @if($post->read_time)
                        <span>⏱ {{ $post->read_time }} {{ __('read') }}</span>
                        @endif
                        @if($post->author)
                        <span>✍️ {{ $post->author->name }}</span>
                        @endif
                    </div>
                </header>

                {{-- Featured image or emoji --}}
                <div class="mb-10 overflow-hidden rounded-2xl">
                    @if($post->image_path)
                        <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->getTranslationWithFallback('title') }}" class="w-full h-auto object-cover">
                    @else
                        <div class="flex h-64 items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50">
                            <span class="text-9xl">{{ $post->emoji ?? '📝' }}</span>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="prose prose-lg prose-indigo dark:prose-invert max-w-none
                    prose-headings:font-bold prose-a:text-indigo-600 dark:prose-a:text-indigo-400
                    prose-img:rounded-xl prose-pre:bg-gray-900 dark:prose-pre:bg-gray-800">
                    {!! $post->getTranslationWithFallback('content') !!}
                </div>

                {{-- Back link --}}
                <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ __('Back to blog') }}
                    </a>
                </div>
            </div>
        </div>
    </article>

    {{-- Related posts --}}
    @if($related->count())
    <section class="bg-gray-50 py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Related articles') }}</h2>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($related as $relatedPost)
                <a href="{{ route('blog.show', $relatedPost->slug) }}" class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-lg dark:bg-gray-950">
                    <div class="flex h-48 items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700">
                        @if($relatedPost->image_path)
                            <img src="{{ Storage::url($relatedPost->image_path) }}" alt="{{ $relatedPost->getTranslationWithFallback('title') }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-6xl">{{ $relatedPost->emoji ?? '📝' }}</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <span class="inline-flex w-fit rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 px-3 py-1 text-xs font-semibold">{{ $relatedPost->cat_label }}</span>
                        <h3 class="mt-3 flex-1 font-bold text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $relatedPost->getTranslationWithFallback('title') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $relatedPost->getTranslationWithFallback('excerpt') }}</p>
                        <div class="mt-4 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $relatedPost->published_at->translatedFormat('M d, Y') }}</span>
                            @if($relatedPost->read_time)
                            <span>·</span>
                            <span>{{ $relatedPost->read_time }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</x-layouts.app>
