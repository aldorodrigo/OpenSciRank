<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- SEO Meta Tags --}}
        <title>{{ $title ?? config('app.name', 'Editorial Standards Platform') }}</title>
        <meta name="description" content="{{ $description ?? __('Editorial Standards Platform - Global platform for editorial evaluation and visibility for scientific journals and academic books.') }}">
        <meta name="keywords" content="{{ $keywords ?? __('editorial evaluation, scientific journals, academic books, editorial standards, editorial standards seal') }}">
        <meta name="author" content="Editorial Standards Platform">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- Hreflang alternate links --}}
        @php
            $defaultLocale = config('app.fallback_locale', 'en');
            $currentPath = request()->path();
            $pathWithoutLocale = $currentPath;
            foreach (config('app.available_locales', []) as $loc) {
                if ($loc !== $defaultLocale && (str_starts_with($currentPath, $loc . '/') || $currentPath === $loc)) {
                    $pathWithoutLocale = $currentPath === $loc ? '' : substr($currentPath, strlen($loc) + 1);
                    break;
                }
            }
        @endphp
        @foreach (config('app.available_locales', ['en']) as $hrefLocale)
            @php
                $hrefPath = $hrefLocale === $defaultLocale
                    ? '/' . ltrim($pathWithoutLocale, '/')
                    : '/' . $hrefLocale . '/' . ltrim($pathWithoutLocale, '/');
                $hrefPath = rtrim($hrefPath, '/') ?: '/';
            @endphp
            <link rel="alternate" hreflang="{{ $hrefLocale }}" href="{{ url($hrefPath) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ url('/' . ltrim($pathWithoutLocale, '/')) }}">

        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $title ?? config('app.name', 'Editorial Standards Platform') }}">
        <meta property="og:description" content="{{ $description ?? __('Global platform for editorial evaluation and visibility for scientific journals.') }}">
        <meta property="og:image" content="{{ $ogImage ?? asset('images/og-default.png') }}">
        <meta property="og:locale" content="{{ app()->getLocale() }}">
        <meta property="og:site_name" content="Editorial Standards Platform">

        {{-- Twitter --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Editorial Standards Platform') }}">
        <meta name="twitter:description" content="{{ $description ?? __('Global platform for editorial evaluation and visibility for scientific journals.') }}">
        <meta name="twitter:image" content="{{ $ogImage ?? asset('images/og-default.png') }}">

        {{-- Favicon --}}
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

        {{-- JSON-LD Structured Data --}}
        @isset($jsonLd){{ $jsonLd }}@endisset

        {{-- Styles --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100">
        {{-- Navigation --}}
        @isset($header)
            <x-site-header />
        @endisset

        {{-- Main Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        @isset($footer)
            {{ $footer }}
        @else
            <footer class="mt-auto border-t border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                <div class="container mx-auto px-4 py-14">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:2.5rem;">
                        {{-- Brand --}}
                        <div style="grid-column:span 2">
                            <a href="/" class="flex items-center gap-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                Editorial Standards
                            </a>
                            <p class="mt-4 max-w-xs text-sm text-gray-600 dark:text-gray-400">{{ __('Global platform for editorial evaluation and visibility for scientific journals and academic books.') }}</p>
                            <div class="mt-5 flex items-center gap-3">
                                <a href="https://twitter.com/editstandards" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-200 text-gray-600 transition hover:bg-indigo-100 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-400" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#e5e7eb;" aria-label="Twitter/X">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                                <a href="https://linkedin.com/company/editorialstandards" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-200 text-gray-600 transition hover:bg-indigo-100 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-400" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#e5e7eb;" aria-label="LinkedIn">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Platform --}}
                        <div>
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900 dark:text-white">{{ __('Platform') }}</h4>
                            <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                                <li><a href="{{ locale_path('/search') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Scientific Journals') }}</a></li>
                                <li><a href="{{ locale_path('/search?type=books') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Academic Books') }}</a></li>
                                <li><a href="{{ locale_path('/blog') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Blog</a></li>
                            </ul>
                        </div>

                        {{-- Services --}}
                        <div>
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900 dark:text-white">{{ __('Services') }}</h4>
                            <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                                <li><a href="{{ locale_path('/methodology') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Methodology') }}</a></li>
                                <li><a href="{{ locale_path('/pricing') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Editorial Evaluation') }}</a></li>
                                <li><a href="{{ locale_path('/register') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Register Journal') }}</a></li>
                                <li><a href="{{ locale_path('/contact') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('For Institutions') }}</a></li>
                            </ul>
                        </div>

                        {{-- Company --}}
                        <div>
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900 dark:text-white">{{ __('Company') }}</h4>
                            <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                                <li><a href="{{ locale_path('/about') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('About Us') }}</a></li>
                                <li><a href="{{ locale_path('/contact') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Contact') }}</a></li>
                                <li><a href="{{ locale_path('/terms') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Terms of Use') }}</a></li>
                                <li><a href="{{ locale_path('/privacy') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Privacy') }}</a></li>
                            </ul>
                        </div>
                    </div>

                    {{-- Bottom bar --}}
                    <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-8 sm:flex-row dark:border-gray-800" style="margin-top:3rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;border-top:1px solid #e5e7eb;padding-top:2rem;">
                        <p class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} Editorial Standards Platform. {{ __('All rights reserved.') }}</p>
                        <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                            <span>{{ __('Transparent editorial evaluation') }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/></svg>
                                Editorial Standards Seal
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        @endisset

        @livewireScripts
    </body>
</html>
