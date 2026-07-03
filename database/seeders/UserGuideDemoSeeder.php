<?php

namespace Database\Seeders;

use App\Models\AdminTask;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\CriteriaItem;
use App\Models\Journal;
use App\Models\JournalEvaluationScore;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeder de datos DEMO para capturas de una guía de usuario (untracked).
 *
 * Aditivo e idempotente: NO corre migrate:fresh. Reutiliza el editor de prueba
 * y sus 6 revistas draft (creadas por EditorTestJournalsSeeder), transicionando
 * 5 de ellas a estados variados, y crea un evaluador de prueba, libros y una
 * conversación con mensajes para poblar la bandeja.
 *
 * Se corre standalone:
 *   ./vendor/bin/sail artisan db:seed --class=UserGuideDemoSeeder
 *
 * Credenciales:
 *   Editor:     editor.prueba@editorialstandards.com     / password
 *   Evaluador:  evaluador.prueba@editorialstandards.com  / password
 */
class UserGuideDemoSeeder extends Seeder
{
    public const EDITOR_EMAIL = 'editor.prueba@editorialstandards.com';

    public const EVALUATOR_EMAIL = 'evaluador.prueba@editorialstandards.com';

    public const PASSWORD = 'password';

    public function run(): void
    {
        // ── 0. Prerrequisitos ───────────────────────────────────────────
        // Asegurar que el editor de prueba y sus revistas draft existan.
        $editor = User::where('email', self::EDITOR_EMAIL)->first();
        if (! $editor || Journal::where('user_id', $editor->id)->count() < 5) {
            $this->command->warn('Editor de prueba o sus revistas no encontradas: corriendo EditorTestJournalsSeeder primero.');
            $this->call(EditorTestJournalsSeeder::class);
            $editor = User::where('email', self::EDITOR_EMAIL)->first();
        }

        // Editor verificado (rutas /app usan middleware `verified`).
        $editor->forceFill(['email_verified_at' => now()])->save();

        // Rol Spatie evaluator.
        if (! Role::where('name', 'evaluator')->exists()) {
            $this->command->warn('Rol evaluator no existe: corriendo EvaluatorRoleSeeder.');
            $this->call(EvaluatorRoleSeeder::class);
        }

        // ── 1. Evaluador de prueba ──────────────────────────────────────
        $evaluator = User::firstOrCreate(
            ['email' => self::EVALUATOR_EMAIL],
            [
                'name' => 'Evaluador de Prueba',
                'password' => Hash::make(self::PASSWORD),
            ]
        );
        $evaluator->forceFill(['email_verified_at' => now()])->save();
        if (! $evaluator->hasRole('evaluator')) {
            $evaluator->assignRole('evaluator');
        }

        // ── 2. Revistas del editor en variedad de estados ───────────────
        // Reutilizamos 5 de las 6 revistas draft por slug (definidas en
        // EditorTestJournalsSeeder). Cada una pasa a un estado distinto.
        $journals = Journal::where('user_id', $editor->id)
            ->orderBy('id')
            ->get();

        if ($journals->count() < 5) {
            $this->command->error('Se esperaban >=5 revistas del editor de prueba; hay '.$journals->count().'. Abortando.');

            return;
        }

        $criteria = CriteriaItem::active()->orderBy('order')->get();
        // Indicadores críticos (excluyentes) según metodología.
        $criticalCodes = ['1.1', '2.1', '3.1', '4.2', '5.1'];

        // -- 2a. SUBMITTED: pagada, task de evaluación asignada al evaluador --
        $submitted = $journals[0];
        $submitted->update([
            'status' => 'submitted',
            'submitted_at' => now()->subDays(3),
            'assigned_evaluator_id' => $evaluator->id,
            'current_score' => null,
            'evaluated_at' => null,
            'evaluation_notes' => null,
        ]);

        $evalProduct = Product::where('slug', 'journal-evaluation')->first();
        $submittedPayment = Payment::updateOrCreate(
            [
                'payable_type' => Journal::class,
                'payable_id' => $submitted->id,
                'status' => 'completed',
            ],
            [
                'user_id' => $editor->id,
                'product_id' => $evalProduct?->id,
                'provider' => 'stripe',
                'transaction_id' => 'demo_pi_'.$submitted->id,
                'amount' => $evalProduct?->price ?? 99,
                'currency' => 'USD',
                'metadata' => [
                    'stripe_session_id' => 'demo_cs_'.$submitted->id,
                    'is_express' => false,
                    'addon_slugs' => '',
                    'coupon_code' => null,
                    'is_renewal' => false,
                ],
            ]
        );

        // Task de evaluación pending, asignada al evaluador (sincroniza ambos campos).
        $evalTask = AdminTask::firstOrCreate(
            [
                'type' => AdminTask::TYPE_EVALUATE_JOURNAL,
                'related_type' => Journal::class,
                'related_id' => $submitted->id,
            ],
            [
                'title_key' => 'tasks.evaluate_journal',
                'title_params' => ['name' => $submitted->getTranslationWithFallback('title')],
                'payment_id' => $submittedPayment->id,
                'status' => AdminTask::STATUS_PENDING,
                'priority' => AdminTask::PRIORITY_NORMAL,
                'due_at' => AdminTask::calculateDueAt(AdminTask::TYPE_EVALUATE_JOURNAL, false, now()->subDays(3)),
            ]
        );
        // assignToUser sincroniza journal.assigned_evaluator_id + task.assigned_to.
        $evalTask->assignToUser($evaluator);

        // -- 2b. CERTIFIED: sello activo, score alto, scores por criterio --
        $certified = $journals[1];
        $certified->update([
            'status' => 'certified',
            'submitted_at' => now()->subMonths(2),
            'evaluated_at' => now()->subMonths(2),
            'current_score' => 82,
            'current_level' => null,
            'evaluation_notes' => "La revista cumple con los estándares editoriales exigidos. "
                ."Se destacan la política de acceso abierto, la revisión por pares doble ciego "
                ."y la transparencia del comité editorial. Recomendaciones menores: ampliar la "
                ."declaración de uso de IA y detallar la política de conservación digital.",
            'seal_awarded_at' => now()->subMonths(2),
            'seal_expires_at' => now()->addMonths(10),
            'seal_status' => 'active',
            'assigned_evaluator_id' => $evaluator->id,
        ]);
        // 82% => la mayoría met; fallan algunos NO críticos.
        $this->seedScores($certified, $criteria, $evaluator, met: true, failCodes: ['1.4', '3.4', '5.3'], criticalCodes: $criticalCodes);

        // -- 2c. EVALUATED: score < 75, sin sello, con notas y scores --
        $evaluated = $journals[2];
        $evaluated->update([
            'status' => 'evaluated',
            'submitted_at' => now()->subMonths(1),
            'evaluated_at' => now()->subMonths(1),
            'current_score' => 61,
            'current_level' => null,
            'evaluation_notes' => "La revista alcanza un 61% de cumplimiento y no obtiene el Sello "
                ."de Estándares Editoriales en esta instancia. Observaciones principales: "
                ."(1) falta publicar la política antiplagio y la herramienta utilizada; "
                ."(2) el comité editorial no expone las afiliaciones institucionales completas; "
                ."(3) no se declara la política de conflictos de interés. Se sugiere subsanar "
                ."estos puntos y solicitar una re-evaluación.",
            'seal_status' => null,
            'assigned_evaluator_id' => $evaluator->id,
        ]);
        $this->seedScores($evaluated, $criteria, $evaluator, met: true, failCodes: ['1.4', '2.3', '3.3', '3.4', '4.3', '5.2', '5.3'], criticalCodes: $criticalCodes);

        // -- 2d. REQUIRES_CHANGES_EVALUATION: notas pidiendo correcciones --
        $requiresChanges = $journals[3];
        $requiresChanges->update([
            'status' => 'requires_changes_evaluation',
            'submitted_at' => now()->subDays(6),
            'evaluated_at' => now()->subDays(2),
            'current_score' => null,
            'evaluation_notes' => "Antes de continuar con la evaluación necesitamos que corrijas "
                ."los siguientes puntos: \n\n"
                ."1) El enlace a la política de acceso abierto (open_access_policy_url) "
                ."devuelve error 404. Actualizá la URL. \n"
                ."2) Falta cargar el ISSN online en el formulario. \n"
                ."3) La declaración de licencia (CC-BY) no aparece visible en los artículos "
                ."publicados; adjuntá una captura o corregí el pie de los PDF. \n\n"
                ."Una vez corregido, reenviá la revista (la reevaluación de estos cambios es gratuita).",
            'assigned_evaluator_id' => $evaluator->id,
        ]);

        // -- 2e. LISTED --
        $listed = $journals[4];
        $listed->update([
            'status' => 'listed',
            'submitted_at' => now()->subMonths(1),
            'listed_at' => now()->subWeeks(3),
            'assigned_evaluator_id' => null,
        ]);

        // ── 3. Libros del editor (draft + listed) ───────────────────────
        $bookDraft = $this->seedBook(
            $editor,
            title: 'Metodologías de Investigación en Ciencias Sociales',
            status: 'draft',
            authors: [
                ['full_name' => 'Dra. Adriana La Fuente', 'role' => 'author', 'country_code' => 'PY', 'order' => 0],
                ['full_name' => 'Mgtr. Rolando Ortega', 'role' => 'author', 'country_code' => 'PY', 'order' => 1],
            ],
            extra: [
                'submission_date' => null,
                'approval_date' => null,
            ]
        );

        $bookListed = $this->seedBook(
            $editor,
            title: 'Innovación Tecnológica y Desarrollo Regional',
            status: 'listed',
            authors: [
                ['full_name' => 'Dr. Carlos Giménez', 'role' => 'editor', 'country_code' => 'AR', 'order' => 0],
                ['full_name' => 'Dra. Lucía Romero', 'role' => 'author', 'country_code' => 'ES', 'order' => 1],
                ['full_name' => 'Mgtr. Pedro Souza', 'role' => 'author', 'country_code' => 'BR', 'order' => 2],
            ],
            extra: [
                'submission_date' => now()->subMonths(1)->toDateString(),
                'approval_date' => now()->subWeeks(2)->toDateString(),
                'submitted_at' => now()->subMonths(1),
                'responsible_editor_id' => $evaluator->id,
            ]
        );

        // ── 4. Conversación anclada a la revista submitted ──────────────
        $this->seedConversation($submitted, $editor, $evaluator);

        // ── Resumen ─────────────────────────────────────────────────────
        $this->command->info('UserGuideDemoSeeder: datos demo generados.');
        $this->command->info('Editor:    '.self::EDITOR_EMAIL.' / '.self::PASSWORD.' (verificado)');
        $this->command->info('Evaluador: '.self::EVALUATOR_EMAIL.' / '.self::PASSWORD.' (verificado, rol evaluator)');
        $this->command->line(" - submitted:                  #{$submitted->id} {$submitted->slug} (task eval asignada al evaluador)");
        $this->command->line(" - certified:                  #{$certified->id} {$certified->slug} (sello activo, 82%)");
        $this->command->line(" - evaluated:                  #{$evaluated->id} {$evaluated->slug} (61%, sin sello)");
        $this->command->line(" - requires_changes_evaluation:#{$requiresChanges->id} {$requiresChanges->slug}");
        $this->command->line(" - listed:                     #{$listed->id} {$listed->slug}");
        $this->command->line(" - book draft:                 #{$bookDraft->id} {$bookDraft->slug}");
        $this->command->line(" - book listed:                #{$bookListed->id} {$bookListed->slug}");
    }

