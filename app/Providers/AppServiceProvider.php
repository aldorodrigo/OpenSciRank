<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Payment;
use App\Observers\BookObserver;
use App\Observers\PaymentObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
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
        $this->registerObservers();
        $this->registerQueryMacros();
    }

    /**
     * Macros de query reutilizables para buscar/ordenar sobre columnas JSON
     * traducibles (Spatie HasTranslations).
     *
     * En MySQL, las columnas JSON se comparan con collation binaria
     * (utf8mb4_bin) => LIKE/ORDER BY sensibles a mayúsculas y acentos. Por eso
     * un buscador plano `where('title','like','%ciencia%')` no encontraba
     * "Ciencia"/"Científica". Estos macros extraen el valor por locale y fuerzan
     * utf8mb4_0900_ai_ci (default MySQL 8.4: insensible a caso y acentos).
     *
     * Usar en cualquier buscador (Livewire y closures de Filament):
     *   $q->whereTranslatableLike(['title', 'abbreviated_name'], $search);
     *   $q->orderByTranslatable('title', $direction);
     */
    protected function registerQueryMacros(): void
    {
        Builder::macro('whereTranslatableLike', function (array|string $columns, ?string $search, array $locales = ['es', 'en', 'pt']) {
            /** @var Builder $this */
            $search = is_string($search) ? trim($search) : '';
            if ($search === '') {
                return $this;
            }

            $columns = (array) $columns;
            $driver = $this->getQuery()->getConnection()->getDriverName();
            $isMysql = in_array($driver, ['mysql', 'mariadb'], true);
            $term = '%'.mb_strtolower($search).'%';

            return $this->where(function (Builder $query) use ($columns, $locales, $term, $isMysql) {
                foreach ($columns as $column) {
                    foreach ($locales as $locale) {
                        $path = '$."'.$locale.'"';
                        if ($isMysql) {
                            $query->orWhereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, '{$path}')) COLLATE utf8mb4_0900_ai_ci LIKE ?",
                                [$term]
                            );
                        } else {
                            // sqlite/pgsql: al menos insensible a mayúsculas (ASCII).
                            $query->orWhereRaw(
                                "LOWER(JSON_EXTRACT(`{$column}`, '{$path}')) LIKE ?",
                                [$term]
                            );
                        }
                    }
                }
            });
        });

        Builder::macro('orderByTranslatable', function (string $column, string $direction = 'asc', string $locale = 'es') {
            /** @var Builder $this */
            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            $driver = $this->getQuery()->getConnection()->getDriverName();
            $path = '$."'.$locale.'"';

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                return $this->orderByRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, '{$path}')) COLLATE utf8mb4_0900_ai_ci {$direction}"
                );
            }

            return $this->orderByRaw("LOWER(JSON_EXTRACT(`{$column}`, '{$path}')) {$direction}");
        });
    }

    protected function registerObservers(): void
    {
        // Sprint 3.6 #32: auto-cerrar admin_tasks de review_listing_book
        // cuando el admin cambia el status del libro a listed/rejected.
        Book::observe(BookObserver::class);

        // Sprint 3.6 #32 sub-tarea 10: auto-cancelar admin_tasks asociadas
        // cuando un Payment pasa a status `refunded` (hook para Sprint 4 #6).
        Payment::observe(PaymentObserver::class);
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
