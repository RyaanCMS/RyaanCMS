<?php

/**
 * Problem Solving Library
 *
 * Converts business pain statements into diagnosis, recommended modules,
 * workflows, automations, and KPIs. This makes RyaanCMS a problem solver,
 * not just an app generator.
 */
return [
    'sales' => [
        'follow_up_delay' => [
            'triggers' => ['follow-up', 'follow up', 'not following', 'lead delay', 'sales team not calling'],
            'diagnosis' => 'Leads are entering the pipeline without ownership, due dates, reminders, or escalation.',
            'solutions' => [
                'Lead assignment rules by territory/source/value',
                'SLA timer per lead stage',
                'Automatic follow-up task creation',
                'Overdue lead escalation to sales manager',
                'Daily rep activity dashboard',
            ],
            'modules' => ['Lead Management', 'Activity Tasks', 'Pipeline SLA', 'Notifications', 'Sales Manager Dashboard'],
            'kpis' => ['First response time', 'Follow-up completion rate', 'Overdue leads', 'Lead-to-opportunity conversion'],
        ],
        'low_conversion' => [
            'triggers' => ['low conversion', 'conversion low', 'lead not converting', 'sales conversion'],
            'diagnosis' => 'Pipeline stages, qualification rules, and loss reasons are not measurable enough.',
            'solutions' => ['Qualification checklist', 'Deal stage probability', 'Loss reason capture', 'Proposal follow-up automation'],
            'modules' => ['CRM Pipeline', 'Deal Scoring', 'Proposal Tracking', 'Conversion Reports'],
            'kpis' => ['Conversion by source', 'Win rate by rep', 'Average sales cycle', 'Loss reason distribution'],
        ],
    ],

    'operations' => [
        'inventory_mismatch' => [
            'triggers' => ['inventory mismatch', 'stock mismatch', 'stock not matching', 'inventory error', 'wrong stock'],
            'diagnosis' => 'Stock movements are not recorded as immutable events with approvals and audit trails.',
            'solutions' => [
                'Stock ledger for every in/out/adjustment movement',
                'Cycle count workflow',
                'Manager approval for stock adjustment',
                'Barcode/SKU validation at receive and dispatch',
                'Variance report by product and warehouse',
            ],
            'modules' => ['Stock Ledger', 'Warehouse', 'Stock Adjustment', 'Cycle Count', 'Inventory Audit'],
            'kpis' => ['Stock variance value', 'Adjustment frequency', 'Low stock count', 'Inventory accuracy percentage'],
        ],
        'stockouts' => [
            'triggers' => ['stockout', 'out of stock', 'stock shortage', 'low stock'],
            'diagnosis' => 'Reorder points, supplier lead time, and demand forecasting are missing or not connected.',
            'solutions' => ['Reorder level per SKU', 'Supplier lead-time tracking', 'Auto purchase requisition', 'Low stock alerts'],
            'modules' => ['Inventory', 'Supplier Management', 'Purchase Requisition', 'Stock Alerts'],
            'kpis' => ['Stockout rate', 'Days of inventory', 'Reorder alert response time'],
        ],
    ],

    'hr' => [
        'employee_absence' => [
            'triggers' => ['attendance problem', 'employee absence', 'absent', 'late employee', 'attendance mismatch'],
            'diagnosis' => 'Attendance capture, leave approval, shift rules, and payroll deductions are disconnected.',
            'solutions' => [
                'Shift-based attendance rules',
                'Late/absence exception workflow',
                'Leave balance integration',
                'Manager approval for corrections',
                'Payroll-ready attendance summary',
            ],
            'modules' => ['Attendance', 'Shift Management', 'Leave Management', 'Exception Approval', 'Payroll Summary'],
            'kpis' => ['Absence rate', 'Late count', 'Attendance correction count', 'Payroll discrepancy count'],
        ],
        'payroll_errors' => [
            'triggers' => ['payroll error', 'salary mistake', 'wrong salary', 'payroll mismatch'],
            'diagnosis' => 'Payroll is being calculated without locked attendance, allowance, deduction, and approval records.',
            'solutions' => ['Payroll lock period', 'Allowance/deduction ledger', 'Payslip approval workflow', 'Audit trail'],
            'modules' => ['Payroll', 'Attendance Lock', 'Allowance Deduction', 'Payslip Approval'],
            'kpis' => ['Payroll correction count', 'Payroll approval time', 'Deduction variance'],
        ],
    ],

    'ecommerce' => [
        'high_return_rate' => [
            'triggers' => ['return rate', 'high return', 'returns high', 'rto', 'return 35', 'refund high'],
            'diagnosis' => 'Order quality, COD verification, product expectation, and delivery feedback loops are weak.',
            'solutions' => [
                'COD phone/OTP verification before dispatch',
                'Risk scoring for repeat return customers',
                'Product size/fit/content quality checklist',
                'Return reason capture with category tagging',
                'Courier performance report',
            ],
            'modules' => ['COD Verification', 'Return Management', 'Customer Risk Score', 'Courier Analytics', 'Product QA'],
            'kpis' => ['Return rate by product', 'Return rate by courier', 'COD verification pass rate', 'Repeat return customers'],
        ],
        'fake_orders' => [
            'triggers' => ['fake order', 'fake orders', 'fraud order', 'cod fake'],
            'diagnosis' => 'Orders are accepted without identity, phone, address, behavior, and risk checks.',
            'solutions' => ['OTP verification', 'Address blacklist', 'Customer risk score', 'Manual review queue'],
            'modules' => ['Fraud Rules', 'OTP Verification', 'Order Review Queue', 'Customer Risk Profile'],
            'kpis' => ['Rejected fake orders', 'Manual review rate', 'COD confirmation rate'],
        ],
    ],

    'saas' => [
        'user_churn' => [
            'triggers' => ['churn', 'users leaving', 'cancellation', 'retention low'],
            'diagnosis' => 'Activation, habit formation, value moments, and churn signals are not measured.',
            'solutions' => ['Activation checklist', 'Usage health score', 'Churn risk alerts', 'Win-back workflow'],
            'modules' => ['Onboarding', 'Usage Analytics', 'Customer Health Score', 'Retention Automation'],
            'kpis' => ['Activation rate', 'Time to first value', 'Weekly active users', 'Churn risk score'],
        ],
    ],
];
