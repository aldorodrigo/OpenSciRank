<x-layouts.app title="Contacto - Editorial Standards Platform" description="Contáctanos para más información sobre indexación de revistas y libros científicos.">
    <x-slot:header>true</x-slot:header>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-purple-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold sm:text-5xl">Contacto</h1>
            <p class="mx-auto mt-4 max-w-2xl text-indigo-100">¿Tienes preguntas sobre la indexación? Estamos aquí para ayudarte.</p>
        </div>
    </section>

    <section class="bg-gray-50 py-20 dark:bg-gray-950">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-3">
                {{-- Contact Info --}}
                <div class="space-y-8">
                    <div>
                        <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Información de Contacto</h3>
                        <p class="text-gray-600 dark:text-gray-400">Responderemos tu consulta dentro de 24-48 horas hábiles.</p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Email</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">contacto@editorialstandards.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Horario</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lunes a Viernes, 9:00 - 18:00 (GMT-3)</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Plan Institucional</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">¿Eres universidad o grupo editorial? Escríbenos para un plan personalizado.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div class="lg:col-span-2">
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                        <h3 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Envíanos un mensaje</h3>
                        <livewire:contact-form />
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
