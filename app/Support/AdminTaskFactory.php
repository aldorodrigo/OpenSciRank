<?php

namespace App\Support;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Factory de admin_tasks. Centraliza la lógica de "qué task generar y
 * con qué prioridad/SLA" según el escenario (pago aceptado, listing
 * iniciado, pago huérfano). Sprint 3.6 #32.
 *
 * Acá NO se decide si la task se envía por email — eso lo hace el
 * lifecycle del modelo via assignToUser() o el cron tasks:check-overdue.
 */
class AdminTaskFactory
{
    /**
     * Genera la(s) task(s) derivada(s) de un Payment recién aceptado.
     * Llamado desde StripePaymentService::createPaymentFromSession().
     *
     * Reglas:
     *  - journal-evaluation               → TYPE_EVALUATE_JOURNAL
     *  - journal-reevaluation             → TYPE_REEVALUATE_JOURNAL
     *  - seal-renewal-{1,2,3}y            → TYPE_RENEWAL_EVALUATION
     *  - book-listing                     → TYPE_REVIEW_LISTING_BOOK
     *  - book-listing-featured-1y solo    → no genera task (automático)
     *  - addon action-plan-consulting     → TYPE_CONSULTING (task adicional)
     *
     * @return array<int, AdminTask>
     */
    public static function fromPayment(Payment $payment, ?Model $payable, array $metadata = []): array
    {
        $tasks = [];

        $mainSlug = $payment->product?->slug;
        $isExpress = (bool) ($payment->metadata['is_express'] ?? false);

        // Resolver tipo de la task principal según producto + payable
        $primaryType = self::resolvePrimaryType($mainSlug, $payable, $metadata);

        if ($primaryType !== null) {
            $tasks[] = self::createTask(
                type: $primaryType,
                payment: $payment,
                related: $payable,
                isExpress: $isExpress && in_array($primaryType, [
                    AdminTask::TYPE_EVALUATE_JOURNAL,
                    AdminTask::TYPE_REEVALUATE_JOURNAL,
                ], true),
            );
        }

        // Addon: action-plan-consulting (si fue comprado junto con el plan
        // principal viene en metadata.addon_slugs). Genera task aparte.
        $addonSlugs = self::extractAddonSlugs($metadata);
        if (in_array('action-plan-consulting', $addonSlugs, true)) {
            $tasks[] = self::createTask(
                type: AdminTask::TYPE_CONSULTING,
                payment: $payment,
                related: $payable,
                isExpress: false,
            );
        }

        return $tasks;
    }

    /**
     * Task generada cuando un editor solicita listing gratuito de revista.
     * Llamado desde SubmissionWizard::listJournal() (sin pago de por medio).
     */
    public static function forJournalListing(Journal $journal): AdminTask
    {
        return self::createTask(
            type: AdminTask::TYPE_REVIEW_LISTING_JOURNAL,
            payment: null,
            related: $journal,
            isExpress: false,
        );
    }

