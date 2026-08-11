<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
    <div style="color: #71717a; font-size: 12px; line-height: 1.5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <p style="margin: 0 0 8px 0; color: #52525b;">
            © {{ date('Y') }} Editorial Standards Platform.
            {{ __('Evaluación editorial transparente para revistas científicas y libros académicos.') }}
        </p>
        <p style="margin: 0; color: #a1a1aa; font-size: 11px;">
            {{-- Roadmap #62 — no enlazamos páginas que el admin deshabilitó (darían 404). --}}
            <a href="{{ url('/') }}" style="color: #1E3A8A; text-decoration: none;">{{ __('Sitio web') }}</a>
            @if(page_enabled('contact'))
                &nbsp;·&nbsp;
                <a href="{{ url('/contact') }}" style="color: #1E3A8A; text-decoration: none;">{{ __('Contacto') }}</a>
            @endif
            @if(page_enabled('privacy'))
                &nbsp;·&nbsp;
                <a href="{{ url('/privacy') }}" style="color: #1E3A8A; text-decoration: none;">{{ __('Privacidad') }}</a>
            @endif
            @if(page_enabled('terms'))
                &nbsp;·&nbsp;
                <a href="{{ url('/terms') }}" style="color: #1E3A8A; text-decoration: none;">{{ __('Términos') }}</a>
            @endif
        </p>
        @if($slot ?? false)
            <div style="margin-top: 12px; color: #a1a1aa; font-size: 11px;">
                {{ Illuminate\Mail\Markdown::parse($slot) }}
            </div>
        @endif
    </div>
</td>
</tr>
</table>
</td>
</tr>
