<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Step 1: Open Access Compliance
            $table->boolean('is_open_access')->nullable();
            $table->string('access_type')->nullable(); // full_oa, hybrid, restricted
            $table->boolean('articles_accessible_without_registration')->nullable();
            $table->boolean('allows_self_archiving')->nullable();
            $table->string('open_access_policy_url')->nullable();
            $table->boolean('has_embargo')->nullable();
            $table->integer('embargo_months')->nullable();

            // Step 2: About the Journal
            $table->string('abbreviated_name')->nullable();
            $table->text('description')->nullable();
            $table->json('subject_areas')->nullable(); // multiselect
            $table->json('target_audience')->nullable(); // multiselect
            $table->json('publication_languages')->nullable(); // multiselect
            $table->year('start_year')->nullable();

            // Step 3: Copyright and Licenses
            $table->string('license_type')->nullable(); // CC-BY, CC-BY-NC, etc
            $table->string('license_url')->nullable();
            $table->boolean('authors_retain_copyright')->nullable();
            $table->boolean('allows_commercial_reuse')->nullable();
            $table->text('copyright_policy')->nullable();
            $table->boolean('licenses_visible_in_articles')->nullable();

            // Step 4: Editorial
            $table->string('publishing_institution')->nullable();
            $table->string('editor_name')->nullable();
            $table->string('institutional_email')->nullable();
            $table->boolean('editorial_board_visible')->nullable();
            $table->string('editorial_board_url')->nullable();
            $table->string('peer_review_type')->nullable(); // double_blind, single_blind, open
            $table->string('publication_frequency')->nullable(); // annual, biannual, quarterly, continuous

            // Step 5: Business Model
            $table->boolean('charges_apc')->nullable();
            $table->decimal('apc_amount', 10, 2)->nullable();
            $table->string('apc_currency', 3)->nullable();
            $table->boolean('has_apc_waivers')->nullable();
            $table->json('funding_sources')->nullable(); // multiselect
            $table->boolean('has_advertising')->nullable();
            $table->boolean('business_model_transparent')->nullable();

            // Step 6: Best Practices
            $table->boolean('has_ethics_policy')->nullable();
            $table->boolean('adheres_to_cope')->nullable();
            $table->boolean('has_antiplagiarism_policy')->nullable();
            $table->string('antiplagiarism_tool')->nullable();
            $table->boolean('has_conflict_of_interest_policy')->nullable();
            $table->boolean('declares_ai_use')->nullable();
            $table->boolean('assigns_doi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                // Step 1
                'is_open_access', 'access_type', 'articles_accessible_without_registration',
                'allows_self_archiving', 'open_access_policy_url', 'has_embargo', 'embargo_months',
                // Step 2
                'abbreviated_name', 'description', 'subject_areas', 'target_audience',
                'publication_languages', 'start_year',
                // Step 3
                'license_type', 'license_url', 'authors_retain_copyright', 'allows_commercial_reuse',
                'copyright_policy', 'licenses_visible_in_articles',
                // Step 4
                'publishing_institution', 'editor_name', 'institutional_email',
                'editorial_board_visible', 'editorial_board_url', 'peer_review_type', 'publication_frequency',
                // Step 5
                'charges_apc', 'apc_amount', 'apc_currency', 'has_apc_waivers', 'funding_sources',
                'has_advertising', 'business_model_transparent',
                // Step 6
                'has_ethics_policy', 'adheres_to_cope', 'has_antiplagiarism_policy', 'antiplagiarism_tool',
                'has_conflict_of_interest_policy', 'declares_ai_use', 'assigns_doi',
            ]);
        });
    }
};
