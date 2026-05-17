{{-- Global loading indicator — barra de progreso en el top + overlay sutil
     mientras Livewire/AJAX está procesando.

     Aprovecha eventos nativos de Livewire 3:
       - 'livewire:request' → arranca progress bar
       - 'livewire:request-complete' → la cierra

     Para acciones largas específicas (subir archivo, generar PDF), el componente
     puede mostrar un overlay más prominente con `wire:loading.flex`. Esto es
     SOLO el indicador top discreto que cubre todo el sitio.
--}}
<div x-data="globalLoader()">
    {{-- Barra de progreso top (estilo NProgress) --}}
    <div
        x-show="active"
        x-transition.opacity.duration.150ms
        x-cloak
        class="fixed left-0 right-0 top-0 z-[200] h-0.5 overflow-hidden bg-transparent pointer-events-none"
        role="progressbar"
        aria-label="{{ __('Cargando') }}"
    >
        <div class="h-full w-full origin-left animate-progress-indeterminate bg-brand"></div>
    </div>
</div>

<style>
@keyframes progress-indeterminate {
    0% { transform: translateX(-100%) scaleX(0.6); }
    50% { transform: translateX(0%) scaleX(0.4); }
    100% { transform: translateX(100%) scaleX(0.6); }
}
.animate-progress-indeterminate {
    animation: progress-indeterminate 1.2s ease-in-out infinite;
}
[x-cloak] { display: none !important; }
</style>

<script>
function globalLoader() {
    return {
        active: false,
        // Debounce: no mostrar para requests < 200ms (no flickeo en acciones rápidas)
        showTimer: null,
        pendingCount: 0,

        init() {
            // Engancharse a Livewire 3 lifecycle hooks
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    this.start();
                    succeed(() => this.stop());
                    fail(() => this.stop());
                });
            });

            // También cubrir fetch/XHR plain (formularios non-livewire que hacen AJAX)
            this.wrapFetch();
        },

        start() {
            this.pendingCount++;
            // Debounce: solo mostrar si la request tarda > 200ms
            clearTimeout(this.showTimer);
            this.showTimer = setTimeout(() => {
                if (this.pendingCount > 0) this.active = true;
            }, 200);
        },

        stop() {
            this.pendingCount = Math.max(0, this.pendingCount - 1);
            if (this.pendingCount === 0) {
                clearTimeout(this.showTimer);
                this.active = false;
            }
        },

        show() { this.start(); },
        hide() { this.stop(); },

        wrapFetch() {
            const originalFetch = window.fetch;
            window.fetch = (...args) => {
                this.start();
                return originalFetch(...args)
                    .then(res => { this.stop(); return res; })
                    .catch(err => { this.stop(); throw err; });
            };
        },
    };
}
</script>
