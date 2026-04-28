<?php

return [
    /*
     * If a translation has not been set for a given locale, this locale will
     * be used as a fallback. By default the application's fallback locale
     * (see config('app.fallback_locale')) is used.
     */
    'fallback_locale' => config('app.fallback_locale'),

    /*
     * If a translation has not been set for a given locale and the fallback
     * locale, any other available locale will be chosen instead.
     */
    'fallback_any' => true,

    /*
     * If translation is missing for given key, this callback will be called.
     */
    'missing_key_callback' => null,

    /*
     * Allow null/empty string values to be saved as translations.
     */
    'allow_null_for_translation' => false,
    'allow_empty_string_for_translation' => false,
];
