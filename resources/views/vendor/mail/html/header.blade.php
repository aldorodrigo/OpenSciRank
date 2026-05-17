@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    {{-- Mail header — Editorial Standards Platform. Mark inline (SVG) + wordmark + PLATFORM.
         Diseño Sprint 5 #52 (BRAND.md). Mark blanca sobre fondo Editorial Blue Deep,
         consistente con el sello y el logo del header del sitio. --}}
    <table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse: collapse; margin: 0 auto;">
        <tr>
            <td style="vertical-align: middle; padding-right: 14px;">
                {{-- Mark SVG en cuadrado Editorial Blue Deep --}}
                <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="background-color: #172554; border-radius: 8px; width: 44px; height: 44px; text-align: center; vertical-align: middle; padding: 7px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 100 100" fill="#FFFFFF" style="display: block;">
                                <rect x="8" y="8" width="14" height="84"/>
                                <rect x="22" y="8" width="56" height="14"/>
                                <rect x="78" y="8" width="14" height="32"/>
                                <rect x="22" y="43" width="30" height="14"/>
                                <rect x="22" y="78" width="56" height="14"/>
                                <rect x="78" y="60" width="14" height="32"/>
                            </svg>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: middle; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;">
                <div style="color: #0F172A; font-size: 18px; font-weight: 600; line-height: 1.1;">Editorial Standards</div>
                <div style="color: #1E3A8A; font-size: 10px; font-weight: 500; line-height: 1.1; letter-spacing: 2px; margin-top: 4px;">PLATFORM</div>
            </td>
        </tr>
    </table>
</a>
</td>
</tr>
