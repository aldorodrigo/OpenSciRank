<x-filament-panels::page>
    <div class="space-y-6">

        {{-- PREMIUM HERO HEADER --}}
        <div class="relative overflow-hidden rounded-2xl bg-slate-900 text-white shadow-xl lg:shadow-2xl">
            {{-- Background decorative elements --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            
            <div class="relative z-10 p-6 lg:p-10">
                <div class="flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                    {{-- Left: Journal Info --}}
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center gap-3">
                            @if($record->status === 'listed')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold tracking-wide text-emerald-400 ring-1 ring-emerald-500/30">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    LISTADA
                                </span>
                            @elseif($record->status === 'rejected')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/20 px-3 py-1 text-xs font-bold tracking-wide text-rose-400 ring-1 ring-rose-500/30">
                                    RECHAZADA
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
                                {{ $record->getTranslationWithFallback('title') }}
                            </h1>
                            @if($record->getTranslationWithFallback('abbreviated_name'))
                                <p class="mt-1 text-sm font-medium text-slate-400">{{ $record->getTranslationWithFallback('abbreviated_name') }}</p>
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
                </div>
            </div>
        </div>

        {{-- JOURNAL DETAIL PANEL (expanded by default for listing review) --}}
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{-- Description --}}
            @php $journalDescription = $record->getTranslationWithFallback('description'); @endphp
            @if($journalDescription)
                <div class="border-b border-slate-100 p-6 dark:border-slate-800">
                    <h4 class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Descripción Editorial</h4>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $journalDescription }}</p>
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
                <div class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Registrada: <span class="text-slate-600 dark:text-slate-300">{{ $record->created_at?->format('d/m/Y') ?? '—' }}</span></div>
            </div>
        </div>

        {{-- EVALUATION NOTES --}}
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-slate-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    Observaciones (Requeridas si se piden correcciones)
                </h3>
            </div>
            <div class="p-6">
                <textarea
                    wire:model="evaluation_notes"
                    rows="4"
                    class="block w-full rounded-xl border-slate-200 bg-slate-50/50 p-4 text-sm transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300 dark:focus:border-indigo-500 dark:focus:bg-slate-900"
                    placeholder="Escribe aquí observaciones si la revista necesita correcciones o si decides rechazar el listado..."
                ></textarea>
            </div>
        </div>

        {{-- GLASSMORPHY STICKY FOOTER --}}
        <div class="sticky bottom-6 z-30 mt-10">
            <div class="rounded-2xl border border-white/20 bg-white/80 p-4 shadow-2xl backdrop-blur-xl dark:border-slate-700/30 dark:bg-slate-900/80">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <a href="{{ \App\Filament\Resources\JournalResource::getUrl('index') }}"
                       class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Volver al listado
                    </a>

                    <div class="flex items-center gap-4">
                        <div class="flex gap-2">
                            <button wire:click="confirmSave" wire:loading.attr="disabled"
                                    class="relative overflow-hidden rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-black text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-indigo-300 active:scale-95 dark:shadow-none">
                                Finalizar Revisión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PREMIUM CONFIRMATION MODAL --}}
        @if($showConfirmModal)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 backdrop-blur-md bg-slate-900/60 transition-opacity">
                <div x-data x-init="$el.focus()" class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 animate-in zoom-in duration-200 dark:bg-slate-900 dark:ring-slate-800">
                    {{-- Modal Header --}}
                    <div class="relative bg-slate-900 px-8 py-10 text-white overflow-hidden">
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-500/20 blur-3xl"></div>
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500 shadow-lg shadow-indigo-500/40">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <h3 class="text-2xl font-black tracking-tight">¿Confirmar Revisión?</h3>
                            <p class="mt-2 text-sm font-medium text-slate-400">{{ $record->getTranslationWithFallback('title') }}</p>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-8 py-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Decisión sobre el Listado</label>
                            <select wire:model="assigned_status" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                <option value="listed">Aprobar y Listar ✅</option>
                                <option value="requires_changes_listing">Pedir Correcciones 🔄</option>
                                <option value="rejected">Rechazar Listado ❌</option>
                            </select>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-8 py-6 dark:border-slate-800 dark:bg-slate-900/50">
                        <button wire:click="cancelSave" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-200/50 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-white">
                            Cancelar
                        </button>
                        <button wire:click="save" wire:loading.attr="disabled" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-2.5 text-sm font-black text-white transition hover:bg-indigo-700 active:scale-95">
                            <span wire:loading.remove wire:target="save">Confirmar Registro</span>
                            <span wire:loading wire:target="save">Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
