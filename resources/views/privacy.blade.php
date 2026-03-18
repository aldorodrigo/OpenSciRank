<x-layouts.app title="Política de Privacidad - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto max-w-4xl px-4">
            {{-- Header --}}
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">Política de Privacidad</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Última actualización: {{ date('d/m/Y') }}</p>
            </div>

            {{-- Content --}}
            <div class="prose prose-indigo max-w-none rounded-2xl bg-white p-8 shadow-sm dark:prose-invert dark:bg-gray-900 sm:p-12">
                <p class="lead">En Editorial Standards Platform (ESP), nos tomamos muy en serio la privacidad de nuestros usuarios y la seguridad de sus datos. Esta política describe cómo recopilamos, utilizamos y protegemos su información.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">1. Información que Recopilamos</h2>
                <p>Recopilamos información necesaria para proporcionar nuestros servicios de evaluación y visibilidad editorial:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Información de Registro:</strong> Nombre, dirección de correo electrónico, afiliación institucional y cargo.</li>
                    <li><strong>Información de la Publicación:</strong> Metadatos de revistas y libros (títulos, ISSN/ISBN, URLs, políticas editoriales).</li>
                    <li><strong>Datos de Navegación:</strong> Dirección IP, tipo de navegador e interacciones con la plataforma para mejorar la experiencia del usuario.</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">2. Uso de la Información</h2>
                <p>Utilizamos los datos recopilados para los siguientes fines:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Gestionar su cuenta y procesar solicitudes de evaluación.</li>
                    <li>Mostrar información pública de las revistas y libros en nuestro directorio global.</li>
                    <li>Cosechar metadatos vía OAI-PMH para análisis bibliométricos técnicos.</li>
                    <li>Enviar comunicaciones importantes relacionadas con el estado de su evaluación o actualizaciones del servicio.</li>
                    <li>Mejorar la seguridad y funcionalidad de la Plataforma.</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">3. Compartición de Datos</h2>
                <p>Editorial Standards Platform no vende ni alquila sus datos personales a terceros. Sus datos pueden ser compartidos solo en los siguientes casos:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Información Pública:</strong> Los datos de las revistas y sus calificaciones técnicas son públicos por la naturaleza del servicio de transparencia editorial.</li>
                    <li><strong>Proveedores de Servicios:</strong> Con terceros que nos ayudan a operar la plataforma (ej. procesadores de pago, servicios de hosting), bajo estrictos acuerdos de confidencialidad.</li>
                    <li><strong>Requerimientos Legales:</strong> Si es requerido por ley o para proteger los derechos de la Plataforma.</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">4. Seguridad de los Datos</h2>
                <p>Implementamos medidas de seguridad técnicas y organizativas para proteger su información contra pérdida, robo o acceso no autorizado. Esto incluye el uso de protocolos de cifrado y firewalls de última generación.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">5. Sus Derechos</h2>
                <p>Usted tiene derecho a acceder, rectificar o eliminar sus datos personales almacenados en nuestra plataforma. Puede gestionar la mayoría de esta información directamente desde su panel de usuario o contactándonos si desea cerrar su cuenta permanentemente.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">6. Cookies</h2>
                <p>Utilizamos cookies para mantener su sesión activa y analizar el tráfico del sitio. Puede configurar su navegador para rechazar las cookies, aunque esto podría afectar la funcionalidad de algunas áreas de la Plataforma.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">7. Cambios en esta Política</h2>
                <p>Nos reservamos el derecho de actualizar esta Política de Privacidad para reflejar cambios en nuestras prácticas o requerimientos legales. Le notificaremos cualquier cambio significativo a través de su correo electrónico registrado.</p>

                <div class="mt-12 border-t border-gray-100 pt-8 dark:border-gray-800">
                    <p class="text-sm text-gray-500">Para cualquier consulta sobre el tratamiento de sus datos, puede contactarnos en: <a href="mailto:privacy@esp.u-cal.org" class="text-indigo-600 hover:underline dark:text-indigo-400">privacy@esp.u-cal.org</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
