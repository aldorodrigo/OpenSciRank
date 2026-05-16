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
            <a href="{{ url('/') }}" style="color: #4f46e5; text-decoration: none;">{{ __('Sitio web') }}</a>
            &nbsp;·&nbsp;
            <a href="{{ url('/contact') }}" style="color: #4f46e5; text-decoration: none;">{{ __('Contacto') }}</a>
            &nbsp;·&nbsp;
            <a href="{{ url('/privacy') }}" style="color: #4f46e5; text-decoration: none;">{{ __('Privacidad') }}</a>
            &nbsp;·&nbsp;
            <a href="{{ url('/terms') }}" style="color: #4f46e5; text-decoration: none;">{{ __('Términos') }}</a>
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
