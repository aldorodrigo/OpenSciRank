@php
    $isSearchActive = request()->routeIs('search');
    // Badge de mensajes para usuarios autenticados
    $unreadMessages = 0;
    // Roadmap #35 — rol principal para diferenciación visual condicional.
    $panelRole = null;
    if (auth()->check()) {
        $unreadMessages = \App\Models\Conversation::forUser(auth()->user())
            ->where('status', 'open')
            ->get()
            ->sum(fn ($c) => $c->unreadCountFor(auth()->user()));
        $panelRole = auth()->user()->primaryPanelRole();
    }
    $isEvaluator = $panelRole === 'evaluator';
    // Roadmap #62 — grupos del nav: se usan para decidir si el separador vertical
    // sigue teniendo sentido cuando el admin apaga páginas.
    $hasDirectoryLinks = page_in_menu('search') || page_in_menu('ranking');
    $hasContentLinks = collect(['methodology', 'pricing', 'blog', 'about', 'contact'])
        ->contains(fn ($page) => page_in_menu($page));
@endphp
{{-- Roadmap #35 — acento ámbar de contexto: señal de que el evaluador está en
     un panel de trabajo, distinto del sitio del editor. --}}
<header class="{{ request()->is('admin*') ? 'relative' : 'sticky top-0' }} w-full border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80" style="{{ request()->is('admin*') ? 'z-index: 50;' : 'z-index: 9999;' }}" x-data="{ mobileOpen: false }">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        {{-- Logo: mark + wordmark (Editorial Standards Platform). Ver BRAND.md --}}
        <a href="{{ locale_path('/') }}" class="flex items-center gap-3" aria-label="Editorial Standards Platform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 shrink-0" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                <rect x="8" y="8" width="14" height="84"/>
                <rect x="22" y="8" width="56" height="14"/>
                <rect x="78" y="8" width="14" height="32"/>
                <rect x="22" y="43" width="30" height="14"/>
                <rect x="22" y="78" width="56" height="14"/>
                <rect x="78" y="60" width="14" height="32"/>
            </svg>
            <span class="hidden flex-col leading-none sm:flex">
                <span class="text-base font-semibold text-slate-900 dark:text-white">Editorial Standards</span>
                <span class="mt-0.5 text-[10px] font-medium tracking-[0.2em] text-brand dark:text-blue-300">PLATFORM</span>
            </span>
            <span class="text-base font-semibold text-slate-900 sm:hidden dark:text-white">ESP</span>
        </a>

        {{-- Desktop Nav — Roadmap #62: cada link se pinta solo si el admin lo dejó
             visible en Sistema → Menús (`page_in_menu`). --}}
        <nav class="hidden items-center gap-1 md:flex">
            {{-- Direct directory links --}}
            @if(page_in_menu('search'))
                <a href="{{ locale_path('/search') }}"
                   @class([
                       'flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold transition',
                       'bg-blue-50 text-brand dark:bg-blue-900/30 dark:text-blue-400' => $isSearchActive,
                       'text-gray-700 hover:bg-gray-100 hover:text-brand dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-blue-400' => !$isSearchActive,
                   ])
                   @if($isSearchActive) aria-current="page" @endif>
                    <span aria-hidden="true">📰</span>{{ __('Journals') }}
                </a>
                <a href="{{ locale_path('/search?type=books') }}"
                   class="flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 hover:text-brand dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-blue-400">
                    <span aria-hidden="true">📚</span>{{ __('Books') }}
                </a>
            @endif
            @if(page_in_menu('ranking'))
                <a href="{{ locale_path('/ranking') }}"
                   @class([
                       'flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold transition',
                       'bg-blue-50 text-brand dark:bg-blue-900/30 dark:text-blue-400' => request()->routeIs('ranking'),
                       'text-gray-700 hover:bg-gray-100 hover:text-brand dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-blue-400' => !request()->routeIs('ranking'),
                   ])
                   @if(request()->routeIs('ranking')) aria-current="page" @endif>
                    <span aria-hidden="true">🏆</span>{{ __('Ranking') }}
                </a>
            @endif
            {{-- El separador solo tiene sentido si quedan links a ambos lados. --}}
            @if($hasDirectoryLinks && $hasContentLinks)
                <span class="mx-2 h-5 w-px bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
            @endif

            @if(page_in_menu('methodology'))
                <a href="{{ locale_path('/methodology') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-blue-400">{{ __('Methodology') }}</a>
            @endif
            @if(page_in_menu('pricing'))
                <a href="{{ locale_path('/pricing') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-blue-400">{{ __('Pricing') }}</a>
            @endif
            @if(page_in_menu('blog'))
                <a href="{{ locale_path('/blog') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-blue-400">Blog</a>
            @endif
            @if(page_in_menu('about'))
                <a href="{{ locale_path('/about') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-blue-400">{{ __('About Us') }}</a>
            @endif
            @if(page_in_menu('contact'))
                <a href="{{ locale_path('/contact') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-blue-400">{{ __('Contact') }}</a>
            @endif
        </nav>

        {{-- Auth Actions (Desktop) --}}
        <div class="hidden items-center gap-3 md:flex">
            <x-language-switcher />
            @auth
                @if(Auth::user()->hasRole('super_admin'))
                    <a href="/admin" class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-brand hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                        {{ __('Administration') }}
                    </a>
                @endif
                {{-- Roadmap #35 — acceso directo del evaluador a su escritorio (panel de evaluación). --}}
                <a href="{{ locale_path('/app') }}" class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-brand hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                    {{ __('My Dashboard') }}
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="relative flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                        <span>{{ Auth::user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        {{-- Roadmap #35 — indicador de mensajes sin leer visible sin abrir el menú. --}}
                        @if($unreadMessages > 0)
                            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white shadow-sm ring-2 ring-white dark:ring-gray-950">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
                        @endif
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
                                <a href="{{ route('admin.conversations') }}" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Conversaciones admin
                                    @if($unreadMessages > 0)
                                        <span class="rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">{{ $unreadMessages }}</span>
                                    @endif
                                </a>
                            @endif
                            @if($isEvaluator)
                                <a href="{{ route('filament.admin.pages.evaluator-desk') }}" class="block px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-900/20">{{ __('evaluator_access.button') }}</a>
                            @endif
                            <a href="{{ route('app.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('My Dashboard') }}</a>
                            <a href="{{ route('app.messages') }}" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                Mensajes
                                @if($unreadMessages > 0)
                                    <span class="rounded-full bg-brand px-1.5 text-xs font-bold text-white">{{ $unreadMessages }}</span>
                                @endif
                            </a>
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
                <a href="{{ locale_path('/login') }}" class="text-sm font-medium text-gray-600 hover:text-brand dark:text-gray-300">{{ __('Sign In') }}</a>
                <a href="{{ locale_path('/register') }}" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">{{ __('Sign Up') }}</a>
            @endauth
        </div>

        {{-- Mobile Hamburger --}}
        <button @click="mobileOpen = !mobileOpen" class="relative rounded-md p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:hidden">
            <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{-- Roadmap #35 — punto de mensajes sin leer en mobile. --}}
            @auth
                @if($unreadMessages > 0)
                    <span class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-950"></span>
                @endif
            @endauth
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
            @if(page_in_menu('search'))
                <a href="{{ locale_path('/search') }}" class="flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2.5 text-sm font-bold text-brand dark:bg-blue-900/30 dark:text-blue-300">📰 {{ __('Journals directory') }}</a>
                <a href="{{ locale_path('/search?type=books') }}" class="flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2.5 text-sm font-bold text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">📚 {{ __('Books directory') }}</a>
            @endif
            @if(page_in_menu('ranking'))
                <a href="{{ locale_path('/ranking') }}" class="flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2.5 text-sm font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">🏆 {{ __('Ranking') }}</a>
            @endif
            @if($hasDirectoryLinks && $hasContentLinks)
                <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            @endif
            @if(page_in_menu('methodology'))
                <a href="{{ locale_path('/methodology') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-brand dark:text-gray-300 dark:hover:bg-blue-900/30">{{ __('Methodology') }}</a>
            @endif
            @if(page_in_menu('pricing'))
                <a href="{{ locale_path('/pricing') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-brand dark:text-gray-300 dark:hover:bg-blue-900/30">{{ __('Pricing') }}</a>
            @endif
            @if(page_in_menu('blog'))
                <a href="{{ locale_path('/blog') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-brand dark:text-gray-300 dark:hover:bg-blue-900/30">Blog</a>
            @endif
            @if(page_in_menu('about'))
                <a href="{{ locale_path('/about') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-brand dark:text-gray-300 dark:hover:bg-blue-900/30">{{ __('About Us') }}</a>
            @endif
            @if(page_in_menu('contact'))
                <a href="{{ locale_path('/contact') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-brand dark:text-gray-300 dark:hover:bg-blue-900/30">{{ __('Contact') }}</a>
            @endif
            <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            @auth
                @if($isEvaluator)
                    <a href="{{ route('filament.admin.pages.evaluator-desk') }}" class="block rounded-lg bg-amber-50 px-3 py-2.5 text-sm font-bold text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">🛡️ {{ __('evaluator_access.button') }}</a>
                @endif
                <a href="{{ locale_path('/app') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30">{{ __('My Dashboard') }}</a>
                <a href="{{ route('app.messages') }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                    Mensajes
                    @if($unreadMessages > 0)
                        <span class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-white">{{ $unreadMessages }}</span>
                    @endif
                </a>
                <a href="{{ route('app.profile') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('My Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">{{ __('Sign Out') }}</button>
                </form>
            @else
                <a href="{{ locale_path('/login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300">{{ __('Sign In') }}</a>
                <a href="{{ locale_path('/register') }}" class="block rounded-lg bg-brand px-3 py-2 text-center text-sm font-semibold text-white hover:bg-blue-500">{{ __('Sign Up') }}</a>
            @endauth
            <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
            <div class="px-3 py-1">
                <x-language-switcher />
            </div>
        </nav>
    </div>
</header>