    /**
     * Task de pago huérfano (payable no encontrado al procesar webhook).
     * Prioridad high siempre. Sprint 1 #27 + Sprint 3.6 #32.
     */
    public static function forOrphanPayment(Payment $payment, string $payableType, int $payableId): AdminTask
    {
        return AdminTask::create([
            'type' => AdminTask::TYPE_ORPHAN_PAYMENT,
            'title_key' => 'tasks.orphan_payment',
            'title_params' => [
                'payable_type' => class_basename($payableType),
                'payable_id' => $payableId,
                'transaction' => $payment->transaction_id ?? '',
            ],
            'payment_id' => $payment->id,
            // related_type/related_id quedan null — el recurso fue soft-deleted
            'status' => AdminTask::STATUS_PENDING,
            'priority' => AdminTask::PRIORITY_HIGH,
            'due_at' => AdminTask::calculateDueAt(AdminTask::TYPE_ORPHAN_PAYMENT),
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────

    protected static function resolvePrimaryType(?string $slug, ?Model $payable, array $metadata): ?string
    {
        if ($slug === null || $payable === null) {
            return null;
        }

        $isRenewal = ($metadata['is_renewal'] ?? '0') === '1';

        // Renovación (Opción B vigente): siempre genera task de re-evaluación
        if ($isRenewal && str_starts_with($slug, 'seal-renewal-')) {
            return AdminTask::TYPE_RENEWAL_EVALUATION;
        }

        return match (true) {
            $slug === 'journal-evaluation' => AdminTask::TYPE_EVALUATE_JOURNAL,
            $slug === 'journal-reevaluation' => AdminTask::TYPE_REEVALUATE_JOURNAL,
            $slug === 'book-listing' => AdminTask::TYPE_REVIEW_LISTING_BOOK,
            // book-listing-featured-1y standalone (libro ya listed): sin task
            $slug === 'book-listing-featured-1y' => null,
            // action-plan-consulting NUNCA es producto principal en flujo real,
            // pero por defensa devolvemos consulting si llega así
            $slug === 'action-plan-consulting' => AdminTask::TYPE_CONSULTING,
            default => null,
        };
    }

    protected static function createTask(
        string $type,
        ?Payment $payment,
        ?Model $related,
        bool $isExpress = false,
    ): AdminTask {
        return AdminTask::create([
            'type' => $type,
            'title_key' => self::titleKeyFor($type),
            'title_params' => self::titleParamsFor($type, $related, $payment),
            'payment_id' => $payment?->id,
            'related_type' => $related ? $related::class : null,
            'related_id' => $related?->id,
            'status' => AdminTask::STATUS_PENDING,
            'priority' => $isExpress ? AdminTask::PRIORITY_HIGH : AdminTask::PRIORITY_NORMAL,
            'due_at' => AdminTask::calculateDueAt($type, $isExpress),
        ]);
    }

    protected static function titleKeyFor(string $type): string
    {
        return match ($type) {
            AdminTask::TYPE_EVALUATE_JOURNAL => 'tasks.evaluate_journal',
            AdminTask::TYPE_REEVALUATE_JOURNAL => 'tasks.reevaluate_journal',
            AdminTask::TYPE_RENEWAL_EVALUATION => 'tasks.renewal_evaluation',
            AdminTask::TYPE_REVIEW_LISTING_JOURNAL => 'tasks.review_listing_journal',
            AdminTask::TYPE_REVIEW_LISTING_BOOK => 'tasks.review_listing_book',
            AdminTask::TYPE_CONSULTING => 'tasks.consulting',
            AdminTask::TYPE_ORPHAN_PAYMENT => 'tasks.orphan_payment',
            default => 'tasks.unknown',
        };
    }

    protected static function titleParamsFor(string $type, ?Model $related, ?Payment $payment): array
    {
        $name = self::resourceName($related);

        $params = ['name' => $name];

        if ($type === AdminTask::TYPE_RENEWAL_EVALUATION && $related instanceof Journal) {
            $params['years'] = $related->pending_renewal_years ?? 1;
        }

        if ($type === AdminTask::TYPE_CONSULTING && $payment) {
            $params['amount'] = (float) $payment->amount;
            $params['currency'] = $payment->currency;
        }

        return $params;
    }

    protected static function resourceName(?Model $related): string
    {
        if ($related === null) {
            return '—';
        }

        if (method_exists($related, 'getTranslationWithFallback')) {
            // Journal/Book con HasTranslations: title está traducido
            return $related->getTranslationWithFallback('title');
        }

        return $related->name ?? (string) $related->id;
    }

    /**
     * @return array<string>
     */
    protected static function extractAddonSlugs(array $metadata): array
    {
        $raw = $metadata['addon_slugs'] ?? '';

        if (is_array($raw)) {
            return array_filter(array_map('trim', $raw));
        }

        if (is_string($raw) && $raw !== '') {
            return array_filter(array_map('trim', explode(',', $raw)));
        }

        return [];
    }
}
