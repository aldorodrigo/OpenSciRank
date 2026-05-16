@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    {{-- Sprint 3.7 — Branding custom. Reemplaza el logo de laravel.com
         que viene en el template default y delataba el framework. --}}
    <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
        <tr>
            <td style="vertical-align: middle; padding-right: 12px;">
                {{-- Sello editorial inline (sin asset externo para que renderice incluso si bloquean imágenes) --}}
                <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="background-color: #4f46e5; border-radius: 8px; width: 40px; height: 40px; text-align: center; vertical-align: middle;">
                            <span style="display: inline-block; color: #ffffff; font-size: 22px; font-weight: 700; line-height: 40px;">✓</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: middle; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;">
                <div style="color: #18181b; font-size: 20px; font-weight: 700; line-height: 1.2;">Editorial Standards</div>
                <div style="color: #71717a; font-size: 12px; font-weight: 400; line-height: 1.2; margin-top: 2px;">{{ __('Evaluación editorial transparente') }}</div>
            </td>
        </tr>
    </table>
</a>
</td>
</tr>
