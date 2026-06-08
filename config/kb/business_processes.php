<?php
/**
 * Business Process Knowledge Base
 * Standard business workflows that AI should map to correctly
 * Level 3: Business Knowledge
 */
return [

    // ── E-Commerce Order Lifecycle ────────────────────────────────────────────
    'ecommerce_order' => [
        'flow' => [
            'cart'         => 'Add to cart → apply coupon → calculate shipping',
            'checkout'     => 'Address → payment method → review → place order',
            'payment'      => 'Initiate payment → webhook confirm → mark paid',
            'fulfillment'  => 'Notify warehouse → pack → create shipment → dispatch',
            'delivery'     => 'Tracking update → delivered → auto-complete after X days',
            'post_order'   => 'Review request → return/refund window opens',
        ],
        'states' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'],
        'events' => ['OrderPlaced', 'OrderConfirmed', 'OrderShipped', 'OrderDelivered', 'OrderCancelled', 'RefundProcessed'],
        'notifications' => ['customer: confirm email', 'customer: shipped + tracking', 'admin: new order alert'],
    ],

    // ── CRM Sales Pipeline ────────────────────────────────────────────────────
    'crm_sales_pipeline' => [
        'flow' => [
            'lead'         => 'Capture → qualify (BANT) → assign to sales rep',
            'contact'      => 'Discovery call → needs assessment → demo',
            'proposal'     => 'Quote generation → send proposal → follow up',
            'negotiation'  => 'Counter proposal → terms agreement',
            'won'          => 'Contract signed → handover to onboarding',
            'lost'         => 'Record reason → nurture sequence',
        ],
        'stages'       => ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won', 'lost'],
        'automation'   => ['Auto-assign lead by territory', 'Follow-up reminder if no activity 3 days', 'Escalate if deal stalls 2 weeks'],
        'reports'      => ['Pipeline value by stage', 'Win rate by rep', 'Average deal cycle time', 'Lead source ROI'],
    ],

    // ── HRM Workflow ──────────────────────────────────────────────────────────
    'hrm_workflows' => [
        'recruitment' => [
            'flow' => 'Job posting → applications → screening → interview → offer → onboarding',
            'stages' => ['applied', 'screened', 'interviewed', 'offer_sent', 'hired', 'rejected'],
        ],
        'attendance' => [
            'flow' => 'Check-in (biometric/manual/GPS) → work hours calculation → overtime detection → approval',
            'monthly' => 'Attendance report → leave deduction → payroll input',
        ],
        'leave_management' => [
            'flow' => 'Apply leave → manager approval → HR confirmation → deduct balance → notify',
            'types' => ['annual', 'sick', 'casual', 'maternity', 'paternity', 'unpaid'],
        ],
        'payroll' => [
            'flow' => [
                1 => 'Pull attendance data for month',
                2 => 'Calculate basic salary prorated by attendance',
                3 => 'Add allowances (house, transport, medical)',
                4 => 'Calculate overtime',
                5 => 'Deduct: absent days, advance, tax, insurance',
                6 => 'Generate payslips',
                7 => 'Bank transfer list / BKash disbursement',
                8 => 'Payroll locked (immutable record)',
            ],
        ],
    ],

    // ── Purchase & Procurement ────────────────────────────────────────────────
    'procurement' => [
        'flow' => [
            'requisition' => 'Department request → budget check → manager approval',
            'rfq'         => 'Request for quotation → vendor submission → comparison',
            'purchase_order'=> 'PO creation → vendor confirmation → expected delivery date',
            'receiving'   => 'Goods received → quality check → quantity match → GRN created',
            'invoice'     => 'Vendor invoice → match to PO → approval workflow → payment',
            'payment'     => 'Payment terms check → bank transfer → mark paid',
        ],
        'approval_levels' => [
            'amount < 10K'   => 'Department head approval',
            'amount 10K–100K'=> 'Director approval',
            'amount > 100K'  => 'CEO/Board approval',
        ],
    ],

    // ── Accounting Workflow ────────────────────────────────────────────────────
    'accounting' => [
        'double_entry'     => 'Every transaction: Debit one account + Credit another',
        'chart_of_accounts'=> ['Assets', 'Liabilities', 'Equity', 'Revenue', 'Expenses'],
        'journal_flow'     => 'Transaction → Journal Entry → Post to Ledger → Trial Balance → Reports',
        'period_end'       => 'Monthly close → reconciliation → P&L → Balance Sheet → Cash Flow',
        'reports'          => ['Profit & Loss', 'Balance Sheet', 'Cash Flow Statement', 'Aged Receivables', 'Aged Payables', 'Trial Balance'],
        'golden_rules'     => [
            'never_delete_posted' => 'Posted accounting entries are immutable — void and repost',
            'reconcile_regularly' => 'Match bank statement to system records monthly',
        ],
    ],

    // ── Hospital / Healthcare ──────────────────────────────────────────────────
    'hospital' => [
        'patient_flow' => [
            'registration'  => 'New patient → create medical record number (MRN) → basic demographics',
            'appointment'   => 'Book → confirm → reminder → check-in',
            'consultation'  => 'Vitals → doctor consultation → diagnosis → prescription → test orders',
            'lab/imaging'   => 'Sample collection → lab processing → result upload → doctor review',
            'billing'       => 'Itemize services → insurance claim → patient payment → receipt',
            'discharge'     => 'Discharge summary → medications → follow-up appointment',
        ],
        'privacy' => 'Patient data is HIPAA/PDPO sensitive — encrypted storage, audit log every access',
    ],

    // ── Inventory Management ──────────────────────────────────────────────────
    'inventory' => [
        'flow' => [
            'receiving'  => 'Purchase order → GRN → stock increase → reorder check',
            'sales'      => 'Order placed → stock reserved → picking list → dispatch → stock decrease',
            'transfer'   => 'Branch transfer request → approve → dispatch → receive → update both warehouses',
            'adjustment' => 'Physical count → discrepancy report → manager approval → stock adjustment log',
        ],
        'valuation_methods' => ['FIFO', 'LIFO', 'Weighted Average Cost'],
        'reorder_logic'    => 'Alert when stock < reorder_level. Auto-generate PO if auto_reorder enabled.',
    ],

    // ── SaaS Customer Success ─────────────────────────────────────────────────
    'saas_customer_success' => [
        'onboarding_flow' => 'Register → verify → setup → first value → habit formation',
        'health_scoring'  => 'Login frequency + feature usage + support tickets → health score',
        'churn_prevention'=> 'Detect unhealthy accounts → CS team intervention → save offer',
        'expansion'       => 'Usage near limit → upgrade nudge → upsell additional seats/features',
        'renewal'         => '60 days before renewal → QBR → renewal confirmed → invoice sent',
    ],

];
