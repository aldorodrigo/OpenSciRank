{{-- Cookie consent banner — GDPR/LGPD/LFPDPPP.
     Se incluye desde el layout principal y aparece solo si el visitante no
     dió consentimiento todavía. Persiste la decisión en localStorage (365 días).

     Categorías:
       - "necessary" → siempre on (sesión, CSRF). No requiere consentimiento.
       - "analytics" → opcional, off por default. Si user acepta → activa GA/etc.
       - "marketing" → opcional, off por default. Si user acepta → activa pixels.

     Otros componentes JS pueden consultar el consentimiento con:
       window.cookieConsent.get('analytics') → true/false
       window.cookieConsent.get('marketing') → true/false
--}}
<div
    x-data="cookieConsentBanner()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-cloak
    class="fixed bottom-4 left-4 right-4 z-[100] mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-700 dark:bg-gray-900 sm:p-6"
    role="dialog"
    aria-label="{{ __('Aviso de cookies') }}"
    aria-live="polite"
>
    {{-- Panel principal (simple) --}}
    <template x-if="!showDetails">
        <div>
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Usamos cookies') }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                        {{ __('Usamos cookies necesarias para que el sitio funcione (sesión, seguridad) y, con tu consentimiento, cookies opcionales para analizar tráfico y mejorar tu experiencia.') }}
                        @if(page_enabled('privacy'))
                            <a href="{{ url('/privacy') }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400" target="_blank" rel="noopener">
                                {{ __('Política de privacidad') }}
                            </a>
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    @click="rejectOptional()"
                    class="order-3 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:order-1 sm:w-auto"
                >
                    {{ __('Rechazar opcionales') }}
                </button>
                <button
                    type="button"
                    @click="showDetails = true"
                    class="order-2 w-full rounded-lg bg-gray-100 px-4 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:w-auto"
                >
                    {{ __('Personalizar') }}
                </button>
                <button
                    type="button"
                    @click="acceptAll()"
                    class="order-1 w-full rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500 sm:order-3 sm:w-auto"
                >
                    {{ __('Aceptar todas') }}
                </button>
            </div>
        </div>
    </template>

    {{-- Panel detallado (granular) --}}
    <template x-if="showDetails">
        <div>
            <div class="flex items-start justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Preferencias de cookies') }}</h3>
                <button type="button" @click="showDetails = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="{{ __('Volver') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                {{-- Necesarias (siempre on, no toggle) --}}
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                    <input type="checkbox" checked disabled class="mt-0.5 h-4 w-4 cursor-not-allowed rounded border-gray-300 text-indigo-600">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ __('Necesarias') }}</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('Siempre activas') }}</span>
                        </div>
                        <p class="mt-1 text-[11px] leading-relaxed text-gray-600 dark:text-gray-400">{{ __('Sesión, protección CSRF y preferencia de idioma. Sin estas el sitio no funciona.') }}</p>
                    </div>
                </label>

                {{-- Analytics --}}
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-indigo-300 dark:border-gray-700 dark:hover:border-indigo-600">
                    <input type="checkbox" x-model="prefs.analytics" class="mt-0.5 h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="flex-1">
                        <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ __('Analítica') }}</span>
                        <p class="mt-1 text-[11px] leading-relaxed text-gray-600 dark:text-gray-400">{{ __('Nos ayuda a entender qué páginas visitás y mejorar la experiencia. No te identifica personalmente.') }}</p>
                    </div>
                </label>

                {{-- Marketing --}}
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-indigo-300 dark:border-gray-700 dark:hover:border-indigo-600">
                    <input type="checkbox" x-model="prefs.marketing" class="mt-0.5 h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="flex-1">
                        <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ __('Marketing') }}</span>
                        <p class="mt-1 text-[11px] leading-relaxed text-gray-600 dark:text-gray-400">{{ __('Pixels de redes sociales y campañas. Solo si decidís recibir contenido relevante fuera del sitio.') }}</p>
                    </div>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    @click="saveCustom()"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500 sm:w-auto"
                >
                    {{ __('Guardar preferencias') }}
                </button>
            </div>
        </div>
    </template>
</div>

<script>
function cookieConsentBanner() {
    const STORAGE_KEY = 'esp_cookie_consent';
    const VERSION = 1; // si cambian las categorías, bump → re-pregunta

    return {
        visible: false,
        showDetails: false,
        prefs: {
            necessary: true, // siempre on
            analytics: false,
            marketing: false,
        },

        init() {
            const stored = this.read();
            if (! stored) {
                // Primera visita o consentimiento expirado → mostrar banner
                // Pequeño delay para no aparecer antes que la página renderice
                setTimeout(() => { this.visible = true; }, 800);
            } else {
                // Ya hay consentimiento → expone API global para componentes que consulten
                this.prefs = { ...this.prefs, ...stored.prefs };
                this.exposeApi();
            }
        },

        read() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (! raw) return null;
                const data = JSON.parse(raw);
                if (data.version !== VERSION) return null;
                // Expira después de 365 días
                const expiresAt = new Date(data.savedAt).getTime() + (365 * 24 * 60 * 60 * 1000);
                if (Date.now() > expiresAt) return null;
                return data;
            } catch (e) {
                return null;
            }
        },

        write() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    version: VERSION,
                    prefs: this.prefs,
                    savedAt: new Date().toISOString(),
                }));
            } catch (e) {
                console.warn('cookieConsent: no se pudo persistir', e);
            }
            this.exposeApi();
        },

        exposeApi() {
            window.cookieConsent = {
                get: (category) => !! this.prefs[category],
                all: () => ({ ...this.prefs }),
            };
            // Disparar evento para que otros scripts reaccionen (cargar GA, etc.)
            window.dispatchEvent(new CustomEvent('cookie-consent:updated', { detail: { ...this.prefs } }));
        },

        acceptAll() {
            this.prefs.analytics = true;
            this.prefs.marketing = true;
            this.write();
            this.visible = false;
        },

        rejectOptional() {
            this.prefs.analytics = false;
            this.prefs.marketing = false;
            this.write();
            this.visible = false;
        },

        saveCustom() {
            this.write();
            this.visible = false;
            this.showDetails = false;
        },
    };
}
</script>
