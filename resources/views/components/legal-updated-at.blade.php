@props(['document'])

{{--
    Fecha de última actualización de un documento legal (Roadmap #64).

    La fecha viene de `config/legal.php` — fija y versionada junto al texto, no
    `date()`, que hacía que el documento dijera haberse actualizado hoy siempre.

    Se formatea según el idioma activo (`isoFormat('LL')` → "17 de marzo de 2026",
    "17 de março de 2026", "March 17, 2026") en vez de `d/m/Y`, que es ambiguo
    para un público internacional (¿03/04 es 3 de abril o 4 de marzo?). El
    atributo `datetime` mantiene el valor ISO legible por máquinas.
--}}
@php
    $legalDate = \Illuminate\Support\Carbon::parse(config("legal.{$document}.updated_at"));
@endphp

<p {{ $attributes }}>
    {{ __('Last updated:') }}
    <time datetime="{{ $legalDate->toDateString() }}">{{ $legalDate->locale(app()->getLocale())->isoFormat('LL') }}</time>
</p>
