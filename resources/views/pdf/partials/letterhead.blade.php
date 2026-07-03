{{-- Membrete institucional para certificado e informe (#65). Recibe $brand. --}}
@php
    $inst = $brand['institution'] ?? [];
    $logos = $brand['logos'] ?? [];
    $contacts = implode(' · ', array_filter([
        $inst['address'] ?? null,
        $inst['phone'] ?? null,
        $inst['email'] ?? null,
        $inst['website'] ?? null,
    ]));
@endphp
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:24%; vertical-align:middle;">
            @if(! empty($logos[0]))
                <img src="{{ $logos[0] }}" alt="" style="max-height:58px; max-width:160px;">
            @endif
        </td>
        <td style="vertical-align:middle; text-align:center;">
            @if(! empty($inst['name']))
                <div style="font-size:13px; font-weight:bold; color:#172554; letter-spacing:1px;">{{ $inst['name'] }}</div>
            @endif
            @if($contacts !== '')
                <div style="font-size:8.5px; color:#64748b; margin-top:3px;">{{ $contacts }}</div>
            @endif
        </td>
        <td style="width:24%; vertical-align:middle; text-align:right;">
            @if(! empty($logos[1]))
                <img src="{{ $logos[1] }}" alt="" style="max-height:46px; max-width:92px; margin-left:6px;">
            @endif
            @if(! empty($logos[2]))
                <img src="{{ $logos[2] }}" alt="" style="max-height:46px; max-width:92px; margin-left:6px;">
            @endif
        </td>
    </tr>
</table>
