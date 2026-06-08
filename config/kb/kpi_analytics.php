<?php
/**
 * KPI & Analytics Knowledge Base
 * Domain-specific metrics that matter to business stakeholders.
 * AI uses these to generate meaningful dashboards — not generic "total records" pages.
 * Level 3: Business Knowledge — "Build dashboards that answer real business questions."
 */
return [

    // ── KPI Design Rule ───────────────────────────────────────────────────────
    'rule' => 'Every dashboard must answer: What happened? Why? What to do next? Avoid vanity metrics.',

    // ── eCommerce KPIs ────────────────────────────────────────────────────────
    'ecommerce' => [
        'primary_kpis' => [
            'gmv'               => 'Gross Merchandise Value — total order value before returns/refunds',
            'aov'               => 'Average Order Value = GMV / order count',
            'conversion_rate'   => 'Orders / Unique Visitors (%) — target > 2% for mature stores',
            'cart_abandonment'  => '(Carts Created - Orders) / Carts Created (%) — target < 65%',
            'repeat_purchase'   => '% customers who bought 2+ times — indicates product-market fit',
            'customer_ltv'      => 'Lifetime Value = AOV × Purchase Frequency × Customer Lifespan',
        ],
        'operational_kpis' => [
            'order_fulfillment_time' => 'Hours from order placed to shipped',
            'return_rate'            => 'Returns / Orders (%) — high rate = product quality issue',
            'stock_turnover'         => 'COGS / Average Inventory — how fast stock sells',
            'out_of_stock_rate'      => '% of products with 0 stock',
            'payment_success_rate'   => 'Successful payments / Initiated payments (%) — < 95% needs attention',
        ],
        'dashboard_widgets' => [
            'Revenue Today/MTD/YTD',
            'Orders by Status (pie)',
            'Top 10 Products by Revenue',
            'Sales Trend (line chart - 30 days)',
            'Low Stock Alert Table',
            'Recent Failed Payments',
        ],
        'report_frequency' => ['daily' => 'Sales summary', 'weekly' => 'Top products + abandonment', 'monthly' => 'Full P&L + LTV cohort'],
    ],

    // ── CRM KPIs ──────────────────────────────────────────────────────────────
    'crm' => [
        'primary_kpis' => [
            'lead_conversion_rate'  => 'Deals Won / Total Leads (%) — industry avg 10-15%',
            'sales_cycle_length'    => 'Avg days from lead to close',
            'pipeline_value'        => 'Sum of all open deal values × probability',
            'win_rate'              => 'Won Deals / (Won + Lost) (%) — target > 30%',
            'average_deal_size'     => 'Revenue / Won Deals count',
            'revenue_per_rep'       => 'Total closed revenue per sales person',
        ],
        'operational_kpis' => [
            'activity_per_deal'     => 'Avg calls/emails/meetings per won deal',
            'follow_up_rate'        => '% leads contacted within 24 hours of creation',
            'overdue_tasks'         => 'Number of tasks past due date',
            'deals_by_stage'        => 'Count and value of deals in each pipeline stage',
        ],
        'dashboard_widgets' => [
            'Pipeline Value by Stage (funnel chart)',
            'Win Rate vs Last Month',
            'My Tasks Due Today',
            'Top Deals (sortable)',
            'Deals Won This Month',
            'Activity Feed (timeline)',
        ],
    ],

    // ── HRM KPIs ─────────────────────────────────────────────────────────────
    'hrm' => [
        'primary_kpis' => [
            'attendance_rate'     => 'Present Days / Working Days (%) — target > 95%',
            'leave_utilization'   => 'Leave Days Used / Total Leave Entitlement (%)',
            'payroll_cost'        => 'Total payroll expense as % of revenue',
            'overtime_hours'      => 'Total OT hours per department per month',
            'turnover_rate'       => 'Employees Left / Avg Headcount × 100 (%/year) — high = culture issue',
            'time_to_hire'        => 'Days from job posting to offer accepted',
        ],
        'operational_kpis' => [
            'late_arrivals_count' => 'Employees arriving late more than X times/month',
            'pending_leaves'      => 'Leave applications awaiting approval',
            'payroll_accuracy'    => '% payrolls with no revision needed after publish',
        ],
        'dashboard_widgets' => [
            'Today\'s Attendance Summary',
            'Headcount by Department',
            'Pending Leave Requests',
            'Payroll Cost Trend (monthly)',
            'Birthday/Anniversary This Week',
            'Leave Calendar (team view)',
        ],
    ],

    // ── SaaS / Subscription KPIs ──────────────────────────────────────────────
    'saas' => [
        'primary_kpis' => [
            'mrr'              => 'Monthly Recurring Revenue — core SaaS health metric',
            'arr'              => 'Annual Recurring Revenue = MRR × 12',
            'churn_rate'       => '% customers who cancel per month — target < 2% for B2B',
            'net_mrr_growth'   => 'New MRR + Expansion MRR - Churned MRR',
            'ltv'              => 'Customer LTV = ARPU / Churn Rate',
            'ltv_cac_ratio'    => 'LTV / CAC > 3× = healthy, < 1× = unsustainable',
            'trial_conversion' => '% free trials converting to paid — target > 20%',
            'nrr'              => 'Net Revenue Retention — MRR from existing customers incl expansion',
        ],
        'operational_kpis' => [
            'arpu'             => 'Average Revenue Per User = MRR / Active Users',
            'dau_mau'          => 'Daily Active / Monthly Active ratio — product stickiness',
            'feature_adoption' => '% users using core features — low = onboarding problem',
        ],
        'dashboard_widgets' => [
            'MRR Card with Growth %',
            'Active Subscriptions Count',
            'Churn This Month',
            'Trial → Paid Funnel',
            'Revenue Cohort Chart',
            'Recent Churned Customers',
        ],
        'alerts' => [
            'Payment failed → retry logic + email within 1 hour',
            'Customer churn → trigger exit survey',
            'Trial expires in 3 days → send upgrade nudge',
        ],
    ],

    // ── Hospital KPIs ──────────────────────────────────────────────────────────
    'hospital' => [
        'primary_kpis' => [
            'patient_satisfaction' => 'Survey score out of 5 — target > 4.2',
            'bed_occupancy_rate'   => 'Occupied Beds / Total Beds (%) — target 75-85%',
            'avg_length_of_stay'   => 'Days from admission to discharge',
            'revenue_per_patient'  => 'Total billing / patients seen',
            'appointment_no_show'  => '% booked appointments not attended',
            'doctor_utilization'   => '% of scheduled slots booked',
        ],
        'dashboard_widgets' => [
            'Patients Today (OPD/IPD)',
            'Revenue vs Target',
            'Appointment Completion Rate',
            'Bed Status (room map)',
            'Outstanding Invoices',
            'Doctor Schedule Summary',
        ],
    ],

    // ── Restaurant KPIs ────────────────────────────────────────────────────────
    'restaurant' => [
        'primary_kpis' => [
            'revenue_per_cover'    => 'Revenue / Guests Served',
            'table_turnover_rate'  => 'Guests Served / Table Capacity per shift',
            'food_cost_percent'    => 'Food Cost / Revenue (%) — target 25-35%',
            'average_ticket'       => 'Revenue / Number of Checks',
            'most_sold_items'      => 'Top 10 items by quantity — informs menu decisions',
        ],
        'dashboard_widgets' => [
            'Revenue Today vs Yesterday',
            'Orders by Status (live)',
            'Top Selling Items',
            'Table Occupancy Map',
            'Kitchen Queue Length',
        ],
    ],

    // ── School KPIs ────────────────────────────────────────────────────────────
    'school' => [
        'primary_kpis' => [
            'student_attendance_rate'  => 'Present / Enrolled (%) — national benchmark > 90%',
            'fee_collection_rate'      => 'Fees Collected / Fees Due (%) per month',
            'pass_rate'                => '% students passing with required grade',
            'outstanding_fees'         => 'Total unpaid fees — top debtors list',
            'teacher_attendance_rate'  => 'Teacher present rate',
        ],
        'dashboard_widgets' => [
            'Today\'s Attendance %',
            'Fee Collection This Month',
            'Upcoming Exams Calendar',
            'Class-wise Performance',
            'Outstanding Fee Alerts',
        ],
    ],

    // ── Inventory KPIs ─────────────────────────────────────────────────────────
    'inventory' => [
        'primary_kpis' => [
            'stock_accuracy'       => 'Physical count / System count (%) — target > 99%',
            'stockout_rate'        => '% of orders delayed due to stock shortage',
            'inventory_turnover'   => 'COGS / Avg Inventory (times/year) — higher = better',
            'days_inventory'       => '365 / Inventory Turnover — days of stock on hand',
            'carrying_cost'        => 'Cost of holding inventory (storage + capital)',
        ],
        'dashboard_widgets' => [
            'Current Stock Value',
            'Low Stock Alerts',
            'Stock Movement (in/out chart)',
            'Slow Moving Items (> 90 days)',
            'Top Suppliers by Volume',
        ],
    ],

    // ── Dashboard Design Rules ─────────────────────────────────────────────────
    'dashboard_design_rules' => [
        'above_fold'     => 'Most critical KPIs must be visible without scrolling (4-6 cards max)',
        'trend_arrows'   => 'Always show up/down trend vs previous period — not just current value',
        'color_coding'   => 'Green (good), Yellow (warning), Red (critical) — consistent across app',
        'time_filter'    => 'Allow Today / This Week / This Month / Custom Range on every dashboard',
        'export_option'  => 'Every report table should have CSV/Excel export',
        'real_time'      => 'Sales dashboards should auto-refresh every 60 seconds (not manual)',
        'mobile_kpis'    => 'Mobile dashboard: 2-3 KPIs only, with drill-down on tap',
    ],

];
