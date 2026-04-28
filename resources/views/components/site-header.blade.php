<header class="{{ request()->is('admin*') ? 'relative' : 'sticky top-0' }} w-full border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80" style="{{ request()->is('admin*') ? 'z-index: 50;' : 'z-index: 9999;' }}" x-data="{ mobileOpen: false }">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        {{-- Logo --}}
        <a href="{{ locale_path('/') }}" class="flex items-center gap-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            <span class="hidden sm:inline">Editorial Standards</span>
            <span class="sm:hidden">ESP</span>
        </a>

        {{-- Desktop Nav --}}
        <nav class="hidden items-center gap-1 md:flex">
            {{-- Directory Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                    class="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                    {{ __('Directory') }}
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-52 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
                    style="display: none;">
                    <div class="p-2">
                        <a href="{{ locale_path('/search') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400">
                            <span class="text-lg">📰</span>
                            <div>
                                <p class="font-medium">{{ __('Scientific Journals') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Browse the directory') }}</p>
                            </div>
                        </a>
                        <a href="{{ locale_path('/search?type=books') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400">
                            <span class="text-lg">📚</span>
                            <div>
                                <p class="font-medium">{{ __('Academic Books') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Scientific books index') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ locale_path('/methodology') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">{{ __('Methodology') }}</a>
            <a href="{{ locale_path('/pricing') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">{{ __('Pricing') }}</a>
            <a href="{{ locale_path('/blog') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">Blog</a>
            <a href="{{ locale_path('/about') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">{{ __('About Us') }}</a>
            <a href="{{ locale_path('/contact') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400">{{ __('Contact') }}</a>
        </nav>

        {{-- Auth Actions (Desktop) --}}
        <div class="hidden items-center gap-3 md:flex">
            <x-language-switcher />
            @auth
                @if(Auth::user()->hasRole('super_admin'))
                    <a href="/admin" class="rounded-lg bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50">
                        {{ __('Administration') }}
                    </a>
                @endif
                <a href="{{ locale_path('/app') }}" class="rounded-lg bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50">
                    {{ __('My Dashboard') }}
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <span>{{ Auth::user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute flex flex-col right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        style="display: none;">
                        <div class="py-1">
                            @if(Auth::user()->hasRole('super_admin'))
                                <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Administration') }}</a>
                            @endif
                            <a href="{{ route('app.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('My Dashboard') }}</a>
                            <a href="{{ route('app.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('My Profile') }}</a>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-700">
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full cursor-pointer px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    {{ __('Sign Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ locale_path('/login') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-300">{{ __('Sign In') }}</a>
                <a href="{{ locale_path('/register') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Sign Up') }}</a>
            @endauth
        </div>

        {{-- Mobile Hamburger --}}
        <button @click="mobileOpen = !mobileOpen" class="rounded-md p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:hidden">
            <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-950 md:hidden"
        style="display:none;">
        <nav class="container mx-auto space-y-1 px-4 py-4">
            <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ __('Directory') }}</p>
            <a href="{{ locale_path('/search') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">📰 {{ __('Scientific Journals') }}</a>
            <a href="{{ locale_path('/search?type=books') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">📚 {{ __('Academic Books') }}</a>
            <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            <a href="{{ locale_path('/methodology') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">{{ __('Methodology') }}</a>
            <a href="{{ locale_path('/pricing') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">{{ __('Pricing') }}</a>
            <a href="{{ locale_path('/blog') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">Blog</a>
            <a href="{{ locale_path('/about') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">{{ __('About Us') }}</a>
            <a href="{{ locale_path('/contact') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-indigo-900/30">{{ __('Contact') }}</a>
            <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            @auth
                <a href="{{ locale_path('/app') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">{{ __('My Dashboard') }}</a>
                <a href="{{ route('app.profile') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('My Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">{{ __('Sign Out') }}</button>
                </form>
            @else
                <a href="{{ locale_path('/login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300">{{ __('Sign In') }}</a>
                <a href="{{ locale_path('/register') }}" class="block rounded-lg bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Sign Up') }}</a>
            @endauth
            <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            <div class="px-3 py-1">
                <x-language-switcher />
            </div>
        </nav>
    </div>
</header>
