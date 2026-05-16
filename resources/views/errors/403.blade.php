@include('errors.layout', [
    'code' => '403',
    'title' => __('No tenés acceso'),
    'message' => __('No tenés permisos para ver esta página. Si creés que es un error, contactá al administrador.'),
    'badgeIcon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
])
