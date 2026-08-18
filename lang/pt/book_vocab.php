<?php

/**
 * Vocabulários controlados de livros (issue #71). Ver App\Support\BookVocabulary.
 *
 * As chaves são os valores persistidos em `books`; NÃO alterá-las sem uma
 * migração de normalização.
 */
return [

    'book_type' => [
        'monograph' => 'Monografia',
        'edited_volume' => 'Volume Organizado',
        'textbook' => 'Livro Didático',
        'conference_proceedings' => 'Anais de Congresso',
        'reference_work' => 'Obra de Referência',
        'other' => 'Outro',
    ],

    'author_role' => [
        'author' => 'Autor',
        'editor' => 'Organizador',
        'translator' => 'Tradutor',
        'coordinator' => 'Coordenador',
        'compiler' => 'Compilador',
        'illustrator' => 'Ilustrador',
    ],

    'academic_level' => [
        'pregrado' => 'Graduação',
        'postgrado' => 'Pós-graduação (Mestrado, Especialização)',
        'doctorado' => 'Doutorado',
        'investigadores' => 'Pesquisadores / Profissionais',
        'publico_general' => 'Público Geral / Divulgação',
    ],

    'knowledge_area' => [
        'ciencias_exactas_y_naturales' => 'Ciências Exatas e Naturais',
        'ingenieria_y_tecnologia' => 'Engenharia e Tecnologia',
        'ciencias_medicas_y_de_la_salud' => 'Ciências Médicas e da Saúde',
        'ciencias_agricolas' => 'Ciências Agrárias',
        'ciencias_sociales' => 'Ciências Sociais',
        'humanidades' => 'Humanidades',
    ],

    'access_type' => [
        'gold' => 'Dourado (Gold)',
        'green' => 'Verde (Green)',
        'hybrid' => 'Híbrido (Hybrid)',
        'bronze' => 'Bronze',
        'diamond' => 'Diamante / Platina (Diamond/Platinum)',
    ],

    'license_type' => [
        'cc_by' => 'CC BY (Atribuição)',
        'cc_by_sa' => 'CC BY-SA (Atribuição-CompartilhaIgual)',
        'cc_by_nd' => 'CC BY-ND (Atribuição-SemDerivações)',
        'cc_by_nc' => 'CC BY-NC (Atribuição-NãoComercial)',
        'cc_by_nc_sa' => 'CC BY-NC-SA (Atribuição-NãoComercial-CompartilhaIgual)',
        'cc_by_nc_nd' => 'CC BY-NC-ND (Atribuição-NãoComercial-SemDerivações)',
        'copyright_all_rights_reserved' => 'Copyright (Todos os direitos reservados)',
        'public_domain' => 'Domínio Público',
        'other' => 'Outra licença',
    ],

    'publication_model' => [
        'open_apc' => 'Acesso Aberto (com taxa de publicação - APC)',
        'open_no_apc' => 'Acesso Aberto Diamante (sem custo para autor nem leitor)',
        'pay_download' => 'Pagamento por Download (digital)',
        'pay_print' => 'Impressão sob demanda (pagamento por cópia)',
        'subscription' => 'Assinatura institucional',
        'freemium' => 'Freemium (básico gratuito, avançado pago)',
    ],

    'funded_by' => [
        'institution' => 'Instituição do Autor',
        'grant' => 'Financiamento / Projeto de Pesquisa',
        'society' => 'Sociedade Científica',
        'government' => 'Governo',
        'crowdfunding' => 'Financiamento coletivo',
        'self_funded' => 'Autofinanciado',
        'other' => 'Outra fonte',
        'none' => 'Sem financiamento externo',
    ],

    'format' => [
        'digital' => 'Digital (PDF, EPUB, etc.)',
        'impreso' => 'Impresso',
        'hibrido' => 'Híbrido (impresso e digital)',
    ],

    'review_type' => [
        'single_blind' => 'Cego Simples',
        'double_blind' => 'Duplo Cego',
        'open_review' => 'Revisão Aberta',
        'post_publication' => 'Revisão Pós-Publicação',
        'editorial_review' => 'Revisão Editorial Interna',
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
        'other' => 'Outro índice regional ou internacional',
    ],

    'language' => [
        'es' => 'Espanhol',
        'en' => 'Inglês',
        'pt' => 'Português',
        'fr' => 'Francês',
        'de' => 'Alemão',
        'it' => 'Italiano',
        'other' => 'Outro',
    ],

];
