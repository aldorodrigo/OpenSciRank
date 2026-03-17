<div>
    @if($sent)
        <div class="rounded-xl bg-emerald-50 p-8 text-center dark:bg-emerald-900/20">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-800 dark:text-emerald-300">¡Mensaje enviado!</h3>
            <p class="mt-2 text-emerald-700 dark:text-emerald-400">Gracias por contactarnos. Te responderemos dentro de 24-48 horas hábiles.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo *</label>
                    <input wire:model="name" type="text" id="name" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Tu nombre">
                    @error('name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico *</label>
                    <input wire:model="email" type="email" id="email" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="tu@email.com">
                    @error('email') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="institution" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Institución</label>
                    <input wire:model="institution" type="text" id="institution" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Universidad o institución">
                    @error('institution') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="subject" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto *</label>
                    <select wire:model="subject" id="subject" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Seleccionar...</option>
                        <option value="Indexación de revista">Indexación de revista</option>
                        <option value="Indexación de libro">Indexación de libro</option>
                        <option value="Plan institucional">Plan institucional</option>
                        <option value="Soporte técnico">Soporte técnico</option>
                        <option value="Facturación">Facturación</option>
                        <option value="Otro">Otro</option>
                    </select>
                    @error('subject') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label for="message" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mensaje *</label>
                <textarea wire:model="message" id="message" rows="5" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Escribe tu mensaje..."></textarea>
                @error('message') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500 sm:w-auto" style="cursor:pointer;">
                <span wire:loading.remove>Enviar Mensaje</span>
                <span wire:loading>Enviando...</span>
            </button>
        </form>
    @endif
</div>
