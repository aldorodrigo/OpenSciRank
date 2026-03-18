<x-filament-panels::page>
    <div x-data="{
        activeCategory: '{{ addslashes($this->getCriteriaByCategory()->keys()->first()) }}',
        setCategory(name) { this.activeCategory = name; }
    }" class="space-y-6">

        {{-- NEW PREMIUM HERO HEADER --}}
        <div class="relative overflow-hidden rounded-2xl bg-slate-900 text-white shadow-xl lg:shadow-2xl">
            {{-- Background decorative elements --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            
            <div class="relative z-10 p-6 lg:p-10">
                <div class="flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                    {{-- Left: Journal Info --}}
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center gap-3">
                            @if($record->isEvaluated())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold tracking-wide text-emerald-400 ring-1 ring-emerald-500/30">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    EVALUADO
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold tracking-wide text-amber-400 ring-1 ring-amber-500/30">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    PENDIENTE
                                </span>
                            @endif
                            <span class="text-xs font-medium text-slate-400">{{ $record->issn_online ?: ($record->issn_print ?: 'Sin ISSN') }}</span>
                        </div>

                        <div>
                            <h1 class="text-2xl font-black tracking-tight text-white lg:text-4xl">
                                {{ $record->title }}
                            </h1>
                            @if($record->abbreviated_name)
                                <p class="mt-1 text-sm font-medium text-slate-400">{{ $record->abbreviated_name }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-400">
                            @if($record->publisher)
                                <span class="flex items-center gap-2"><svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0 1 12 12.25c-1.317 0-2.527-.525-3.414-1.382V21" /></svg>{{ $record->publisher }}</span>
                            @endif
                            @if($record->country_code)
                                <span class="flex items-center gap-2"><svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>{{ $record->country_code }}</span>
                            @endif
                            @if($record->start_year)
                                <span class="flex items-center gap-2"><svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>{{ $record->start_year }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 pt-2">
                            @if($record->url)
                                <a href="{{ $record->url }}" target="_blank" class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                    Sitio Web
                                </a>
                            @endif
                            @if($record->editorial_board_url)
                                <a href="{{ $record->editorial_board_url }}" target="_blank" class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.998 5.998 0 0 0-12 0m12 0c0-.856-.33-1.635-.873-2.219m-.306-5.674a3 3 0 1 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" /></svg>
                                    Comité Editorial
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Animated Score Circle --}}
                    <div class="flex items-center gap-8">
                        @php $score = $this->calculateScore(); @endphp
                        <div class="relative flex flex-col items-center">
                            <div class="relative flex h-28 w-28 items-center justify-center rounded-full bg-slate-800/50 shadow-inner ring-4 {{ $score >= 80 ? 'ring-emerald-500' : ($score >= 50 ? 'ring-amber-500' : 'ring-rose-500') }} transition-all duration-500">
                                <span class="text-3xl font-black text-white">{{ number_format($score, 0) }}<span class="text-lg opacity-60">%</span></span>
                            </div>
                            <span class="mt-2 text-[10px] font-black uppercase tracking-widest text-slate-500">Puntaje Final</span>
                        </div>

                        <div class="hidden flex-col gap-3 sm:flex">
                            <div class="flex items-center gap-4 rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        <span>Progreso de Evaluación</span>
                                        <span>{{ $this->getCompletionPercentage() }}%</span>
                                    </div>
                                    <div class="h-1.5 w-40 overflow-hidden rounded-full bg-slate-700">
                                        <div class="h-full bg-indigo-500 transition-all duration-700" style="width: {{ $this->getCompletionPercentage() }}%"></div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span class="block text-lg font-black leading-none text-white">{{ $this->getCompletedCount() }}</span>
                                    <span class="text-[10px] font-bold text-slate-500">/{{ $this->getTotalCount() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                                @if($this->getCoresFailedCount() > 0)
                                    <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                    <span class="text-xs font-bold text-rose-400">{{ $this->getCoresFailedCount() }} Criterios Excluyentes sin cumplir</span>
                                @else
                                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-xs font-bold text-emerald-400 text-shadow-sm">Todo OK en base metodológica</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div        {{-- JOURNAL DETAIL PANEL (collapsible) --}}
        <div x-data="{ showDetails: false }" class="mt-[-1rem]">
            <button type="button" @click="showDetails = !showDetails"
                    class="group flex w-full items-center justify-center gap-2 rounded-b-2xl border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800">
                <span x-show="!showDetails" class="flex items-center gap-2">
                    <svg class="h-4 w-4 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    Ver detalles de la revista
                </span>
                <span x-show="showDetails" class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                    Ocultar detalles
                </span>
            </button>

            <div x-show="showDetails" x-collapse x-cloak class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                {{-- Description --}}
                @if($record->description)
                    <div class="border-b border-slate-100 p-6 dark:border-slate-800">
                        <h4 class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Descripción Editorial</h4>
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $record->description }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    {{-- SECTION: Identification --}}
                    <div class="border-b border-slate-100 p-5 dark:border-slate-800 sm:border-r">
                        <h5 class="mb-4 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-indigo-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" /></svg>
                            Identificación
                        </h5>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">ISSN Impreso</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $record->issn_print ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">e-ISSN</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $record->issn_online ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Año inicio</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $record->start_year ?: '—' }}</span>
                            </div>
                            @if($record->subject_areas)
                            <div class="space-y-2 pt-1">
                                <span class="text-xs font-bold text-slate-400">Áreas temáticas</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($record->subject_areas as $area)
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $area }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION: Open Access --}}
                    <div class="border-b border-slate-100 p-5 dark:border-slate-800 lg:border-r">
                        <h5 class="mb-4 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-emerald-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            Acceso Abierto
                        </h5>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Acceso Abierto</span>
                                <span>{!! $record->is_open_access ? '✅' : '❌' !!}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Tipo de acceso</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ match($record->access_type) { 'full_oa' => 'Completo', 'hybrid' => 'Híbrido', 'restricted' => 'Restringido', default => '—' } }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Sin registro</span>
                                <span>{!! $record->articles_accessible_without_registration ? '✅' : '❌' !!}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Embargo</span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">
                                    @if($record->has_embargo) ⚠️ {{ $record->embargo_months }} meses @else No @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Editorial --}}
                    <div class="border-b border-slate-100 p-5 dark:border-slate-800">
                        <h5 class="mb-4 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                            Editorial y Pares
                        </h5>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Revisión por Pares</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ match($record->peer_review_type) { 'double_blind' => 'Doble ciego', 'single_blind' => 'Simple ciego', 'open' => 'Abierta', 'post_publication' => 'Post publicación', default => '—' } }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">Frecuencia</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ match($record->publication_frequency) { 'annual' => 'Anual', 'biannual' => 'Semestral', 'quarterly' => 'Trimestral', 'bimonthly' => 'Bimestral', 'monthly' => 'Mensual', 'continuous' => 'Continua', default => '—' } }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-50 pb-1 dark:border-slate-800/50">
                                <span class="text-slate-400">DOI asignado</span>
                                <span>{!! $record->assigns_doi ? '✅' : '❌' !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Admin Footer --}}
                <div class="flex flex-wrap items-center gap-6 bg-slate-50/50 px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Propietario: <span class="text-slate-600 dark:text-slate-300">{{ $record->user->name ?? '—' }}</span></div>
                    @if($record->assignedEvaluator)
                        <div class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Evaluador: <span class="text-slate-600 dark:text-slate-300">{{ $record->assignedEvaluator->name }}</span></div>
                    @endif
                    <div class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Registrada: <span class="text-slate-600 dark:text-slate-300">{{ $record->created_at?->format('d/m/Y') ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        {{-- NEW CATEGORY NAVIGATION (Tabs) --}}
        @php $categoryProgress = $this->getCategoryProgress(); @endphp
        <div class="no-scrollbar flex gap-2 overflow-x-auto pb-4">
            @foreach($this->getCriteriaByCategory() as $categoryName => $items)
                @php
                    $prog = $categoryProgress[$categoryName] ?? ['completed' => 0, 'total' => 0];
                    $allDone = $prog['completed'] === $prog['total'];
                @endphp
                <button type="button"
                        @click="setCategory('{{ addslashes($categoryName) }}')"
                        class="relative flex shrink-0 items-center gap-3 rounded-xl border px-4 py-3 transition-all duration-300"
                        :class="activeCategory === '{{ addslashes($categoryName) }}' 
                            ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-200 dark:shadow-none translate-y-[-2px]' 
                            : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800'">
                    
                    <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black"
                         :class="activeCategory === '{{ addslashes($categoryName) }}' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                        @if($allDone)
                            <svg class="h-4 w-4 text-emerald-500" :class="activeCategory === '{{ addslashes($categoryName) }}' ? 'text-white' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        @else
                            {{ $prog['completed'] }}
                        @endif
                    </div>
                    
                    <span class="text-sm font-bold tracking-tight">{{ $categoryName }}</span>

                    @if($allDone)
                        <div class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-white ring-2 ring-white shadow-sm dark:ring-slate-900">
                             <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
limit($categoryName, 28) }}
                </button>
            @endforeach
        </div>

        {{-- CRITERIA PANELS --}}
        @foreach($this->getCriteriaByCategory() as $categoryName => $items)
            @php
                $prog = $categoryProgress[$categoryName] ?? ['completed' => 0, 'total' => 0];
                $allDone = $prog['completed'] === $prog['total'];
                $catPercent = $prog['total'] > 0 ? round(($prog['completed'] / $prog['total']) * 100) : 0;
            @endphp
            <div x-show="activeCategory === '{{ addslashes($categoryName) }}'" x-cloak>

                {{-- Category Header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <h2 style="font-size: 18px; font-weight: 700; margin: 0;">{{ $categoryName }}</h2>
                        <span style="font-size: 13px; color: #9ca3af;">{{ $prog['completed'] }}/{{ $prog['total'] }}</span>
                        <div style="width: 60px; height: 5px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
                            <div style="height: 100%; border-radius: 9999px; transition: width 0.3s; background: {{ $allDone ? '#22c55e' : '#3b82f6' }}; width: {{ $catPercent }}%;"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button"
                                wire:click="toggleAllInCategory('{{ addslashes($categoryName) }}', true)"
                                style="font-size: 12px; color: #3b82f6; font-weight: 500; padding: 4px 10px; border-radius: 6px; border: none; background: transparent; cursor: pointer;"
                                onmouseover="this.style.background='#eff6ff'"
                                onmouseout="this.style.background='transparent'">
                            ☑ Marcar todos
                        </button>
                        <button type="button"
                                wire:click="toggleAllInCategory('{{ addslashes($categoryName) }}', false)"
                                style="font-size: 12px; color: #9ca3af; font-weight: 500; padding: 4px 10px; border-radius: 6px; border: none; background: transparent; cursor: pointer;"
                                onmouseover="this.style.background='#f3f4f6'"
                                onmouseout="this.style.background='transparent'">
                            ☐ Desmarcar
                        </button>
                    </div>
                </div>

                {{-- Criteria Cards --}}
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($items as $item)
                        @php $isChecked = !empty($scores[$item->id]); @endphp
                        <label style="display: block; border-radius: 12px; background: white; padding: 16px; cursor: pointer; transition: all 0.2s; border: 1px solid {{ $isChecked ? '#bbf7d0' : ($item->is_core && !$isChecked ? '#fecaca' : '#e5e7eb') }}; border-left: 4px solid {{ $isChecked ? '#22c55e' : ($item->is_core ? '#ef4444' : '#d1d5db') }};"
                               onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'; this.style.transform='translateY(-1px)';"
                               onmouseout="this.style.boxShadow='none'; this.style.transform='none';">
                            <div style="display: flex; align-items: flex-start; gap: 14px;">
                                {{-- Checkbox --}}
                                <div style="padding-top: 2px;">
                                    <input
                                        type="checkbox"
                                        wire:model.live="scores.{{ $item->id }}"
                                        style="width: 18px; height: 18px; border-radius: 4px; accent-color: #4f46e5; cursor: pointer;"
                                    >
                                </div>

                                {{-- Content --}}
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span style="font-family: monospace; font-size: 11px; padding: 2px 6px; border-radius: 4px; background: #f3f4f6; color: #9ca3af;">{{ $item->code }}</span>
                                        <span style="font-weight: 600; font-size: 14px; color: #111827; {{ $isChecked ? 'text-decoration: line-through; opacity: 0.45;' : '' }}">{{ $item->name }}</span>
                                    </div>
                                    @if($item->description)
                                        <p style="margin: 6px 0 0; font-size: 13px; color: #6b7280; line-height: 1.5;">{{ $item->description }}</p>
                                    @endif
                                </div>

                                {{-- Badges --}}
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0;">
                                    @if($item->is_core)
                                        <span style="border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; background: #fef2f2; color: #dc2626;">Excluyente</span>
                                    @endif
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="border-radius: 9999px; padding: 2px 8px; font-size: 11px; font-weight: 500;
                                            {{ $item->type === 'core' ? 'background: #eff6ff; color: #2563eb;' : '' }}
                                            {{ $item->type === 'advanced' ? 'background: #fffbeb; color: #d97706;' : '' }}
                                            {{ $item->type === 'excellence' ? 'background: #f0fdf4; color: #16a34a;' : '' }}
                                        ">{{ ucfirst($item->type) }}</span>
                                        <span style="border-radius: 9999px; padding: 2px 8px; font-size: 11px; background: #f3f4f6; color: #6b7280;">×{{ $item->weight }}</span>
                                    </div>
                                    <span style="font-size: 18px;">{{ $isChecked ? '✅' : '⬜' }}</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- EVALUATION NOTES --}}
        <div style="margin-top: 24px;">
            <div style="border-radius: 16px; background: white; border: 1px solid #e5e7eb; overflow: hidden;">
                <div style="padding: 12px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <h3 style="font-weight: 600; font-size: 15px; margin: 0;">📝 Observaciones</h3>
                </div>
                <div style="padding: 20px;">
                    <textarea
                        wire:model="evaluation_notes"
                        rows="4"
                        style="display: block; width: 100%; border-radius: 10px; border: 1px solid #d1d5db; padding: 10px 14px; font-size: 14px; line-height: 1.5; resize: vertical; transition: border-color 0.2s;"
                        placeholder="Observaciones adicionales sobre la evaluación..."
                        onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79,70,229,0.1)';"
                        onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                    ></textarea>
                </div>
            </div>
        </div>

        {{-- STICKY FOOTER --}}
        <div style="position: sticky; bottom: 0; z-index: 20; margin: 24px -16px 0; padding: 12px 16px; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); border-top: 1px solid #e5e7eb; box-shadow: 0 -4px 12px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <a href="{{ \App\Filament\Resources\JournalResource::getUrl('index') }}"
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; font-weight: 500; color: #6b7280; background: white; border-radius: 10px; border: 1px solid #e5e7eb; text-decoration: none; transition: background 0.2s;"
                   onmouseover="this.style.background='#f9fafb'"
                   onmouseout="this.style.background='white'">
                    ← Volver
                </a>

                <div style="display: flex; align-items: center; gap: 12px;">
                    {{-- Live Summary --}}
                    <div style="display: none; align-items: center; gap: 12px; margin-right: 8px; font-size: 13px;" class="fi-hidden sm:fi-flex" id="eval-live-summary">
                        <span style="color: #9ca3af;">Criterios:</span>
                        <span style="font-weight: 700;">{{ $this->getCompletedCount() }}/{{ $this->getTotalCount() }}</span>
                        <span style="width: 1px; height: 16px; background: #d1d5db; display: inline-block;"></span>
                        <span style="color: #9ca3af;">Nota:</span>
                        <span style="font-weight: 800; font-size: 16px; color: {{ $this->calculateScore() >= 80 ? '#16a34a' : ($this->calculateScore() >= 50 ? '#d97706' : '#dc2626') }};">
                            {{ $this->calculateScore() }}%
                        </span>
                    </div>
                    <script>
                        // Show on wider screens
                        if (window.innerWidth >= 640) {
                            document.getElementById('eval-live-summary').style.display = 'flex';
                        }
                    </script>

                    <button
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; font-weight: 500; color: #374151; background: white; border-radius: 10px; border: 1px solid #e5e7eb; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#f9fafb'"
                        onmouseout="this.style.background='white'">
                        <span wire:loading.remove wire:target="saveDraft">💾 Guardar Borrador</span>
                        <span wire:loading wire:target="saveDraft">⏳ Guardando...</span>
                    </button>

                    <button
                        wire:click="confirmSave"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; font-size: 13px; font-weight: 600; color: white; background: #4f46e5; border-radius: 10px; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(79,70,229,0.3); transition: background 0.2s;"
                        onmouseover="this.style.background='#4338ca'"
                        onmouseout="this.style.background='#4f46e5'">
                        ✅ Completar Evaluación
                    </button>
                </div>
            </div>
        </div>

        {{-- CONFIRMATION MODAL --}}
        @if($showConfirmModal)
            <div style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
                <div style="background: white; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.12); max-width: 460px; width: 100%; overflow: hidden; animation: modalIn 0.2s ease-out;">
                    <style>
                        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
                    </style>
                    {{-- Modal Header --}}
                    <div style="padding: 20px 24px; background: linear-gradient(135deg, #4f46e5, #6366f1); color: white;">
                        <h3 style="font-size: 18px; font-weight: 700; margin: 0;">Confirmar Evaluación</h3>
                        <p style="font-size: 13px; color: rgba(255,255,255,0.7); margin: 4px 0 0;">{{ $record->title }}</p>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 24px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                            <div style="background: #f9fafb; border-radius: 12px; padding: 16px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 800; color: #111827;">{{ $this->getCompletedCount() }}/{{ $this->getTotalCount() }}</div>
                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">Criterios cumplidos</div>
                            </div>
                            <div style="background: #f9fafb; border-radius: 12px; padding: 16px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 800; color: {{ $this->calculateScore() >= 50 ? '#16a34a' : '#dc2626' }};">{{ $this->calculateScore() }}%</div>
                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">Nota final</div>
                            </div>
                        </div>

                        @if($this->getCoresFailedCount() > 0)
                            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #dc2626; margin-bottom: 12px;">
                                ⚠️ {{ $this->getCoresFailedCount() }} criterio(s) excluyente(s) sin cumplir — nota limitada al 49%
                            </div>
                        @endif

                        {{-- Level & Status Assignment --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">Nivel asignado</label>
                                <select wire:model="assigned_level"
                                        style="width: 100%; padding: 8px 12px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827; cursor: pointer;">
                                    <option value="">— Sin asignar —</option>
                                    <option value="A">Nivel A (80-100%)</option>
                                    <option value="B">Nivel B (60-79%)</option>
                                    <option value="C">Nivel C (40-59%)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">Estado</label>
                                <select wire:model="assigned_status"
                                        style="width: 100%; padding: 8px 12px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827; cursor: pointer;">
                                    <option value="indexed">✅ Indexado</option>
                                    <option value="requires_changes">🔄 Requiere correcciones</option>
                                </select>
                            </div>
                        </div>

                        <p style="font-size: 13px; color: #6b7280; margin: 0;">
                            Se guardará la evaluación con el nivel y estado seleccionados. Esta acción no se puede deshacer fácilmente.
                        </p>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; background: #f9fafb; display: flex; justify-content: flex-end; gap: 10px;">
                        <button wire:click="cancelSave" type="button"
                                style="padding: 8px 16px; font-size: 13px; font-weight: 500; color: #6b7280; background: white; border-radius: 10px; border: 1px solid #e5e7eb; cursor: pointer;">
                            Cancelar
                        </button>
                        <button wire:click="save" wire:loading.attr="disabled" type="button"
                                style="padding: 8px 20px; font-size: 13px; font-weight: 600; color: white; background: #4f46e5; border-radius: 10px; border: none; cursor: pointer; box-shadow: 0 2px 8px rgba(79,70,229,0.3);">
                            <span wire:loading.remove wire:target="save">Sí, Completar</span>
                            <span wire:loading wire:target="save">⏳ Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
