<?php

/*
|--------------------------------------------------------------------------
| Documentos legales públicos (Roadmap #64)
|--------------------------------------------------------------------------
|
| Fecha de última actualización de cada documento legal, en formato ISO
| (YYYY-MM-DD). Se muestra al pie del encabezado de /terms y /privacy vía
| el componente <x-legal-updated-at>.
|
| ⚠️ IMPORTANTE — actualizá esta fecha en el MISMO commit en que cambies el
| texto del documento. El texto vive en `resources/views/{terms,privacy}.blade.php`
| y en las claves correspondientes de `lang/{es,en,pt}.json`; la fecha vive acá,
| al lado, para que no puedan divergir. Antes esto era `date('d/m/Y')`, es decir
| "hoy" cada día: un documento legal que afirmaba haberse actualizado siempre.
|
| Traducir el documento NO cambia su contenido jurídico: las traducciones es/pt
| agregadas el 2026-08-11 reflejan la misma versión del 2026-03-17, así que la
| fecha no se movió.
|
*/

return [

    'terms' => [
        // Redactado el 2026-03-17. El commit del 2026-04-28 solo envolvió el
        // texto en __() para i18n, sin cambiar el fondo.
        'updated_at' => '2026-03-17',
    ],

    'privacy' => [
        'updated_at' => '2026-03-17',
    ],

];
