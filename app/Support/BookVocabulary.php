<?php

namespace App\Support;

/**
 * Fuente única de los vocabularios controlados de libros (issue #71).
 *
 * Antes cada pantalla declaraba sus propias opciones: el wizard público
 * (`BookSubmissionWizard`) guardaba `monograph`/`postgrado`/`cc_by_nc_sa`, y el
 * admin (`BookResource`) sólo aceptaba `libro_academico`/`posgrado`/`CC-BY-NC-SA`.
 * Resultado: ningún libro cargado por un editor podía guardarse desde Filament
 * — el admin quedaba obligado a re-elegir los selects, pisando lo que el editor
 * había cargado, y eso bloqueaba la aprobación del listado (pagado o de cortesía).
 *
 * El vocabulario canónico es el del wizard: es el que produce todo el dato nuevo
 * y usa términos internacionales. Los valores viejos del admin se convierten con
 * `normalize()`, que además usa la migración de normalización de datos.
 *
 * Las etiquetas viven en `lang/{es,en,pt}/book_vocab.php`.
 */
class BookVocabulary
{
    /**
     * Campos con vocabulario controlado. Los cuatro últimos son multivaluados
     * o auxiliares; el resto mapea 1:1 a una columna de `books`.
     */
    public const FIELDS = [
        'book_type',
        'author_role',
        'academic_level',
        'knowledge_area',
        'access_type',
        'license_type',
        'publication_model',
        'funded_by',
        'format',
        'review_type',
        'index',
        'language',
    ];

    /**
     * Columnas de `books` que guardan un único valor del vocabulario homónimo.
     *
     * @var array<string, string> columna => vocabulario
     */
    public const SINGLE_VALUE_COLUMNS = [
        'book_type' => 'book_type',
        'academic_level' => 'academic_level',
        'access_type' => 'access_type',
        'license_type' => 'license_type',
        'publication_model' => 'publication_model',
        'format' => 'format',
        'review_type' => 'review_type',
    ];

    /**
     * Columnas JSON de `books` con listas de valores.
     *
     * @var array<string, string> columna => vocabulario
     */
    public const MULTI_VALUE_COLUMNS = [
        'indexes' => 'index',
        'funded_by' => 'funded_by',
        'knowledge_areas' => 'knowledge_area',
    ];

    /**
     * Valores heredados del vocabulario viejo del admin → valor canónico.
     *
     * Criterios de las equivalencias no obvias:
     *  - `libro_academico`/`libro_cientifico` eran categorías genéricas sin
     *    equivalente directo; la más cercana es la monografía.
     *  - `free` y `sponsored` describían el precio para el lector, no el modelo:
     *    ambos son acceso abierto sin cargo (diamante). Quién financia queda en
     *    `funded_by`.
     *  - `immediate`/`embargo`/`closed` medían el *momento* de apertura; el
     *    vocabulario canónico usa la taxonomía de colores del acceso abierto,
     *    así que se mapean a su color equivalente.
     *
     * @var array<string, array<string, string>>
     */
    private const LEGACY = [
        'book_type' => [
            'libro_cientifico' => 'monograph',
            'libro_academico' => 'monograph',
            'libro_tecnico' => 'reference_work',
            'manual' => 'textbook',
            'capitulo_libro' => 'other',
        ],
        'academic_level' => [
            'posgrado' => 'postgrado',
            'investigacion' => 'investigadores',
        ],
        'license_type' => [
            'cc-by' => 'cc_by',
            'cc-by-sa' => 'cc_by_sa',
            'cc-by-nd' => 'cc_by_nd',
            'cc-by-nc' => 'cc_by_nc',
            'cc-by-nc-sa' => 'cc_by_nc_sa',
            'cc-by-nc-nd' => 'cc_by_nc_nd',
            'copyright' => 'copyright_all_rights_reserved',
        ],
        'publication_model' => [
            'free' => 'open_no_apc',
            'sponsored' => 'open_no_apc',
        ],
        'access_type' => [
            'immediate' => 'gold',
            'embargo' => 'green',
            'closed' => 'bronze',
        ],
        'format' => [
            'pdf' => 'digital',
            'epub' => 'digital',
            'print' => 'impreso',
            'hybrid' => 'hibrido',
        ],
        'index' => [
            'scopus' => 'scopus_book_citation_index',
        ],
        'funded_by' => [
            'university' => 'institution',
            'project' => 'grant',
            'author' => 'self_funded',
        ],
        // Los seeders viejos guardaban las áreas en inglés.
        'knowledge_area' => [
            'natural_sciences' => 'ciencias_exactas_y_naturales',
            'engineering' => 'ingenieria_y_tecnologia',
            'medical_sciences' => 'ciencias_medicas_y_de_la_salud',
            'health_sciences' => 'ciencias_medicas_y_de_la_salud',
            'agricultural_sciences' => 'ciencias_agricolas',
            'social_sciences' => 'ciencias_sociales',
            'humanities' => 'humanidades',
        ],
    ];

    /**
     * Opciones `valor => etiqueta traducida` de un vocabulario.
     *
     * @return array<string, string>
     */
    public static function options(string $field): array
    {
        $options = __("book_vocab.{$field}");

        // __() devuelve la clave cruda si el archivo o la clave no existen.
        return is_array($options) ? $options : [];
    }

    /**
     * Valores válidos de un vocabulario.
     *
     * @return array<int, string>
     */
    public static function values(string $field): array
    {
        return array_keys(self::options($field));
    }

    /**
     * Etiqueta de un valor. Devuelve el valor crudo si no está en el
     * vocabulario, para no esconder datos sucios.
     */
    public static function label(string $field, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::options($field)[$value] ?? $value;
    }

    /**
     * Etiquetas de una lista de valores (campos JSON como `indexes`).
     *
     * @param  iterable<string>|null  $values
     * @return array<int, string>
     */
    public static function labels(string $field, ?iterable $values): array
    {
        $labels = [];

        foreach ($values ?? [] as $value) {
            if (is_string($value) && $value !== '') {
                $labels[] = self::label($field, $value);
            }
        }

        return $labels;
    }

    /**
     * Convierte un valor legacy al canónico. Deja pasar sin tocar los valores
     * que ya son canónicos y los desconocidos (para no inventar datos).
     */
    public static function normalize(string $field, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (in_array($value, self::values($field), true)) {
            return $value;
        }

        // Las licencias viejas venían en mayúsculas (`CC-BY-NC-SA`).
        return self::LEGACY[$field][strtolower($value)] ?? $value;
    }

    /**
     * Normaliza una lista de valores, sin duplicados y sin vacíos.
     *
     * @param  iterable<string>|null  $values
     * @return array<int, string>
     */
    public static function normalizeMany(string $field, ?iterable $values): array
    {
        $normalized = [];

        foreach ($values ?? [] as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $normalized[] = self::normalize($field, $value);
        }

        return array_values(array_unique(array_filter($normalized)));
    }
}
