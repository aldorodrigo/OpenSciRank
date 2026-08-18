<?php

/**
 * Vocabularios controlados de libros (issue #71).
 *
 * Un único juego de valores para el wizard público y para el admin: antes cada
 * uno declaraba el suyo y ningún libro cargado por un editor podía guardarse en
 * Filament. Ver App\Support\BookVocabulary.
 *
 * Las claves son los valores que se persisten en `books`; NO cambiarlas sin una
 * migración de normalización.
 */
return [

    'book_type' => [
        'monograph' => 'Monografía',
        'edited_volume' => 'Volumen Editado',
        'textbook' => 'Libro de Texto',
        'conference_proceedings' => 'Actas de Congreso',
        'reference_work' => 'Obra de Referencia',
        'other' => 'Otro',
    ],

    'author_role' => [
        'author' => 'Autor',
        'editor' => 'Editor',
        'translator' => 'Traductor',
        'coordinator' => 'Coordinador',
        'compiler' => 'Compilador',
        'illustrator' => 'Ilustrador',
    ],

    'academic_level' => [
        'pregrado' => 'Pregrado (Grado)',
        'postgrado' => 'Postgrado (Máster, Especialización)',
        'doctorado' => 'Doctorado',
        'investigadores' => 'Investigadores / Profesionales',
        'publico_general' => 'Público General / Divulgación',
    ],

    'knowledge_area' => [
        'ciencias_exactas_y_naturales' => 'Ciencias Exactas y Naturales',
        'ingenieria_y_tecnologia' => 'Ingeniería y Tecnología',
        'ciencias_medicas_y_de_la_salud' => 'Ciencias Médicas y de la Salud',
        'ciencias_agricolas' => 'Ciencias Agrícolas',
        'ciencias_sociales' => 'Ciencias Sociales',
        'humanidades' => 'Humanidades',
    ],

    'access_type' => [
        'gold' => 'Dorado (Gold)',
        'green' => 'Verde (Green)',
        'hybrid' => 'Híbrido (Hybrid)',
        'bronze' => 'Bronce (Bronze)',
        'diamond' => 'Diamante / Platino (Diamond/Platinum)',
    ],

    'license_type' => [
        'cc_by' => 'CC BY (Atribución)',
        'cc_by_sa' => 'CC BY-SA (Atribución-CompartirIgual)',
        'cc_by_nd' => 'CC BY-ND (Atribución-SinDerivadas)',
        'cc_by_nc' => 'CC BY-NC (Atribución-NoComercial)',
        'cc_by_nc_sa' => 'CC BY-NC-SA (Atribución-NoComercial-CompartirIgual)',
        'cc_by_nc_nd' => 'CC BY-NC-ND (Atribución-NoComercial-SinDerivadas)',
        'copyright_all_rights_reserved' => 'Copyright (Todos los derechos reservados)',
        'public_domain' => 'Dominio Público',
        'other' => 'Otra licencia',
    ],

    'publication_model' => [
        'open_apc' => 'Acceso Abierto (con cargo por publicación - APC)',
        'open_no_apc' => 'Acceso Abierto Diamante (sin cargo ni para autor ni lector)',
        'pay_download' => 'Pago por Descarga (digital)',
        'pay_print' => 'Impreso bajo demanda (pago por copia)',
        'subscription' => 'Suscripción institucional',
        'freemium' => 'Freemium (básico gratis, avanzado de pago)',
    ],

    'funded_by' => [
        'institution' => 'Institución del Autor',
        'grant' => 'Subvención / Proyecto de Investigación',
        'society' => 'Sociedad Científica',
        'government' => 'Gobierno',
        'crowdfunding' => 'Crowdfunding',
        'self_funded' => 'Autofinanciado',
        'other' => 'Otra fuente',
        'none' => 'Sin financiación externa',
    ],

    'format' => [
        'digital' => 'Digital (PDF, EPUB, etc.)',
        'impreso' => 'Impreso',
        'hibrido' => 'Híbrido (impreso y digital)',
    ],

    'review_type' => [
        'single_blind' => 'Ciego Simple',
        'double_blind' => 'Doble Ciego',
        'open_review' => 'Revisión Abierta',
        'post_publication' => 'Revisión Post-Publicación',
        'editorial_review' => 'Revisión Editorial Interna',
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
        'other' => 'Otro índice regional o internacional',
    ],

    'language' => [
        'es' => 'Español',
        'en' => 'Inglés',
        'pt' => 'Portugués',
        'fr' => 'Francés',
        'de' => 'Alemán',
        'it' => 'Italiano',
        'other' => 'Otro',
    ],

];
