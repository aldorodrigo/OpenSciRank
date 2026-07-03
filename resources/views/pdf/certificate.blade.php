<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Editorial Standards Certificate') }} — {{ $journal->getTranslation('title', app()->getLocale()) ?? $journal->title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }
        .frame {
            /* A4 landscape ≈ 1122×793px (96dpi). Alto fijo para que el marco
               llene la página completa y el bloque inferior ancle al fondo. */
            border: 12px solid #172554;
            margin: 14px;
            /* dompdf aplica height al content-box (ignora box-sizing):
               793 (A4 landscape) − 28 margin − 24 borde − 48 padding ≈ 693 */
            padding: 24px 40px 24px 40px;
            height: 688px;
            position: relative;
        }
        /* Línea interior fina: div real con top/right/bottom/left explícitos.
           (dompdf no soporta la propiedad `inset` — un ::after con inset
           quedaba desfasado del marco grueso.) */
        .frame-inner {
            position: absolute;
            top: 6px;
            right: 6px;
            bottom: 6px;
            left: 6px;
            border: 1px solid #c7d2fe;
        }
        /* Bloque inferior (fechas + QR + firmas + leyendas) anclado al fondo
           del marco: el espacio sobrante queda entre el score y este bloque. */
        .bottom {
            position: absolute;
            left: 40px;
            right: 40px;
            bottom: 22px;
        }
        .lh-divider {
            border-bottom: 2px solid #172554;
            margin: 8px 0 14px 0;
        }
        .title {
            font-size: 30px;
            font-weight: bold;
            margin: 14px 0 5px 0;
            color: #172554;
            text-align: center;
        }
        .subtitle {
            font-size: 12px;
            color: #64748b;
            text-align: center;
            margin-bottom: 18px;
        }
        .awardedto {
            font-size: 10px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 6px;
        }
        .journal-name {
            font-size: 25px;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
            margin-bottom: 6px;
        }
        .journal-meta {
            font-size: 10px;
            color: #475569;
            text-align: center;
            margin-bottom: 14px;
        }
        .description {
            font-size: 11px;
            color: #475569;
            text-align: center;
            line-height: 1.55;
            margin: 0 auto 16px auto;
            max-width: 640px;
            font-style: italic;
        }
        .scoreblock {
            text-align: center;
            margin: 8px 0 0 0;
        }
        .scoreblock .num {
            font-size: 48px;
            font-weight: bold;
            color: #1E3A8A;
            line-height: 1;
        }
        .scoreblock .lbl {
            font-size: 9px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
        }
        .meta-row { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta-row td { vertical-align: bottom; }
        .meta-label {
            font-size: 8px;
            letter-spacing: 2px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .meta-value { font-size: 11px; color: #0f172a; font-weight: bold; }
        .qr-box { text-align: right; }
        .qr-box img { width: 78px; height: 78px; }
        .qr-cap { font-size: 7px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
        .footer-note {
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            margin-top: 10px;
        }
        .verify {
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="frame-inner"></div>
    @include('pdf.partials.letterhead', ['brand' => $brand])
    <div class="lh-divider"></div>

    <div class="title">{{ __('Editorial Standards Seal') }}</div>
    <div class="subtitle">{{ __('Certificate of editorial compliance') }}</div>

    <div class="awardedto">{{ __('Awarded to') }}</div>
    <div class="journal-name">{{ $journal->getTranslation('title', app()->getLocale()) ?: $journal->title }}</div>

    <div class="journal-meta">
        @if($journal->issn_print || $journal->issn_online)
            @if($journal->issn_print)ISSN (print): <strong>{{ $journal->issn_print }}</strong>@endif
            @if($journal->issn_print && $journal->issn_online) · @endif
            @if($journal->issn_online)ISSN (online): <strong>{{ $journal->issn_online }}</strong>@endif
        @endif
        @if($journal->publishing_institution)
            <br>{{ $journal->getTranslation('publishing_institution', app()->getLocale()) ?: $journal->publishing_institution }}
        @endif
    </div>

    @if(! empty($brand['certificate_description']))
        <div class="description">{{ $brand['certificate_description'] }}</div>
    @endif

    <div class="scoreblock">
        <div class="num">{{ rtrim(rtrim(number_format((float) $journal->current_score, 1), '0'), '.') }}%</div>
        <div class="lbl">{{ __('Compliance score') }}</div>
    </div>

    <div class="bottom">
        <table class="meta-row">
            <tr>
                <td style="width: 30%;">
                    <div class="meta-label">{{ __('Awarded') }}</div>
                    <div class="meta-value">{{ optional($journal->seal_awarded_at)->format('Y-m-d') ?? '—' }}</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div class="meta-label">{{ __('Valid until') }}</div>
                    <div class="meta-value">{{ optional($journal->seal_expires_at)->format('Y-m-d') ?? '—' }}</div>
                </td>
                <td style="width: 20%;">
                    <div class="meta-label">{{ __('Certificate ID') }}</div>
                    <div class="meta-value">ESP-{{ str_pad((string) $journal->id, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td style="width: 20%;" class="qr-box">
                    @if($qr)
                        <img src="{{ $qr }}" alt="QR">
                        <div class="qr-cap">{{ __('View public profile') }}</div>
                    @endif
                </td>
            </tr>
        </table>

        @include('pdf.partials.signatures', ['signatories' => $brand['signatories'] ?? []])

        @if(! empty($brand['footer_note']))
            <div class="footer-note">{{ $brand['footer_note'] }}</div>
        @endif
        <div class="verify">{{ __('Verify at') }} {{ url('/badge/'.$journal->slug) }}</div>
    </div>
</div>
</body>
</html>
