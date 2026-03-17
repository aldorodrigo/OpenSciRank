<x-layouts.app title="Nosotros - Editorial Standards Platform" description="Conoce el propósito, principios y alcance de Editorial Standards Platform, plataforma global de evaluación editorial para revistas científicas.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-24 text-white">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
        <div class="container relative mx-auto px-4 text-center">
            <div class="mb-4 inline-flex items-center rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                🌍 Alcance global · Evaluación editorial transparente
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Editorial Standards Platform
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-indigo-100 sm:text-xl">
                Plataforma global de evaluación técnica y visibilidad para revistas científicas basada en criterios transparentes.
            </p>
        </div>
    </section>

    {{-- Purpose --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-2">
                <div class="rounded-2xl bg-indigo-50 p-8 dark:bg-indigo-900/20">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Propósito</h2>
                    <p class="text-gray-600 dark:text-gray-400">Contribuir a la transparencia y confianza en la comunicación científica mediante evaluación editorial estructurada para revistas científicas, visibilidad pública de resultados e indexación de libros académicos.</p>
                </div>
                <div class="rounded-2xl bg-purple-50 p-8 dark:bg-purple-900/20">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    </div>
                    <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Alcance</h2>
                    <p class="text-gray-600 dark:text-gray-400">El proyecto tiene un alcance global y busca contribuir al fortalecimiento de la comunicación científica internacional, trabajando con revistas científicas y libros académicos de todo el mundo.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">Problema que abordamos</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $problems = [
                            ['🔎', 'Dificultad para identificar revistas con buenas prácticas editoriales'],
                            ['🔒', 'Falta de transparencia en algunos procesos editoriales'],
                            ['📈', 'Crecimiento de revistas con estándares poco claros'],
                            ['📖', 'Baja visibilidad de libros académicos y científicos'],
                            ['🗂️', 'Dispersión de información sobre políticas editoriales'],
                        ];
                    @endphp
                    @foreach($problems as [$icon, $desc])
                    <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
                        <span class="text-2xl">{{ $icon }}</span>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Principles --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Principios del Proyecto</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">Cinco principios fundamentales que guían el sistema de evaluación editorial.</p>
            </div>
            <div class="mx-auto grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @php
                    $principles = [
                        ['🔍', 'Transparencia', 'Los criterios de evaluación editorial son públicos.'],
                        ['🧪', 'Rigor Técnico', 'Las evaluaciones se basan en indicadores verificables.'],
                        ['⚖️', 'Independencia', 'Los resultados no dependen del pago por el proceso.'],
                        ['🔄', 'Mejora Continua', 'Las evaluaciones buscan fortalecer la calidad editorial.'],
                        ['🌐', 'Acceso al Conocimiento', 'Promovemos la visibilidad de publicaciones científicas.'],
                    ];
                @endphp
                @foreach($principles as [$icon, $title, $desc])
                <div class="rounded-xl bg-gray-50 p-6 text-center shadow-sm transition hover:shadow-md dark:bg-gray-800">
                    <div class="mb-4 text-4xl">{{ $icon }}</div>
                    <h3 class="mb-2 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- What it is / What it is not --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-2">
                <div class="rounded-2xl bg-emerald-50 p-8 dark:bg-emerald-900/20">
                    <h3 class="mb-4 text-xl font-bold text-emerald-800 dark:text-emerald-300">✅ Qué es la plataforma</h3>
                    <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <li>• Un sistema de evaluación editorial para revistas científicas</li>
                        <li>• Un directorio estructurado de revistas académicas</li>
                        <li>• Un índice de libros científicos y académicos</li>
                        <li>• Una herramienta de transparencia editorial</li>
                        <li>• Un espacio de visibilidad para publicaciones científicas</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-red-50 p-8 dark:bg-red-900/20">
                    <h3 class="mb-4 text-xl font-bold text-red-800 dark:text-red-300">❌ Qué no es la plataforma</h3>
                    <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <li>• No es una editorial científica</li>
                        <li>• No es un sistema de publicación de artículos</li>
                        <li>• No garantiza aprobación de revistas</li>
                        <li>• No es un sistema de evaluación de libros</li>
                        <li>• No es un ranking basado en pagos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Target Audience --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Público Objetivo</h2>
            </div>
            <div class="mx-auto grid max-w-4xl gap-8 sm:grid-cols-2 md:grid-cols-4">
                @php
                    $audiences = [
                        ['📰', 'Editores de Revistas', 'Que desean evaluar y fortalecer sus estándares editoriales.'],
                        ['✍️', 'Autores e Investigadores', 'Que buscan identificar revistas confiables y publicaciones académicas.'],
                        ['🏛️', 'Instituciones Académicas', 'Que necesitan información estructurada sobre publicaciones científicas.'],
                        ['📚', 'Bibliotecas', 'Que buscan herramientas de descubrimiento de publicaciones académicas.'],
                    ];
                @endphp
                @foreach($audiences as [$icon, $title, $desc])
                <div class="text-center">
                    <div class="mb-3 text-4xl">{{ $icon }}</div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Institutional Formula --}}
    <section class="bg-gray-50 py-12 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl rounded-2xl bg-indigo-50 p-8 text-center dark:bg-indigo-900/20">
                <p class="text-lg font-medium text-gray-800 dark:text-gray-200">
                    "Las revistas pueden ser listadas en la plataforma, evaluadas mediante criterios técnicos transparentes y, si alcanzan el nivel de cumplimiento requerido, obtener el sello editorial de la plataforma."
                </p>
                <p class="mt-4 text-sm text-indigo-600 dark:text-indigo-400">— Fórmula institucional del proyecto</p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-20 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 text-center">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Únete a la comunidad científica</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-indigo-100 italic">"Evaluación técnica independiente para fortalecer la comunicación científica global."</p>
                    <p class="mx-auto mt-4 max-w-xl text-indigo-100">Ayudamos a las publicaciones a ganar visibilidad y credibilidad mediante estándares internacionales.</p>
                    
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            Registrar mi Revista — Gratis
                        </a>
                        <a href="/contact" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            Contactar al equipo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
