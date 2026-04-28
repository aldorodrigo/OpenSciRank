<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureTranslatable();
    }

    protected function configureTranslatable(): void
    {
        $translatable = app(Translatable::class);
        $translatable->fallback(
            fallbackLocale: config('translatable.fallback_locale', config('app.fallback_locale')),
            fallbackAny: (bool) config('translatable.fallback_any', true),
            missingKeyCallback: config('translatable.missing_key_callback'),
        );
        $translatable->allowNullForTranslation((bool) config('translatable.allow_null_for_translation', false));
        $translatable->allowEmptyStringForTranslation((bool) config('translatable.allow_empty_string_for_translation', false));
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
