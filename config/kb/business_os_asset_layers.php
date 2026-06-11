<?php

/**
 * Business OS Asset Layers
 *
 * This file defines the long-term moat for RyaanCMS:
 * blueprints, problem-solving knowledge, and accumulated intelligence.
 */
return [
    'layers' => [
        'blueprint_library' => [
            'name' => 'Blueprint Library',
            'purpose' => 'Reusable business-system structures for apps, websites, landing pages, portals, dashboards, and APIs.',
            'target_count' => 1000,
            'categories' => [
                'business' => ['CRM', 'ERP', 'Accounting', 'HRM', 'Payroll', 'Inventory', 'POS', 'Asset Management', 'Procurement', 'Vendor Management'],
                'ecommerce' => ['Single Vendor', 'Multi Vendor', 'Wholesale', 'Dropshipping', 'Subscription Commerce', 'B2B Commerce'],
                'healthcare' => ['Hospital', 'Clinic', 'Diagnostic', 'Dental', 'Pharmacy', 'Telemedicine'],
                'education' => ['School ERP', 'University ERP', 'LMS', 'Coaching Center', 'Student Portal'],
                'real_estate' => ['Property CRM', 'Broker CRM', 'Rental Management', 'Construction ERP'],
                'manufacturing' => ['Production Planning', 'Factory ERP', 'Warehouse', 'Quality Control'],
                'restaurant' => ['POS', 'Kitchen', 'Delivery', 'Restaurant ERP'],
                'ngo' => ['Donor Management', 'Grant Management', 'Beneficiary Tracking'],
                'government' => ['Citizen Service', 'Complaint Management', 'Permit Management'],
                'legal' => ['Law Firm CRM', 'Case Management', 'Contract Management'],
                'logistics' => ['Courier', 'Fleet', 'Dispatch', 'Tracking'],
                'finance' => ['Microfinance', 'Loan Management', 'Investment Management'],
                'agriculture' => ['Farm Management', 'Livestock', 'Crop Planning'],
                'travel' => ['Tour Operator', 'Hotel Management', 'Booking Engine'],
            ],
        ],

        'problem_solving_library' => [
            'name' => 'Problem Solving Library',
            'purpose' => 'Map business pain statements to diagnosis, proven solutions, workflows, modules, automations, and KPIs.',
            'target_count' => 5000,
            'problem_domains' => [
                'sales' => ['Low Conversion', 'Lead Leakage', 'Follow-up Delay', 'Sales Pipeline Chaos', 'Quote Delay'],
                'marketing' => ['Low ROAS', 'High CAC', 'Poor Retention', 'Abandoned Cart', 'Low Engagement'],
                'hr' => ['Employee Absence', 'High Turnover', 'Payroll Errors', 'Recruitment Delays'],
                'finance' => ['Cash Flow Issues', 'Late Payments', 'Revenue Leakage', 'Expense Tracking'],
                'operations' => ['Inventory Mismatch', 'Order Delays', 'Stockouts', 'Process Bottlenecks'],
                'ecommerce' => ['COD Verification', 'High Return Rate', 'Fake Orders', 'Low Repeat Purchase'],
                'saas' => ['User Churn', 'Low Activation', 'Feature Adoption'],
            ],
        ],

        'intelligence_library' => [
            'name' => 'Intelligence Library',
            'purpose' => 'Capture what worked, what failed, why it happened, and how to optimize future builds.',
            'target_count' => 100000,
            'pattern_types' => ['Decision Patterns', 'Success Patterns', 'Failure Patterns', 'Optimization Patterns'],
        ],
    ],

    'expansion_libraries' => [
        'ui_ux' => ['Dashboard Patterns', 'Admin Panels', 'Landing Pages', 'Mobile UI'],
        'database' => ['Accounting Schema', 'CRM Schema', 'ERP Schema', 'Healthcare Schema'],
        'api' => ['Payment APIs', 'SMS APIs', 'WhatsApp APIs', 'Email APIs'],
        'security' => ['RBAC', '2FA', 'Audit Logs', 'GDPR', 'SOC2'],
        'devops' => ['Docker', 'Kubernetes', 'CI/CD', 'Monitoring'],
        'saas' => ['Subscription', 'Tenant Management', 'Billing', 'Usage Tracking'],
    ],

    'evolution_stages' => [
        'Build Apps',
        'Build Business Systems',
        'Solve Business Problems',
        'Recommend Best Solution Automatically',
        'Business Operating System Architect',
    ],

    'long_term_targets' => [
        'industry_blueprints' => 1000,
        'business_problem_blueprints' => 5000,
        'modules' => 10000,
        'components' => 50000,
        'decision_patterns' => 100000,
    ],
];
