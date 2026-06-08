<?php
/**
 * Project Estimation Knowledge Base
 * Senior developers estimate effort accurately based on experience.
 * Level 6: Experience Knowledge
 */
return [

    // ── Module Complexity Points ───────────────────────────────────────────────
    'module_complexity' => [
        'simple_crud'        => ['points' => 1, 'example' => 'Categories, Countries, Tags'],
        'crud_with_relations'=> ['points' => 2, 'example' => 'Products with variants, Employees with documents'],
        'workflow_module'    => ['points' => 3, 'example' => 'Leave management, Purchase orders with approval'],
        'financial_module'   => ['points' => 5, 'example' => 'Payroll, Invoicing, Accounting ledger'],
        'realtime_module'    => ['points' => 4, 'example' => 'Chat, Live notifications, Order tracking'],
        'reporting_module'   => ['points' => 3, 'example' => 'Dashboard analytics, Export reports'],
        'integration_module' => ['points' => 4, 'example' => 'Payment gateway, SMS gateway, courier API'],
        'auth_system'        => ['points' => 2, 'example' => 'Login, register, 2FA, social auth, RBAC'],
        'search_module'      => ['points' => 3, 'example' => 'Full-text search with filters and facets'],
        'ai_module'          => ['points' => 5, 'example' => 'AI chat, recommendations, automation'],
    ],

    // ── Project Estimates by Type ──────────────────────────────────────────────
    'project_estimates' => [

        'small_crm' => [
            'complexity' => 'Low-Medium',
            'modules'    => 8,
            'features'   => ['Contacts', 'Companies', 'Deals', 'Activities', 'Notes', 'Tasks', 'Reports', 'Auth+RBAC'],
            'team'       => '1–2 developers',
            'timeline'   => '3–5 weeks',
            'tech_stack' => 'Laravel + Blade/Livewire + MySQL',
        ],

        'ecommerce_basic' => [
            'complexity' => 'Medium',
            'modules'    => 12,
            'features'   => ['Products', 'Variants', 'Cart', 'Orders', 'Payments', 'Shipping', 'Coupons', 'Reviews', 'Auth', 'Admin Dashboard', 'Inventory', 'Reports'],
            'team'       => '2–3 developers',
            'timeline'   => '6–10 weeks',
            'tech_stack' => 'Laravel + MySQL + Redis + Stripe/SSLCommerz',
        ],

        'hrm_system' => [
            'complexity' => 'Medium-High',
            'modules'    => 14,
            'features'   => ['Employees', 'Departments', 'Attendance (biometric)', 'Leave', 'Payroll', 'Recruitment', 'Performance', 'Training', 'Assets', 'Documents', 'Self Service', 'Reports', 'Auth+RBAC', 'Notifications'],
            'team'       => '3–4 developers',
            'timeline'   => '10–16 weeks',
        ],

        'erp_system' => [
            'complexity' => 'High',
            'modules'    => 20,
            'features'   => ['All HRM', 'Accounting', 'Procurement', 'Inventory', 'Sales', 'Projects', 'Manufacturing', 'CRM', 'Payroll', 'Fixed Assets', 'Reports Suite'],
            'team'       => '4–6 developers',
            'timeline'   => '6–12 months',
            'note'       => 'Consider phased delivery: Core modules first, then expand',
        ],

        'hospital_management' => [
            'complexity' => 'High',
            'modules'    => 16,
            'features'   => ['Patient Registration', 'Appointments', 'EMR', 'Lab', 'Pharmacy', 'Billing', 'IPD', 'OPD', 'Nursing', 'Radiology', 'Blood Bank', 'Reports', 'Auth', 'Notifications', 'Insurance', 'Telemedicine'],
            'team'       => '4–5 developers',
            'timeline'   => '4–8 months',
        ],

        'saas_starter' => [
            'complexity' => 'Medium',
            'modules'    => 10,
            'features'   => ['Multi-tenant', 'Auth', 'Plans/Billing', 'Team Management', 'Core Feature', 'Settings', 'Notifications', 'Onboarding', 'Reports', 'Admin Dashboard'],
            'team'       => '2–3 developers',
            'timeline'   => '8–12 weeks',
            'tech_stack' => 'Laravel + PostgreSQL + Redis + Cashier + Stripe',
        ],

        'pos_system' => [
            'complexity' => 'Medium',
            'modules'    => 10,
            'features'   => ['Products', 'Inventory', 'Sales Terminal', 'Payments', 'Returns', 'Customers', 'Discounts', 'End-of-Day Report', 'Expense', 'Settings'],
            'team'       => '2 developers',
            'timeline'   => '5–8 weeks',
        ],

        'school_management' => [
            'complexity' => 'Medium-High',
            'modules'    => 15,
            'features'   => ['Students', 'Classes/Sections', 'Subjects', 'Attendance', 'Exams/Results', 'Fees', 'Library', 'Transport', 'Hostel', 'Staff', 'Parents Portal', 'SMS Notifications', 'Reports', 'Auth', 'Admin'],
            'team'       => '3 developers',
            'timeline'   => '10–14 weeks',
        ],

        'marketplace' => [
            'complexity' => 'High',
            'modules'    => 18,
            'features'   => ['Vendor Registration', 'Product Catalog', 'Multi-vendor Cart', 'Order Split', 'Vendor Payout', 'Reviews', 'Search', 'Shipping Integration', 'Payment', 'Admin Commission', 'Analytics', 'Auth', 'Notifications', 'Customer Portal', 'Vendor Dashboard', 'Returns', 'Promotions', 'Mobile API'],
            'team'       => '4–6 developers',
            'timeline'   => '4–8 months',
        ],
    ],

    // ── Complexity Multipliers ─────────────────────────────────────────────────
    'multipliers' => [
        'multi_language'     => '× 1.3',
        'mobile_app_android' => '× 1.5 (separate Flutter/React Native effort)',
        'real_time_features' => '× 1.2',
        'payment_integration'=> '× 1.2 (testing, webhook handling, edge cases)',
        'multi_tenant_saas'  => '× 1.4',
        'third_party_apis'   => '+ 1 week per major integration',
        'legacy_data_import' => '+ 1–2 weeks for data migration',
    ],

    // ── Phased Delivery Recommendation ────────────────────────────────────────
    'phased_delivery' => [
        'rule'    => 'For projects > 3 months, always recommend phased delivery',
        'phase_1' => 'Core entities, auth, RBAC — deploy to staging (2–3 weeks)',
        'phase_2' => 'Primary workflow modules — user-testable MVP (4–6 weeks)',
        'phase_3' => 'Secondary modules, reports, notifications',
        'phase_4' => 'Integrations, mobile, advanced features',
        'benefit' => 'Early feedback catches scope misalignment before expensive rework',
    ],

];
