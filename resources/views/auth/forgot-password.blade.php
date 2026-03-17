<x-layouts.app title="Recuperar Contraseña - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[60vh] items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Recuperar Contraseña</h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>

                @if(session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white transition hover:bg-indigo-500">
                        Enviar Enlace
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Volver a iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
