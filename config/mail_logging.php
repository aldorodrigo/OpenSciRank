<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cola de correo
    |--------------------------------------------------------------------------
    | Cola en la que QueuedNotification despacha el canal `mail`. El worker la
    | prioriza con `queue:work --queue=mail,default`.
    */
    'queue_name' => env('MAIL_QUEUE', 'mail'),

    /*
    |--------------------------------------------------------------------------
    | Rate-limit de SES
    |--------------------------------------------------------------------------
    | Mensajes por segundo permitidos al worker de la cola `mail`. SES tiene un
    | límite (~14/s por defecto); se deja headroom. CORRECTO CON 1 WORKER: el
    | límite es por proceso sobre el cache; con >1 worker sin store compartido
    | (Redis) el límite efectivo se multiplica. Ver README (sección worker).
    */
    'ses_rate_per_second' => (int) env('MAIL_SES_RATE_PER_SECOND', 12),

    /*
    |--------------------------------------------------------------------------
    | Retención del log de correos
    |--------------------------------------------------------------------------
    | Días que se conservan las filas de `email_logs` antes de que el comando
    | `email-logs:prune` las borre. `recipient_email` es dato personal (RGPD):
    | la retención acotada es la salvaguarda.
    */
    'retention_days' => (int) env('MAIL_LOG_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Persistir cuerpo HTML
    |--------------------------------------------------------------------------
    | Si true, `email_logs.html_body` guarda el HTML renderizado. OFF por
    | defecto: el HTML puede contener datos personales y enlaces firmados
    | (RGPD). Actívalo solo con una política de retención/acceso adecuada.
    */
    'store_html' => (bool) env('MAIL_LOG_STORE_HTML', false),

];
