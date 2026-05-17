<?php

use Illuminate\Support\Facades\Route;
use App\Models\Journal;
use App\Models\Book;
use App\Livewire\EditorDashboard;
use App\Livewire\SubmissionWizard;
use App\Livewire\BookSubmissionWizard;
use App\Livewire\PaymentCheckout;
use App\Livewire\BookPaymentCheckout;
use App\Livewire\UserProfile;
use App\Http\Controllers\CheckoutSuccessController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\AdminTaskConversationController;

// Sitemap & SEO (no locale prefix)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/badge/{slug}.svg', [BadgeController::class, 'show'])->name('badge.svg');

// Stripe Webhook (CSRF excluded in bootstrap/app.php)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

// ────────────────────────────────────────────────────────
// Adjuntos de mensajes (ruta firmada — auth requerido)
// Sprint 3.7 #40
// ────────────────────────────────────────────────────────
Route::get('/messages/attachments/{attachment}', [MessageAttachmentController::class, 'download'])
    ->name('messages.attachment')
    ->middleware(['auth', 'signed']);

// ────────────────────────────────────────────────────────
// Preview de páginas de error — SOLO en entorno local.
// Útil para QA visual sin tener que forzar el error real (que con APP_DEBUG=true
// muestra el Whoops de Laravel en vez de las vistas custom).
// Bloqueado fuera de local para no exponer en producción.
// ────────────────────────────────────────────────────────
if (app()->environment('local')) {
    Route::get('/preview/error/{code}', function (string $code) {
        $allowed = ['401', '403', '404', '419', '429', '500', '503'];
        abort_unless(in_array($code, $allowed, true), 404);

        return response()->view("errors.{$code}", [], (int) $code);
    })->name('preview.error');
}

// ────────────────────────────────────────────────────────
// Checkout iniciado desde un link de pago de admin_task (Sprint 3.7 #44)
// Signed URL: el admin la genera al crear la task con producto seleccionado,
// el editor la abre vía mensaje en el hilo o copia/pega. Vencimiento 30 días.
// ────────────────────────────────────────────────────────
Route::get('/checkout/from-task/{task}', [\App\Http\Controllers\CheckoutFromTaskController::class, 'show'])
    ->name('checkout.from-task')
    ->middleware(['auth', 'signed']);

// Sprint 3.7 #46 — Success URL para checkouts task-based con payable=User.
// Stripe redirige acá tras pago confirmado. Sin `signed` middleware porque
// el redirect lo controla Stripe (no podemos firmar la URL antes de saber
// el session_id). La validación de ownership ocurre en el controller.
Route::get('/checkout/from-task/{task}/success', [\App\Http\Controllers\CheckoutFromTaskController::class, 'success'])
    ->name('checkout.from-task.success')
    ->middleware(['auth']);

// ────────────────────────────────────────────────────────
// Admin conversations (standalone, fuera de Filament)
// Sprint 3.7 #40
// ────────────────────────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\EnsureSuperAdmin::class])->group(function () {
    Route::get('/admin/conversations', \App\Livewire\AdminConversationsInbox::class)
        ->name('admin.conversations');

    Route::get('/admin/conversations/{conversation}', \App\Livewire\AdminConversationsInbox::class)
        ->name('admin.conversations.show');

    Route::post('/admin/tasks/{task}/conversation', [AdminTaskConversationController::class, 'open'])
        ->name('admin.tasks.open-conversation');
});

