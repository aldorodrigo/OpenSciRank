<x-layouts.app title="Evaluación Editorial - Editorial Standards Platform" description="Solicita una evaluación editorial técnica para tu revista científica. Proceso transparente basado en criterios verificables.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">Evaluación Editorial</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">Solicita un proceso formal de evaluación técnica editorial para tu revista científica. El registro es gratuito.</p>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-white py-20 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">¿Cómo funciona el modelo?</h2>
                <div class="rounded-xl bg-indigo-50 p-8 dark:bg-indigo-900/20">
                    <p class="text-gray-700 dark:text-gray-300">La plataforma utiliza un modelo híbrido que combina <strong>acceso abierto al directorio</strong> con procesos de <strong>evaluación técnica editorial</strong>. El pago se realiza por el proceso de evaluación técnica, no por la obtención del sello editorial.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Participation Levels --}}
    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="grid gap-8 md:grid-cols-3">
                {{-- Listed --}}
                <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                    <div class="mb-4 text-3xl">📋</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revista Listada</h3>
                    <p class="mb-2 text-sm text-indigo-600 dark:text-indigo-400">Listed Journal</p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">La revista se registra y forma parte del directorio público de la plataforma.</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">Gratis</span>
                    </div>
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Ficha pública de la revista
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Visibilidad en el directorio
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Información editorial básica
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block w-full rounded-lg border border-indigo-600 py-3 text-center font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400 dark:text-indigo-400" style="cursor:pointer;">
                        Registrar mi Revista
                    </a>
                </div>

                {{-- Evaluated --}}
                <div class="relative rounded-xl bg-indigo-600 p-8 text-white shadow-xl">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-amber-400 px-4 py-1 text-xs font-bold text-amber-900">
                        📊 EVALUACIÓN EDITORIAL
                    </div>
                    <div class="mb-4 text-3xl">🔍</div>
                    <h3 class="text-lg font-semibold">Revista Evaluada</h3>
                    <p class="mb-2 text-sm text-indigo-200">Evaluated Journal</p>
                    <p class="mt-2 text-sm text-indigo-200">La revista completa el proceso formal de evaluación técnica editorial.</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold">Contactar</span>
                    </div>
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center text-sm">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Todo lo del registro gratuito
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Revisión de indicadores editoriales
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Editorial Standards Score
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Informe técnico detallado
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Recomendaciones de mejora
                        </li>
                    </ul>
                    <a href="/contact" class="mt-8 block w-full rounded-lg bg-white py-3 text-center font-semibold text-indigo-600 transition hover:bg-indigo-50" style="cursor:pointer;">
                        Solicitar Evaluación
                    </a>
                </div>

                {{-- Institutional --}}
                <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                    <div class="mb-4 text-3xl">🏛️</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Institucional</h3>
                    <p class="mb-2 text-sm text-indigo-600 dark:text-indigo-400">Múltiples publicaciones</p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Para universidades, editoriales y grupos con múltiples revistas.</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">Contactar</span>
                    </div>
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Todo lo de la evaluación
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Múltiples revistas
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Reportes institucionales
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="mr-3 h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Facturación institucional
                        </li>
                    </ul>
                    <a href="/contact" class="mt-8 block w-full rounded-lg border border-indigo-600 py-3 text-center font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400 dark:text-indigo-400" style="cursor:pointer;">
                        Contactar
                    </a>
                </div>
            </div>

            {{-- Independence Notice --}}
            <div class="mx-auto mt-12 max-w-3xl rounded-xl border-2 border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-700 dark:bg-amber-900/20">
                <p class="font-semibold text-amber-800 dark:text-amber-300">⚖️ Principio de independencia</p>
                <p class="mt-2 text-sm text-amber-700 dark:text-amber-400">El pago por el proceso de evaluación no garantiza la obtención del Editorial Standards Seal. El resultado depende exclusivamente del cumplimiento de los criterios técnicos definidos.</p>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 dark:text-white">Preguntas Frecuentes</h2>
            <div class="mx-auto max-w-3xl space-y-4">
                @php
                    $faqs = [
                        ['¿Qué incluye la evaluación?', 'La evaluación cubre cinco áreas fundamentales: identidad editorial, transparencia del proceso editorial, ética editorial, acceso y derechos, e infraestructura técnica. Cada indicador se evalúa como Cumple, Parcial o No cumple.'],
                        ['¿El pago garantiza obtener el sello?', 'No. El pago cubre el proceso de evaluación técnica. El resultado depende exclusivamente del cumplimiento de los criterios. Este principio de independencia asegura la credibilidad del sistema.'],
                        ['¿Qué pasa si mi revista no obtiene el sello?', 'La revista permanece como "Revista Evaluada" y recibe un informe técnico con observaciones y recomendaciones para mejorar. Puede solicitar una nueva evaluación después de implementar mejoras.'],
                        ['¿Los libros también se evalúan?', 'No. La plataforma incluye un índice de libros académicos para facilitar su descubrimiento, pero no realiza evaluación editorial de libros.'],
                        ['¿El sello tiene vigencia?', 'Sí. El Editorial Standards Seal tiene una vigencia temporal. Una vez finalizado el período, la revista puede solicitar una nueva evaluación para renovar su reconocimiento.'],
                        ['¿Puedo solicitar una revisión del resultado?', 'Sí. Las revistas evaluadas pueden solicitar una revisión cuando consideren que existe un error. La solicitud debe incluir evidencia que respalde la petición.'],
                        ['¿Ofrecen evaluación para instituciones con múltiples revistas?', 'Sí. Contacta a nuestro equipo para una propuesta personalizada con evaluación de múltiples publicaciones.'],
                    ];
                @endphp
                @foreach($faqs as $faq)
                <details class="group rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-gray-900 dark:text-white">
                        {{ $faq[0] }}
                        <svg class="h-5 w-5 shrink-0 text-gray-500 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $faq[1] }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-20 overflow-hidden bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="relative overflow-hidden rounded-3xl bg-indigo-600 p-8 text-center text-white shadow-2xl md:p-16">
                {{-- Decorative background elements --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=%2220%22 height=%2220%22 viewBox=%220 0 20 20%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22 fill-rule=%22evenodd%22%3E%3Ccircle cx=%223%22 cy=%223%22 r=%223%22/%3E%3Ccircle cx=%2213%22 cy=%2213%22 r=%223%22/%3E%3C/g%3E%3C/svg%3E')]"></div>
                
                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">¿Quieres evaluar tu revista?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-indigo-100">Registra tu revista de forma gratuita e inicia el camino hacia la excelencia editorial.</p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="/contact" class="group relative inline-flex items-center justify-center overflow-hidden rounded-xl bg-white px-8 py-4 font-bold text-indigo-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl active:scale-95">
                            Solicitar Evaluación
                        </a>
                        <a href="/register" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur-sm transition-all hover:border-white hover:bg-white/20 active:scale-95">
                            Registrarse Gratis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
