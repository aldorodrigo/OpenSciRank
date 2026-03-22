<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="70" viewBox="0 0 280 70" role="img" aria-label="Editorial Standards Seal">
    <title>Editorial Standards Seal - {{ $journal->title }}</title>

    @if($isActive)
        @php
            $score = (int) ($journal->current_score ?? 0);
            $scoreColor = $score >= 90 ? '#059669' : ($score >= 75 ? '#0D9488' : '#D97706');
        @endphp

        <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#1E1B4B"/>
                <stop offset="100%" stop-color="#312E81"/>
            </linearGradient>
        </defs>

        {{-- Main background with gradient --}}
        <rect width="280" height="70" rx="6" fill="url(#bg)"/>

        {{-- Score section --}}
        <rect x="220" y="0" width="60" height="70" fill="{{ $scoreColor }}"/>
        <rect x="274" y="0" width="6" height="70" rx="6" fill="{{ $scoreColor }}"/>

        {{-- Shield icon --}}
        <g transform="translate(14, 11)" fill="none" stroke="#A5B4FC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2l7 4v5c0 5.25-3.5 10.74-7 12-3.5-1.26-7-6.75-7-12V6l7-4z"/>
            <path d="M8.5 12.5l2 2 4.5-4.5" stroke="#22C55E" stroke-width="2"/>
        </g>

        {{-- Text --}}
        <text x="42" y="26" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#FFFFFF">Editorial Standards Seal</text>
        <text x="42" y="44" font-family="Arial, Helvetica, sans-serif" font-size="9" fill="#C7D2FE">Evaluacion tecnica verificada</text>

        {{-- Platform URL --}}
        <text x="110" y="62" font-family="Arial, Helvetica, sans-serif" font-size="8" fill="#A5B4FC" text-anchor="middle">editorialstandards.org</text>

        {{-- Score percentage --}}
        <text x="250" y="34" font-family="Arial, Helvetica, sans-serif" font-size="20" font-weight="bold" fill="#FFFFFF" text-anchor="middle">{{ $score }}%</text>

        {{-- Year --}}
        <text x="250" y="52" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="rgba(255,255,255,0.8)" text-anchor="middle">{{ $year }}</text>
    @else
        {{-- Expired seal --}}
        <rect width="280" height="70" rx="6" fill="#6B7280"/>

        {{-- Shield icon (grayed) --}}
        <g transform="translate(14, 11)" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2l7 4v5c0 5.25-3.5 10.74-7 12-3.5-1.26-7-6.75-7-12V6l7-4z"/>
            <path d="M9 12h6" stroke="#D1D5DB" stroke-width="2"/>
        </g>

        {{-- Text --}}
        <text x="42" y="26" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#E5E7EB">Editorial Standards Seal</text>
        <text x="42" y="44" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="#D1D5DB">Sello Expirado</text>

        {{-- Platform URL --}}
        <text x="140" y="62" font-family="Arial, Helvetica, sans-serif" font-size="8" fill="#9CA3AF" text-anchor="middle">editorialstandards.org</text>
    @endif
</svg>
