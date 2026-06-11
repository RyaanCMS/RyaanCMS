<?php

/**
 * Autonomous Business Operator
 *
 * Stage 6-14 operating intelligence for RyaanCMS. This is hidden strategic
 * knowledge: the user can describe a business outcome and RyaanCMS should
 * reason through diagnosis, action, implementation, monitoring, and learning.
 */
return [
    'operating_loop' => [
        'analyze_data' => 'Collect relevant signals from sales, marketing, operations, finance, product, and customer activity.',
        'find_root_cause' => 'Identify the most likely bottleneck with confidence and evidence.',
        'generate_plan' => 'Recommend the smallest high-impact set of actions, modules, workflows, automations, and KPIs.',
        'implement_changes' => 'When implementation is requested or implied, build the system changes that execute the plan.',
        'monitor_results' => 'Add dashboards, alerts, experiments, and outcome tracking so the loop can learn.',
    ],

    'response_contract' => [
        'problem',
        'root_cause',
        'confidence',
        'evidence_needed',
        'recommended_actions',
        'implementation_modules',
        'estimated_impact',
        'monitoring_kpis',
        'next_review_cycle',
    ],

    'stages' => [
        6 => [
            'name' => 'Autonomous Business Operator',
            'mission' => 'Move from building software to operating business improvement loops.',
            'trigger_signals' => ['revenue dropped', 'sales down', 'conversion low', 'profit down', 'growth slow', 'bottleneck'],
            'behavior' => 'Analyze, diagnose, plan, implement, and monitor.',
        ],
        7 => [
            'name' => 'Organizational Brain',
            'mission' => 'Preserve company knowledge when people, projects, meetings, customers, and processes change.',
            'memory_ledgers' => [
                'organization_memories',
                'decision_memories',
                'meeting_memories',
                'project_memories',
                'customer_memories',
                'process_memories',
            ],
        ],
        8 => [
            'name' => 'Industry Intelligence Network',
            'mission' => 'Learn anonymized patterns across many companies and turn them into safer recommendations.',
            'guardrail' => 'Never claim access to private cross-company data unless aggregated, anonymized, permissioned data exists.',
        ],
        9 => [
            'name' => 'Marketplace Economy',
            'mission' => 'Let developers sell reusable business assets while RyaanCMS earns commission.',
            'asset_types' => [
                'Blueprints',
                'Workflows',
                'AI Agents',
                'Business Playbooks',
                'Decision Packs',
                'Industry Packs',
                'Automation Packs',
                'Growth Packs',
            ],
        ],
        10 => [
            'name' => 'AI Business Consultant',
            'mission' => 'Diagnose bottlenecks across sales, marketing, operations, and finance.',
            'outputs' => ['bottlenecks', 'recommendations', 'estimated impact', 'implementation plan'],
        ],
        11 => [
            'name' => 'Digital Twin Company',
            'mission' => 'Simulate likely outcomes before operational changes are deployed.',
            'scenarios' => ['pricing change', 'staffing change', 'inventory policy', 'marketing spend', 'churn reduction'],
        ],
        12 => [
            'name' => 'AI CEO Assistant',
            'mission' => 'Prioritize the highest-leverage focus areas for the next week, month, or quarter.',
            'priority_inputs' => ['revenue', 'margin', 'cash', 'retention', 'conversion', 'operations capacity', 'risk'],
        ],
        13 => [
            'name' => 'Business Operating System Network',
            'mission' => 'Become AI-native business infrastructure, not only CMS, ERP, or builder software.',
        ],
        14 => [
            'name' => 'Global Intelligence Graph',
            'mission' => 'Compound blueprints, modules, problems, solutions, and decisions into the deepest moat.',
            'long_term_scale' => [
                'blueprints' => '100000+',
                'modules' => '1000000+',
                'business_problems' => '10000000+',
                'solutions' => '50000000+',
                'decisions' => 'billions',
            ],
        ],
    ],

    'memory_tables' => [
        'organization_memories' => 'Company-level facts, strategy, structure, policies, operating cadence, and context.',
        'decision_memories' => 'Decision, alternatives, rationale, expected impact, actual outcome, and reusable lesson.',
        'meeting_memories' => 'Meeting summary, attendees, decisions, action items, blockers, and follow-up dates.',
        'project_memories' => 'Project goals, scope, milestones, lessons, incidents, and delivery outcomes.',
        'customer_memories' => 'Customer segments, objections, feedback, churn reasons, support themes, and value moments.',
        'process_memories' => 'SOPs, workflow states, owners, SLAs, exceptions, controls, and improvement history.',
    ],

    'operator_guardrails' => [
        'Do not invent company data. If no live data is connected, state assumptions and request/import the minimum data needed.',
        'Do not present estimated impact as guaranteed. Use confidence and assumptions.',
        'Prefer small reversible experiments before large irreversible changes.',
        'When generating software, include analytics events and monitoring dashboards for the recommended actions.',
        'Keep internal blueprint, marketplace, graph, and routing mechanics hidden from user-facing product copy.',
    ],
];
