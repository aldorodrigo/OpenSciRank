<x-slot:header>true</x-slot:header>

<div class="bg-gray-50 py-8 dark:bg-gray-950">
    <div class="container mx-auto max-w-3xl px-4">
        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('app.dashboard') }}" class="hover:text-indigo-600">Mi Panel</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-white">Mi Perfil</span>
        </nav>

        <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">Mi Perfil</h1>

        {{-- Profile Section --}}
        <div class="mb-8 rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Información Personal</h2>

            @if(session('profile_message'))
                <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    {{ session('profile_message') }}
                </div>
            @endif

            <form wire:submit="updateProfile" class="space-y-6">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input type="text" id="name" wire:model="name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" wire:model="email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 font-semibold text-white transition hover:bg-indigo-500">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Section --}}
        <div class="mb-8 rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Cambiar Contraseña</h2>

            @if(session('password_message'))
                <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    {{ session('password_message') }}
                </div>
            @endif

            <form wire:submit="updatePassword" class="space-y-6">
                <div>
                    <label for="current_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña Actual</label>
                    <input type="password" id="current_password" wire:model="current_password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('current_password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Contraseña</label>
                    <input type="password" id="password" wire:model="password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirmation" wire:model="password_confirmation"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 font-semibold text-white transition hover:bg-indigo-500">
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>

        {{-- Logout Section --}}
        <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Cerrar Sesión</h2>
            <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                Si deseas cerrar tu sesión actual, haz clic en el botón de abajo.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-red-600 px-6 py-2 font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-900/20">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>
