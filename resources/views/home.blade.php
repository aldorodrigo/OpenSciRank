<x-layouts.app title="Editorial Standards Platform - Evaluación Editorial para Revistas Científicas" description="Plataforma global de evaluación técnica y visibilidad para revistas científicas. Criterios transparentes, Editorial Standards Seal y directorio de publicaciones académicas.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero Section --}}
    <section class="relative min-h-[80vh] flex items-center overflow-hidden bg-indigo-900 text-white">
        {{-- High-end background --}}
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600 opacity-90"></div>
            {{-- Decorative mesh/pattern --}}
            <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            {{-- Animated-like blurs --}}
            <div class="absolute -left-1/4 -top-1/4 h-[80%] w-[80%] rounded-full bg-indigo-500/30 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[80%] w-[80%] rounded-full bg-purple-500/20 blur-[120px]"></div>
        </div>

        <div class="container relative z-10 mx-auto px-4 py-24 text-center">
            <div class="mx-auto mb-8 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur-md border border-white/20">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                🌍 Estándares Globales de Calidad Editorial
            </div>
            <h1 class="mx-auto max-w-4xl text-5xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl leading-tight">
                Fortaleciendo la calidad editorial de la ciencia
            </h1>
            <p class="mx-auto mt-8 max-w-2xl text-xl text-indigo-100/90 leading-relaxed">
                Evaluación técnica independiente para revistas científicas e índice global de libros académicos. Transparencia, rigor y reconocimiento internacional.
            </p>
            <div class="mt-12 flex flex-col items-center justify-center gap-5 sm:flex-row">
                <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-2xl bg-white px-10 py-5 text-lg font-bold text-indigo-700 shadow-2xl transition-all hover:scale-105 hover:bg-indigo-50 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-6 w-6 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Registrar mi Revista Gratis
                </a>
                <a href="/pricing" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10 px-10 py-5 text-lg font-bold text-white backdrop-blur-md transition-all hover:border-white hover:bg-white/20 active:scale-95">
                    Solicitar Evaluación →
                </a>
            </div>
            <div class="mt-12 flex items-center justify-center gap-8 text-indigo-100/60 font-medium">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Criterios Públicos
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Independencia Técnica
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section (Dynamic) --}}
    @php
        try {
            $journalCount = \App\Models\Journal::where('status', 'indexed')->count();
            $bookCount = \App\Models\Book::where('status', 'indexed')->count();
            $countryCount = \App\Models\Journal::where('status', 'indexed')->whereNotNull('country_code')->distinct('country_code')->count('country_code');
        } catch (\Exception $e) {
            $journalCount = 0;
            $bookCount = 0;
            $countryCount = 0;
        }
    @endphp
    <section class="border-b border-gray-200 bg-white py-12 dark:border-gray-800 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div class="text-center">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($journalCount) }}</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Revistas en el directorio</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($bookCount) }}</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Libros indexados</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">{{ $countryCount ?: '10+' }}</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Países</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">5</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Áreas de Evaluación</div>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">¿Cómo funciona?</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">Un proceso transparente de registro, evaluación y reconocimiento editorial.</p>
            </div>
            <div class="relative mx-auto max-w-5xl">
                <div class="absolute left-1/2 top-12 hidden h-0.5 w-2/3 -translate-x-1/2 bg-gradient-to-r from-indigo-200 via-purple-200 to-emerald-200 dark:from-indigo-800 dark:via-purple-800 dark:to-emerald-800 md:block"></div>
                <div class="grid gap-8 md:grid-cols-3">
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-indigo-100 text-4xl dark:bg-indigo-900/50">📝</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Paso 1</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Registro y Listado</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Registra tu revista de forma gratuita. Obtén una ficha pública con información editorial básica en nuestro directorio.</p>
                    </div>
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-purple-100 text-4xl dark:bg-purple-900/50">🔍</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-purple-600 dark:text-purple-400">Paso 2</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Evaluación Editorial</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Solicita una evaluación técnica editorial. Nuestro equipo revisa 5 áreas con indicadores verificables y asigna un Editorial Standards Score.</p>
                    </div>
                    <div class="relative text-center">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-emerald-100 text-4xl dark:bg-emerald-900/50">✅</div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Paso 3</div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Informe y Sello</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Recibe un informe técnico detallado. Si alcanzas ≥75 puntos y cumples los indicadores críticos, obtén el Editorial Standards Seal.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Principios del proyecto</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">Un sistema basado en transparencia, rigor e independencia.</p>
            </div>
            <div class="grid gap-8 md:grid-cols-3 lg:grid-cols-5">
                @php
                    $principles = [
                        ['🔍', 'Transparencia', 'Los criterios de evaluación editorial son públicos y accesibles.'],
                        ['🧪', 'Rigor Técnico', 'Las evaluaciones se basan en indicadores verificables.'],
                        ['⚖️', 'Independencia', 'Los resultados no dependen del pago por el proceso de evaluación.'],
                        ['🔄', 'Mejora Continua', 'Las evaluaciones buscan fortalecer la calidad editorial de las revistas.'],
                        ['🌐', 'Acceso al Conocimiento', 'Promovemos la visibilidad de publicaciones científicas.'],
                    ];
                @endphp
                @foreach($principles as [$icon, $title, $desc])
                <div class="rounded-xl bg-white p-6 text-center shadow-lg transition hover:shadow-xl dark:bg-gray-900">
                    <div class="mb-3 text-3xl">{{ $icon }}</div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Participation Model --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Modelo de participación</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">Las revistas pueden ser listadas, evaluadas y, si cumplen los estándares, obtener el sello editorial.</p>
            </div>
            <div class="mx-auto grid max-w-5xl gap-8 md:grid-cols-3">
                <div class="rounded-xl border-2 border-gray-200 bg-white p-8 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 text-3xl">📋</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Listed Journal</h3>
                    <p class="mb-4 text-sm font-medium text-indigo-600 dark:text-indigo-400">Revista Listada</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Ficha pública de la revista</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Visibilidad dentro del directorio</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Información editorial básica</li>
                        <li class="flex items-start gap-2"><span class="text-gray-400">—</span> <span class="text-gray-400">No implica evaluación</span></li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-emerald-600 dark:text-emerald-400">Gratuito</p>
                </div>

                <div class="rounded-xl border-2 border-purple-200 bg-white p-8 shadow-lg dark:border-purple-700 dark:bg-gray-800">
                    <div class="mb-4 text-3xl">📊</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Evaluated Journal</h3>
                    <p class="mb-4 text-sm font-medium text-purple-600 dark:text-purple-400">Revista Evaluada</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Informe técnico completo</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Resultado por criterios</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Editorial Standards Score</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Recomendaciones de mejora</li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-purple-600 dark:text-purple-400">Proceso de evaluación</p>
                </div>

                <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-8 shadow-lg dark:border-emerald-700 dark:bg-emerald-900/20">
                    <div class="mb-4 text-3xl">🏅</div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Certified Journal</h3>
                    <p class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">Editorial Standards Seal</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Score ≥ 75 puntos</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Cumple indicadores críticos</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Sello editorial verificable</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span> Certificado descargable</li>
                    </ul>
                    <p class="mt-6 text-center text-sm font-semibold text-emerald-600 dark:text-emerald-400">Basado en cumplimiento</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Journals --}}
    @php
        try {
            $featuredJournals = \App\Models\Journal::where('status', 'indexed')
                ->whereNotNull('current_score')
                ->orderByDesc('current_score')
                ->take(6)
                ->get();
        } catch (\Exception $e) {
            $featuredJournals = collect();
        }
    @endphp
    @if($featuredJournals->count() > 0)
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Revistas en nuestro directorio</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-400">Publicaciones que participan en el sistema de evaluación editorial.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($featuredJournals as $fj)
                <a href="{{ route('journal.show', $fj->slug) }}" class="group rounded-xl border border-gray-200 bg-white p-6 transition hover:border-indigo-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600" style="text-decoration:none;">
                    <div class="mb-4 flex items-center gap-4">
                        @if($fj->logo)
                            <img src="{{ Storage::url($fj->logo) }}" alt="{{ $fj->title }}" class="h-12 w-12 rounded-lg object-cover">
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $fj->title }}</h3>
                            @if($fj->publishing_institution)
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $fj->publishing_institution }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        @if($fj->current_score && $fj->current_score >= 75)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400">
                                🏅 Certified
                            </span>
                        @elseif($fj->current_score)
                            <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-800 dark:bg-purple-900/50 dark:text-purple-400">
                                Evaluated
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Listed
                            </span>
                        @endif
                        @if($fj->current_score)
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-indigo-600" style="width: {{ min($fj->current_score, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $fj->current_score }}/100</span>
                            </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="/search" class="inline-flex items-center font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400" style="cursor:pointer;">
                    Ver directorio completo →
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- CTA Section --}}
    <section class="relative py-20 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>
                <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-purple-400/20 blur-3xl"></div>

                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">¿Quieres fortalecer la calidad editorial de tu revista?</h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-indigo-100 sm:text-xl">
                        Registra tu revista de forma gratuita. Solicita una evaluación técnica basada en criterios transparentes y obtén el Editorial Standards Seal si cumples los estándares.
                    </p>
                    
                    <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/register" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-10 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            <span class="relative">Registrar mi Revista — Gratis</span>
                        </a>
                        <a href="/contact" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-10 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            Contactar al equipo
                        </a>
                    </div>
                    
                    <div class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-4 text-sm font-medium text-white/70">
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             Registro gratuito
                        </span>
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             Criterios técnicos públicos
                        </span>
                        <span class="flex items-center gap-2">
                             <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                             Independencia garantizada
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
