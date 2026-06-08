<?php
/**
 * Business Rules Knowledge Base
 * Domain-specific invariants that must NEVER be violated.
 * These should not require AI reasoning every time — stored as hard rules.
 * Level 3: Business Knowledge
 */
return [

    // ── Accounting Rules ──────────────────────────────────────────────────────
    'accounting' => [
        'double_entry_required'   => 'Every transaction MUST have debit total = credit total',
        'immutable_posted_entries'=> 'Posted journal entries NEVER updated — void + repost',
        'no_negative_balance'     => 'Asset accounts cannot go negative without reason code',
        'period_lock'             => 'Closed accounting periods are read-only — no backdating',
        'currency_consistency'    => 'All line items in a transaction must share the same currency',
        'tax_precision'           => 'Tax amounts stored as DECIMAL(15,4) — never rounded during calculation',
        'audit_every_change'      => 'Every accounting record change must be audit-logged with user, time, reason',
        'reconciliation_required' => 'Bank reconciliation must match before period close',
    ],

    // ── eCommerce Rules ────────────────────────────────────────────────────────
    'ecommerce' => [
        'order_status_flow'          => 'pending → confirmed → processing → shipped → delivered → completed (or cancelled)',
        'cannot_cancel_delivered'    => 'Order in "delivered" or "completed" status cannot be directly cancelled — requires return request',
        'payment_before_fulfillment' => 'Orders must have confirmed payment before dispatching (except COD)',
        'stock_lock_on_order'        => 'Reserve stock when order placed, decrement when payment confirmed',
        'stock_restore_on_cancel'    => 'Cancellation must restore stock atomically',
        'price_at_order_time'        => 'Store item price AT time of order — never recalculate from current price',
        'cod_separate_flow'          => 'COD orders: fulfill first, mark paid on delivery confirmation',
        'coupon_once_per_user'       => 'Single-use coupons must check usage before applying',
        'min_order_value'            => 'Validate minimum order value before payment initiation',
        'refund_window'              => 'Define refund window (e.g., 7 days) — enforce in business logic',
    ],

    // ── HRM / Payroll Rules ────────────────────────────────────────────────────
    'hrm' => [
        'payroll_immutable'          => 'Published payroll cannot be modified — void and republish',
        'attendance_before_payroll'  => 'Payroll processing requires attendance data for the period',
        'leave_balance_check'        => 'Leave application must check available balance before approval',
        'overtime_calculation'       => 'Overtime hours = hours worked - standard hours, minimum 0',
        'advance_deduction'          => 'Salary advances deducted over agreed installment periods',
        'probation_no_leave'         => 'Employees on probation typically not eligible for annual leave',
        'notice_period_calculation'  => 'Last working day = resignation date + notice period days',
        'tax_slab_current_year'      => 'Always use current fiscal year tax slabs — store by year',
        'gratuity_eligibility'       => 'Gratuity typically requires minimum service period (e.g., 1 year)',
    ],

    // ── Inventory Rules ────────────────────────────────────────────────────────
    'inventory' => [
        'no_negative_stock'          => 'Stock cannot go negative — reject or backorder if insufficient',
        'fifo_default'               => 'FIFO stock valuation by default — track batches with purchase dates',
        'grn_before_stock_increase'  => 'Stock only increases after Goods Received Note (GRN) creation',
        'stock_adjustment_requires_reason' => 'Manual stock adjustments must have reason code and approver',
        'expiry_tracking'            => 'Pharmacy/food inventory must track expiry dates and alert on near-expiry',
        'reorder_alert'              => 'Trigger alert/auto-PO when stock < reorder_level',
        'bin_location'               => 'In warehouses: items must be assigned to specific bin/shelf location',
    ],

    // ── Hospital / Healthcare Rules ────────────────────────────────────────────
    'hospital' => [
        'patient_data_privacy'       => 'Patient records only accessible to treating doctors and authorized staff',
        'prescription_by_doctor_only'=> 'Only licensed doctors can issue prescriptions',
        'lab_result_before_discharge'=> 'Pending lab tests should be flagged before discharge',
        'blood_type_verify'          => 'Blood transfusion requires double-verification of blood type',
        'drug_interaction_check'     => 'Pharmacy should check for drug interactions before dispensing',
        'allergy_alert'              => 'Always check patient allergies before prescribing',
        'billing_after_service'      => 'Never discharge patient with unpaid bill (unless insurance/credit)',
    ],

    // ── SaaS / Subscription Rules ──────────────────────────────────────────────
    'saas' => [
        'feature_limit_enforce'      => 'Enforce plan limits server-side — never trust client',
        'payment_failure_grace'      => 'Allow 3–7 day grace period before suspending on payment failure',
        'trial_auto_convert'         => 'Trial does NOT auto-convert — require payment method collection',
        'downgrade_impact'           => 'Downgrading plan must archive/lock excess data beyond new limit',
        'tenant_data_isolation'      => 'One tenant can NEVER read another tenant\'s data — enforce at query level',
        'subscription_proration'     => 'Mid-cycle upgrades should be prorated — charge difference only',
        'cancel_access_until_period' => 'Cancellation grants access until current period end — no immediate cutoff',
    ],

    // ── Financial / Payment Rules ─────────────────────────────────────────────
    'payments' => [
        'idempotency'                => 'Payment initiation must be idempotent — same request should not double-charge',
        'webhook_verification'       => 'Verify webhook signature before processing payment confirmation',
        'no_redirect_trust'          => 'Never trust redirect params for payment confirmation — use webhook',
        'refund_audit'               => 'Every refund must have: reason, approver, reference to original payment',
        'partial_refund_allowed'     => 'Allow partial refund for partial returns',
        'currency_no_conversion_in_txn' => 'One transaction = one currency — convert before initiating',
    ],

    // ── Multi-Tenant Rules ─────────────────────────────────────────────────────
    'multitenancy' => [
        'tenant_id_everywhere'       => 'Every user-created resource must have tenant_id — no exceptions',
        'cross_tenant_query_blocked' => 'All queries scoped by global tenant scope — never bypass',
        'tenant_deletion_cascade'    => 'Deleting a tenant should cascade or archive all tenant data',
        'super_admin_separate'       => 'Super-admin login separate from tenant admin — different guard',
        'tenant_limits_enforced'     => 'Plan limits enforced per-tenant — not globally',
    ],

    // ── General System Rules ───────────────────────────────────────────────────
    'general' => [
        'delete_confirmation'        => 'Permanent deletes require explicit user confirmation — prefer soft delete',
        'bulk_operation_transaction' => 'Bulk operations (import, bulk update) MUST be wrapped in transaction',
        'export_authorization'       => 'Data export routes require explicit permission check',
        'api_idempotency'            => 'POST endpoints that create resources should accept idempotency key',
        'rate_limit_sensitive_ops'   => 'Login, OTP, password reset — always rate limit',
        'status_machine'             => 'Status transitions must follow defined state machine — no arbitrary jumps',
    ],

];
