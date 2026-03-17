<x-layouts.app title="Verificar Email - Editorial Standards Platform">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[60vh] items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Verificar Email</h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Antes de continuar, verifica tu email haciendo clic en el enlace que te enviamos.
                </p>

                @if(session('status') === 'verification-link-sent')
                    <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        Se ha enviado un nuevo enlace de verificación a tu email.
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-500">
                            Reenviar Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:underline dark:text-gray-400">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
