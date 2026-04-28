<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', ['en']);
        $defaultLocale = config('app.locale', 'en');
        $fromUrl = false;

        // Priority 1: ?lang= query param (explicit user selection)
        $queryLang = $request->query('lang');
        if ($queryLang && in_array($queryLang, $availableLocales)) {
            $locale = $queryLang;

            // Build clean redirect URL: strip locale prefix, drop ?lang
            // We rely on the cookie (just set below) to drive locale on the next
            // request, avoiding 404s on routes that aren't locale-prefixed (e.g. /login).
            $segments = explode('/', trim($request->path(), '/'));
            if (isset($segments[0]) && in_array($segments[0], $availableLocales)) {
                array_shift($segments);
            }
            $newPath = '/' . implode('/', $segments);
            $newPath = $newPath === '/' ? '/' : rtrim($newPath, '/');

            // Preserve other query params
            $otherQuery = collect($request->query())->except('lang')->all();
            $cleanUrl = url($newPath) . (! empty($otherQuery) ? '?' . http_build_query($otherQuery) : '');

            $response = redirect($cleanUrl);
            $response->headers->setCookie(cookie('locale', $locale, 525600));
            return $response;
        }

        // Priority 2: URL segment
        $firstSegment = $request->segment(1);
        if ($firstSegment && in_array($firstSegment, $availableLocales)) {
            $locale = $firstSegment;
            $fromUrl = true;
        }
        // Priority 3: Cookie
        elseif ($request->cookie('locale') && in_array($request->cookie('locale'), $availableLocales)) {
            $locale = $request->cookie('locale');
        }
        // Priority 4: Accept-Language header
        else {
            $locale = $request->getPreferredLanguage($availableLocales) ?? $defaultLocale;
        }

        App::setLocale($locale);

        $response = $next($request);

        // Persist cookie when locale came from URL or differs from stored cookie
        if ($fromUrl || !$request->cookie('locale') || $request->cookie('locale') !== $locale) {
            $response->headers->setCookie(cookie('locale', $locale, 525600));
        }

        return $response;
    }
}
