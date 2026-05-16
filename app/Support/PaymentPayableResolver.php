<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Journal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Sprint 3.7 #44 — resolver el payable correcto para un producto + editor.
 *
 * Cada producto requiere un payable de un tipo específico (Journal/Book/User).
 * Si el contexto del hilo (related del task) no provee uno compatible, el
 * sistema intenta resolverlo de los recursos del editor.
 *
 * Reglas:
 *  - new-journal-consulting → User (el editor mismo)
 *  - support-credit         → User (el editor mismo, Sprint 3.7 #46)
 *  - book-listing*          → Book del editor
 *  - resto (eval, reeval,
 *    renewals, action-plan) → Journal del editor
 */
class PaymentPayableResolver
{
    /**
     * Devuelve la clase del payable que espera el producto.
     *
     * @return class-string<Model>
     */
    public static function expectedPayableTypeFor(Product $product): string
    {
        return match (true) {
            in_array($product->slug, ['new-journal-consulting', 'support-credit'], true) => User::class,
            str_starts_with($product->slug, 'book-listing') => Book::class,
            default => Journal::class,
        };
    }

    /**
     * Devuelve un nombre legible del tipo esperado para mensajes de error.
     */
    public static function expectedPayableLabel(Product $product): string
    {
        return match (self::expectedPayableTypeFor($product)) {
            User::class => __('usuario (editor)'),
            Book::class => __('libro'),
            Journal::class => __('revista'),
            default => __('recurso'),
        };
    }

    /**
     * Devuelve TODOS los payables compatibles con el producto disponibles
     * para el editor. Se usa en la UI para mostrar el selector.
     *
     * @return Collection<int, Model>
     */
    public static function availablePayablesFor(Product $product, User $editor): Collection
    {
        return match (self::expectedPayableTypeFor($product)) {
            User::class => collect([$editor]),
            Journal::class => Journal::where('user_id', $editor->id)->orderBy('id')->get(),
            Book::class => Book::where('user_id', $editor->id)->orderBy('id')->get(),
            default => collect(),
        };
    }

    /**
     * Resuelve el payable correcto a usar para el pago.
     *
     * Lógica:
     *  1. Si `$defaultRelated` ya es del tipo esperado y pertenece al editor → devolverlo.
     *  2. Si no, buscar el primer recurso compatible del editor.
     *  3. Si no hay → null (el caller debe disparar error).
     */
    public static function resolveCorrectPayableFor(Product $product, ?Model $defaultRelated, User $editor): ?Model
    {
        $expectedType = self::expectedPayableTypeFor($product);

        // Caso 1: defaultRelated YA es del tipo correcto Y pertenece al editor
        if ($defaultRelated instanceof $expectedType) {
            $belongsToEditor = match ($expectedType) {
                User::class => $defaultRelated->id === $editor->id,
                default => $defaultRelated->user_id === $editor->id,
            };

            if ($belongsToEditor) {
                return $defaultRelated;
            }
        }

        // Caso 2: buscar el primer recurso compatible del editor (auto-asign)
        return self::availablePayablesFor($product, $editor)->first();
    }

    /**
     * Nombre legible de un payable para mostrar en UI/Stripe/exports.
     * Sprint 3.7 #46: User no tiene `getTranslationWithFallback`, así que
     * centralizamos acá para evitar BadMethodCallException en cada call site.
     */
    public static function payableDisplayName(Model $payable): string
    {
        if (method_exists($payable, 'getTranslationWithFallback')) {
            $title = $payable->getTranslationWithFallback('title');
            if (filled($title)) {
                return $title;
            }
        }

        return $payable->name ?? $payable->email ?? '#'.$payable->id;
    }

    /**
     * Resuelve un payable por id, validando que sea del tipo correcto y del editor.
     * Usado cuando el admin elige explícitamente del selector.
     */
    public static function findPayableForProduct(Product $product, int $payableId, User $editor): ?Model
    {
        $expectedType = self::expectedPayableTypeFor($product);

        $candidate = match ($expectedType) {
            User::class => User::find($payableId),
            Journal::class => Journal::find($payableId),
            Book::class => Book::find($payableId),
            default => null,
        };

        if (! $candidate) return null;

        $belongsToEditor = match ($expectedType) {
            User::class => $candidate->id === $editor->id,
            default => $candidate->user_id === $editor->id,
        };

        return $belongsToEditor ? $candidate : null;
    }
}
