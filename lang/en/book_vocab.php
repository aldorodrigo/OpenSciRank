<?php

/**
 * Controlled book vocabularies (issue #71). See App\Support\BookVocabulary.
 *
 * Keys are the values persisted in `books`; do NOT change them without a
 * normalisation migration.
 */
return [

    'book_type' => [
        'monograph' => 'Monograph',
        'edited_volume' => 'Edited Volume',
        'textbook' => 'Textbook',
        'conference_proceedings' => 'Conference Proceedings',
        'reference_work' => 'Reference Work',
        'other' => 'Other',
    ],

    'author_role' => [
        'author' => 'Author',
        'editor' => 'Editor',
        'translator' => 'Translator',
        'coordinator' => 'Coordinator',
        'compiler' => 'Compiler',
        'illustrator' => 'Illustrator',
    ],

    'academic_level' => [
        'pregrado' => 'Undergraduate',
        'postgrado' => 'Postgraduate (Master, Specialisation)',
        'doctorado' => 'Doctorate',
        'investigadores' => 'Researchers / Professionals',
        'publico_general' => 'General Public / Outreach',
    ],

    'knowledge_area' => [
        'ciencias_exactas_y_naturales' => 'Natural Sciences',
        'ingenieria_y_tecnologia' => 'Engineering and Technology',
        'ciencias_medicas_y_de_la_salud' => 'Medical and Health Sciences',
        'ciencias_agricolas' => 'Agricultural Sciences',
        'ciencias_sociales' => 'Social Sciences',
        'humanidades' => 'Humanities',
    ],

    'access_type' => [
        'gold' => 'Gold',
        'green' => 'Green',
        'hybrid' => 'Hybrid',
        'bronze' => 'Bronze',
        'diamond' => 'Diamond / Platinum',
    ],

    'license_type' => [
        'cc_by' => 'CC BY (Attribution)',
        'cc_by_sa' => 'CC BY-SA (Attribution-ShareAlike)',
        'cc_by_nd' => 'CC BY-ND (Attribution-NoDerivatives)',
        'cc_by_nc' => 'CC BY-NC (Attribution-NonCommercial)',
        'cc_by_nc_sa' => 'CC BY-NC-SA (Attribution-NonCommercial-ShareAlike)',
        'cc_by_nc_nd' => 'CC BY-NC-ND (Attribution-NonCommercial-NoDerivatives)',
        'copyright_all_rights_reserved' => 'Copyright (All rights reserved)',
        'public_domain' => 'Public Domain',
        'other' => 'Other licence',
    ],

    'publication_model' => [
        'open_apc' => 'Open Access (with publication charge - APC)',
        'open_no_apc' => 'Diamond Open Access (no charge for author or reader)',
        'pay_download' => 'Pay per Download (digital)',
        'pay_print' => 'Print on demand (pay per copy)',
        'subscription' => 'Institutional subscription',
        'freemium' => 'Freemium (basic free, advanced paid)',
    ],

    'funded_by' => [
        'institution' => "Author's Institution",
        'grant' => 'Grant / Research Project',
        'society' => 'Scientific Society',
        'government' => 'Government',
        'crowdfunding' => 'Crowdfunding',
        'self_funded' => 'Self-funded',
        'other' => 'Other source',
        'none' => 'No external funding',
    ],

    'format' => [
        'digital' => 'Digital (PDF, EPUB, etc.)',
        'impreso' => 'Print',
        'hibrido' => 'Hybrid (print and digital)',
    ],

    'review_type' => [
        'single_blind' => 'Single Blind',
        'double_blind' => 'Double Blind',
        'open_review' => 'Open Review',
        'post_publication' => 'Post-Publication Review',
        'editorial_review' => 'Internal Editorial Review',
    ],

    'index' => [
        'scopus_book_citation_index' => 'Scopus / Book Citation Index',
        'doab' => 'DOAB (Directory of Open Access Books)',
        'scielo_livros' => 'SciELO Livros',
        'latindex' => 'Latindex',
        'dialnet' => 'Dialnet',
        'redib' => 'REDIB',
        'google_scholar' => 'Google Scholar',
        'google_books' => 'Google Books',
        'wos' => 'Web of Science',
        'other' => 'Other regional or international index',
    ],

    'language' => [
        'es' => 'Spanish',
        'en' => 'English',
        'pt' => 'Portuguese',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'other' => 'Other',
    ],

];
