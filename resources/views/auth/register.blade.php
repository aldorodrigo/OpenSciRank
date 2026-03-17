<x-layouts.app title="Registrarse - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[80vh] items-center justify-center bg-gradient-to-br from-purple-50 via-white to-indigo-50 px-4 py-12 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950">
        <div class="w-full max-w-sm">
            {{-- Logo --}}
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Editorial Standards Platform
                </a>
            </div>

            {{-- Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Crea tu cuenta</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Únete a Editorial Standards Platform</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            placeholder="Tu nombre"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            placeholder="tu@email.com"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                        <input type="password" id="password" name="password" required
                            placeholder="Mínimo 8 caracteres"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            placeholder="Repite tu contraseña"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 font-semibold text-white shadow-lg transition hover:bg-indigo-500">
                        Crear cuenta
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