// ────────────────────────────────────────────────────────
// Localizable public routes
// ────────────────────────────────────────────────────────
$publicRoutes = function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::get('/search', function () {
        return view('search');
    })->name('search');

    Route::get('/pricing', function () {
        $products = \App\Models\Product::where('is_active', true)->get()->keyBy('slug');
        return view('pricing', compact('products'));
    })->name('pricing');

    Route::get('/seal-renewal', fn () => view('seal-renewal-info'))->name('seal.renewal.info');

    Route::get('/methodology', function () {
        return view('methodology');
    })->name('methodology');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/ranking', function () {
        return view('ranking');
    })->name('ranking');

    Route::get('/terms', function () {
        return view('terms');
    })->name('terms');

    Route::get('/privacy', function () {
        return view('privacy');
    })->name('privacy');

    Route::get('/blog', function (Illuminate\Http\Request $request) {
        $query = App\Models\CmsPost::published()->latest('published_at');

        if ($request->filled('category') && $request->category !== 'todos') {
            $query->byCategory($request->category);
        }

        $posts = $query->paginate(12);
        $featured = App\Models\CmsPost::published()->featured()->latest('published_at')->first();

        return view('blog.index', compact('posts', 'featured'));
    })->name('blog.index');

    Route::get('/blog/{slug}', function (string $slug) {
        $post = App\Models\CmsPost::published()->where('slug', $slug)->firstOrFail();
        $related = App\Models\CmsPost::published()
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    })->name('blog.show');

    Route::get('/journal/{slug}', function (string $slug) {
        $journal = Journal::where('slug', $slug)
            ->with(['evaluationScores.criteriaItem.category', 'user', 'harvestedArticles'])
            ->firstOrFail();
        return view('journal.show', compact('journal'));
    })->name('journal.show');

    Route::get('/journal/{slug}/articles', function (string $slug) {
        $journal = Journal::where('slug', $slug)->firstOrFail();
        $articles = $journal->harvestedArticles()->orderByDesc('date')->paginate(20);
        return view('journal.articles', compact('journal', 'articles'));
    })->name('journal.articles');

    Route::get('/book/{slug}', function (string $slug) {
        $book = Book::where('slug', $slug)->firstOrFail();
        return view('book.show', compact('book'));
    })->name('book.show');
};

// Default locale (en) — no prefix
Route::group([], $publicRoutes);

// Non-default locales (es, pt) — with /{locale} prefix
$nonDefaultLocales = collect(config('app.available_locales', ['en']))
    ->reject(fn ($l) => $l === config('app.locale', 'en'))
    ->all();

foreach ($nonDefaultLocales as $locale) {
    Route::prefix($locale)->group($publicRoutes);
}

// ──────────────────────────────────────────────────────
// Authenticated Portal Routes (locale-aware)
// ──────────────────────────────────────────────────────
$authenticatedRoutes = function () {
    Route::get('/', EditorDashboard::class)->name('dashboard');
    Route::get('/profile', UserProfile::class)->name('profile');
    Route::get('/payments', \App\Livewire\MyPayments::class)->name('payments');

    // Consultorías del editor (Sprint 3.7 #39)
    // Static routes must come before the {task} wildcard to avoid collision.
    Route::get('/consulting', \App\Livewire\EditorConsultingPanel::class)->name('consulting');
    Route::get('/consulting/checkout/new-journal', [\App\Http\Controllers\NewJournalConsultingCheckoutController::class, 'redirect'])->name('consulting.checkout.new');
    Route::get('/consulting/{task}/calendar.ics', [\App\Http\Controllers\ConsultingIcsController::class, 'download'])->name('consulting.ics');

    // Messages — Sprint 3.7 #40
    Route::get('/messages', \App\Livewire\EditorMessagesInbox::class)->name('messages');
    Route::get('/messages/{conversation}', \App\Livewire\EditorMessagesInbox::class)->name('messages.show');

    // Journal routes
    Route::get('/submit', SubmissionWizard::class)->name('submit');
    Route::get('/submit/{journal}', SubmissionWizard::class)->name('submit.edit');
    Route::get('/checkout/{journal}', PaymentCheckout::class)->name('checkout');
    Route::get('/checkout/{journal}/success', [CheckoutSuccessController::class, 'journal'])->name('checkout.success');
    Route::get('/renew/{journal}', PaymentCheckout::class)->name('renew');
    Route::get('/badge/{journal}', [BadgeController::class, 'page'])->name('badge');

    // Book routes
    Route::get('/book/submit', BookSubmissionWizard::class)->name('book.submit');
    Route::get('/book/submit/{book}', BookSubmissionWizard::class)->name('book.submit.edit');
    Route::get('/book/checkout/{book}', BookPaymentCheckout::class)->name('book.checkout');
    Route::get('/book/checkout/{book}/success', [CheckoutSuccessController::class, 'book'])->name('book.checkout.success');
};

// Default locale — /app/*
Route::middleware(['auth', 'verified'])->prefix('app')->name('app.')->group($authenticatedRoutes);

// Non-default locales — /{locale}/app/*
foreach ($nonDefaultLocales as $locale) {
    Route::middleware(['auth', 'verified'])->prefix($locale . '/app')->name('app.')->group($authenticatedRoutes);
}
