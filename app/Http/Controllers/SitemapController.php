<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Journal;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $journals = Journal::whereIn('status', ['listed', 'evaluated', 'certified', 'indexed'])
            ->where(fn($q) => $q
                ->where('status', '!=', 'certified')
                ->orWhereNull('seal_expires_at')
                ->orWhere('seal_expires_at', '>', now())
            )
            ->whereNotNull('slug')
            ->select('slug', 'updated_at')
            ->get();

        $books = Book::whereIn('status', ['listed', 'indexed'])
            ->whereNotNull('slug')
            ->select('slug', 'updated_at')
            ->get();

        $content = view('sitemap', compact('journals', 'books'))->render();

        return response($content, 200)->header('Content-Type', 'application/xml');
    }
}
