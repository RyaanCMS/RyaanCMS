<?php

/**
 * Intelligence Library
 *
 * Static seed patterns for decisions, successes, failures, and optimization.
 * Dynamic project outcomes still live in the ProjectWisdom ledger.
 */
return [
    'decision_patterns' => [
        'problem_first' => [
            'rule' => 'When the prompt is a business pain, diagnose the root cause before generating software.',
            'apply_when' => ['low', 'high', 'mismatch', 'delay', 'churn', 'retention', 'return rate', 'stockout'],
            'output' => 'Diagnosis, 3-5 proven fixes, recommended modules, KPIs, and implementation plan.',
        ],
        'workflow_before_ui' => [
            'rule' => 'For operations-heavy systems, design workflow states before screens.',
            'apply_when' => ['inventory', 'hospital', 'logistics', 'restaurant', 'manufacturing'],
            'output' => 'State machine, roles, permissions, audit events, then UI.',
        ],
        'measurement_before_automation' => [
            'rule' => 'Do not automate a business process until the KPI and event source are defined.',
            'apply_when' => ['automation', 'notification', 'reminder', 'escalation'],
            'output' => 'Event, trigger, owner, SLA, success KPI, failure state.',
        ],
        'operator_loop' => [
            'rule' => 'When the user gives a business outcome, run analyze -> root cause -> plan -> implement -> monitor.',
            'apply_when' => ['revenue dropped', 'sales down', 'profit down', 'growth slow', 'what should i focus'],
            'output' => 'Problem, root cause, confidence, evidence needed, actions, modules, estimated impact, KPIs.',
        ],
        'simulation_before_change' => [
            'rule' => 'For pricing, churn, staffing, inventory, or growth changes, simulate likely impact before implementing.',
            'apply_when' => ['increase price', 'reduce churn', 'optimize pricing', 'marketing spend', 'inventory holding'],
            'output' => 'Scenario assumptions, revenue impact, margin impact, churn risk, confidence, monitoring plan.',
        ],
        'memory_before_recommendation' => [
            'rule' => 'Use organization, decision, meeting, project, customer, and process memory before recommending actions.',
            'apply_when' => ['company', 'team', 'customer', 'process', 'meeting', 'decision'],
            'output' => 'Relevant memory, missing data, recommended action, reusable lesson to record.',
        ],
    ],

    'success_patterns' => [
        'crm_follow_up_sla' => [
            'pattern' => 'CRM systems perform better when every lead has owner, next_action_at, SLA status, and overdue escalation.',
            'modules' => ['Lead Assignment', 'Activity Tasks', 'SLA Alerts', 'Manager Dashboard'],
        ],
        'ecommerce_cod_quality_gate' => [
            'pattern' => 'COD stores reduce return/fake orders with OTP verification, risk scoring, and pre-dispatch review.',
            'modules' => ['OTP Verification', 'Order Risk Score', 'Review Queue', 'Return Analytics'],
        ],
        'inventory_ledger' => [
            'pattern' => 'Inventory accuracy improves when stock is an immutable movement ledger, not an editable quantity field.',
            'modules' => ['Stock Ledger', 'Adjustment Approval', 'Cycle Count', 'Variance Report'],
        ],
        'executive_revenue_drop_diagnostics' => [
            'pattern' => 'Revenue drop diagnosis should separate traffic/lead volume, conversion, AOV, churn, pricing, and fulfillment capacity before recommending fixes.',
            'modules' => ['Executive KPI Dashboard', 'Revenue Variance', 'Funnel Analytics', 'Cohort Retention'],
        ],
        'ceo_priority_operating_review' => [
            'pattern' => 'Monthly company focus works best when priorities are limited to three, each with owner, KPI, deadline, and weekly review.',
            'modules' => ['CEO Priority Board', 'Weekly Review', 'Impact Effort Matrix', 'KPI Ownership'],
        ],
    ],

    'failure_patterns' => [
        'editable_stock_quantity' => [
            'mistake' => 'Letting staff directly edit product stock without a movement ledger.',
            'consequence' => 'No audit trail, unexplained mismatches, and weak accountability.',
            'fix' => 'Use stock_movements plus adjustment approvals.',
        ],
        'crm_without_next_action' => [
            'mistake' => 'CRM lead records without next action, owner, and due date.',
            'consequence' => 'Follow-up delay and lead leakage.',
            'fix' => 'Require next_action_at and auto-create tasks after stage changes.',
        ],
        'returns_without_reason_taxonomy' => [
            'mistake' => 'Collecting returns without structured return reasons.',
            'consequence' => 'Cannot identify product, courier, customer, or expectation issues.',
            'fix' => 'Use tagged return reasons and product/courier/customer reports.',
        ],
        'advice_without_connected_data' => [
            'mistake' => 'Giving confident business advice without connected data, assumptions, or confidence level.',
            'consequence' => 'The user may execute the wrong change and lose trust.',
            'fix' => 'State assumptions, ask for the smallest missing dataset, and label impact as estimated.',
        ],
        'automation_without_monitoring' => [
            'mistake' => 'Implementing an automation without a KPI, owner, alert, or review cycle.',
            'consequence' => 'The system acts but nobody knows whether the business improved.',
            'fix' => 'Add dashboards, alerts, experiment tracking, and weekly outcome review.',
        ],
    ],

    'optimization_patterns' => [
        'reuse_before_generate' => [
            'principle' => 'Use blueprint, module, component, and rule libraries before making a large AI call.',
            'benefit' => 'Lower cost, faster builds, more consistent quality.',
        ],
        'diagnosis_to_module_map' => [
            'principle' => 'Map a problem to modules and KPIs before writing code.',
            'benefit' => 'The resulting system solves the business problem instead of only storing data.',
        ],
        'closed_loop_learning' => [
            'principle' => 'Record decision, expected outcome, actual outcome, and recommendation after each project.',
            'benefit' => 'Future recommendations become more accurate and cheaper.',
        ],
        'organizational_memory_compounding' => [
            'principle' => 'Store every reusable decision, meeting, process, customer signal, and project lesson as structured memory.',
            'benefit' => 'The company keeps learning even when employees or vendors change.',
        ],
        'marketplace_asset_compounding' => [
            'principle' => 'Convert repeated solutions into marketplace assets: playbooks, workflows, AI agents, packs, and blueprints.',
            'benefit' => 'Developers earn, RyaanCMS earns commission, and customers get faster proven solutions.',
        ],
        'anonymous_network_learning' => [
            'principle' => 'Only use cross-company intelligence when aggregated, anonymized, permissioned, and privacy-safe.',
            'benefit' => 'Industry recommendations improve without exposing customer data.',
        ],
    ],
];
