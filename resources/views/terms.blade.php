<x-layouts.app title="Términos de Uso - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto max-w-4xl px-4">
            {{-- Header --}}
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">Términos de Uso</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Última actualización: {{ date('d/m/Y') }}</p>
            </div>

            {{-- Content --}}
            <div class="prose prose-indigo max-w-none rounded-2xl bg-white p-8 shadow-sm dark:prose-invert dark:bg-gray-900 sm:p-12">
                <p class="lead">Bienvenido a Editorial Standards Platform (ESP). Al utilizar nuestro sitio web y servicios, usted acepta cumplir con los siguientes términos y condiciones.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">1. Aceptación de los Términos</h2>
                <p>Al acceder o utilizar la plataforma Editorial Standards Platform (en adelante, "la Plataforma"), usted acepta estar legalmente vinculado por estos Términos de Uso y nuestra Política de Privacidad. Si no está de acuerdo con alguna parte de estos términos, no podrá acceder a la Plataforma ni utilizar nuestros servicios.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">2. Descripción del Servicio</h2>
                <p>ESP es una plataforma global diseñada para la evaluación editorial y visibilidad de revistas científicas y libros académicos. Los servicios incluyen, entre otros, la indexación en nuestro directorio, la evaluación técnica independiente basada en criterios de Ciencia Abierta y la emisión de sellos de calidad editorial.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">3. Registro de Usuarios y Responsabilidades</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Para acceder a ciertas funcionalidades, como la solicitud de evaluación, es necesario registrarse como usuario.</li>
                    <li>Usted es responsable de mantener la confidencialidad de su cuenta y contraseña.</li>
                    <li>Usted garantiza que toda la información proporcionada durante el registro y la solicitud de evaluación es veraz, exacta y está actualizada.</li>
                    <li>Se prohíbe el uso de identidades falsas o la suplantación de editores o instituciones.</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">4. Propiedad Intelectual</h2>
                <p>Todo el contenido de la Plataforma, incluyendo textos, logotipos, gráficos, iconos, imágenes y software, es propiedad de Editorial Standards Platform o de sus licenciantes y está protegido por leyes de propiedad intelectual internacionales.</p>
                <p>Los editores conservan los derechos sobre la información y metadatos de sus revistas, pero otorgan a ESP una licencia para mostrar y procesar dicha información con fines de evaluación y visualización en el directorio.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">5. Proceso de Evaluación y Sellos</h2>
                <p>La obtención de un sello de calidad editorial de ESP está sujeta al cumplimiento de criterios técnicos específicos evaluados por nuestro equipo independiente. ESP se reserva el derecho de retirar o revocar cualquier sello si se detectan prácticas editoriales poco éticas o si la publicación deja de cumplir con los estándares requeridos.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">6. Limitación de Responsabilidad</h2>
                <p>ESP proporciona sus servicios "tal cual" y no garantiza que la plataforma esté libre de errores o interrupciones. En ningún caso ESP será responsable por daños indirectos, incidentales o consecuentes derivados del uso o la imposibilidad de uso de la plataforma.</p>
                <p>ESP no se hace responsable de las decisiones tomadas por terceros (autores, agencias de acreditación, instituciones) basadas en las calificaciones o sellos otorgados por la Plataforma.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">7. Modificaciones</h2>
                <p>Nos reservamos el derecho de modificar estos Términos de Uso en cualquier momento. Los cambios entrarán en vigor inmediatamente después de su publicación en el sitio web. El uso continuado de la Plataforma después de dichas modificaciones constituye su aceptación de los nuevos términos.</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">8. Ley Aplicable y Jurisdicción</h2>
                <p>Estos términos se rigen por las leyes internacionales de comercio electrónico y propiedad intelectual. Cualquier disputa relacionada con estos términos quedará sujeta a la jurisdicción exclusiva de los tribunales competentes definidos por la administración de la Plataforma.</p>

                <div class="mt-12 border-t border-gray-100 pt-8 dark:border-gray-800">
                    <p class="text-sm text-gray-500">Si tiene alguna pregunta sobre estos Términos de Uso, por favor contáctenos a través de nuestra <a href="/contact" class="text-indigo-600 hover:underline dark:text-indigo-400">página de contacto</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
