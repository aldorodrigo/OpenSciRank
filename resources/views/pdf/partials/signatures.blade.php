{{-- Bloque de firmas para certificado e informe (#65). Recibe $signatories. --}}
@php($sigs = $signatories ?? [])
@if(count($sigs) > 0)
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @foreach($sigs as $s)
                <td style="text-align:center; vertical-align:bottom; padding:0 12px; width:{{ intdiv(100, count($sigs)) }}%;">
                    <div style="height:42px;">
                        @if(! empty($s['signature']))
                            <img src="{{ $s['signature'] }}" alt="" style="max-height:42px; max-width:160px;">
                        @endif
                    </div>
                    <div style="border-top:1px solid #0f172a; margin-top:2px; padding-top:4px;">
                        <div style="font-size:11px; font-weight:bold; color:#0f172a;">{{ $s['name'] }}</div>
                        @if(! empty($s['title']))
                            <div style="font-size:9px; color:#64748b;">{{ $s['title'] }}</div>
                        @endif
                    </div>
                </td>
            @endforeach
        </tr>
    </table>
@endif
