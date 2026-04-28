<?php

use Illuminate\Support\Facades\App;

if (! function_exists('locale_url')) {
    /**
     * Generate a localized URL for the given route name.
     * Default locale (en) has no prefix; other locales get /{locale}/ prefix.
     */
    function locale_url(string $routeName, mixed $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $defaultLocale = config('app.fallback_locale', 'en');

        $url = route($routeName, $params);

        if ($locale !== $defaultLocale) {
            $baseUrl = rtrim(config('app.url'), '/');
            $path = parse_url($url, PHP_URL_PATH) ?? '/';

            // Skip prefix for non-locale-aware routes (Fortify, admin)
            $unprefixedRoutes = [
                '/login', '/register', '/logout',
                '/forgot-password', '/reset-password',
                '/verify-email', '/email/verification-notification',
                '/two-factor-challenge', '/confirm-password', '/user/confirmed-password-status',
                '/admin',
            ];
            foreach ($unprefixedRoutes as $unprefixed) {
                if ($path === $unprefixed || str_starts_with($path, $unprefixed . '/')) {
                    return $url;
                }
            }

            $url = $baseUrl . '/' . $locale . $path;
        }

        return $url;
    }
}

if (! function_exists('locale_path')) {
    /**
     * Generate a localized path (without domain) for the given path.
     * Default locale has no prefix; other locales get /{locale} prefix.
     * Skips the prefix for routes that are not locale-aware (e.g. Fortify auth routes).
     */
    function locale_path(string $path, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $defaultLocale = config('app.fallback_locale', 'en');
        $cleanPath = '/' . ltrim($path, '/');

        // Routes registered without locale prefix (Fortify auth, admin, etc.)
        $unprefixedRoutes = [
            '/login', '/register', '/logout',
            '/forgot-password', '/reset-password',
            '/verify-email', '/email/verification-notification',
            '/two-factor-challenge', '/confirm-password', '/user/confirmed-password-status',
            '/admin',
        ];

        $pathOnly = strtok($cleanPath, '?');
        foreach ($unprefixedRoutes as $unprefixed) {
            if ($pathOnly === $unprefixed || str_starts_with($pathOnly, $unprefixed . '/')) {
                return $cleanPath;
            }
        }

        if ($locale !== $defaultLocale) {
            return '/' . $locale . $cleanPath;
        }

        return $cleanPath;
    }
}
