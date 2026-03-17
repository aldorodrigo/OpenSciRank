<x-layouts.app title="Iniciar Sesión - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[80vh] items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-12 dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950">
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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">¡Bienvenido!</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Ingresa tus credenciales</p>
                </div>

                @if(session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="tu@email.com"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                        <input type="password" id="password" name="password" required
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Recordarme</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 font-semibold text-white shadow-lg transition hover:bg-indigo-500">
                        Iniciar Sesión
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Regístrate</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
