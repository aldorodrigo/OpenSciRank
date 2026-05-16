@include('errors.layout', [
    'code' => '429',
    'title' => __('Demasiadas solicitudes'),
    'message' => __('Hiciste muchas solicitudes en poco tiempo. Por seguridad pausamos temporalmente. Esperá unos minutos antes de volver a intentar.'),
    'hideBack' => true,
    'badgeIcon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
])
