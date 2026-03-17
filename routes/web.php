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

// Public Routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/search', function () {
    return view('search');
})->name('search');

Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

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
    return redirect('/search');
})->name('ranking');

Route::get('/blog', function () {
    return view('blog.index');
})->name('blog.index');

Route::get('/blog/{slug}', function (string $slug) {
    // Future: return view('blog.show', compact('post'));
    return redirect()->route('blog.index');
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

// Authenticated Portal Routes
Route::middleware(['auth'])->prefix('app')->name('app.')->group(function () {
    Route::get('/', EditorDashboard::class)->name('dashboard');
    Route::get('/profile', UserProfile::class)->name('profile');
    
    // Journal routes
    Route::get('/submit', SubmissionWizard::class)->name('submit');
    Route::get('/submit/{journal}', SubmissionWizard::class)->name('submit.edit');
    Route::get('/checkout/{journal}', PaymentCheckout::class)->name('checkout');
    
    // Book routes
    Route::get('/book/submit', BookSubmissionWizard::class)->name('book.submit');
    Route::get('/book/submit/{book}', BookSubmissionWizard::class)->name('book.submit.edit');
    Route::get('/book/checkout/{book}', BookPaymentCheckout::class)->name('book.checkout');
});
