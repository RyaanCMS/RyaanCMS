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

    private function healthcareBlueprints(): array { return []; }
    private function educationBlueprints(): array  { return []; }

    // ═════════════════════════════════════════════════════════════════════
    // PART 3 — HOSPITALITY, REAL ESTATE, FINANCE & LOGISTICS
    // ═════════════════════════════════════════════════════════════════════

    private function hospitalityBlueprints(): array { return []; }
    private function realEstateBlueprints(): array  { return []; }
    private function financeBlueprints(): array     { return []; }
    private function logisticsBlueprints(): array   { return []; }

    // ═════════════════════════════════════════════════════════════════════
    // PART 4 — MEDIA, SAAS & NICHE
    // ═════════════════════════════════════════════════════════════════════

    private function mediaBlueprints(): array    { return []; }
    private function saasNicheBlueprints(): array { return []; }
}
