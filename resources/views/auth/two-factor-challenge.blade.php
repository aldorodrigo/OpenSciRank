<x-layouts.app title="Verificación en Dos Pasos - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[60vh] items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Verificación en Dos Pasos</h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Por favor ingresa el código de tu aplicación de autenticación.
                </p>

                <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
                    @csrf

                    <div x-data="{ recovery: false }">
                        <div x-show="!recovery">
                            <label for="code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
                            <input type="text" id="code" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div x-show="recovery" style="display: none;">
                            <label for="recovery_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Código de Recuperación</label>
                            <input type="text" id="recovery_code" name="recovery_code" autocomplete="one-time-code"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <button type="button" @click="recovery = !recovery" class="mt-3 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                            <span x-show="!recovery">Usar código de recuperación</span>
                            <span x-show="recovery" style="display: none;">Usar código de autenticación</span>
                        </button>
                    </div>

                    @error('code')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white transition hover:bg-indigo-500">
                        Verificar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
