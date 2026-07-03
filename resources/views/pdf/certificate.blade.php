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
            margin: 10px 0 14px 0;
        }
        .title {
            font-size: 36px;
            font-weight: bold;
            margin: 8px 0 6px 0;
            color: #172554;
            text-align: center;
        }
        .subtitle {
            font-size: 14px;
            color: #64748b;
            text-align: center;
            margin-bottom: 12px;
        }
        .awardedto {
            font-size: 11px;
            letter-spacing: 4px;
            color: #64748b;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 6px;
        }
        .journal-name {
            font-size: 32px;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
            margin-bottom: 8px;
        }
        .journal-meta {
            font-size: 12px;
            color: #475569;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .description {
            font-size: 12.5px;
            color: #475569;
            text-align: center;
            line-height: 1.6;
            margin: 0 auto 2px auto;
            max-width: 740px;
            font-style: italic;
        }
        /* Fila del puntaje: score centrado + QR a la derecha (sobre las firmas).
           El espaciador izquierdo iguala el ancho de la celda del QR para que
           el score quede realmente centrado en la página. */
        .score-row { width: 100%; border-collapse: collapse; }
        .score-row td { vertical-align: middle; }
        .scoreblock { text-align: center; }
        .scoreblock .num {
            font-size: 58px;
            font-weight: bold;
            color: #1E3A8A;
            line-height: 1;
        }
        .scoreblock .lbl {
            font-size: 11px;
            letter-spacing: 4px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .qr-cell { text-align: right; vertical-align: top; }
        /* Envoltura de ancho = QR para que el caption quede centrado respecto
           al código (no respecto a toda la celda). Es <a>, clickable en el PDF. */
        .qr-wrap {
            display: inline-block;
            width: 100px;
            text-align: center;
            text-decoration: none;
            color: inherit;
        }
        .qr-img { width: 100px; height: 100px; }
        .qr-cap {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
            text-align: center;
            white-space: nowrap;
        }
        .sig-divider {
            border-top: 1px solid #e2e8f0;
            margin: 14px 0 10px 0;
        }
        .meta-line {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
        .meta-line strong { color: #64748b; font-weight: bold; }
        .footer-line {
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            margin-top: 5px;
        }
        .footer-line a { color: #64748b; text-decoration: none; }
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

    <table class="score-row">
        <tr>
            <td style="width: 150px;"></td>
            <td>
                <div class="scoreblock">
                    <div class="num">{{ rtrim(rtrim(number_format((float) $journal->current_score, 1), '0'), '.') }}%</div>
                    <div class="lbl">{{ __('Compliance score') }}</div>
                </div>
            </td>
            <td style="width: 150px;" class="qr-cell">
                @if($qr)
                    <a href="{{ route('journal.show', $journal->slug) }}" class="qr-wrap">
                        <img class="qr-img" src="{{ $qr }}" alt="QR">
                        <div class="qr-cap">{{ __('View public profile') }}</div>
                    </a>
                @endif
            </td>
        </tr>
    </table>

    <div class="bottom">
        @include('pdf.partials.signatures', ['signatories' => $brand['signatories'] ?? []])

        <div class="sig-divider"></div>

        {{-- Zona registral: letra chica centrada (metadatos + nota legal + verify) --}}
        <div class="meta-line">
            {{ __('Awarded') }}: <strong>{{ optional($journal->seal_awarded_at)->format('Y-m-d') ?? '—' }}</strong>
            · {{ __('Valid until') }}: <strong>{{ optional($journal->seal_expires_at)->format('Y-m-d') ?? '—' }}</strong>
            · {{ __('Certificate ID') }}: <strong>ESP-{{ str_pad((string) $journal->id, 6, '0', STR_PAD_LEFT) }}</strong>
        </div>
        {{-- Pie en una sola línea: nota legal | Verificar en <url clickable> --}}
        <div class="footer-line">
            @if(! empty($brand['footer_note'])){{ $brand['footer_note'] }} | @endif
            {{ __('Verify at') }}&nbsp;<a href="{{ url('/badge/'.$journal->slug) }}">{{ url('/badge/'.$journal->slug) }}</a>
        </div>
    </div>
</div>
</body>
</html>
