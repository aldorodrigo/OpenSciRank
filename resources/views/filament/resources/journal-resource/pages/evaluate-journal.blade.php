<x-filament-panels::page>
    <div x-data="{
        activeCategory: '{{ addslashes($this->getCriteriaByCategory()->keys()->first()) }}',
        setCategory(name) { this.activeCategory = name; }
    }">

        {{-- HERO HEADER --}}
        <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e1b4b 100%); border-radius: 16px; padding: 24px; margin-bottom: 0; color: white;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
                {{-- Left: Journal Info --}}
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        @if($record->isEvaluated())
                            <span style="display: inline-flex; align-items: center; border-radius: 9999px; background: rgba(34,197,94,0.2); color: #86efac; padding: 4px 12px; font-size: 12px; font-weight: 600;">✓ Evaluado</span>
                        @else
                            <span style="display: inline-flex; align-items: center; border-radius: 9999px; background: rgba(245,158,11,0.2); color: #fcd34d; padding: 4px 12px; font-size: 12px; font-weight: 600;">⏳ Pendiente</span>
                        @endif
                    </div>
                    <h1 style="font-size: 22px; font-weight: 800; margin: 0; line-height: 1.3;">{{ $record->title }}</h1>
                    @if($record->abbreviated_name)
                        <div style="font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 2px;">{{ $record->abbreviated_name }}</div>
                    @endif
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 8px; font-size: 13px; color: rgba(255,255,255,0.55);">
                        @if($record->publisher)<span>📚 {{ $record->publisher }}</span>@endif
                        @if($record->publishing_institution)<span>🏛️ {{ $record->publishing_institution }}</span>@endif
                        @if($record->country_code)<span>🌍 {{ $record->country_code }}</span>@endif
                        @if($record->issn_print)<span>ISSN: {{ $record->issn_print }}</span>@endif
                        @if($record->issn_online)<span>e-ISSN: {{ $record->issn_online }}</span>@endif
                        @if($record->start_year)<span>📅 Desde {{ $record->start_year }}</span>@endif
                        @if($record->license_type)<span>📄 {{ $record->license_type }}</span>@endif
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                        @if($record->url)
                            <a href="{{ $record->url }}" target="_blank" rel="noopener noreferrer"
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 9999px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s;"
                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                🔗 {{ parse_url($record->url, PHP_URL_HOST) ?? 'Visitar sitio' }}
                            </a>
                        @endif
                        @if($record->editorial_board_url)
                            <a href="{{ $record->editorial_board_url }}" target="_blank" rel="noopener noreferrer"
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 9999px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s;"
                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                👥 Comité Editorial
                            </a>
                        @endif
                        @if($record->open_access_policy_url)
                            <a href="{{ $record->open_access_policy_url }}" target="_blank" rel="noopener noreferrer"
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 9999px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s;"
                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                🔓 Política OA
                            </a>
                        @endif
                        @if($record->license_url)
                            <a href="{{ $record->license_url }}" target="_blank" rel="noopener noreferrer"
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 9999px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s;"
                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                📜 Licencia
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Right: Score & Stats --}}
                <div style="display: flex; align-items: center; gap: 24px;">
                    @php $score = $this->calculateScore(); @endphp
                    <div style="text-align: center;">
                        <div style="width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; background: rgba(255,255,255,0.08);
                            border: 5px solid {{ $score >= 80 ? '#22c55e' : ($score >= 50 ? '#f59e0b' : '#ef4444') }}; color: {{ $score >= 80 ? '#86efac' : ($score >= 50 ? '#fcd34d' : '#fca5a5') }};">
                            {{ number_format($score, 0) }}<span style="font-size: 14px;">%</span>
                        </div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 6px;">Nota</div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.06); border-radius: 10px; padding: 8px 14px; font-size: 13px;">
                            <span style="color: rgba(255,255,255,0.45);">Progreso</span>
                            <div style="width: 80px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 9999px; overflow: hidden;">
                                <div style="height: 100%; border-radius: 9999px; background: #60a5fa; transition: width 0.5s; width: {{ $this->getCompletionPercentage() }}%;"></div>
                            </div>
                            <span style="font-weight: 700; color: white;">{{ $this->getCompletedCount() }}/{{ $this->getTotalCount() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.06); border-radius: 10px; padding: 8px 14px; font-size: 13px;">
                            <span style="color: rgba(255,255,255,0.45);">Excluyentes</span>
                            @if($this->getCoresFailedCount() > 0)
                                <span style="font-weight: 700; color: #fca5a5;">⚠ {{ $this->getCoresFailedCount() }} sin cumplir</span>
                            @else
                                <span style="font-weight: 700; color: #86efac;">✓ Todo OK</span>
                            @endif
                        </div>
                        @if($record->evaluated_at)
                            <div style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.06); border-radius: 10px; padding: 8px 14px; font-size: 13px;">
                                <span style="color: rgba(255,255,255,0.45);">Evaluado</span>
                                <span style="color: white;">{{ $record->evaluated_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- JOURNAL DETAIL PANEL (collapsible) --}}
        <div x-data="{ showDetails: false }" style="margin-bottom: 24px;">
            <button type="button" @click="showDetails = !showDetails"
                    style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; font-size: 13px; font-weight: 600; color: #6366f1; background: #eef2ff; border: 1px solid #c7d2fe; border-top: none; border-radius: 0 0 16px 16px; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='#e0e7ff'"
                    onmouseout="this.style.background='#eef2ff'">
                <span x-text="showDetails ? '▲ Ocultar datos de la revista' : '▼ Ver todos los datos de la revista'"></span>
            </button>

            <div x-show="showDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 style="margin-top: 12px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">

                {{-- Description --}}
                @if($record->description)
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 6px;">Descripción</div>
                        <p style="font-size: 14px; color: #374151; line-height: 1.6; margin: 0;">{{ $record->description }}</p>
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 0;">

                    {{-- SECTION: Identification --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; border-right: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6366f1; margin-bottom: 10px;">📋 Identificación</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 140px;">Título</td>
                                <td style="padding: 4px 0; color: #111827; font-weight: 500;">{{ $record->title }}</td>
                            </tr>
                            @if($record->abbreviated_name)
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Nombre abreviado</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->abbreviated_name }}</td>
                            </tr>
                            @endif
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">ISSN impreso</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->issn_print ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">ISSN electrónico</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->issn_online ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">País</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->country_code ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Año de inicio</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->start_year ?: '—' }}</td>
                            </tr>
                            @if($record->subject_areas && count($record->subject_areas))
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Áreas temáticas</td>
                                <td style="padding: 4px 0;">
                                    @foreach($record->subject_areas as $area)
                                        <span style="display: inline-block; background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 9999px; font-size: 11px; margin: 1px 2px;">{{ $area }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                            @if($record->target_audience && count($record->target_audience))
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Público objetivo</td>
                                <td style="padding: 4px 0;">
                                    @foreach($record->target_audience as $aud)
                                        <span style="display: inline-block; background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 9999px; font-size: 11px; margin: 1px 2px;">{{ $aud }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                            @if($record->publication_languages && count($record->publication_languages))
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Idiomas</td>
                                <td style="padding: 4px 0;">
                                    @foreach($record->publication_languages as $lang)
                                        <span style="display: inline-block; background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 9999px; font-size: 11px; margin: 1px 2px;">{{ $lang }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    {{-- SECTION: Open Access --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669; margin-bottom: 10px;">🔓 Acceso Abierto</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            @php
                                $boolIcon = fn($val) => $val === null ? '<span style="color:#d1d5db;">—</span>' : ($val ? '<span style="color:#22c55e;">✓</span>' : '<span style="color:#ef4444;">✗</span>');
                            @endphp
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 180px;">Acceso abierto</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->is_open_access) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Tipo de acceso</td>
                                <td style="padding: 4px 0; color: #111827;">
                                    @if($record->access_type)
                                        <span style="background: #ecfdf5; color: #059669; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                            {{ match($record->access_type) { 'full_oa' => 'Completo', 'hybrid' => 'Híbrido', 'restricted' => 'Restringido', default => $record->access_type } }}
                                        </span>
                                    @else — @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Accesible sin registro</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->articles_accessible_without_registration) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Permite auto-archivo</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->allows_self_archiving) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Tiene embargo</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_embargo) !!} @if($record->embargo_months) <span style="color:#6b7280; font-size:12px;">({{ $record->embargo_months }} meses)</span> @endif</td>
                            </tr>
                        </table>
                    </div>

                    {{-- SECTION: Copyright & Licensing --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; border-right: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #7c3aed; margin-bottom: 10px;">📄 Copyright y Licencias</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 180px;">Licencia</td>
                                <td style="padding: 4px 0; color: #111827; font-weight: 500;">{{ $record->license_type ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Autores retienen copyright</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->authors_retain_copyright) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Permite uso comercial</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->allows_commercial_reuse) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Licencia visible en artículos</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->licenses_visible_in_articles) !!}</td>
                            </tr>
                            @if($record->copyright_policy)
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Política de copyright</td>
                                <td style="padding: 4px 0; color: #6b7280; font-size: 12px;">{{ Str::limit($record->copyright_policy, 150) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    {{-- SECTION: Editorial --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0891b2; margin-bottom: 10px;">📰 Editorial</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 180px;">Institución editora</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->publishing_institution ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Editor responsable</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->editor_name ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Email institucional</td>
                                <td style="padding: 4px 0; color: #111827;">{{ $record->institutional_email ?: '—' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Comité editorial visible</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->editorial_board_visible) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Revisión por pares</td>
                                <td style="padding: 4px 0; color: #111827;">
                                    @if($record->peer_review_type)
                                        {{ match($record->peer_review_type) { 'double_blind' => 'Doble ciego', 'single_blind' => 'Simple ciego', 'open' => 'Abierta', 'post_publication' => 'Post publicación', default => $record->peer_review_type } }}
                                    @else — @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af;">Frecuencia</td>
                                <td style="padding: 4px 0; color: #111827;">
                                    @if($record->publication_frequency)
                                        {{ match($record->publication_frequency) { 'annual' => 'Anual', 'biannual' => 'Semestral', 'quarterly' => 'Trimestral', 'bimonthly' => 'Bimestral', 'monthly' => 'Mensual', 'continuous' => 'Continua', default => $record->publication_frequency } }}
                                    @else — @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- SECTION: Business Model --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; border-right: 1px solid #f3f4f6;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #ca8a04; margin-bottom: 10px;">💰 Modelo de Negocio</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 180px;">Cobra APC</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->charges_apc) !!}
                                    @if($record->charges_apc && $record->apc_amount)
                                        <span style="color: #111827; margin-left: 4px;">{{ $record->apc_currency ?? 'USD' }} {{ number_format($record->apc_amount, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Exenciones de APC</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_apc_waivers) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Tiene publicidad</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_advertising) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Modelo transparente</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->business_model_transparent) !!}</td>
                            </tr>
                            @if($record->funding_sources && count($record->funding_sources))
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Fuentes de financiamiento</td>
                                <td style="padding: 4px 0;">
                                    @foreach($record->funding_sources as $src)
                                        <span style="display: inline-block; background: #fef9c3; color: #854d0e; padding: 2px 8px; border-radius: 9999px; font-size: 11px; margin: 1px 2px;">{{ $src }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    {{-- SECTION: Ethics --}}
                    <div style="padding: 16px 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626; margin-bottom: 10px;">🛡️ Ética y Buenas Prácticas</div>
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af; width: 180px;">Política de ética</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_ethics_policy) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Adhiere a COPE</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->adheres_to_cope) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Política antiplagio</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_antiplagiarism_policy) !!}
                                    @if($record->antiplagiarism_tool)
                                        <span style="color: #6b7280; font-size: 12px; margin-left: 4px;">({{ $record->antiplagiarism_tool }})</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Conflicto de interés</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->has_conflict_of_interest_policy) !!}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f9fafb;">
                                <td style="padding: 4px 0; color: #9ca3af;">Declara uso de IA</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->declares_ai_use) !!}</td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af;">Asigna DOI</td>
                                <td style="padding: 4px 0;">{!! $boolIcon($record->assigns_doi) !!}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Evaluator & Owner Info --}}
                <div style="padding: 12px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: #6b7280;">
                    <span>👤 <strong>Propietario:</strong> {{ $record->user->name ?? '—' }}</span>
                    @if($record->assignedEvaluator)
                        <span>🔍 <strong>Evaluador:</strong> {{ $record->assignedEvaluator->name }}</span>
                    @endif
                    <span>📊 <strong>Estado:</strong> {{ match($record->status) { 'draft' => 'Borrador', 'submitted' => 'Enviado', 'requires_changes' => 'Requiere correcciones', 'indexed' => 'Indexado', default => $record->status } }}</span>
                    @if($record->created_at)
                        <span>📅 <strong>Registrado:</strong> {{ $record->created_at->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- CATEGORY NAVIGATION TABS --}}
        @php $categoryProgress = $this->getCategoryProgress(); @endphp
        <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 16px;">
            @foreach($this->getCriteriaByCategory() as $categoryName => $items)
                @php
                    $prog = $categoryProgress[$categoryName] ?? ['completed' => 0, 'total' => 0];
                    $allDone = $prog['completed'] === $prog['total'];
                @endphp
                <button type="button"
                        @click="setCategory('{{ addslashes($categoryName) }}')"
                        :style="activeCategory === '{{ addslashes($categoryName) }}'
                            ? 'background: #4f46e5; color: white; box-shadow: 0 4px 14px rgba(79,70,229,0.3);'
                            : 'background: white; color: #6b7280; border: 1px solid #e5e7eb;'"
                        style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; font-size: 13px; font-weight: 500; white-space: nowrap; flex-shrink: 0; cursor: pointer; transition: all 0.2s; border: none;">
                    @if($allDone)
                        <span style="width: 20px; height: 20px; border-radius: 50%; background: #22c55e; color: white; font-size: 11px; display: flex; align-items: center; justify-content: center;">✓</span>
                    @else
                        <span style="width: 20px; height: 20px; border-radius: 50%; background: rgba(0,0,0,0.08); font-size: 11px; display: flex; align-items: center; justify-content: center;"
                              :style="activeCategory === '{{ addslashes($categoryName) }}' ? 'background: rgba(255,255,255,0.2); color: white;' : ''">
                            {{ $prog['completed'] }}
                        </span>
                    @endif
                    {{ Str::limit($categoryName, 28) }}
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
