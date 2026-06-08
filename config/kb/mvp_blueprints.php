<?php
/**
 * MVP Blueprint Knowledge Base
 * Complete minimum viable project structures per domain.
 * AI uses these to understand scope, suggest phasing, and avoid over-building.
 * Level 5: Decision Knowledge — "What is truly minimum?"
 */
return [

    // ── MVP Rule ──────────────────────────────────────────────────────────────
    'mvp_rule' => 'Build the minimum that delivers core value. Skip advanced features until validated.',

    // ── CRM MVP ───────────────────────────────────────────────────────────────
    'crm' => [
        'description' => 'Customer Relationship Management — manage leads, customers, and sales pipeline',
        'mvp_modules' => ['Auth + RBAC', 'Contacts', 'Companies', 'Deals + Pipeline', 'Activities', 'Notes', 'Dashboard'],
        'mvp_tables'  => ['users', 'roles', 'contacts', 'companies', 'deals', 'pipeline_stages', 'activities', 'notes'],
        'mvp_roles'   => ['Admin', 'Sales Manager', 'Sales Rep'],
        'mvp_reports' => ['Pipeline summary', 'Deals by stage', 'Activities by rep'],
        'skip_for_mvp'=> ['Email sync', 'AI scoring', 'Marketing automation', 'Mobile app', 'SMS integration'],
        'first_value' => 'First lead converted through the pipeline',
    ],

    // ── eCommerce MVP ──────────────────────────────────────────────────────────
    'ecommerce' => [
        'description' => 'Online store with products, orders, and payment',
        'mvp_modules' => ['Auth', 'Product Catalog', 'Cart', 'Checkout', 'Orders', 'Basic Inventory', 'Payment Gateway', 'Admin Dashboard'],
        'mvp_tables'  => ['users', 'products', 'product_categories', 'product_images', 'carts', 'cart_items', 'orders', 'order_items', 'payments', 'addresses'],
        'mvp_roles'   => ['Admin', 'Customer'],
        'mvp_reports' => ['Sales today/week/month', 'Top products', 'Orders by status'],
        'skip_for_mvp'=> ['Loyalty points', 'AI recommendations', 'Wishlist', 'Multi-currency', 'Subscription products'],
        'first_value' => 'First completed order with payment confirmation',
        'bd_specific' => 'Add bKash/Nagad for Bangladesh market — COD option essential',
    ],

    // ── HRM MVP ───────────────────────────────────────────────────────────────
    'hrm' => [
        'description' => 'Human Resource Management — employees, attendance, leaves, payroll',
        'mvp_modules' => ['Auth + RBAC', 'Employees', 'Departments', 'Attendance', 'Leave Management', 'Basic Payroll', 'Dashboard'],
        'mvp_tables'  => ['users', 'employees', 'departments', 'designations', 'attendance', 'leaves', 'leave_types', 'payrolls', 'payroll_items'],
        'mvp_roles'   => ['Admin', 'HR Manager', 'Manager', 'Employee'],
        'mvp_reports' => ['Monthly attendance', 'Leave summary', 'Payroll sheet'],
        'skip_for_mvp'=> ['Recruitment module', 'Performance management', 'Training', 'Asset management', 'Biometric integration'],
        'first_value' => 'First payroll generated with correct attendance deductions',
    ],

    // ── Hospital MVP ──────────────────────────────────────────────────────────
    'hospital' => [
        'description' => 'Hospital Management System — patient records, appointments, billing',
        'mvp_modules' => ['Auth + RBAC', 'Patient Registration', 'Appointments', 'Doctor Schedule', 'Basic EMR', 'Billing', 'Dashboard'],
        'mvp_tables'  => ['users', 'patients', 'doctors', 'appointments', 'medical_records', 'diagnoses', 'prescriptions', 'invoices', 'invoice_items', 'payments'],
        'mvp_roles'   => ['Admin', 'Doctor', 'Nurse', 'Receptionist', 'Cashier'],
        'mvp_reports' => ['Daily patient count', 'Revenue by doctor', 'Appointment summary'],
        'skip_for_mvp'=> ['Lab module', 'Pharmacy', 'Radiology', 'Insurance module', 'Telemedicine'],
        'critical'    => 'Patient data encryption mandatory from day one — not a future enhancement',
        'first_value' => 'First complete patient visit: registration → appointment → consultation → billing',
    ],

    // ── School MVP ────────────────────────────────────────────────────────────
    'school' => [
        'description' => 'School Management System — students, attendance, exams, fees',
        'mvp_modules' => ['Auth + RBAC', 'Students', 'Classes/Sections', 'Subjects', 'Attendance', 'Exam + Results', 'Fees', 'Dashboard'],
        'mvp_tables'  => ['users', 'students', 'classes', 'sections', 'subjects', 'attendance', 'exams', 'exam_results', 'fee_structures', 'fee_payments'],
        'mvp_roles'   => ['Admin', 'Teacher', 'Accountant', 'Parent (read-only)'],
        'mvp_reports' => ['Attendance summary', 'Exam results report', 'Fee collection'],
        'skip_for_mvp'=> ['Library', 'Transport', 'Hostel', 'Online classes', 'Mobile app'],
        'first_value' => 'First student enrolled with attendance tracked and fees collected',
    ],

    // ── SaaS MVP ──────────────────────────────────────────────────────────────
    'saas' => [
        'description' => 'Multi-tenant SaaS platform with subscriptions',
        'mvp_modules' => ['Auth', 'Multi-tenancy', 'Plans', 'Subscription + Billing', 'Team Management', 'Core Feature (domain-specific)', 'Onboarding', 'Dashboard'],
        'mvp_tables'  => ['users', 'tenants', 'plans', 'subscriptions', 'team_members', 'invoices'],
        'mvp_roles'   => ['Super Admin', 'Tenant Owner', 'Team Member'],
        'mvp_reports' => ['MRR', 'Active subscriptions', 'Trial conversions'],
        'skip_for_mvp'=> ['White-label', 'Affiliate system', 'SSO', 'API for external use', 'Mobile app'],
        'first_value' => 'First paying tenant using core feature after onboarding',
        'billing_stack'=> 'Laravel Cashier + Stripe (or local gateway with custom billing)',
    ],

    // ── Inventory MVP ─────────────────────────────────────────────────────────
    'inventory' => [
        'description' => 'Warehouse and inventory management system',
        'mvp_modules' => ['Auth + RBAC', 'Products/SKUs', 'Warehouses', 'Stock In (Purchase)', 'Stock Out (Sales)', 'Stock Adjustment', 'Reports'],
        'mvp_tables'  => ['users', 'products', 'categories', 'warehouses', 'stock_movements', 'purchase_orders', 'purchase_items', 'suppliers'],
        'mvp_roles'   => ['Admin', 'Warehouse Manager', 'Staff'],
        'mvp_reports' => ['Current stock levels', 'Low stock alert', 'Stock movement history'],
        'skip_for_mvp'=> ['Barcode scanner integration', 'Multi-location transfer', 'Expiry tracking', 'FIFO costing reports'],
    ],

    // ── Restaurant MVP ────────────────────────────────────────────────────────
    'restaurant' => [
        'description' => 'Restaurant management with menu, orders, and billing',
        'mvp_modules' => ['Auth + RBAC', 'Menu Management', 'Tables', 'Order Taking', 'Kitchen Display', 'Billing/POS', 'Basic Reports'],
        'mvp_tables'  => ['users', 'menu_categories', 'menu_items', 'tables', 'orders', 'order_items', 'invoices'],
        'mvp_roles'   => ['Admin', 'Waiter', 'Kitchen', 'Cashier'],
        'mvp_reports' => ['Daily sales', 'Top items', 'Table occupancy'],
        'skip_for_mvp'=> ['Online ordering', 'Delivery tracking', 'Loyalty', 'Inventory integration', 'Reservation system'],
        'first_value' => 'First complete dine-in order from table selection to bill printing',
    ],

    // ── Marketplace MVP ───────────────────────────────────────────────────────
    'marketplace' => [
        'description' => 'Multi-vendor marketplace platform',
        'mvp_modules' => ['Auth', 'Vendor Registration', 'Product Catalog', 'Customer Cart + Checkout', 'Orders', 'Vendor Dashboard', 'Admin Dashboard', 'Commission Engine'],
        'mvp_tables'  => ['users', 'vendors', 'products', 'product_variants', 'orders', 'order_items', 'vendor_payouts', 'commissions'],
        'mvp_roles'   => ['Super Admin', 'Vendor', 'Customer'],
        'mvp_reports' => ['Platform revenue', 'Vendor performance', 'Commission summary'],
        'skip_for_mvp'=> ['Dispute system', 'Reviews initially', 'Featured listings', 'Analytics dashboard', 'Mobile app'],
        'warning'     => 'Multi-vendor cart split by vendor is the hardest part — plan this schema carefully',
    ],

    // ── POS MVP ────────────────────────────────────────────────────────────────
    'pos' => [
        'description' => 'Point of Sale system for retail',
        'mvp_modules' => ['Auth + RBAC', 'Products + Barcode', 'Sales Terminal', 'Payments (Cash/Card/Mobile)', 'Daily Reports', 'Basic Inventory'],
        'mvp_tables'  => ['users', 'products', 'customers', 'sales', 'sale_items', 'payments', 'cash_drawers'],
        'mvp_roles'   => ['Admin', 'Cashier'],
        'mvp_reports' => ['End of day report', 'Sales summary', 'Stock alert'],
        'skip_for_mvp'=> ['Loyalty', 'Customer credit', 'Multiple branches initially', 'E-commerce sync'],
        'note'        => 'POS must work offline or with flaky internet — consider PWA or desktop app',
    ],

    // ── ERP MVP (Phase 1 only) ─────────────────────────────────────────────────
    'erp' => [
        'description' => 'Enterprise Resource Planning — full business operations suite',
        'phase_1' => [
            'modules' => ['Auth + RBAC', 'Accounting Core', 'Basic Inventory', 'HR/Employees'],
            'note'    => 'Start with phase 1, validate, then expand',
        ],
        'phase_2' => [
            'modules' => ['CRM', 'Procurement', 'Payroll', 'Asset Management'],
        ],
        'phase_3' => [
            'modules' => ['Manufacturing', 'Project Management', 'Business Intelligence'],
        ],
        'warning' => 'Full ERP is 12+ months for 1 developer — always phase delivery',
        'mvp_roles' => ['Super Admin', 'Department Head', 'Staff', 'Accountant', 'HR'],
    ],

];
