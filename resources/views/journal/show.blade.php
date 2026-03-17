<x-layouts.app
    :title="$journal->title . ' - Editorial Standards Platform'"
    :description="'Perfil de ' . $journal->title . '. Calificación, métricas y criterios de evaluación.'"
>
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-8 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            {{-- Breadcrumbs --}}
            <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                <a href="/" class="hover:text-indigo-600">Inicio</a>
                <span class="mx-2">/</span>
                <a href="/search" class="hover:text-indigo-600">Buscar</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-white">{{ $journal->title }}</span>
            </nav>

            {{-- Header Card --}}
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <div class="flex flex-col items-start gap-6 md:flex-row md:items-center">
                    {{-- Logo --}}
                    @if($journal->logo)
                        <img src="{{ Storage::url($journal->logo) }}" alt="{{ $journal->title }}" class="max-h-24 w-auto rounded-xl object-contain shadow-md" style="max-width: 112px;">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            @if($journal->current_level)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                                    @if($journal->current_level === 'A') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                                    @elseif($journal->current_level === 'B') bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400
                                    @endif
                                ">
                                    Nivel {{ $journal->current_level }}
                                </span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-sm font-medium
                                @if($journal->status === 'indexed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                                @elseif($journal->status === 'requires_changes') bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-400
                                @elseif($journal->status === 'submitted') bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400
                                @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                                @endif
                            ">
                                {{ match($journal->status) { 'draft' => 'Borrador', 'submitted' => 'Enviado', 'requires_changes' => 'Requiere correcciones', 'indexed' => 'Indexado', default => ucfirst($journal->status) } }}
                            </span>
                            @if($journal->is_open_access)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/50 dark:text-green-400">
                                    🔓 Open Access
                                </span>
                            @endif
                            @if($journal->license_type)
                                <span class="rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700 dark:bg-purple-900/50 dark:text-purple-400">
                                    📄 {{ $journal->license_type }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $journal->title }}</h1>
                        @if($journal->abbreviated_name)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">{{ $journal->abbreviated_name }}</p>
                        @endif
                        @if($journal->publisher || $journal->publishing_institution)
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ $journal->publisher }}
                                @if($journal->publisher && $journal->publishing_institution) — @endif
                                @if($journal->publishing_institution)
                                    <span class="text-gray-500">{{ $journal->publishing_institution }}</span>
                                @endif
                            </p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            @if($journal->country_code)
                                <span>🌍 {{ $journal->country_code }}</span>
                            @endif
                            @if($journal->start_year)
                                <span>📅 Desde {{ $journal->start_year }}</span>
                            @endif
                            @if($journal->publication_frequency)
                                <span>📰 {{ match($journal->publication_frequency) { 'annual' => 'Anual', 'biannual' => 'Semestral', 'quarterly' => 'Trimestral', 'bimonthly' => 'Bimestral', 'monthly' => 'Mensual', 'continuous' => 'Continua', default => $journal->publication_frequency } }}</span>
                            @endif
                            @if($journal->peer_review_type)
                                <span>🔍 {{ match($journal->peer_review_type) { 'double_blind' => 'Doble ciego', 'single_blind' => 'Simple ciego', 'open' => 'Revisión abierta', default => $journal->peer_review_type } }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Score --}}
                    @if($journal->current_score !== null)
                        <div class="text-center">
                            <div class="relative inline-flex flex-col items-center justify-center">
                                <svg class="h-32 w-32" viewBox="0 0 100 55">
                                    <!-- Background Track -->
                                    <path class="stroke-gray-200 dark:stroke-gray-700"
                                          d="M 10 50 A 40 40 0 0 1 90 50"
                                          fill="none" stroke-width="10" stroke-linecap="round" />
                                    <!-- Progress Arc -->
                                    <path class="{{ $journal->current_score >= 80 ? 'stroke-emerald-500' : ($journal->current_score >= 50 ? 'stroke-amber-500' : 'stroke-red-500') }}"
                                          d="M 10 50 A 40 40 0 0 1 90 50"
                                          fill="none" stroke-width="10" stroke-linecap="round"
                                          stroke-dasharray="{{ ($journal->current_score / 100) * 125.6 }}, 125.6" />
                                </svg>
                                
                                <div class="absolute -bottom-1 flex flex-col items-center">
                                    <span class="text-3xl font-bold {{ $journal->current_score >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($journal->current_score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                        {{ number_format($journal->current_score, 0) }}<span class="text-lg align-top">%</span>
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 font-medium">Puntaje</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Details Section --}}
    <div class="container mx-auto px-4 py-12">
        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- About --}}
                <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Acerca de la Revista</h2>
                    @if($journal->description)
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $journal->description }}</p>
                    @else
                        <p class="text-gray-400 dark:text-gray-500 italic">Sin descripción disponible.</p>
                    @endif

                    {{-- Tags: Subject Areas, Target Audience, Languages --}}
                    @if(($journal->subject_areas && count($journal->subject_areas)) || ($journal->target_audience && count($journal->target_audience)) || ($journal->publication_languages && count($journal->publication_languages)))
                        <div class="mt-6 space-y-4">
                            @if($journal->subject_areas && count($journal->subject_areas))
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Áreas Temáticas</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($journal->subject_areas as $area)
                                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($journal->target_audience && count($journal->target_audience))
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Público Objetivo</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($journal->target_audience as $aud)
                                            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 dark:bg-teal-900/30 dark:text-teal-400">{{ $aud }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($journal->publication_languages && count($journal->publication_languages))
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Idiomas de Publicación</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($journal->publication_languages as $lang)
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $lang }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- Evaluation Criteria (Real Data) --}}
                @if($journal->evaluationScores->isNotEmpty())
                    @php
                        $scoresByCategory = $journal->evaluationScores->groupBy(fn($s) => $s->criteriaItem?->category?->name ?? 'Sin categoría');
                        $colors = ['bg-indigo-600', 'bg-emerald-600', 'bg-amber-600', 'bg-purple-600', 'bg-rose-600', 'bg-cyan-600', 'bg-orange-600', 'bg-pink-600'];
                        $colorIndex = 0;
                    @endphp
                    <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Criterios de Evaluación</h2>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Resultados basados en los estándares de Editorial Standards Platform.</p>

                        <div class="space-y-5">
                            @foreach($scoresByCategory as $catName => $scores)
                                @php
                                    $total = $scores->count();
                                    $met = $scores->where('is_met', true)->count();
                                    $percent = $total > 0 ? round(($met / $total) * 100) : 0;
                                    $color = $colors[$colorIndex % count($colors)];
                                    $colorIndex++;
                                @endphp
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $catName }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-400">{{ $met }}/{{ $total }}</span>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $percent }}%</span>
                                        </div>
                                    </div>
                                    <div class="h-2.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full {{ $color }} transition-all duration-500" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Harvested Articles (OAI-PMH) --}}
                @php
                    $recentArticles = $journal->harvestedArticles->sortByDesc('date')->take(10);
                @endphp
                @if($recentArticles->isNotEmpty())
                    <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">📄 Artículos Recientes</h2>
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                {{ $journal->harvestedArticles->count() }} artículos
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($recentArticles as $article)
                                <div class="py-4 first:pt-0 last:pb-0">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                                        @if($article->url)
                                            <a href="{{ $article->url }}" target="_blank" rel="noopener" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                {{ $article->title }}
                                            </a>
                                        @else
                                            {{ $article->title }}
                                        @endif
                                    </h3>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        @if($article->authors_json)
                                            <div class="flex flex-wrap gap-2 text-xs">
                                                @foreach($article->authors_json as $author)
                                                    <span class="flex items-center gap-1">
                                                        <span class="font-medium" title="{{ $author['affiliation'] ?? '' }}">👤 {{ $author['name'] }}</span>
                                                        @if(!empty($author['orcid']))
                                                            <a href="https://orcid.org/{{ $author['orcid'] }}" target="_blank" rel="noopener"
                                                                class="text-[#A6CE39] hover:text-[#8dae30]"
                                                                title="ORCID: {{ $author['orcid'] }}">
                                                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="currentColor">
                                                                    <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zM7.369 4.378c.525 0 .947.431.947.947s-.422.947-.947.947a.95.95 0 0 1-.947-.947c0-.525.422-.947.947-.947zm-.722 3.038h1.444v10.041H6.647V7.416zm3.562 0h3.9c3.712 0 5.344 2.653 5.344 5.025 0 2.578-2.016 5.025-5.325 5.025h-3.919V7.416zm1.444 1.306v7.444h2.297c3.272 0 4.022-2.484 4.022-3.722 0-2.016-1.212-3.722-4.097-3.722h-2.222z"/>
                                                                </svg>
                                                            </a>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif($article->authors)
                                            <span>👤 {{ \Illuminate\Support\Str::limit($article->authors, 60) }}</span>
                                        @endif
                                        @if($article->date)
                                            <span>📅 {{ $article->date->format('d/m/Y') }}</span>
                                        @endif
                                        @if($article->language)
                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 dark:bg-gray-800">{{ $article->language }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex gap-3">
                                        @if($article->url)
                                            <a href="{{ $article->url }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                Ver artículo
                                            </a>
                                        @endif
                                        @if($article->pdf_url)
                                            <a href="{{ $article->pdf_url }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                </svg>
                                                Descargar PDF
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($journal->harvestedArticles->count() > 10)
                            <div class="mt-6 text-center">
                                <a href="{{ route('journal.articles', $journal->slug) }}"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-offset-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Ver todos los artículos ({{ $journal->harvestedArticles->count() }})
                                </a>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Open Access & Copyright Details --}}
                <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Acceso y Licencias</h2>
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Open Access --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">🔓 Acceso Abierto</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Acceso abierto</dt>
                                    <dd>{!! $journal->is_open_access === null ? '<span class="text-gray-300">—</span>' : ($journal->is_open_access ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                                @if($journal->access_type)
                                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                        <dt class="text-gray-500 dark:text-gray-400">Tipo de acceso</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">{{ match($journal->access_type) { 'full_oa' => 'Completo (Gold)', 'hybrid' => 'Híbrido', 'restricted' => 'Restringido', default => $journal->access_type } }}</dd>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Sin registro</dt>
                                    <dd>{!! $journal->articles_accessible_without_registration === null ? '<span class="text-gray-300">—</span>' : ($journal->articles_accessible_without_registration ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Auto-archivo</dt>
                                    <dd>{!! $journal->allows_self_archiving === null ? '<span class="text-gray-300">—</span>' : ($journal->allows_self_archiving ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                            </dl>
                        </div>
                        {{-- Copyright --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">📄 Copyright y Licencias</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Licencia</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->license_type ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Retención de copyright</dt>
                                    <dd>{!! $journal->authors_retain_copyright === null ? '<span class="text-gray-300">—</span>' : ($journal->authors_retain_copyright ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Reuso comercial</dt>
                                    <dd>{!! $journal->allows_commercial_reuse === null ? '<span class="text-gray-300">—</span>' : ($journal->allows_commercial_reuse ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <dt class="text-gray-500 dark:text-gray-400">Licencia visible</dt>
                                    <dd>{!! $journal->licenses_visible_in_articles === null ? '<span class="text-gray-300">—</span>' : ($journal->licenses_visible_in_articles ? '<span class="font-semibold text-emerald-600">Sí</span>' : '<span class="font-semibold text-red-500">No</span>') !!}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                {{-- Ethics & Best Practices --}}
                <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Ética y Buenas Prácticas</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @php
                            $ethicsItems = [
                                ['label' => 'Política de ética', 'value' => $journal->has_ethics_policy, 'icon' => '🛡️'],
                                ['label' => 'Adhiere a COPE', 'value' => $journal->adheres_to_cope, 'icon' => '✅'],
                                ['label' => 'Política antiplagio', 'value' => $journal->has_antiplagiarism_policy, 'icon' => '🔎'],
                                ['label' => 'Conflicto de interés', 'value' => $journal->has_conflict_of_interest_policy, 'icon' => '⚖️'],
                                ['label' => 'Declara uso de IA', 'value' => $journal->declares_ai_use, 'icon' => '🤖'],
                                ['label' => 'Asigna DOI', 'value' => $journal->assigns_doi, 'icon' => '🔗'],
                            ];
                        @endphp
                        @foreach($ethicsItems as $item)
                            <div class="flex items-center gap-3 rounded-lg border p-3
                                @if($item['value'] === true) border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20
                                @elseif($item['value'] === false) border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20
                                @else border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800
                                @endif
                            ">
                                <span class="text-xl">{{ $item['icon'] }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['label'] }}</p>
                                    <p class="text-xs
                                        @if($item['value'] === true) text-emerald-600 dark:text-emerald-400
                                        @elseif($item['value'] === false) text-red-500 dark:text-red-400
                                        @else text-gray-400
                                        @endif
                                    ">
                                        @if($item['value'] === true) Cumple
                                        @elseif($item['value'] === false) No cumple
                                        @else Sin información
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                        @if($journal->antiplagiarism_tool)
                            <div class="sm:col-span-2 lg:col-span-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Herramienta antiplagio:</span> {{ $journal->antiplagiarism_tool }}
                                </p>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Business Model --}}
                <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Modelo de Negocio</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Cobra APC</span>
                            <span class="text-sm font-semibold {{ $journal->charges_apc ? 'text-amber-600' : 'text-emerald-600' }}">
                                @if($journal->charges_apc === null) <span class="text-gray-300">—</span>
                                @elseif($journal->charges_apc) Sí
                                    @if($journal->apc_amount) — {{ $journal->apc_currency ?? 'USD' }} {{ number_format($journal->apc_amount, 2) }} @endif
                                @else No cobra
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Exenciones de APC</span>
                            <span class="text-sm font-semibold">{!! $journal->has_apc_waivers === null ? '<span class="text-gray-300">—</span>' : ($journal->has_apc_waivers ? '<span class="text-emerald-600">Disponibles</span>' : '<span class="text-gray-500">No</span>') !!}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Publicidad</span>
                            <span class="text-sm font-semibold">{!! $journal->has_advertising === null ? '<span class="text-gray-300">—</span>' : ($journal->has_advertising ? '<span class="text-amber-600">Tiene</span>' : '<span class="text-emerald-600">Sin publicidad</span>') !!}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Modelo transparente</span>
                            <span class="text-sm font-semibold">{!! $journal->business_model_transparent === null ? '<span class="text-gray-300">—</span>' : ($journal->business_model_transparent ? '<span class="text-emerald-600">Sí</span>' : '<span class="text-red-500">No</span>') !!}</span>
                        </div>
                    </div>
                    @if($journal->funding_sources && count($journal->funding_sources))
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Fuentes de Financiamiento</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($journal->funding_sources as $src)
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ $src }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                {{-- Quick Info --}}
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Información General</h3>
                    <dl class="space-y-3 text-sm">
                        @if($journal->issn_print)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">ISSN Impreso</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->issn_print }}</dd>
                            </div>
                        @endif
                        @if($journal->issn_online)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">ISSN Electrónico</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->issn_online }}</dd>
                            </div>
                        @endif
                        @if($journal->country_code)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">País</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->country_code }}</dd>
                            </div>
                        @endif
                        @if($journal->start_year)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Año de Inicio</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->start_year }}</dd>
                            </div>
                        @endif
                        @if($journal->publication_frequency)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Frecuencia</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ match($journal->publication_frequency) { 'annual' => 'Anual', 'biannual' => 'Semestral', 'quarterly' => 'Trimestral', 'bimonthly' => 'Bimensual', 'monthly' => 'Mensual', 'continuous' => 'Continua', default => $journal->publication_frequency } }}</dd>
                            </div>
                        @endif
                        @if($journal->peer_review_type)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Revisión por Pares</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ match($journal->peer_review_type) { 'double_blind' => 'Doble ciego', 'single_blind' => 'Simple ciego', 'open' => 'Abierta', default => $journal->peer_review_type } }}</dd>
                            </div>
                        @endif
                        @if($journal->current_score !== null)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Puntaje</dt>
                                <dd class="font-bold {{ $journal->current_score >= 80 ? 'text-emerald-600' : ($journal->current_score >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ number_format($journal->current_score, 0) }}%</dd>
                            </div>
                        @endif
                        @if($journal->evaluated_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Última Evaluación</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->evaluated_at->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Editorial Info --}}
                @if($journal->editor_name || $journal->institutional_email || $journal->editorial_board_visible !== null)
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">📰 Información Editorial</h3>
                        <dl class="space-y-3 text-sm">
                            @if($journal->editor_name)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Editor</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ $journal->editor_name }}</dd>
                                </div>
                            @endif
                            @if($journal->institutional_email)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">
                                        <a href="mailto:{{ $journal->institutional_email }}" class="text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $journal->institutional_email }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if($journal->editorial_board_visible !== null)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Comité visible</dt>
                                    <dd class="font-medium {{ $journal->editorial_board_visible ? 'text-emerald-600' : 'text-red-500' }}">{{ $journal->editorial_board_visible ? 'Sí' : 'No' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                {{-- External Links --}}
                <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Enlaces</h3>
                    <div class="space-y-3">
                        @if($journal->url)
                            <a href="{{ $journal->url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Sitio Web Oficial
                            </a>
                        @endif
                        @if($journal->editorial_board_url)
                            <a href="{{ $journal->editorial_board_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Comité Editorial
                            </a>
                        @endif
                        @if($journal->open_access_policy_url)
                            <a href="{{ $journal->open_access_policy_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Política de Acceso Abierto
                            </a>
                        @endif
                        @if($journal->license_url)
                            <a href="{{ $journal->license_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Licencia
                            </a>
                        @endif
                        @if(!$journal->url && !$journal->editorial_board_url && !$journal->open_access_policy_url && !$journal->license_url)
                            <p class="text-sm italic text-gray-400">No hay enlaces disponibles.</p>
                        @endif
                    </div>
                </div>

                {{-- Registered By --}}
                @if($journal->user)
                    <div class="rounded-xl bg-white p-6 shadow-lg dark:bg-gray-900">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Registrado Por</h3>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 font-bold text-sm">
                                {{ strtoupper(substr($journal->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $journal->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $journal->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

</x-layouts.app>
