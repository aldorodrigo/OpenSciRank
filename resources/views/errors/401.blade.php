@include('errors.layout', [
    'code' => '401',
    'title' => __('Necesitás iniciar sesión'),
    'message' => __('Esta página requiere autenticación. Iniciá sesión para continuar.'),
    'badgeIcon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
])
