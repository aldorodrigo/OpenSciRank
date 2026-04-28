<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Response;

class BadgeController extends Controller
{
    /**
     * Public SVG badge endpoint.
     */
    public function show(string $slug): Response
    {
        $journal = Journal::where('slug', $slug)->firstOrFail();

        $isActive = $journal->status === 'certified'
            && $journal->seal_expires_at
            && $journal->seal_expires_at->isFuture();

        $content = view('badge.seal', [
            'journal' => $journal,
            'isActive' => $isActive,
            'level' => $journal->current_level,
            'year' => $journal->seal_awarded_at?->year ?? now()->year,
        ])->render();

        return response($content, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Vary', 'Accept-Encoding');
    }

    /**
     * Authenticated badge page with embed codes.
     */
    public function page(Journal $journal)
    {
        if ($journal->user_id !== auth()->id()) {
            abort(403);
        }

        $isActive = $journal->status === 'certified'
            && $journal->seal_expires_at
            && $journal->seal_expires_at->isFuture();

        if (!$isActive) {
            return redirect()->route('app.dashboard')
                ->with('error', __('The badge is only available for journals with an active seal.'));
        }

        return view('badge.show', compact('journal'));
    }
}