    /**
     * Crea/actualiza los scores por criterio de una revista.
     *
     * @param  array<int,string>  $failCodes     códigos de criterio que quedan NO cumplidos
     * @param  array<int,string>  $criticalCodes códigos críticos (se fuerzan a met si $met=true)
     */
    protected function seedScores(Journal $journal, $criteria, User $evaluator, bool $met, array $failCodes, array $criticalCodes): void
    {
        foreach ($criteria as $item) {
            $isMet = ! in_array($item->code, $failCodes, true);
            // Los críticos nunca deben caer en la lista de fallidos por accidente
            // salvo que se declaren explícitamente (aquí no lo hacemos).
            if (in_array($item->code, $criticalCodes, true) && ! in_array($item->code, $failCodes, true)) {
                $isMet = true;
            }

            JournalEvaluationScore::updateOrCreate(
                [
                    'journal_id' => $journal->id,
                    'criteria_item_id' => $item->id,
                ],
                [
                    'is_met' => $isMet,
                    'evaluator_id' => $evaluator->id,
                    'notes' => $isMet ? null : 'Indicador no cumplido — ver observaciones generales.',
                ]
            );
        }
    }

    /**
     * Crea/actualiza un libro con sus autores.
     *
     * @param  array<int,array<string,mixed>>  $authors
     * @param  array<string,mixed>  $extra
     */
    protected function seedBook(User $editor, string $title, string $status, array $authors, array $extra = []): Book
    {
        $slug = Str::slug($title);

        $book = Book::updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'user_id' => $editor->id,
                'primary_locale' => 'es',
                'title' => ['es' => $title, 'en' => $title],
                'subtitle' => ['es' => 'Fundamentos, enfoques y aplicaciones', 'en' => 'Foundations, approaches and applications'],
                'abstract' => [
                    'es' => 'Obra académica de acceso abierto que reúne aportes de investigadores '
                        .'iberoamericanos sobre el tema, con revisión por pares y comité editorial.',
                    'en' => 'Open access academic work gathering contributions from Ibero-American '
                        .'researchers, with peer review and an editorial committee.',
                ],
                'status' => $status,
                'book_type' => 'libro_academico',
                'primary_language' => 'es',
                'publication_year' => 2024,
                'edition' => '1a',
                'isbn' => '978-99967-'.rand(100, 999).'-'.rand(10, 99).'-'.rand(1, 9),
                'publisher' => 'Editorial Universitaria CLIC',
                'publisher_country' => 'PY',
                'publisher_city' => 'Asunción',
                'total_pages' => rand(180, 420),
                'format' => 'pdf',
                'keywords' => ['investigación', 'metodología', 'ciencias sociales', 'iberoamérica'],
                'knowledge_areas' => ['social_sciences', 'humanities'],
                'main_discipline' => 'Ciencias Sociales',
                'academic_level' => 'posgrado',
                'is_open_access' => true,
                'access_type' => 'immediate',
                'license_type' => 'cc_by',
                'rights_holder' => ['es' => 'Autores', 'en' => 'Authors'],
                'allows_reuse' => true,
                'allows_commercial_use' => false,
                'publication_model' => 'free',
                'has_peer_review' => true,
                'review_type' => 'double_blind',
                'has_editorial_committee' => true,
                'has_editorial_standards' => true,
                'has_antiplagiarism' => true,
                'has_ethics_code' => true,
                'is_indexed' => $status === 'listed',
                'indexes' => $status === 'listed' ? ['google_books', 'google_scholar', 'latindex'] : null,
            ], $extra)
        );

        // Autores (idempotente: limpiamos y recreamos).
        $book->authors()->delete();
        foreach ($authors as $a) {
            BookAuthor::create(array_merge(['book_id' => $book->id], $a));
        }

        return $book;
    }

    /**
     * Crea una conversación anclada a la revista con 2-3 mensajes editor↔evaluador.
     * Idempotente: si ya existe un hilo abierto para esa revista, no duplica.
     */
    protected function seedConversation(Journal $journal, User $editor, User $evaluator): void
    {
        $existing = Conversation::where('subject_type', Journal::class)
            ->where('subject_id', $journal->id)
            ->where('status', Conversation::STATUS_OPEN)
            ->first();

        if ($existing) {
            return;
        }

        $conversation = Conversation::create([
            'subject' => 'Consulta sobre la evaluación de «'.$journal->getTranslationWithFallback('title').'»',
            'subject_type' => Journal::class,
            'subject_id' => $journal->id,
            'started_by_user_id' => $evaluator->id,
            'status' => Conversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $evaluator->id,
            'role' => Conversation::ROLE_EVALUATOR,
            'joined_at' => now()->subDays(2),
            'last_read_at' => now(),
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $editor->id,
            'role' => Conversation::ROLE_EDITOR,
            'joined_at' => now()->subDays(2),
            'last_read_at' => now()->subDays(1),
        ]);

        $messages = [
            [$evaluator->id, 'Hola, soy el evaluador asignado a tu revista. Estoy revisando la política de acceso abierto y no encuentro el enlace a la licencia en los artículos. ¿Podrías indicarme dónde está publicada?', now()->subDays(2)],
            [$editor->id, 'Hola, gracias por el aviso. La licencia CC-BY figura en el pie de cada PDF, pero es cierto que no está en la página del artículo. La vamos a agregar esta semana.', now()->subDays(1)],
            [$evaluator->id, 'Perfecto, quedo a la espera. Con eso resuelto podemos avanzar con el resto de los indicadores. Gracias.', now()->subHours(4)],
        ];

        foreach ($messages as [$userId, $body, $at]) {
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'body' => $body,
                'notification_sent_at' => $at, // ya "enviado" para no disparar el batch en dev
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        $conversation->update(['last_message_at' => now()->subHours(4)]);
    }
}
