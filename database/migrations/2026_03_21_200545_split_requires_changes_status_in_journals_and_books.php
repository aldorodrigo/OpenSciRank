<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Split 'requires_changes' into 'requires_changes_listing' and 'requires_changes_evaluation'.
     *
     * Heuristic: if the journal has a current_score (was evaluated), it's evaluation changes.
     * Otherwise, it's listing changes.
     */
    public function up(): void
    {
        // Journals with evaluation score → requires_changes_evaluation
        DB::table('journals')
            ->where('status', 'requires_changes')
            ->whereNotNull('current_score')
            ->update(['status' => 'requires_changes_evaluation']);

        // Journals without evaluation score → requires_changes_listing
        DB::table('journals')
            ->where('status', 'requires_changes')
            ->update(['status' => 'requires_changes_listing']);

        // Books only have listing flow
        DB::table('books')
            ->where('status', 'requires_changes')
            ->update(['status' => 'requires_changes_listing']);
    }

    /**
     * Merge back into single 'requires_changes' status.
     */
    public function down(): void
    {
        DB::table('journals')
            ->whereIn('status', ['requires_changes_listing', 'requires_changes_evaluation'])
            ->update(['status' => 'requires_changes']);

        DB::table('books')
            ->whereIn('status', ['requires_changes_listing', 'requires_changes_evaluation'])
            ->update(['status' => 'requires_changes']);
    }
};
