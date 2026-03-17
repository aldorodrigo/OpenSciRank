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
                @php
                    $cats = ['Todos', 'Ciencia Abierta', 'Indexación', 'Criterios', 'Guías', 'Casos de Éxito', 'Novedades'];
                @endphp
                @foreach($cats as $i => $cat)
                <a href="#" class="rounded-full px-4 py-1.5 text-sm font-medium transition
                    {{ $i === 0 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400' }}">
                    {{ $cat }}
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Blog Grid --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            @php
                $posts = [
                    [
                        'cat' => 'Guías',
                        'catColor' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400',
                        'emoji' => '📋',
                        'title' => 'Cómo preparar tu revista científica para la indexación',
                        'excerpt' => 'Una guía paso a paso para que tu revista cumpla con los criterios básicos antes de postular a la evaluación de Editorial Standards Platform.',
                        'date' => 'Mar 10, 2026',
                        'readTime' => '8 min',
                        'featured' => true,
                    ],
                    [
                        'cat' => 'Ciencia Abierta',
                        'catColor' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                        'emoji' => '🌍',
                        'title' => 'El estado de la Ciencia Abierta en América Latina en 2026',
                        'excerpt' => 'Un análisis del crecimiento del acceso abierto en la región, los desafíos pendientes y las políticas nacionales que impulsan el cambio.',
                        'date' => 'Mar 5, 2026',
                        'readTime' => '12 min',
                        'featured' => false,
                    ],
                    [
                        'cat' => 'Criterios',
                        'catColor' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                        'emoji' => '🏆',
                        'title' => 'Los 10 criterios más importantes para obtener el Nivel A',
                        'excerpt' => 'Aprende cuáles son los indicadores con mayor peso en nuestra metodología y cómo asegurarte de cumplirlos.',
                        'date' => 'Feb 28, 2026',
                        'readTime' => '6 min',
                        'featured' => false,
                    ],
                    [
                        'cat' => 'Casos de Éxito',
                        'catColor' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400',
                        'emoji' => '✅',
                        'title' => 'De Nivel C a Nivel A: la historia de Revista de Biociencias',
                        'excerpt' => 'Entrevistamos a los editores de esta publicación que logró subir dos niveles en menos de un año implementando mejoras clave.',
                        'date' => 'Feb 20, 2026',
                        'readTime' => '10 min',
                        'featured' => false,
                    ],
                    [
                        'cat' => 'Novedades',
                        'catColor' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-400',
                        'emoji' => '🚀',
                        'title' => 'Editorial Standards Platform incorpora evaluación de libros académicos',
                        'excerpt' => 'Anunciamos la expansión de nuestra plataforma para incluir monografías y libros científicos de acceso abierto.',
                        'date' => 'Feb 10, 2026',
                        'readTime' => '4 min',
                        'featured' => false,
                    ],
                    [
                        'cat' => 'Indexación',
                        'catColor' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400',
                        'emoji' => '🔗',
                        'title' => 'OAI-PMH: qué es y por qué tu revista debe implementarlo',
                        'excerpt' => 'Explicamos el protocolo de interoperabilidad OAI-PMH, cómo funciona y sus beneficios para la visibilidad de tu publicación.',
                        'date' => 'Ene 28, 2026',
                        'readTime' => '7 min',
                        'featured' => false,
                    ],
                ];
            @endphp

            {{-- Featured post --}}
            @php $featured = collect($posts)->firstWhere('featured', true); @endphp
            @if($featured)
            <div class="mb-12">
                <a href="#" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-xl dark:bg-gray-900 md:flex-row">
                    <div class="flex h-64 items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100 p-12 text-8xl dark:from-indigo-900/50 dark:to-purple-900/50 md:h-auto md:w-72 md:shrink-0">
                        {{ $featured['emoji'] }}
                    </div>
                    <div class="p-8">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full {{ $featured['catColor'] }} px-3 py-1 text-xs font-semibold">{{ $featured['cat'] }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">⭐ Destacado</span>
                        </div>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400 sm:text-3xl">{{ $featured['title'] }}</h2>
                        <p class="mt-3 text-gray-600 dark:text-gray-400">{{ $featured['excerpt'] }}</p>
                        <div class="mt-6 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>📅 {{ $featured['date'] }}</span>
                            <span>⏱ {{ $featured['readTime'] }} lectura</span>
                        </div>
                        <span class="mt-6 inline-flex items-center font-semibold text-indigo-600 transition group-hover:gap-2 dark:text-indigo-400">
                            Leer artículo <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>
            </div>
            @endif

            {{-- Grid --}}
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    @if(!$post['featured'])
                    <a href="#" class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-lg dark:bg-gray-900">
                        <div class="flex h-48 items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-6xl dark:from-gray-800 dark:to-gray-700">
                            {{ $post['emoji'] }}
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="inline-flex w-fit rounded-full {{ $post['catColor'] }} px-3 py-1 text-xs font-semibold">{{ $post['cat'] }}</span>
                            <h3 class="mt-3 flex-1 font-bold text-gray-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $post['title'] }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $post['excerpt'] }}</p>
                            <div class="mt-4 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $post['date'] }}</span>
                                <span>·</span>
                                <span>{{ $post['readTime'] }}</span>
                            </div>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>

            {{-- Load More --}}
            <div class="mt-12 text-center">
                <button class="inline-flex items-center gap-2 rounded-lg border border-indigo-600 px-8 py-3 font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400 dark:text-indigo-400 dark:hover:bg-indigo-900/20">
                    Cargar más artículos
                </button>
            </div>
        </div>
    </section>

    {{-- Newsletter CTA --}}
    <section class="relative py-16 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-12">
                {{-- Decorative background elements --}}
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
