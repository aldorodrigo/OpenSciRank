<x-layouts.app title="Confirmar Contraseña - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[60vh] items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Confirmar Contraseña</h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Esta es un área segura. Por favor confirma tu contraseña para continuar.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                        <input type="password" id="password" name="password" required autofocus
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white transition hover:bg-indigo-500">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
