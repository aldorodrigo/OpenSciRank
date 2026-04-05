<x-layouts.app title="Blog - Editorial Standards Platform" description="Recursos, guías y noticias sobre Ciencia Abierta, indexación de revistas científicas y buenas prácticas editoriales.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">Blog & Recursos</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">Guías, noticias y buenas prácticas sobre Ciencia Abierta e indexación científica.</p>
        </div>
    </section>

    {{-- Categories --}}
    <section class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Categorías:</span>
                @php $activeCategory = request('category'); @endphp
                <a href="{{ route('blog.index') }}" class="rounded-full px-4 py-1.5 text-sm font-medium transition
                    {{ !$activeCategory ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400' }}">
                    Todos
                </a>
                @foreach(\App\Models\CmsPost::CATEGORIES as $slug => $cat)
                <a href="{{ route('blog.index', ['category' => $slug]) }}" class="rounded-full px-4 py-1.5 text-sm font-medium transition
                    {{ $activeCategory === $slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400' }}">
                    {{ $cat['label'] }}
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Blog Grid --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">

            {{-- Featured post --}}
            @if($featured && !$activeCategory)
            <div class="mb-12">
                <a href="{{ route('blog.show', $featured->slug) }}" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-xl dark:bg-gray-900 md:flex-row">
                    <div class="flex h-64 items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100 p-12 dark:from-indigo-900/50 dark:to-purple-900/50 md:h-auto md:w-72 md:shrink-0">
                        @if($featured->image_path)
                            <img src="{{ Storage::url($featured->image_path) }}" alt="{{ $featured->title }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-8xl">{{ $featured->emoji ?? '📝' }}</span>
                        @endif
                    </div>
                    <div class="p-8">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full {{ $featured->cat_color }} px-3 py-1 text-xs font-semibold">{{ $featured->cat_label }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">⭐ Destacado</span>
                        </div>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400 sm:text-3xl">{{ $featured->title }}</h2>
                        <p class="mt-3 text-gray-600 dark:text-gray-400">{{ $featured->excerpt }}</p>
                        <div class="mt-6 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>📅 {{ $featured->published_at->translatedFormat('M d, Y') }}</span>
                            @if($featured->read_time)
                            <span>⏱ {{ $featured->read_time }} lectura</span>
                            @endif
                        </div>
                        <span class="mt-6 inline-flex items-center font-semibold text-indigo-600 transition group-hover:gap-2 dark:text-indigo-400">
                            Leer artículo <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>
            </div>
            @endif

            {{-- Grid --}}
            @if($posts->count())
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    @if(!$post->is_featured || $activeCategory)
                    <a href="{{ route('blog.show', $post->slug) }}" class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-lg dark:bg-gray-900">
                        <div class="flex h-48 items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700">
                            @if($post->image_path)
                                <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-6xl">{{ $post->emoji ?? '📝' }}</span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="inline-flex w-fit rounded-full {{ $post->cat_color }} px-3 py-1 text-xs font-semibold">{{ $post->cat_label }}</span>
                            <h3 class="mt-3 flex-1 font-bold text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $post->title }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $post->excerpt }}</p>
                            <div class="mt-4 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $post->published_at->translatedFormat('M d, Y') }}</span>
                                @if($post->read_time)
                                <span>·</span>
                                <span>{{ $post->read_time }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12">
                {{ $posts->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No hay artículos disponibles en esta categoría.</p>
                <a href="{{ route('blog.index') }}" class="mt-4 inline-flex items-center font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    ← Ver todos los artículos
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- Newsletter CTA --}}
    <section class="relative py-16 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-12">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative z-10 mx-auto max-w-2xl">
                    <h2 class="text-3xl font-extrabold tracking-tight">Suscríbete al newsletter</h2>
                    <p class="mt-4 text-lg text-indigo-100">Recibe las últimas noticias, guías y actualizaciones sobre Ciencia Abierta e indexación directamente en tu email.</p>
                    <form class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <input type="email" placeholder="tu@email.com" class="flex-1 rounded-xl bg-white/10 px-4 py-4 text-white placeholder-indigo-200 backdrop-blur-sm border border-white/20 focus:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                        <button type="submit" class="rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:bg-indigo-50 active:scale-95">
                            Suscribirme
                        </button>
                    </form>
                    <p class="mt-4 text-xs font-medium text-white/60">Sin spam. Puedes darte de baja en cualquier momento.</p>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
