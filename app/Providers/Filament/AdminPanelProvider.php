<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Editorial Blue (oxford-inspired) — primary unificado de la marca.
            // Reemplaza el Color::Amber legacy (Sprint 5 #51 — ver BRAND.md, sección "Excepción").
            ->colors([
                'primary' => Color::hex('#1E3A8A'),
            ])
            // Branding profesional — reemplaza el "Filament" default.
            ->brandName('Editorial Standards')
            ->brandLogoHeight('2rem')
            // Favicon: SVG con mark Editorial Blue (Sprint 5 #51 — ver BRAND.md).
            // SVG funciona en browsers modernos (Chrome 80+, Firefox 41+, Safari 9+).
            // .ico legacy pendiente de regenerar externamente (rasterización fuera del setup local).
            ->favicon(asset('favicon.svg'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget quitado — mostraba "Filament v3.x" + "Star on GitHub"
                // y delataba el framework underneath en el dashboard admin.
            ])
            ->navigationGroups([
                __('navigation.content'),
                __('navigation.evaluation'),
                __('navigation.commercial'),
                __('navigation.system'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                // Roadmap #35 — el evaluator aterriza en su escritorio, no en el
                // Dashboard genérico de admin.
                \App\Http\Middleware\RedirectEvaluatorToDesk::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::STYLES_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@vite(\'resources/css/app.css\')'),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_START,
                fn (): string => \Illuminate\Support\Facades\Blade::render('<x-site-header />'),
            );
    }
}
