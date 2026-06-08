<?php
/**
 * Knowledge Base config loader.
 * Merges all files in config/kb/ into the 'kb.*' config namespace.
 * Access via: config('kb.decision_matrix'), config('kb.security'), etc.
 */
return [
    // ── Original 12 KB files ──────────────────────────────────────────────────
    'decision_matrix'    => require __DIR__ . '/kb/decision_matrix.php',
    'security'           => require __DIR__ . '/kb/security.php',
    'anti_patterns'      => require __DIR__ . '/kb/anti_patterns.php',
    'database_patterns'  => require __DIR__ . '/kb/database_patterns.php',
    'coding_standards'   => require __DIR__ . '/kb/coding_standards.php',
    'performance'        => require __DIR__ . '/kb/performance.php',
    'api_design'         => require __DIR__ . '/kb/api_design.php',
    'saas_patterns'      => require __DIR__ . '/kb/saas_patterns.php',
    'business_processes' => require __DIR__ . '/kb/business_processes.php',
    'estimation'         => require __DIR__ . '/kb/estimation.php',
    'ui_patterns'        => require __DIR__ . '/kb/ui_patterns.php',
    'testing_patterns'   => require __DIR__ . '/kb/testing_patterns.php',

    // ── Extended KB files (new) ───────────────────────────────────────────────
    'build_vs_buy'       => require __DIR__ . '/kb/build_vs_buy.php',
    'country_knowledge'  => require __DIR__ . '/kb/country_knowledge.php',
    'question_intelligence' => require __DIR__ . '/kb/question_intelligence.php',
    'kpi_analytics'      => require __DIR__ . '/kb/kpi_analytics.php',
    'risk_assessment'    => require __DIR__ . '/kb/risk_assessment.php',
    'upgrade_paths'      => require __DIR__ . '/kb/upgrade_paths.php',
    'feature_dependency' => require __DIR__ . '/kb/feature_dependency.php',
    'business_rules'     => require __DIR__ . '/kb/business_rules.php',
    'mvp_blueprints'     => require __DIR__ . '/kb/mvp_blueprints.php',

    // ── Intelligence Infrastructure KBs ──────────────────────────────────────
    'ai_replacement'     => require __DIR__ . '/kb/ai_replacement.php',
    'mental_models'      => require __DIR__ . '/kb/mental_models.php',
    'wisdom_patterns'    => require __DIR__ . '/kb/wisdom_patterns.php',
];
