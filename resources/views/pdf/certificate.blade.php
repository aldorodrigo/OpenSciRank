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
            border: 12px solid #172554;
            margin: 18px;
            padding: 28px 40px 30px 40px;
            min-height: 540px;
            position: relative;
        }
        .frame::after {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid #c7d2fe;
            pointer-events: none;
        }
        .header { text-align: center; }
        .mark {
            display: inline-block;
            width: 56px;
            height: 56px;
            background: #1E3A8A;
            position: relative;
            margin-bottom: 6px;
        }
        .brandline {
            font-size: 13px;
            letter-spacing: 5px;
            color: #1E3A8A;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 2px;
        }
        .brandsub {
            font-size: 9px;
            letter-spacing: 4px;
            color: #64748b;
            text-transform: uppercase;
        }
        .title {
            font-size: 30px;
            font-weight: bold;
            margin: 18px 0 6px 0;
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
            font-size: 11px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 6px;
        }
        .journal-name {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
            margin-bottom: 6px;
        }
        .journal-meta {
            font-size: 11px;
            color: #475569;
            text-align: center;
            margin-bottom: 18px;
        }
        .scoreblock {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .scoreblock .num {
            font-size: 48px;
            font-weight: bold;
            color: #1E3A8A;
            line-height: 1;
        }
        .scoreblock .lbl {
            font-size: 10px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
        }
        .footer {
            position: absolute;
            bottom: 18px;
            left: 40px;
            right: 40px;
            font-size: 10px;
            color: #475569;
        }
        .footer table { width: 100%; }
        .footer td { padding: 2px 0; }
        .footer .label {
            font-size: 8px;
            letter-spacing: 2px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .footer .value { font-size: 11px; color: #0f172a; font-weight: bold; }
        .verify {
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-top: 6px;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="header">
        <div class="mark"></div>
        <div class="brandline">Editorial Standards</div>
        <div class="brandsub">Platform</div>
    </div>

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

    <div class="scoreblock">
        <div class="num">{{ rtrim(rtrim(number_format((float) $journal->current_score, 1), '0'), '.') }}%</div>
        <div class="lbl">{{ __('Compliance score') }}</div>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>
                    <div class="label">{{ __('Awarded') }}</div>
                    <div class="value">{{ optional($journal->seal_awarded_at)->format('Y-m-d') ?? '—' }}</div>
                </td>
                <td style="text-align: center;">
                    <div class="label">{{ __('Valid until') }}</div>
                    <div class="value">{{ optional($journal->seal_expires_at)->format('Y-m-d') ?? '—' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="label">{{ __('Certificate ID') }}</div>
                    <div class="value">ESP-{{ str_pad((string) $journal->id, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
        <div class="verify">{{ __('Verify at') }} {{ url('/badge/'.$journal->slug) }}</div>
    </div>
</div>
</body>
</html>
