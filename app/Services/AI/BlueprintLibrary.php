<?php

namespace App\Services\AI;

/**
 * BlueprintLibrary — 100 pre-built "money blueprints" (0 AI tokens)
 *
 * When a user's request matches a known business type, the full blueprint
 * is returned instantly from this library — no AI API call made.
 *
 * Structure per blueprint (Blueprint Genome):
 *   name, industry, problem_solved, target_users,
 *   required_modules, optional_modules, key_entities,
 *   workflows, reports, permissions, questions_to_ask,
 *   business_rules, integrations, pages, ai_fallback_areas
 *
 * Used by BlueprintService::discover() — library checked first.
 * Only falls through to AI when no blueprint matches.
 */
class BlueprintLibrary
{
    // ─────────────────────────────────────────────────────────────────────
    // Lookup Methods
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Find a blueprint by exact key (e.g. 'ecommerce', 'hospital').
     */
    public function findByKey(string $key): ?array
    {
        $catalog = $this->catalog();
        return $catalog[$key] ?? null;
    }

    /**
     * Find the best-matching blueprint for a free-text description.
     * Uses keyword scoring — zero AI tokens.
     */
    public function findByDescription(string $description): ?array
    {
        $normalized = mb_strtolower(trim($description), 'UTF-8');
        $catalog    = $this->catalog();
        $best       = null;
        $bestScore  = 0;

        foreach ($catalog as $key => $blueprint) {
            $score = $this->score($normalized, $key, $blueprint);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = array_merge(['_key' => $key, 'ai_tokens' => 0], $blueprint);
            }
        }

