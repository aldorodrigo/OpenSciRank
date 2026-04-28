<x-layouts.app :title="__('Verify Email - Editorial Standards Platform')">
    <x-slot:header>true</x-slot:header>

    <div class="flex min-h-[60vh] items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Verify Email Address') }}</h1>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Before continuing, please verify your email by clicking the link we sent you.') }}
                </p>

                @if(session('status') === 'verification-link-sent')
                    <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        {{ __('A new verification link has been sent to your email.') }}
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-500">
                            {{ __('Resend Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:underline dark:text-gray-400">
                            {{ __('Sign Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
