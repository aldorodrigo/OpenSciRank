<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Evaluation Report') }} — {{ $journal->getTranslation('title', app()->getLocale()) ?? $journal->title }}</title>
    <style>
        @page { margin: 22mm 18mm 18mm 18mm; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
        }
        .header {
            border-bottom: 3px solid #172554;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header .brand {
            font-size: 11px;
            letter-spacing: 4px;
            color: #1E3A8A;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .sub {
            font-size: 8px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
        }
        h1 {
            font-size: 19px;
            color: #172554;
            margin: 8px 0 2px 0;
        }
        .subtitle { color: #475569; font-size: 11px; margin-bottom: 12px; }

        .intro {
            font-size: 10.5px;
            color: #475569;
            line-height: 1.55;
            text-align: justify;
            margin-bottom: 16px;
            padding: 10px 14px;
            background: #f8fafc;
            border-left: 3px solid #c7d2fe;
        }

        .signatures { margin-top: 28px; page-break-inside: avoid; }

        .summary {
            background: #f1f5f9;
            border-left: 4px solid #1E3A8A;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .summary table { width: 100%; }
        .summary td { padding: 2px 4px; vertical-align: top; }
        .summary .label {
            font-size: 8px;
            letter-spacing: 2px;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary .value { font-size: 13px; color: #0f172a; font-weight: bold; }
        .summary .score-big {
            font-size: 32px;
            font-weight: bold;
            color: #1E3A8A;
        }

        .core-warning {
            background: #fef2f2;
            border-left: 4px solid #b91c1c;
            color: #7f1d1d;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .category {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .category-head {
            background: #172554;
            color: white;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .category-head .name { letter-spacing: 1px; }
        .category-head .score { float: right; }

        table.indicators {
            width: 100%;
            border-collapse: collapse;
        }
        table.indicators th,
        table.indicators td {
            border-bottom: 1px solid #f1f5f9;
            padding: 6px 10px;
            text-align: left;
            vertical-align: top;
        }
        table.indicators th {
            background: #f8fafc;
            font-size: 9px;
            letter-spacing: 1px;
            color: #64748b;
            text-transform: uppercase;
        }
        .ok { color: #15803d; font-weight: bold; font-size: 13px; }
        .nope { color: #b91c1c; font-weight: bold; font-size: 13px; }
        .core-tag {
            display: inline-block;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 1px 4px;
            border-radius: 2px;
            text-transform: uppercase;
            vertical-align: middle;
            margin-left: 4px;
        }
        .indicator-name { font-weight: bold; color: #0f172a; }
        .indicator-desc { color: #64748b; font-size: 10px; margin-top: 2px; }
        .indicator-notes {
            color: #475569;
            font-size: 9px;
            font-style: italic;
            margin-top: 4px;
            padding: 4px 6px;
            background: #f8fafc;
            border-left: 2px solid #cbd5e1;
        }
        .col-w { width: 50px; text-align: right; }
        .col-met { width: 50px; text-align: center; }

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="header">
    @include('pdf.partials.letterhead', ['brand' => $brand])
</div>

<h1>{{ __('Evaluation Report') }}</h1>
<div class="subtitle">{{ $journal->getTranslation('title', app()->getLocale()) ?: $journal->title }}</div>

@if(! empty($brand['report_intro']))
    <div class="intro">{{ $brand['report_intro'] }}</div>
@endif

<div class="summary">
    <table>
        <tr>
            <td style="width: 35%;">
                <div class="label">{{ __('Compliance score') }}</div>
                <div class="score-big">{{ rtrim(rtrim(number_format((float) $breakdown['total_score'], 1), '0'), '.') }}%</div>
            </td>
            <td>
                <div class="label">{{ __('Evaluated') }}</div>
                <div class="value">{{ optional($journal->evaluated_at)->format('Y-m-d') ?? '—' }}</div>
                <div class="label" style="margin-top: 6px;">{{ __('Status') }}</div>
                <div class="value" style="text-transform: capitalize;">{{ $journal->status }}</div>
            </td>
            <td>
                @if($journal->status === 'certified' && $journal->seal_expires_at)
                    <div class="label">{{ __('Seal valid until') }}</div>
                    <div class="value">{{ $journal->seal_expires_at->format('Y-m-d') }}</div>
                @endif
                @if($journal->issn_print || $journal->issn_online)
                    <div class="label" style="margin-top: 6px;">ISSN</div>
                    <div class="value">{{ $journal->issn_print ?: $journal->issn_online }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

@if($breakdown['core_failed'])
<div class="core-warning">
    <strong>{{ __('Note:') }}</strong>
    {{ __('One or more critical indicators were not met. Total score is capped at 49% until they are resolved.') }}
</div>
@endif

@foreach($breakdown['categories'] as $cat)
    @if(count($cat['items']) === 0) @continue @endif
    <div class="category">
        <div class="category-head">
            <span class="name">{{ $cat['name'] }}</span>
            <span class="score">{{ rtrim(rtrim(number_format((float) $cat['score'], 1), '0'), '.') }}%</span>
        </div>
        <table class="indicators">
            <thead>
                <tr>
                    <th>{{ __('Indicator') }}</th>
                    <th class="col-w">{{ __('Weight') }}</th>
                    <th class="col-met">{{ __('Met') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($cat['items'] as $item)
                <tr>
                    <td>
                        <span class="indicator-name">{{ $item['name'] }}</span>
                        @if($item['is_core'])<span class="core-tag">{{ __('Critical') }}</span>@endif
                        @if($item['description'])<div class="indicator-desc">{{ $item['description'] }}</div>@endif
                        @if($item['notes'])<div class="indicator-notes">{{ $item['notes'] }}</div>@endif
                    </td>
                    <td class="col-w">{{ $item['weight'] }}</td>
                    <td class="col-met">
                        @if($item['is_met'])
                            <span class="ok">✓</span>
                        @else
                            <span class="nope">✕</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@if(! empty($brand['signatories']))
    <div class="signatures">
        @include('pdf.partials.signatures', ['signatories' => $brand['signatories']])
    </div>
@endif

<div class="footer">
    {{ __('Generated :date · Verify at :url', ['date' => now()->format('Y-m-d'), 'url' => url('/badge/'.$journal->slug)]) }}
</div>

</body>
</html>