        // Only return if confidence is high enough
        return $bestScore >= 2 ? $best : null;
    }

    /**
     * Return all blueprint keys grouped by category.
     */
    public function allKeys(): array
    {
        $grouped = [];
        foreach ($this->catalog() as $key => $bp) {
            $grouped[$bp['category']][] = $key;
        }
        return $grouped;
    }

    /**
     * Return lightweight list for UI (key + name + icon + category).
     */
    public function listing(): array
    {
        return array_map(fn($key, $bp) => [
            'key'      => $key,
            'name'     => $bp['name'],
            'icon'     => $bp['icon'],
            'category' => $bp['category'],
            'industry' => $bp['industry'],
        ], array_keys($this->catalog()), $this->catalog());
    }

    // ─────────────────────────────────────────────────────────────────────
    // Scoring
    // ─────────────────────────────────────────────────────────────────────

    private function score(string $text, string $key, array $blueprint): float
    {
        $score = 0.0;

        // Key match
        if (str_contains($text, $key)) $score += 3.0;

        // Keywords match
        foreach ($blueprint['keywords'] ?? [] as $kw) {
            $kw = mb_strtolower($kw, 'UTF-8');
            if (str_contains($text, $kw)) {
                $score += max(1.0, mb_strlen($kw) / 4);
            }
        }

        return $score;
    }

    // ─────────────────────────────────────────────────────────────────────
    // CATALOG — 100 Money Blueprints
    // Parts are loaded via private methods to keep the file navigable.
    // ─────────────────────────────────────────────────────────────────────

    private function catalog(): array
    {
        return array_merge(
            $this->commerceBlueprints(),
            $this->healthcareBlueprints(),
            $this->educationBlueprints(),
            $this->hospitalityBlueprints(),
            $this->realEstateBlueprints(),
            $this->financeBlueprints(),
            $this->logisticsBlueprints(),
            $this->mediaBlueprints(),
            $this->saasNicheBlueprints(),
        );
    }

    // ═════════════════════════════════════════════════════════════════════
    // PART 1 — COMMERCE & BUSINESS
    // ═════════════════════════════════════════════════════════════════════

    private function commerceBlueprints(): array
    {
        return [

            // ── eCommerce ────────────────────────────────────────────────
            'ecommerce' => [
                'name'             => 'eCommerce Store',
                'icon'             => '🏬',
                'category'         => 'commerce',
                'industry'         => 'retail',
                'problem_solved'   => 'Sell products online with inventory, orders, and payments',
                'target_users'     => ['admin', 'customer', 'staff'],
                'keywords'         => ['ecommerce', 'e-commerce', 'online store', 'online shop', 'shop', 'store', 'sell products', 'product catalog', 'shopping cart', 'অনলাইন শপ', 'অনলাইন স্টোর', 'دكان إلكتروني', 'tienda online'],
                'required_modules' => ['auth', 'rbac', 'payments', 'media', 'notifications'],
                'optional_modules' => ['reports', 'multi_currency', 'reviews', 'wishlist', 'affiliate'],
                'key_entities'     => ['Product', 'Category', 'Order', 'OrderItem', 'Cart', 'CartItem', 'Payment', 'Review', 'Coupon', 'ShippingAddress', 'Wishlist', 'Brand'],
                'workflows'        => ['browse_products', 'add_to_cart', 'checkout', 'payment_processing', 'order_fulfillment', 'order_tracking', 'return_refund', 'review_submission'],
                'reports'          => ['sales_summary', 'top_products', 'revenue_by_category', 'customer_lifetime_value', 'inventory_status', 'abandoned_carts'],
                'pages'            => ['home', 'shop', 'product_detail', 'cart', 'checkout', 'order_confirmation', 'my_orders', 'wishlist', 'admin_dashboard', 'product_management', 'order_management'],
                'permissions'      => ['manage_products', 'manage_orders', 'manage_customers', 'manage_coupons', 'view_reports', 'manage_inventory'],
                'questions_to_ask' => ['Single vendor or multi-vendor marketplace?', 'Physical or digital products?', 'Which payment gateways? (Stripe, bKash, SSLCommerz)', 'Need delivery/courier integration?'],
                'business_rules'   => ['stock_check_before_order', 'payment_confirmed_before_fulfillment', 'refund_within_policy_window', 'coupon_single_use_per_customer'],
                'integrations'     => ['stripe', 'sslcommerz', 'bkash', 'nagad', 'fedex', 'google_analytics', 'facebook_pixel'],
                'ai_fallback_areas'=> ['recommendation_engine', 'dynamic_pricing', 'fraud_detection', 'ai_product_descriptions'],
            ],

            // ── Marketplace ──────────────────────────────────────────────
            'marketplace' => [
                'name'             => 'Multi-Vendor Marketplace',
                'icon'             => '🛒',
                'category'         => 'commerce',
                'industry'         => 'marketplace',
                'problem_solved'   => 'Connect multiple vendors with buyers on a single platform',
                'target_users'     => ['admin', 'vendor', 'customer'],
                'keywords'         => ['marketplace', 'multi vendor', 'multi-vendor', 'vendor', 'sellers', 'daraz', 'amazon clone', 'platform', 'commission', 'মার্কেটপ্লেস', 'سوق إلكتروني'],
                'required_modules' => ['auth', 'rbac', 'payments', 'media', 'notifications'],
                'optional_modules' => ['reports', 'reviews', 'affiliate', 'subscription', 'chat'],
                'key_entities'     => ['Vendor', 'VendorProfile', 'Product', 'Category', 'Order', 'OrderItem', 'Commission', 'VendorPayout', 'Review', 'Cart', 'Coupon', 'ShippingAddress'],
                'workflows'        => ['vendor_registration', 'vendor_approval', 'product_listing', 'customer_purchase', 'commission_calculation', 'vendor_payout', 'dispute_resolution'],
                'reports'          => ['platform_revenue', 'vendor_performance', 'top_vendors', 'commission_report', 'payout_history', 'category_sales'],
                'pages'            => ['home', 'vendor_directory', 'product_listing', 'vendor_storefront', 'cart', 'checkout', 'vendor_dashboard', 'admin_dashboard'],
                'permissions'      => ['manage_vendors', 'approve_products', 'manage_commissions', 'manage_payouts', 'view_platform_reports'],
                'questions_to_ask' => ['Fixed commission or per-category rates?', 'Vendor self-onboarding or admin-approved?', 'Unified checkout or per-vendor checkout?', 'Physical or digital products?'],
                'business_rules'   => ['commission_before_vendor_payout', 'vendor_approval_required', 'minimum_payout_threshold', 'admin_holds_escrow'],
                'integrations'     => ['stripe_connect', 'sslcommerz', 'bkash', 'paypal', 'google_analytics'],
                'ai_fallback_areas'=> ['smart_vendor_matching', 'fraud_detection', 'personalized_recommendations'],
            ],

            // ── POS ──────────────────────────────────────────────────────
            'pos' => [
                'name'             => 'Point of Sale (POS)',
                'icon'             => '🖥️',
                'category'         => 'commerce',
                'industry'         => 'retail',
                'problem_solved'   => 'Manage in-store sales, inventory, and cashier operations',
                'target_users'     => ['admin', 'cashier', 'manager'],
                'keywords'         => ['pos', 'point of sale', 'point-of-sale', 'cashier', 'retail pos', 'cash register', 'barcode', 'receipt', 'পস', 'পয়েন্ট অফ সেল'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'loyalty', 'multi_branch', 'supplier'],
                'key_entities'     => ['Product', 'Category', 'Sale', 'SaleItem', 'Customer', 'Payment', 'CashDrawer', 'Barcode', 'Supplier', 'PurchaseOrder', 'StockAdjustment', 'Shift'],
                'workflows'        => ['barcode_scan', 'sale_processing', 'payment_collection', 'receipt_printing', 'daily_closing', 'stock_replenishment', 'return_exchange'],
                'reports'          => ['daily_sales', 'shift_report', 'product_sales', 'payment_method_breakdown', 'stock_movement', 'profit_margin'],
                'pages'            => ['pos_terminal', 'product_management', 'stock_management', 'sales_history', 'cashier_shift', 'reports_dashboard', 'supplier_management'],
                'permissions'      => ['process_sales', 'apply_discount', 'process_refund', 'manage_inventory', 'view_reports', 'manage_suppliers'],
                'questions_to_ask' => ['Single outlet or multi-branch?', 'Cash only or card/MFS payments?', 'Need customer loyalty points?', 'Print receipts or digital only?'],
                'business_rules'   => ['cash_drawer_balanced_before_close', 'discount_requires_manager_approval', 'negative_stock_not_allowed', 'refund_within_policy_window'],
                'integrations'     => ['bkash', 'nagad', 'stripe', 'sslcommerz', 'thermal_printer', 'barcode_scanner'],
                'ai_fallback_areas'=> ['demand_forecasting', 'smart_reorder_alerts', 'sales_pattern_analysis'],
            ],

            // ── CRM ──────────────────────────────────────────────────────
            'crm' => [
                'name'             => 'CRM — Customer Relationship Management',
                'icon'             => '🤝',
                'category'         => 'business',
                'industry'         => 'sales',
                'problem_solved'   => 'Manage leads, deals, follow-ups, and customer relationships',
                'target_users'     => ['admin', 'sales_rep', 'manager'],
                'keywords'         => ['crm', 'customer relationship', 'leads', 'pipeline', 'deals', 'follow up', 'follow-up', 'sales management', 'contact management', 'সেলস', 'লিড', 'إدارة علاقات العملاء'],
                'required_modules' => ['auth', 'rbac', 'notifications'],
                'optional_modules' => ['reports', 'email_integration', 'sms', 'calendar', 'invoicing'],
                'key_entities'     => ['Contact', 'Lead', 'Deal', 'Pipeline', 'Stage', 'Activity', 'Note', 'Task', 'Company', 'Product', 'Quote', 'Invoice', 'EmailLog'],
                'workflows'        => ['lead_capture', 'lead_qualification', 'pipeline_progression', 'follow_up_scheduling', 'deal_closing', 'customer_onboarding'],
                'reports'          => ['pipeline_summary', 'deal_forecast', 'win_loss_rate', 'rep_performance', 'lead_source_analysis', 'activity_report'],
                'pages'            => ['dashboard', 'contacts', 'leads', 'pipeline_board', 'deals', 'activities', 'reports', 'settings'],
                'permissions'      => ['manage_leads', 'manage_deals', 'view_all_contacts', 'delete_records', 'export_data', 'view_reports'],
                'questions_to_ask' => ['B2B (company contacts) or B2C (individual customers)?', 'How many pipeline stages?', 'Need email/SMS integration?', 'WhatsApp follow-up automation?'],
                'business_rules'   => ['lead_assigned_to_one_rep', 'deal_stage_progression_locked', 'follow_up_sla_enforced', 'closed_deals_cannot_be_deleted'],
                'integrations'     => ['gmail', 'outlook', 'whatsapp_business', 'sms_gateway', 'google_calendar'],
                'ai_fallback_areas'=> ['lead_scoring', 'sentiment_analysis', 'churn_prediction', 'smart_follow_up_suggestions'],
            ],

            // ── ERP ──────────────────────────────────────────────────────
            'erp' => [
                'name'             => 'Enterprise Resource Planning (ERP)',
                'icon'             => '🏗️',
                'category'         => 'business',
                'industry'         => 'manufacturing',
                'problem_solved'   => 'Unified system for accounting, HR, inventory, procurement, and manufacturing',
                'target_users'     => ['admin', 'accountant', 'hr_manager', 'warehouse_manager', 'procurement_officer'],
                'keywords'         => ['erp', 'enterprise resource', 'manufacturing', 'production', 'procurement', 'supply chain management', 'ইআরপি', 'نظام تخطيط موارد المؤسسات'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'multi_branch', 'multi_currency', 'api_builder'],
                'key_entities'     => ['Department', 'Employee', 'Supplier', 'Customer', 'Product', 'BOM', 'ProductionOrder', 'PurchaseOrder', 'SalesOrder', 'Invoice', 'Journal', 'Account', 'Warehouse', 'StockMovement'],
                'workflows'        => ['purchase_to_pay', 'order_to_cash', 'hire_to_retire', 'plan_to_produce', 'requisition_approval', 'goods_receipt'],
                'reports'          => ['profit_and_loss', 'balance_sheet', 'cash_flow', 'inventory_valuation', 'production_cost', 'budget_vs_actual'],
                'pages'            => ['dashboard', 'accounting', 'hr', 'inventory', 'procurement', 'sales', 'manufacturing', 'reports'],
                'permissions'      => ['manage_accounts', 'manage_payroll', 'manage_inventory', 'approve_purchase_orders', 'manage_production'],
                'questions_to_ask' => ['Manufacturing or trading company?', 'Multi-branch or single location?', 'Multi-currency needed?', 'Existing accounting system to migrate from?'],
                'business_rules'   => ['double_entry_accounting', 'purchase_order_approval_chain', 'negative_inventory_blocked', 'payroll_period_locked_after_close'],
                'integrations'     => ['bank_feeds', 'vat_filing', 'nbr_bangladesh', 'gst_india', 'email'],
                'ai_fallback_areas'=> ['demand_planning', 'financial_forecasting', 'anomaly_detection'],
            ],

            // ── HRM ──────────────────────────────────────────────────────
            'hrm' => [
                'name'             => 'Human Resource Management (HRM)',
                'icon'             => '👥',
                'category'         => 'business',
                'industry'         => 'hr',
                'problem_solved'   => 'Manage employees, attendance, leave, payroll, and performance',
                'target_users'     => ['admin', 'hr_manager', 'employee', 'manager'],
                'keywords'         => ['hrm', 'hr management', 'human resource', 'employee management', 'attendance', 'leave management', 'payroll', 'এইচআর', 'কর্মী', 'إدارة الموارد البشرية'],
                'required_modules' => ['auth', 'rbac', 'notifications'],
                'optional_modules' => ['reports', 'biometric', 'payroll_module', 'training', 'recruitment'],
                'key_entities'     => ['Employee', 'Department', 'Designation', 'Attendance', 'Leave', 'LeaveType', 'Salary', 'SalarySlip', 'Allowance', 'Deduction', 'Performance', 'Appraisal', 'Document'],
                'workflows'        => ['employee_onboarding', 'attendance_tracking', 'leave_application', 'leave_approval', 'payroll_processing', 'performance_review', 'offboarding'],
                'reports'          => ['attendance_summary', 'leave_balance', 'payroll_register', 'department_headcount', 'overtime_report', 'employee_turnover'],
                'pages'            => ['dashboard', 'employees', 'attendance', 'leave', 'payroll', 'performance', 'recruitment', 'reports', 'settings'],
                'permissions'      => ['manage_employees', 'approve_leave', 'process_payroll', 'view_all_attendance', 'manage_departments'],
                'questions_to_ask' => ['Biometric device integration needed?', 'Shift-based or fixed-time attendance?', 'Bangladesh labour law compliance needed?', 'Provident fund / gratuity calculation?'],
                'business_rules'   => ['leave_balance_cannot_go_negative', 'payroll_locked_after_approval', 'overtime_rate_per_labour_law', 'probation_period_no_annual_leave'],
                'integrations'     => ['biometric_device', 'sms_gateway', 'bank_transfer', 'email'],
                'ai_fallback_areas'=> ['attrition_prediction', 'performance_insight', 'smart_scheduling'],
            ],

            // ── Accounting ───────────────────────────────────────────────
            'accounting' => [
                'name'             => 'Accounting & Finance System',
                'icon'             => '💰',
                'category'         => 'business',
                'industry'         => 'finance',
                'problem_solved'   => 'Double-entry bookkeeping, invoicing, and financial reporting',
                'target_users'     => ['admin', 'accountant', 'manager'],
                'keywords'         => ['accounting', 'accounts', 'bookkeeping', 'double entry', 'ledger', 'invoice', 'billing', 'financial', 'হিসাব', 'অ্যাকাউন্টিং', 'محاسبة', 'contabilidad'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'multi_currency', 'tax_module', 'bank_reconciliation'],
                'key_entities'     => ['Account', 'ChartOfAccounts', 'Journal', 'JournalEntry', 'Invoice', 'Bill', 'Payment', 'Receipt', 'Customer', 'Supplier', 'TaxRate', 'FiscalYear', 'BankAccount'],
                'workflows'        => ['invoice_creation', 'payment_receipt', 'bank_reconciliation', 'month_end_close', 'year_end_close', 'tax_filing'],
                'reports'          => ['profit_and_loss', 'balance_sheet', 'cash_flow_statement', 'trial_balance', 'aged_receivables', 'aged_payables', 'tax_summary'],
                'pages'            => ['dashboard', 'chart_of_accounts', 'journal_entries', 'invoices', 'bills', 'payments', 'bank_reconciliation', 'reports'],
                'permissions'      => ['create_journal_entries', 'approve_payments', 'close_period', 'view_financial_reports', 'manage_chart_of_accounts'],
                'questions_to_ask' => ['Bangladesh VAT / India GST compliance needed?', 'Multi-currency transactions?', 'Integration with existing bank?', 'How many fiscal entities?'],
                'business_rules'   => ['debit_must_equal_credit', 'closed_period_no_edit', 'gst_vat_auto_calculated', 'negative_cash_blocked'],
                'integrations'     => ['nbr_vat_bangladesh', 'gst_india', 'bank_api', 'email', 'pdf_export'],
                'ai_fallback_areas'=> ['anomaly_detection', 'cash_flow_forecasting', 'expense_categorisation'],
            ],

            // ── Payroll ──────────────────────────────────────────────────
            'payroll' => [
                'name'             => 'Payroll Management System',
                'icon'             => '💵',
                'category'         => 'business',
                'industry'         => 'hr',
                'problem_solved'   => 'Automate salary calculation, tax deduction, and salary disbursement',
                'target_users'     => ['admin', 'hr_manager', 'accountant', 'employee'],
                'keywords'         => ['payroll', 'salary', 'wage', 'pay slip', 'payslip', 'salary management', 'বেতন', 'পে-রোল', 'رواتب', 'nómina'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'tax_module', 'bank_transfer'],
                'key_entities'     => ['Employee', 'SalaryStructure', 'Allowance', 'Deduction', 'PayrollRun', 'PaySlip', 'TaxSlab', 'PFContribution', 'Gratuity', 'BankAccount'],
                'workflows'        => ['salary_structure_setup', 'monthly_payroll_run', 'payslip_generation', 'tax_deduction', 'bank_transfer', 'payslip_delivery'],
                'reports'          => ['payroll_register', 'bank_advice', 'tax_deduction_report', 'department_cost', 'ytd_summary', 'pf_report'],
                'pages'            => ['dashboard', 'salary_structures', 'payroll_runs', 'payslips', 'tax_settings', 'bank_accounts', 'reports'],
                'permissions'      => ['run_payroll', 'approve_payroll', 'view_own_payslip', 'manage_salary_structures', 'view_payroll_reports'],
                'questions_to_ask' => ['Bangladesh income tax / India TDS / other country?', 'Bank transfer or cash payment?', 'Provident fund and gratuity?', 'Overtime calculation method?'],
                'business_rules'   => ['payroll_locked_after_approval', 'tax_calculated_per_slab', 'pf_both_employer_employee_contribution', 'final_settlement_on_resignation'],
                'integrations'     => ['bank_transfer_api', 'income_tax_portal', 'sms_payslip', 'email'],
                'ai_fallback_areas'=> ['tax_optimisation_suggestions', 'anomaly_flagging'],
            ],

            // ── Inventory ────────────────────────────────────────────────
            'inventory' => [
                'name'             => 'Inventory Management System',
                'icon'             => '📦',
                'category'         => 'commerce',
                'industry'         => 'logistics',
                'problem_solved'   => 'Track stock levels, warehouses, GRN, and supplier orders',
                'target_users'     => ['admin', 'warehouse_manager', 'store_keeper'],
                'keywords'         => ['inventory', 'stock', 'warehouse', 'stock management', 'grn', 'goods receipt', 'fifo', 'lifo', 'স্টক', 'ইনভেন্টরি', 'مخزون', 'inventario'],
                'required_modules' => ['auth', 'rbac', 'notifications'],
                'optional_modules' => ['reports', 'barcode', 'multi_warehouse', 'supplier_portal'],
                'key_entities'     => ['Product', 'Category', 'Warehouse', 'Location', 'StockMovement', 'GoodsReceipt', 'PurchaseOrder', 'Supplier', 'StockAdjustment', 'Batch', 'Expiry'],
                'workflows'        => ['purchase_order', 'goods_receipt', 'stock_transfer', 'stock_adjustment', 'reorder_alert', 'stock_audit'],
                'reports'          => ['stock_summary', 'stock_movement', 'low_stock_alert', 'inventory_valuation', 'grn_report', 'expiry_report'],
                'pages'            => ['dashboard', 'products', 'warehouses', 'stock_movements', 'purchase_orders', 'suppliers', 'reports', 'settings'],
                'permissions'      => ['manage_stock', 'approve_grn', 'create_purchase_orders', 'adjust_stock', 'view_reports'],
                'questions_to_ask' => ['Single warehouse or multi-warehouse?', 'FIFO or weighted average costing?', 'Expiry date tracking (pharma/food)?', 'Barcode scanning integration?'],
                'business_rules'   => ['negative_stock_blocked', 'grn_requires_purchase_order', 'fifo_costing_auto', 'reorder_level_alert'],
                'integrations'     => ['barcode_scanner', 'supplier_portal', 'accounting_system', 'email'],
                'ai_fallback_areas'=> ['demand_forecasting', 'smart_reorder', 'spoilage_prediction'],
            ],

            // ── Project Management ────────────────────────────────────────
            'project_management' => [
                'name'             => 'Project Management System',
                'icon'             => '📋',
                'category'         => 'business',
                'industry'         => 'services',
                'problem_solved'   => 'Manage projects, tasks, teams, timelines, and client delivery',
                'target_users'     => ['admin', 'project_manager', 'team_member', 'client'],
                'keywords'         => ['project management', 'task management', 'tasks', 'projects', 'agile', 'kanban', 'scrum', 'team management', 'agency', 'project tracker', 'প্রজেক্ট', 'مشروع'],
                'required_modules' => ['auth', 'rbac', 'notifications', 'media'],
                'optional_modules' => ['reports', 'time_tracking', 'invoicing', 'chat', 'calendar'],
                'key_entities'     => ['Project', 'Task', 'Milestone', 'Sprint', 'Team', 'Member', 'Comment', 'Attachment', 'TimeLog', 'Invoice', 'Client', 'Label'],
                'workflows'        => ['project_creation', 'task_assignment', 'sprint_planning', 'task_progression', 'time_logging', 'milestone_review', 'client_delivery'],
                'reports'          => ['project_progress', 'team_workload', 'time_vs_budget', 'milestone_status', 'overdue_tasks', 'billable_hours'],
                'pages'            => ['dashboard', 'projects', 'kanban_board', 'task_list', 'milestones', 'team', 'time_tracking', 'reports', 'client_portal'],
                'permissions'      => ['create_projects', 'manage_tasks', 'assign_members', 'view_all_projects', 'manage_billing', 'client_view'],
                'questions_to_ask' => ['Kanban board or list view?', 'Time tracking and billing needed?', 'Client portal for progress viewing?', 'Sprint/Agile workflow or waterfall?'],
                'business_rules'   => ['task_must_have_assignee', 'overdue_task_auto_escalation', 'billable_hours_requires_approval', 'project_budget_alert'],
                'integrations'     => ['slack', 'github', 'google_drive', 'calendar', 'email'],
                'ai_fallback_areas'=> ['smart_task_estimation', 'risk_detection', 'resource_optimisation'],
            ],

        ]; // end commerceBlueprints
    }

    // ═════════════════════════════════════════════════════════════════════
    // PART 2 — HEALTHCARE & EDUCATION  (populated in next step)
    // ═════════════════════════════════════════════════════════════════════

    private function healthcareBlueprints(): array
    {
        return [

            // ── Hospital ─────────────────────────────────────────────────
            'hospital' => [
                'name'             => 'Hospital Management System',
                'icon'             => '🏥',
                'category'         => 'healthcare',
                'industry'         => 'hospital',
                'problem_solved'   => 'Manage patients, doctors, appointments, EMR, billing, and pharmacy',
                'target_users'     => ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'patient'],
                'keywords'         => ['hospital', 'hospital management', 'hms', 'patient management', 'doctor', 'nurse', 'ward', 'icu', 'emergency', 'হাসপাতাল', 'مستشفى', 'अस्पताल'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'telemedicine', 'pharmacy_module', 'lab_module', 'insurance'],
                'key_entities'     => ['Patient', 'Doctor', 'Appointment', 'MedicalRecord', 'Prescription', 'Medicine', 'Ward', 'Bed', 'Admission', 'Discharge', 'Bill', 'LabTest', 'LabResult', 'Department'],
                'workflows'        => ['patient_registration', 'appointment_booking', 'doctor_consultation', 'prescription_writing', 'lab_order', 'pharmacy_dispensing', 'billing', 'discharge'],
                'reports'          => ['daily_opd_report', 'patient_history', 'revenue_report', 'doctor_performance', 'bed_occupancy', 'pharmacy_stock', 'lab_test_summary'],
                'pages'            => ['dashboard', 'patient_registration', 'appointments', 'opd', 'ipd', 'pharmacy', 'laboratory', 'billing', 'doctor_schedule', 'reports'],
                'permissions'      => ['register_patient', 'book_appointment', 'write_prescription', 'order_lab_test', 'dispense_medicine', 'create_bill', 'view_patient_history'],
                'questions_to_ask' => ['OPD only or IPD (inpatient) as well?', 'Pharmacy and lab modules needed?', 'Insurance/panel billing required?', 'Bangladesh DGDA compliance needed?'],
                'business_rules'   => ['prescription_by_registered_doctor_only', 'patient_data_privacy', 'controlled_drug_double_approval', 'lab_result_linked_to_prescription'],
                'integrations'     => ['bkash', 'sslcommerz', 'sms_gateway', 'lab_equipment_api', 'insurance_portal'],
                'ai_fallback_areas'=> ['diagnosis_suggestion', 'drug_interaction_check', 'patient_risk_scoring'],
            ],

            // ── Clinic ───────────────────────────────────────────────────
            'clinic' => [
                'name'             => 'Clinic / Diagnostic Center',
                'icon'             => '🩺',
                'category'         => 'healthcare',
                'industry'         => 'hospital',
                'problem_solved'   => 'Manage appointments, doctor consultations, and diagnostic reports',
                'target_users'     => ['admin', 'doctor', 'receptionist', 'patient'],
                'keywords'         => ['clinic', 'diagnostic', 'diagnostic center', 'chamber', 'health center', 'doctor chamber', 'ক্লিনিক', 'ডায়াগনস্টিক', 'عيادة'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'online_booking', 'sms', 'lab_module'],
                'key_entities'     => ['Patient', 'Doctor', 'Appointment', 'Consultation', 'Prescription', 'LabTest', 'LabResult', 'Bill', 'Service'],
                'workflows'        => ['appointment_booking', 'patient_check_in', 'consultation', 'lab_test_order', 'result_delivery', 'billing'],
                'reports'          => ['daily_appointments', 'doctor_income', 'test_frequency', 'revenue_by_service', 'patient_followup'],
                'pages'            => ['dashboard', 'appointments', 'patients', 'consultations', 'lab_tests', 'billing', 'doctor_schedule'],
                'permissions'      => ['book_appointment', 'view_patient', 'write_prescription', 'order_lab', 'process_payment'],
                'questions_to_ask' => ['Single doctor or multi-doctor clinic?', 'Lab tests in-house or referral?', 'Online appointment booking needed?', 'SMS reminders for appointments?'],
                'business_rules'   => ['appointment_slot_no_double_booking', 'prescription_only_by_doctor', 'lab_result_confidential'],
                'integrations'     => ['sms_gateway', 'bkash', 'nagad', 'email', 'whatsapp'],
                'ai_fallback_areas'=> ['symptom_triage', 'appointment_no_show_prediction'],
            ],

            // ── Pharmacy ─────────────────────────────────────────────────
            'pharmacy' => [
                'name'             => 'Pharmacy Management System',
                'icon'             => '💊',
                'category'         => 'healthcare',
                'industry'         => 'pharmacy',
                'problem_solved'   => 'Manage medicine stock, sales, prescriptions, and supplier orders',
                'target_users'     => ['admin', 'pharmacist', 'cashier'],
                'keywords'         => ['pharmacy', 'medicine', 'drug store', 'pharma', 'ফার্মেসি', 'ওষুধ', 'صيدلية', 'farmacia'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'barcode', 'supplier_portal', 'pos'],
                'key_entities'     => ['Medicine', 'Category', 'Supplier', 'Purchase', 'Sale', 'SaleItem', 'Prescription', 'Stock', 'Batch', 'Expiry', 'Customer'],
                'workflows'        => ['medicine_purchase', 'prescription_verification', 'medicine_dispensing', 'stock_expiry_check', 'reorder_alert', 'supplier_payment'],
                'reports'          => ['daily_sales', 'stock_expiry_report', 'purchase_report', 'profit_margin', 'top_selling_medicines', 'low_stock_alert'],
                'pages'            => ['pos_counter', 'stock_management', 'purchases', 'prescriptions', 'suppliers', 'expiry_management', 'reports'],
                'permissions'      => ['sell_medicine', 'manage_stock', 'create_purchase', 'view_reports', 'manage_suppliers'],
                'questions_to_ask' => ['Controlled drug management (narcotics register)?', 'Prescription required for all medicines?', 'Expiry date FIFO tracking?', 'Multiple branches?'],
                'business_rules'   => ['controlled_drug_requires_prescription', 'negative_stock_blocked', 'expiry_fifo_enforced', 'dgda_schedule_compliance'],
                'integrations'     => ['barcode_scanner', 'bkash', 'supplier_portal', 'sms'],
                'ai_fallback_areas'=> ['drug_interaction_alert', 'demand_forecasting', 'expiry_optimisation'],
            ],

            // ── Telemedicine ─────────────────────────────────────────────
            'telemedicine' => [
                'name'             => 'Telemedicine Platform',
                'icon'             => '📱',
                'category'         => 'healthcare',
                'industry'         => 'hospital',
                'problem_solved'   => 'Online doctor consultations via video, chat, and prescription delivery',
                'target_users'     => ['admin', 'doctor', 'patient'],
                'keywords'         => ['telemedicine', 'telehealth', 'virtual clinic', 'online doctor', 'video consultation', 'online consultation', 'digital health', 'টেলিমেডিসিন'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'video_call', 'chat', 'prescription_delivery'],
                'key_entities'     => ['Patient', 'Doctor', 'Consultation', 'Appointment', 'Prescription', 'Payment', 'Review', 'Availability'],
                'workflows'        => ['patient_registration', 'doctor_search', 'slot_booking', 'video_consultation', 'prescription_issuance', 'payment', 'follow_up'],
                'reports'          => ['consultations_per_day', 'doctor_revenue', 'top_specialties', 'patient_retention'],
                'pages'            => ['home', 'find_doctor', 'doctor_profile', 'booking', 'consultation_room', 'my_consultations', 'prescription', 'doctor_dashboard'],
                'permissions'      => ['book_consultation', 'start_video_call', 'write_prescription', 'view_patient_history'],
                'questions_to_ask' => ['Video call via WebRTC or Zoom API?', 'Prescription home delivery integration?', 'Insurance claim support?'],
                'business_rules'   => ['prescription_only_after_consultation', 'doctor_verified_before_listing', 'payment_held_until_consultation_complete'],
                'integrations'     => ['zoom_api', 'webrtc', 'bkash', 'sslcommerz', 'sms', 'whatsapp'],
                'ai_fallback_areas'=> ['symptom_checker', 'specialty_recommendation', 'health_risk_assessment'],
            ],

            // ── Dental Clinic ─────────────────────────────────────────────
            'dental' => [
                'name'             => 'Dental Clinic Management',
                'icon'             => '🦷',
                'category'         => 'healthcare',
                'industry'         => 'hospital',
                'problem_solved'   => 'Manage dental appointments, treatment plans, and billing',
                'target_users'     => ['admin', 'dentist', 'receptionist', 'patient'],
                'keywords'         => ['dental', 'dentist', 'dental clinic', 'teeth', 'orthodontic', 'ডেন্টাল', 'দাঁতের ক্লিনিক', 'عيادة أسنان'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'online_booking', 'sms', 'media'],
                'key_entities'     => ['Patient', 'Dentist', 'Appointment', 'TreatmentPlan', 'Treatment', 'ToothChart', 'Bill', 'Payment', 'MedicalHistory'],
                'workflows'        => ['appointment_booking', 'patient_check_in', 'tooth_examination', 'treatment_planning', 'treatment_execution', 'billing', 'follow_up'],
                'reports'          => ['daily_appointments', 'treatment_revenue', 'dentist_performance', 'pending_treatments'],
                'pages'            => ['dashboard', 'appointments', 'patients', 'tooth_chart', 'treatment_plans', 'billing', 'reports'],
                'permissions'      => ['book_appointment', 'create_treatment_plan', 'update_tooth_chart', 'process_payment'],
                'questions_to_ask' => ['Tooth chart / odontogram needed?', 'Multiple dentists?', 'X-ray image upload?'],
                'business_rules'   => ['treatment_plan_before_procedure', 'informed_consent_required', 'appointment_no_double_booking'],
                'integrations'     => ['sms_gateway', 'bkash', 'email', 'media_storage'],
                'ai_fallback_areas'=> ['treatment_cost_estimation', 'x_ray_analysis'],
            ],

            // ── Laboratory ───────────────────────────────────────────────
            'laboratory' => [
                'name'             => 'Medical Laboratory System',
                'icon'             => '🔬',
                'category'         => 'healthcare',
                'industry'         => 'hospital',
                'problem_solved'   => 'Manage lab test orders, sample collection, results, and reporting',
                'target_users'     => ['admin', 'lab_technician', 'doctor', 'patient'],
                'keywords'         => ['laboratory', 'lab', 'medical lab', 'pathology', 'blood test', 'lab test', 'diagnostic lab', 'ল্যাবরেটরি', 'مختبر طبي'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'barcode', 'equipment_api', 'sms'],
                'key_entities'     => ['Patient', 'TestOrder', 'TestItem', 'Sample', 'Result', 'ReferenceRange', 'Bill', 'Doctor', 'Equipment'],
                'workflows'        => ['test_order', 'sample_collection', 'sample_processing', 'result_entry', 'result_validation', 'report_delivery', 'billing'],
                'reports'          => ['daily_test_report', 'revenue_report', 'pending_results', 'test_frequency_analysis'],
                'pages'            => ['dashboard', 'test_orders', 'sample_collection', 'result_entry', 'reports', 'billing', 'settings'],
                'permissions'      => ['create_test_order', 'collect_sample', 'enter_results', 'validate_results', 'deliver_report'],
                'questions_to_ask' => ['Equipment interface (HL7/ASTM) integration?', 'SMS/email result delivery to patients?', 'Doctor referral tracking?'],
                'business_rules'   => ['result_validated_before_delivery', 'critical_values_auto_alert', 'sample_barcode_tracking'],
                'integrations'     => ['lab_equipment_hl7', 'sms_gateway', 'email', 'bkash'],
                'ai_fallback_areas'=> ['result_interpretation', 'abnormal_value_flagging'],
            ],

        ]; // end healthcareBlueprints
    }

    private function educationBlueprints(): array
    {
        return [

            // ── School ERP ───────────────────────────────────────────────
            'school' => [
                'name'             => 'School / College ERP',
                'icon'             => '🎓',
                'category'         => 'education',
                'industry'         => 'school',
                'problem_solved'   => 'Manage students, classes, exams, fees, attendance, and parent portal',
                'target_users'     => ['admin', 'teacher', 'student', 'parent', 'accountant'],
                'keywords'         => ['school', 'college', 'school management', 'student management', 'school erp', 'education management', 'ক্লাস', 'বিদ্যালয়', 'مدرسة', 'escuela'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'sms', 'library', 'transport', 'hostel'],
                'key_entities'     => ['Student', 'Class', 'Section', 'Teacher', 'Subject', 'Attendance', 'Exam', 'ExamResult', 'Fee', 'FeePayment', 'Parent', 'Notice', 'Assignment'],
                'workflows'        => ['student_admission', 'class_assignment', 'attendance_marking', 'exam_scheduling', 'result_publication', 'fee_collection', 'notice_publishing'],
                'reports'          => ['attendance_report', 'exam_result', 'fee_collection_report', 'class_progress', 'student_performance', 'defaulter_list'],
                'pages'            => ['dashboard', 'students', 'classes', 'attendance', 'exams', 'results', 'fees', 'notices', 'teachers', 'parent_portal', 'reports'],
                'permissions'      => ['manage_students', 'mark_attendance', 'manage_exams', 'collect_fees', 'publish_results', 'view_reports', 'parent_view'],
                'questions_to_ask' => ['Primary, secondary, or higher secondary?', 'Hostel and transport management needed?', 'Online exam or paper-based?', 'SMS to parents on fees/results?'],
                'business_rules'   => ['attendance_marked_before_class_ends', 'result_published_after_moderation', 'fee_receipt_on_payment', 'admission_number_unique'],
                'integrations'     => ['sms_gateway', 'bkash', 'nagad', 'email'],
                'ai_fallback_areas'=> ['student_performance_prediction', 'personalised_learning'],
            ],

            // ── University ───────────────────────────────────────────────
            'university' => [
                'name'             => 'University Management System',
                'icon'             => '🏛️',
                'category'         => 'education',
                'industry'         => 'school',
                'problem_solved'   => 'Manage university admissions, courses, departments, credits, and research',
                'target_users'     => ['admin', 'faculty', 'student', 'registrar', 'department_head'],
                'keywords'         => ['university', 'varsity', 'higher education', 'college erp', 'university management', 'বিশ্ববিদ্যালয়', 'جامعة'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'library', 'hostel', 'lms_module', 'research_portal'],
                'key_entities'     => ['Student', 'Faculty', 'Department', 'Course', 'Semester', 'Enrollment', 'Grade', 'Credit', 'Thesis', 'Research', 'Scholarship', 'Fee'],
                'workflows'        => ['student_admission', 'course_enrollment', 'class_scheduling', 'grading', 'semester_close', 'graduation'],
                'reports'          => ['enrollment_report', 'cgpa_summary', 'faculty_workload', 'scholarship_report', 'fee_collection'],
                'pages'            => ['dashboard', 'admissions', 'students', 'faculty', 'courses', 'schedule', 'grades', 'fees', 'library', 'reports'],
                'permissions'      => ['manage_admissions', 'submit_grades', 'manage_courses', 'approve_thesis', 'view_department_reports'],
                'questions_to_ask' => ['Credit-based or year-based system?', 'Online admission portal?', 'Research and thesis management?', 'UGC/university regulatory compliance?'],
                'business_rules'   => ['credit_minimum_for_graduation', 'gpa_calculated_on_credit_weight', 'course_prerequisite_enforced', 'grade_change_requires_approval'],
                'integrations'     => ['national_student_id', 'bank_portal', 'sms_gateway', 'email'],
                'ai_fallback_areas'=> ['course_recommendation', 'at_risk_student_detection', 'research_topic_suggestion'],
            ],

            // ── LMS ──────────────────────────────────────────────────────
            'lms' => [
                'name'             => 'Learning Management System (LMS)',
                'icon'             => '💻',
                'category'         => 'education',
                'industry'         => 'school',
                'problem_solved'   => 'Create and sell online courses with video lessons and quizzes',
                'target_users'     => ['admin', 'instructor', 'student'],
                'keywords'         => ['lms', 'online course', 'e-learning', 'elearning', 'course platform', 'udemy clone', 'video course', 'quiz', 'learning management', 'অনলাইন কোর্স', 'منصة تعليمية'],
                'required_modules' => ['auth', 'rbac', 'payments', 'media', 'notifications'],
                'optional_modules' => ['reports', 'certificate', 'live_class', 'forum', 'affiliate'],
                'key_entities'     => ['Course', 'Module', 'Lesson', 'Quiz', 'Question', 'Enrollment', 'Progress', 'Certificate', 'Review', 'Instructor', 'Category', 'Coupon'],
                'workflows'        => ['course_creation', 'course_publishing', 'student_enrollment', 'lesson_progression', 'quiz_attempt', 'certificate_issuance', 'instructor_payout'],
                'reports'          => ['enrollment_stats', 'course_completion_rate', 'revenue_by_course', 'instructor_earnings', 'quiz_performance'],
                'pages'            => ['home', 'course_catalog', 'course_detail', 'checkout', 'my_courses', 'lesson_player', 'quiz', 'certificate', 'instructor_dashboard', 'admin_dashboard'],
                'permissions'      => ['create_course', 'publish_course', 'manage_students', 'grade_quiz', 'issue_certificate', 'view_reports'],
                'questions_to_ask' => ['Video hosting — own server or YouTube/Vimeo?', 'Live class integration (Zoom)?', 'Certificate generation on completion?', 'Affiliate program for instructors?'],
                'business_rules'   => ['video_drm_protection', 'certificate_after_100_percent_completion', 'instructor_payout_after_refund_window', 'course_must_be_approved_before_publish'],
                'integrations'     => ['youtube_api', 'vimeo', 'zoom', 'stripe', 'bkash', 'cloudflare_stream'],
                'ai_fallback_areas'=> ['personalised_learning_path', 'quiz_auto_generation', 'content_recommendation'],
            ],

            // ── Coaching Center ───────────────────────────────────────────
            'coaching_center' => [
                'name'             => 'Coaching Center Management',
                'icon'             => '📚',
                'category'         => 'education',
                'industry'         => 'school',
                'problem_solved'   => 'Manage batches, teachers, student fees, and exam results for coaching institutes',
                'target_users'     => ['admin', 'teacher', 'student', 'parent'],
                'keywords'         => ['coaching', 'coaching center', 'tuition', 'tutorial', 'coaching institute', 'batch', 'কোচিং', 'کوچنگ'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'sms', 'online_test', 'attendance'],
                'key_entities'     => ['Student', 'Teacher', 'Batch', 'Subject', 'Schedule', 'Attendance', 'Fee', 'FeePayment', 'Exam', 'Result', 'Notice'],
                'workflows'        => ['student_enrollment', 'batch_assignment', 'class_scheduling', 'attendance_marking', 'fee_collection', 'exam_conduct', 'result_entry'],
                'reports'          => ['attendance_report', 'fee_collection', 'exam_results', 'batch_performance'],
                'pages'            => ['dashboard', 'students', 'batches', 'teachers', 'attendance', 'fees', 'exams', 'results', 'sms_notices'],
                'permissions'      => ['manage_students', 'manage_batches', 'collect_fees', 'mark_attendance', 'enter_results'],
                'questions_to_ask' => ['Multiple subjects / departments?', 'Online test module needed?', 'SMS fee reminders?'],
                'business_rules'   => ['batch_capacity_limit', 'fee_due_date_auto_alert', 'attendance_sms_to_parent'],
                'integrations'     => ['sms_gateway', 'bkash', 'nagad'],
                'ai_fallback_areas'=> ['weak_student_detection', 'study_plan_generation'],
            ],

        ]; // end educationBlueprints
    }

    // ═════════════════════════════════════════════════════════════════════
    // PART 3 — HOSPITALITY, REAL ESTATE, FINANCE & LOGISTICS
    // ═════════════════════════════════════════════════════════════════════

    private function hospitalityBlueprints(): array
    {
        return [

            // ── Restaurant ───────────────────────────────────────────────
            'restaurant' => [
                'name'             => 'Restaurant Management System',
                'icon'             => '🍽️',
                'category'         => 'hospitality',
                'industry'         => 'restaurant',
                'problem_solved'   => 'Manage dine-in orders, kitchen, delivery, and billing',
                'target_users'     => ['admin', 'waiter', 'kitchen', 'cashier', 'customer'],
                'keywords'         => ['restaurant', 'food', 'cafe', 'dining', 'menu', 'order food', 'kitchen', 'dine in', 'takeaway', 'রেস্টুরেন্ট', 'مطعم', 'restaurante'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'qr_menu', 'delivery_module', 'loyalty', 'kitchen_display'],
                'key_entities'     => ['MenuItem', 'Category', 'Table', 'Order', 'OrderItem', 'KitchenTicket', 'Bill', 'Payment', 'Reservation', 'Customer', 'Coupon', 'Staff'],
                'workflows'        => ['table_reservation', 'order_taking', 'kitchen_dispatch', 'food_preparation', 'serving', 'billing', 'delivery'],
                'reports'          => ['daily_sales', 'top_items', 'table_turnover', 'kitchen_efficiency', 'waste_report', 'delivery_report'],
                'pages'            => ['dashboard', 'menu_management', 'table_map', 'order_entry', 'kitchen_display', 'billing', 'reservations', 'delivery', 'reports'],
                'permissions'      => ['take_orders', 'manage_menu', 'manage_kitchen', 'process_payment', 'manage_reservations', 'view_reports'],
                'questions_to_ask' => ['Dine-in only or delivery as well?', 'QR code menu for self-ordering?', 'Kitchen Display System (KDS) integration?', 'Multiple outlets?'],
                'business_rules'   => ['order_to_kitchen_before_preparation', 'bill_generated_on_order_complete', 'table_released_after_payment', 'delivery_assigned_before_dispatch'],
                'integrations'     => ['bkash', 'nagad', 'pathao_food', 'shohoz_food', 'sms', 'thermal_printer'],
                'ai_fallback_areas'=> ['demand_forecasting', 'menu_optimisation', 'waste_reduction'],
            ],

            // ── Hotel ────────────────────────────────────────────────────
            'hotel' => [
                'name'             => 'Hotel & Hospitality Management',
                'icon'             => '🏨',
                'category'         => 'hospitality',
                'industry'         => 'hotel_hospitality',
                'problem_solved'   => 'Manage room bookings, check-in/out, housekeeping, and billing',
                'target_users'     => ['admin', 'receptionist', 'housekeeping', 'guest', 'manager'],
                'keywords'         => ['hotel', 'resort', 'hospitality', 'room booking', 'check in', 'check-in', 'checkout', 'hostel', 'guest house', 'হোটেল', 'فندق', 'hotel'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'channel_manager', 'restaurant_module', 'spa', 'loyalty'],
                'key_entities'     => ['Room', 'RoomType', 'Booking', 'Guest', 'CheckIn', 'CheckOut', 'Bill', 'HousekeepingTask', 'Amenity', 'RatePlan', 'Invoice'],
                'workflows'        => ['online_booking', 'walk_in_booking', 'check_in', 'room_service_request', 'housekeeping_assignment', 'check_out', 'billing'],
                'reports'          => ['occupancy_rate', 'revenue_per_room', 'booking_source', 'housekeeping_status', 'monthly_revenue'],
                'pages'            => ['dashboard', 'room_management', 'bookings', 'front_desk', 'housekeeping', 'billing', 'guest_history', 'reports'],
                'permissions'      => ['manage_bookings', 'check_in_out', 'manage_rooms', 'housekeeping_assign', 'process_payment', 'view_reports'],
                'questions_to_ask' => ['Online booking portal needed?', 'OTA channel manager (Booking.com, Agoda)?', 'Restaurant and spa integration?', 'Corporate billing?'],
                'business_rules'   => ['room_not_double_booked', 'payment_before_check_in', 'housekeeping_after_check_out', 'late_checkout_surcharge'],
                'integrations'     => ['stripe', 'booking_com_api', 'agoda_api', 'sms', 'bkash'],
                'ai_fallback_areas'=> ['dynamic_pricing', 'occupancy_forecast', 'guest_preference_personalisation'],
            ],

            // ── Food Delivery ─────────────────────────────────────────────
            'food_delivery' => [
                'name'             => 'Food Delivery Platform',
                'icon'             => '🛵',
                'category'         => 'hospitality',
                'industry'         => 'restaurant',
                'problem_solved'   => 'Online food ordering with multi-restaurant and delivery tracking',
                'target_users'     => ['admin', 'restaurant_owner', 'delivery_rider', 'customer'],
                'keywords'         => ['food delivery', 'online food order', 'foodpanda', 'shohoz', 'pathao food', 'delivery platform', 'food app', 'খাবার ডেলিভারি'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'live_tracking', 'loyalty', 'push_notification'],
                'key_entities'     => ['Restaurant', 'MenuItem', 'Category', 'Order', 'OrderItem', 'DeliveryRider', 'Tracking', 'Review', 'Customer', 'Zone', 'Coupon'],
                'workflows'        => ['restaurant_onboarding', 'menu_setup', 'customer_order', 'restaurant_acceptance', 'rider_assignment', 'delivery_tracking', 'rating_review'],
                'reports'          => ['platform_revenue', 'restaurant_performance', 'rider_earnings', 'delivery_time_analysis', 'top_areas'],
                'pages'            => ['home', 'restaurant_list', 'menu', 'cart', 'checkout', 'tracking', 'restaurant_dashboard', 'rider_app', 'admin_dashboard'],
                'permissions'      => ['manage_restaurants', 'accept_orders', 'assign_riders', 'track_delivery', 'manage_platform'],
                'questions_to_ask' => ['Real-time GPS tracking for riders?', 'Commission model?', 'Multi-zone delivery areas?'],
                'business_rules'   => ['restaurant_must_accept_within_timeout', 'rider_assigned_by_proximity', 'commission_auto_deducted'],
                'integrations'     => ['google_maps', 'bkash', 'nagad', 'sslcommerz', 'push_notification', 'sms'],
                'ai_fallback_areas'=> ['delivery_eta_prediction', 'dynamic_delivery_fee', 'smart_rider_dispatch'],
            ],

        ]; // end hospitalityBlueprints
    }

    private function realEstateBlueprints(): array
    {
        return [

            // ── Real Estate CRM ───────────────────────────────────────────
            'real_estate' => [
                'name'             => 'Real Estate CRM & Listing',
                'icon'             => '🏠',
                'category'         => 'real_estate',
                'industry'         => 'real_estate',
                'problem_solved'   => 'Manage property listings, leads, agents, and transactions',
                'target_users'     => ['admin', 'agent', 'buyer', 'seller'],
                'keywords'         => ['real estate', 'property', 'housing', 'land', 'apartment', 'flat', 'broker', 'realty', 'property sale', 'রিয়েল এস্টেট', 'عقارات'],
                'required_modules' => ['auth', 'rbac', 'notifications', 'media'],
                'optional_modules' => ['reports', 'map_integration', 'crm_module', 'mortgage_calculator'],
                'key_entities'     => ['Property', 'PropertyType', 'Lead', 'Agent', 'Client', 'Viewing', 'Offer', 'Deal', 'Transaction', 'Commission', 'Document', 'Location'],
                'workflows'        => ['property_listing', 'lead_capture', 'viewing_scheduling', 'offer_submission', 'negotiation', 'deal_closing', 'commission_settlement'],
                'reports'          => ['listing_performance', 'agent_performance', 'sales_pipeline', 'revenue_report', 'lead_conversion'],
                'pages'            => ['home', 'property_listing', 'property_detail', 'search_filter', 'agent_profile', 'dashboard', 'leads', 'deals', 'reports'],
                'permissions'      => ['manage_listings', 'manage_leads', 'schedule_viewing', 'close_deal', 'view_commission'],
                'questions_to_ask' => ['Sale, rent, or both?', 'Residential, commercial, or land?', 'Google Maps integration?', 'Mortgage/EMI calculator?'],
                'business_rules'   => ['listing_requires_approval', 'commission_on_closed_deal_only', 'exclusive_listing_one_agent'],
                'integrations'     => ['google_maps', 'sms_gateway', 'email', 'bkash', 'facebook_ads'],
                'ai_fallback_areas'=> ['property_valuation', 'lead_scoring', 'demand_heatmap'],
            ],

            // ── Property Rental ───────────────────────────────────────────
            'property_rental' => [
                'name'             => 'Property Rental Management',
                'icon'             => '🏢',
                'category'         => 'real_estate',
                'industry'         => 'real_estate',
                'problem_solved'   => 'Manage rental properties, tenants, leases, and rent collection',
                'target_users'     => ['admin', 'property_manager', 'tenant', 'owner'],
                'keywords'         => ['rental', 'rent', 'tenant', 'lease', 'property rental', 'landlord', 'ভাড়া', 'আবাসন', 'إيجار', 'alquiler'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'maintenance_module', 'document_manager', 'online_portal'],
                'key_entities'     => ['Property', 'Unit', 'Tenant', 'Lease', 'RentPayment', 'MaintenanceRequest', 'Inspection', 'Document', 'Owner', 'Utility'],
                'workflows'        => ['tenant_application', 'lease_signing', 'rent_collection', 'maintenance_request', 'lease_renewal', 'tenant_eviction'],
                'reports'          => ['rent_collection_report', 'occupancy_report', 'maintenance_history', 'income_report', 'overdue_payments'],
                'pages'            => ['dashboard', 'properties', 'tenants', 'leases', 'rent_payments', 'maintenance', 'documents', 'reports'],
                'permissions'      => ['manage_properties', 'manage_tenants', 'collect_rent', 'approve_maintenance', 'view_reports'],
                'questions_to_ask' => ['Residential or commercial properties?', 'Utility bill management?', 'Online tenant portal for rent payment?', 'Maintenance contractor management?'],
                'business_rules'   => ['lease_signed_before_move_in', 'rent_due_date_auto_alert', 'security_deposit_tracked_separately', 'maintenance_sla_enforced'],
                'integrations'     => ['bkash', 'nagad', 'bank_transfer', 'sms_gateway', 'email'],
                'ai_fallback_areas'=> ['rent_pricing_optimisation', 'tenant_risk_scoring'],
            ],

        ]; // end realEstateBlueprints
    }

    private function financeBlueprints(): array
    {
        return [

            // ── Microfinance ──────────────────────────────────────────────
            'microfinance' => [
                'name'             => 'Microfinance / Cooperative MIS',
                'icon'             => '🏦',
                'category'         => 'finance',
                'industry'         => 'microfinance',
                'problem_solved'   => 'Manage loan disbursement, collections, savings, and member accounts',
                'target_users'     => ['admin', 'loan_officer', 'collector', 'member', 'accountant'],
                'keywords'         => ['microfinance', 'ngo finance', 'cooperative', 'loan', 'savings', 'credit', 'mfi', 'ক্ষুদ্রঋণ', 'সমবায়', 'تمويل أصغر'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'field_collection', 'sms', 'passbook'],
                'key_entities'     => ['Member', 'LoanProduct', 'LoanApplication', 'Loan', 'Repayment', 'Savings', 'SavingsTransaction', 'Group', 'CollectionSheet', 'Officer', 'Branch'],
                'workflows'        => ['member_registration', 'loan_application', 'credit_appraisal', 'loan_approval', 'disbursement', 'weekly_collection', 'savings_deposit', 'loan_closure'],
                'reports'          => ['portfolio_at_risk', 'collection_sheet', 'disbursement_report', 'savings_summary', 'npl_report', 'branch_performance'],
                'pages'            => ['dashboard', 'members', 'loan_applications', 'active_loans', 'collections', 'savings', 'groups', 'branches', 'reports'],
                'permissions'      => ['register_member', 'approve_loan', 'disburse_loan', 'collect_repayment', 'manage_savings', 'view_reports'],
                'questions_to_ask' => ['Group lending or individual?', 'Weekly/monthly collection?', 'Bangladesh MRA compliance needed?', 'Field officer mobile app?'],
                'business_rules'   => ['loan_approval_chain', 'no_disbursement_without_approval', 'par_calculation_daily', 'penalty_on_late_repayment'],
                'integrations'     => ['bkash', 'nagad', 'sms_gateway', 'mra_reporting'],
                'ai_fallback_areas'=> ['credit_scoring', 'default_prediction', 'smart_collection_routing'],
            ],

            // ── Insurance ─────────────────────────────────────────────────
            'insurance' => [
                'name'             => 'Insurance Management System',
                'icon'             => '🛡️',
                'category'         => 'finance',
                'industry'         => 'finance',
                'problem_solved'   => 'Manage policies, premiums, claims, and agent commissions',
                'target_users'     => ['admin', 'agent', 'policyholder', 'claims_officer'],
                'keywords'         => ['insurance', 'policy', 'premium', 'claim', 'insurer', 'life insurance', 'general insurance', 'বীমা', 'تأمين'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications', 'media'],
                'optional_modules' => ['reports', 'document_manager', 'agent_portal', 'customer_portal'],
                'key_entities'     => ['Policy', 'PolicyType', 'Policyholder', 'Agent', 'Premium', 'Claim', 'ClaimItem', 'Document', 'Commission', 'Renewal'],
                'workflows'        => ['policy_issuance', 'premium_collection', 'claim_registration', 'claim_investigation', 'claim_settlement', 'policy_renewal', 'agent_commission'],
                'reports'          => ['policy_report', 'premium_income', 'claims_ratio', 'agent_performance', 'renewal_due_list'],
                'pages'            => ['dashboard', 'policies', 'policyholders', 'premiums', 'claims', 'agents', 'renewals', 'reports'],
                'permissions'      => ['issue_policy', 'collect_premium', 'register_claim', 'approve_claim', 'pay_commission'],
                'questions_to_ask' => ['Life, general, or health insurance?', 'Agent portal needed?', 'IDRA (Bangladesh) compliance?'],
                'business_rules'   => ['no_claim_without_active_policy', 'premium_paid_before_coverage', 'claim_investigation_sla', 'commission_after_policy_activation'],
                'integrations'     => ['bkash', 'bank_transfer', 'sms_gateway', 'idra_portal'],
                'ai_fallback_areas'=> ['fraud_detection', 'risk_assessment', 'claim_prediction'],
            ],

        ]; // end financeBlueprints
    }

    private function logisticsBlueprints(): array
    {
        return [

            // ── Courier / Delivery ────────────────────────────────────────
            'courier' => [
                'name'             => 'Courier & Delivery Management',
                'icon'             => '📦',
                'category'         => 'logistics',
                'industry'         => 'logistics',
                'problem_solved'   => 'Manage parcel booking, dispatch, tracking, delivery, and COD collection',
                'target_users'     => ['admin', 'agent', 'rider', 'customer'],
                'keywords'         => ['courier', 'delivery', 'parcel', 'logistics', 'shipping', 'dispatch', 'tracking', 'cod', 'কুরিয়ার', 'ডেলিভারি', 'شحن'],
                'required_modules' => ['auth', 'rbac', 'payments', 'notifications'],
                'optional_modules' => ['reports', 'live_tracking', 'sms', 'cod_management'],
                'key_entities'     => ['Parcel', 'Merchant', 'Rider', 'Hub', 'Route', 'TrackingEvent', 'CodCollection', 'Invoice', 'Zone'],
                'workflows'        => ['parcel_booking', 'pickup_assignment', 'hub_processing', 'rider_dispatch', 'delivery_attempt', 'cod_collection', 'merchant_settlement'],
                'reports'          => ['delivery_performance', 'cod_report', 'rider_performance', 'hub_throughput', 'merchant_billing'],
                'pages'            => ['dashboard', 'parcel_booking', 'tracking', 'rider_management', 'hub_management', 'merchant_portal', 'cod_management', 'reports'],
                'permissions'      => ['book_parcel', 'assign_rider', 'update_tracking', 'collect_cod', 'settle_merchant'],
                'questions_to_ask' => ['Own fleet or third-party riders?', 'COD collection model?', 'Merchant API integration?', 'Multiple hubs/branches?'],
                'business_rules'   => ['parcel_barcode_on_booking', 'cod_collected_before_delivery_complete', 'failed_delivery_auto_return', 'settlement_after_delivery_confirm'],
                'integrations'     => ['google_maps', 'bkash', 'nagad', 'sms_gateway', 'merchant_api'],
                'ai_fallback_areas'=> ['route_optimisation', 'delivery_eta_prediction', 'fraud_detection'],
            ],

            // ── Warehouse ─────────────────────────────────────────────────
            'warehouse' => [
                'name'             => 'Warehouse Management System (WMS)',
                'icon'             => '🏭',
                'category'         => 'logistics',
                'industry'         => 'logistics',
                'problem_solved'   => 'Manage receiving, putaway, picking, packing, and shipping',
                'target_users'     => ['admin', 'warehouse_manager', 'picker', 'receiver'],
                'keywords'         => ['warehouse', 'wms', 'storage', 'fulfilment', 'fulfillment', 'picking', 'packing', 'গুদাম', 'مستودع'],
                'required_modules' => ['auth', 'rbac', 'notifications'],
                'optional_modules' => ['reports', 'barcode', 'rfid', 'multi_warehouse'],
                'key_entities'     => ['Warehouse', 'Zone', 'Location', 'Product', 'Stock', 'GoodsReceipt', 'Putaway', 'PickOrder', 'PackOrder', 'ShipOrder', 'StockCount'],
                'workflows'        => ['goods_receipt', 'putaway', 'pick_order', 'packing', 'shipping', 'stock_count', 'stock_transfer'],
                'reports'          => ['stock_report', 'receiving_report', 'shipping_report', 'accuracy_report', 'space_utilisation'],
                'pages'            => ['dashboard', 'receiving', 'putaway', 'picking', 'packing', 'shipping', 'stock', 'locations', 'reports'],
                'permissions'      => ['receive_goods', 'putaway', 'pick_orders', 'pack_orders', 'ship_orders', 'manage_stock'],
                'questions_to_ask' => ['Barcode or RFID scanning?', 'Multi-client 3PL warehouse?', 'ERP integration needed?'],
                'business_rules'   => ['putaway_after_grn', 'pick_fifo_enforced', 'stock_count_freezes_movements', 'shipping_requires_packed_confirmation'],
                'integrations'     => ['barcode_scanner', 'rfid', 'erp_api', 'courier_api'],
                'ai_fallback_areas'=> ['slotting_optimisation', 'demand_forecasting', 'pick_path_optimisation'],
            ],

        ]; // end logisticsBlueprints
    }

    // ═════════════════════════════════════════════════════════════════════
    // PART 4 — MEDIA, SAAS & NICHE
    // ═════════════════════════════════════════════════════════════════════

    private function mediaBlueprints(): array    { return []; }
    private function saasNicheBlueprints(): array { return []; }
}
