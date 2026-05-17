<?xml version="1.0" encoding="UTF-8"?>
{{-- Editorial Standards Seal — rectangular notarial moderno.
     Diseño Sprint 5 #51 (BRAND.md / BRAND_DIRECTION.md).
     Tipografía Inter con fallback system-ui — el sello se embebe en sitios de
     terceros donde no podemos garantizar Inter cargado. Mark integrada como
     parte del wordmark en top-left. --}}
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="130" viewBox="0 0 400 130" role="img" aria-label="Editorial Standards Seal">
    <title>Editorial Standards Seal — {{ $journal->getTranslationWithFallback('title') }}</title>

    @if($isActive)
        @php($score = (int) ($journal->current_score ?? 0))

        {{-- Fondo: Editorial Blue Deep sólido --}}
        <rect width="400" height="130" rx="8" fill="#172554"/>

        {{-- Accent strip izquierda — emerald (vigente) --}}
        <rect x="0" y="0" width="6" height="130" fill="#10B981"/>

        {{-- Mark blanca en top-left (scaled from 100×100 viewBox to 32×32) --}}
        <g fill="#FFFFFF" transform="translate(22 22) scale(0.32)">
            <rect x="8" y="8" width="14" height="84"/>
            <rect x="22" y="8" width="56" height="14"/>
            <rect x="78" y="8" width="14" height="32"/>
            <rect x="22" y="43" width="30" height="14"/>
            <rect x="22" y="78" width="56" height="14"/>
            <rect x="78" y="60" width="14" height="32"/>
        </g>

        {{-- Wordmark al lado de la mark --}}
        <text x="64" y="36" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="13" font-weight="600" fill="#FFFFFF" letter-spacing="0.8">EDITORIAL STANDARDS</text>
        <text x="64" y="51" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="9" font-weight="500" fill="#93C5FD" letter-spacing="2.4">PLATFORM · SEAL CERTIFIED</text>

        {{-- Tagline --}}
        <text x="22" y="88" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="10" font-weight="400" fill="#CBD5E1">{{ __('Verified technical evaluation') }}</text>

        {{-- Microcopy URL --}}
        <text x="22" y="112" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="9" font-weight="400" fill="#64748B" letter-spacing="0.3">editorialstandards.org/verify</text>

        {{-- Score block (rectangular, NO circular) --}}
        <rect x="295" y="22" width="82" height="64" rx="4" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.18)" stroke-width="1"/>
        <text x="336" y="60" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="26" font-weight="700" fill="#FFFFFF" text-anchor="middle">{{ $score }}<tspan font-size="14" font-weight="500" dy="-2">%</tspan></text>
        <text x="336" y="76" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="8" font-weight="500" fill="#94A3B8" text-anchor="middle" letter-spacing="0.8">SCORE</text>

        {{-- Year --}}
        <text x="336" y="106" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="11" font-weight="600" fill="#E2E8F0" text-anchor="middle" letter-spacing="0.5">{{ $year }}</text>
    @else
        {{-- Sello expirado --}}
        <rect width="400" height="130" rx="8" fill="#334155"/>

        {{-- Accent strip izquierda — rose (expirado) --}}
        <rect x="0" y="0" width="6" height="130" fill="#F43F5E"/>

        {{-- Mark mutada en top-left --}}
        <g fill="#CBD5E1" transform="translate(22 22) scale(0.32)">
            <rect x="8" y="8" width="14" height="84"/>
            <rect x="22" y="8" width="56" height="14"/>
            <rect x="78" y="8" width="14" height="32"/>
            <rect x="22" y="43" width="30" height="14"/>
            <rect x="22" y="78" width="56" height="14"/>
            <rect x="78" y="60" width="14" height="32"/>
        </g>

        {{-- Wordmark mutado --}}
        <text x="64" y="36" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="13" font-weight="600" fill="#CBD5E1" letter-spacing="0.8">EDITORIAL STANDARDS</text>
        <text x="64" y="51" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="9" font-weight="500" fill="#FECDD3" letter-spacing="2.4">PLATFORM · SEAL EXPIRED</text>

        {{-- Mensaje --}}
        <text x="22" y="88" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="10" font-weight="400" fill="#94A3B8">{{ __('Expired Seal') }}</text>

        {{-- Microcopy URL --}}
        <text x="22" y="112" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="9" font-weight="400" fill="#64748B" letter-spacing="0.3">editorialstandards.org/verify</text>

        {{-- Año de expiración (si hay) --}}
        @isset($year)
            <rect x="295" y="22" width="82" height="64" rx="4" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
            <text x="336" y="56" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="10" font-weight="500" fill="#94A3B8" text-anchor="middle" letter-spacing="0.8">EXPIRED</text>
            <text x="336" y="76" font-family="Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" font-size="13" font-weight="600" fill="#CBD5E1" text-anchor="middle" letter-spacing="0.5">{{ $year }}</text>
        @endisset
    @endif
</svg>
