@include('errors.layout', [
    'code' => '419',
    'title' => __('Tu sesión expiró'),
    'message' => __('Pasó demasiado tiempo sin actividad y por seguridad tuvimos que cerrar tu sesión. Refrescá la página y volvé a iniciar sesión.'),
    'badgeIcon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
])
