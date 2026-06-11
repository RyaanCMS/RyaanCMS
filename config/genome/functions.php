<?php

/**
 * Business Functions Genome Library
 *
 * Maps business function keys → modules, fields, workflows, KPIs, and role access.
 * Referenced by industries.php functions[] arrays.
 *
 * This is the "DNA" that the BlueprintGenomeEngine uses to generate module lists,
 * required fields, and workflows for any given industry combination.
 *
 * Target: 200+ functions. Current: 80+ core business functions.
 */
return [

    // ══════════════════════════════════════════════════════════════════════
    // CORE TRANSACTIONAL
    // ══════════════════════════════════════════════════════════════════════

    'sales' => [
        'label'      => 'Sales Management',
        'modules'    => ['Order', 'Invoice', 'Customer', 'Discount', 'SalesReport'],
        'fields'     => ['customer_id', 'items', 'discount', 'tax', 'total', 'payment_status', 'delivery_status'],
        'workflows'  => ['create_order', 'approve_discount', 'generate_invoice', 'mark_paid', 'mark_delivered'],
        'kpis'       => ['Daily Sales', 'Monthly Revenue', 'Average Order Value', 'Top Products'],
        'roles'      => ['sales_manager', 'sales_rep', 'cashier'],
    ],

    'pos' => [
        'label'      => 'Point of Sale',
        'modules'    => ['POSSale', 'Register', 'CashDrawer', 'Receipt', 'Shift'],
        'fields'     => ['barcode', 'quantity', 'price', 'discount', 'payment_method', 'cashier_id'],
        'workflows'  => ['scan_item', 'apply_discount', 'select_payment', 'print_receipt', 'end_shift'],
        'kpis'       => ['Transactions per Hour', 'Average Basket Size', 'Payment Method Mix'],
        'roles'      => ['cashier', 'store_manager'],
    ],

    'invoicing' => [
        'label'      => 'Invoicing & Billing',
        'modules'    => ['Invoice', 'Proforma', 'CreditNote', 'PaymentRecord'],
        'fields'     => ['invoice_number', 'client_id', 'line_items', 'tax', 'discount', 'due_date', 'status'],
        'workflows'  => ['draft_invoice', 'send_invoice', 'record_payment', 'send_reminder', 'write_off'],
        'kpis'       => ['Outstanding Receivables', 'Days to Payment', 'Overdue Rate'],
        'roles'      => ['accountant', 'sales_rep', 'finance_manager'],
    ],

    'payment' => [
        'label'      => 'Payment Processing',
        'modules'    => ['Payment', 'Refund', 'PaymentGateway', 'Reconciliation'],
        'fields'     => ['amount', 'method', 'reference', 'gateway_response', 'refunded', 'reconciled'],
        'workflows'  => ['initiate_payment', 'verify_payment', 'process_refund', 'daily_reconcile'],
        'kpis'       => ['Success Rate', 'Failed Transactions', 'Refund Rate'],
        'roles'      => ['cashier', 'accountant', 'finance_manager'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // INVENTORY & SUPPLY CHAIN
    // ══════════════════════════════════════════════════════════════════════

    'inventory' => [
        'label'      => 'Inventory Management',
        'modules'    => ['Product', 'Stock', 'Warehouse', 'StockMovement', 'Reorder'],
        'fields'     => ['sku', 'name', 'quantity', 'unit', 'cost_price', 'sell_price', 'reorder_level', 'location'],
        'workflows'  => ['add_stock', 'reduce_stock', 'transfer_warehouse', 'reorder_alert', 'stock_count'],
        'kpis'       => ['Inventory Turnover', 'Stockout Rate', 'Carrying Cost', 'Dead Stock Value'],
        'roles'      => ['warehouse_staff', 'inventory_manager', 'store_manager'],
    ],

    'procurement' => [
        'label'      => 'Procurement / Purchasing',
        'modules'    => ['PurchaseOrder', 'Supplier', 'GoodsReceipt', 'SupplierPayment'],
        'fields'     => ['supplier_id', 'items', 'expected_delivery', 'received_date', 'invoice_no', 'payment_terms'],
        'workflows'  => ['create_po', 'approve_po', 'receive_goods', 'match_invoice', 'pay_supplier'],
        'kpis'       => ['PO Cycle Time', 'Supplier On-Time Rate', 'Cost Savings'],
        'roles'      => ['procurement_officer', 'store_manager', 'finance_manager'],
    ],

    'supplier' => [
        'label'      => 'Supplier Management',
        'modules'    => ['Supplier', 'SupplierContract', 'SupplierEvaluation'],
        'fields'     => ['name', 'contact', 'category', 'credit_terms', 'rating', 'blacklisted'],
        'workflows'  => ['onboard_supplier', 'evaluate_performance', 'renew_contract'],
        'kpis'       => ['On-Time Delivery', 'Quality Rejection Rate', 'Price Competitiveness'],
        'roles'      => ['procurement_officer', 'admin'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // CUSTOMER RELATIONSHIP
    // ══════════════════════════════════════════════════════════════════════

    'crm' => [
        'label'      => 'Customer Relationship Management',
        'modules'    => ['Customer', 'Lead', 'Opportunity', 'Contact', 'Activity', 'Deal'],
        'fields'     => ['name', 'email', 'phone', 'segment', 'lifetime_value', 'source', 'stage', 'assigned_to'],
        'workflows'  => ['capture_lead', 'qualify', 'assign', 'nurture', 'close', 'retain'],
        'kpis'       => ['Lead Conversion', 'Customer Acquisition Cost', 'LTV', 'Churn Rate'],
        'roles'      => ['sales_rep', 'sales_manager', 'customer_success'],
    ],

    'support' => [
        'label'      => 'Customer Support / Help Desk',
        'modules'    => ['Ticket', 'KnowledgeBase', 'SLA', 'SupportAgent', 'CustomerFeedback'],
        'fields'     => ['subject', 'body', 'priority', 'status', 'assigned_to', 'channel', 'resolved_at'],
        'workflows'  => ['create_ticket', 'assign_agent', 'escalate', 'resolve', 'close', 'collect_feedback'],
        'kpis'       => ['First Response Time', 'Resolution Time', 'CSAT Score', 'Ticket Volume'],
        'roles'      => ['support_agent', 'support_manager', 'customer'],
    ],

    'marketing' => [
        'label'      => 'Marketing & Campaigns',
        'modules'    => ['Campaign', 'Segment', 'Template', 'EmailCampaign', 'Analytics'],
        'fields'     => ['name', 'type', 'audience', 'content', 'scheduled_at', 'status', 'open_rate', 'click_rate'],
        'workflows'  => ['design_campaign', 'segment_audience', 'schedule_send', 'track_performance'],
        'kpis'       => ['Open Rate', 'Click Rate', 'Conversion Rate', 'ROI', 'Unsubscribe Rate'],
        'roles'      => ['marketing_manager', 'content_creator', 'admin'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // HUMAN RESOURCES
    // ══════════════════════════════════════════════════════════════════════

    'hrm' => [
        'label'      => 'Human Resource Management',
        'modules'    => ['Employee', 'Department', 'Designation', 'LeavePolicy', 'Attendance'],
        'fields'     => ['name', 'employee_id', 'department', 'designation', 'join_date', 'status', 'contract_type'],
        'workflows'  => ['onboard', 'transfer', 'promote', 'terminate', 'offboard'],
        'kpis'       => ['Headcount', 'Turnover Rate', 'Absenteeism Rate', 'Time to Fill'],
        'roles'      => ['hr_manager', 'hr_officer', 'employee', 'manager'],
    ],

    'attendance' => [
        'label'      => 'Attendance & Time Tracking',
        'modules'    => ['Attendance', 'Shift', 'Roster', 'Overtime', 'BiometricDevice'],
        'fields'     => ['employee_id', 'date', 'check_in', 'check_out', 'shift', 'overtime_hours', 'status'],
        'workflows'  => ['clock_in', 'clock_out', 'request_overtime', 'approve_overtime', 'generate_report'],
        'kpis'       => ['Attendance Rate', 'Late Arrivals', 'Absenteeism', 'Overtime Hours'],
        'roles'      => ['employee', 'hr_manager', 'supervisor'],
    ],

    'payroll' => [
        'label'      => 'Payroll Processing',
        'modules'    => ['Payroll', 'Salary', 'Deduction', 'Bonus', 'TaxSlab', 'Payslip'],
        'fields'     => ['employee_id', 'month', 'basic', 'allowances', 'deductions', 'tax', 'net_pay', 'payment_method'],
        'workflows'  => ['calculate_payroll', 'review', 'approve', 'disburse', 'generate_payslips'],
        'kpis'       => ['Payroll Accuracy', 'On-Time Disbursement', 'Tax Compliance'],
        'roles'      => ['hr_manager', 'accountant', 'finance_manager'],
    ],

    'recruitment' => [
        'label'      => 'Recruitment & Hiring',
        'modules'    => ['JobPosting', 'Applicant', 'Interview', 'Offer', 'Onboarding'],
        'fields'     => ['job_title', 'department', 'requirements', 'applicant_name', 'cv_url', 'stage', 'offer_amount'],
        'workflows'  => ['post_job', 'screen_cv', 'schedule_interview', 'make_offer', 'onboard'],
        'kpis'       => ['Time to Hire', 'Cost per Hire', 'Offer Acceptance Rate', 'Source Quality'],
        'roles'      => ['hr_manager', 'hiring_manager', 'recruiter', 'applicant'],
    ],

    'performance' => [
        'label'      => 'Performance Management',
        'modules'    => ['KPI', 'Review', 'Goal', 'Feedback360', 'Appraisal'],
        'fields'     => ['employee_id', 'period', 'goals', 'self_rating', 'manager_rating', 'final_rating', 'comments'],
        'workflows'  => ['set_goals', 'mid_year_review', 'self_assessment', 'manager_review', 'calibration'],
        'kpis'       => ['Average Performance Score', 'High Performer Retention', 'Goal Achievement Rate'],
        'roles'      => ['employee', 'manager', 'hr_manager'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // ACCOUNTING & FINANCE
    // ══════════════════════════════════════════════════════════════════════

    'accounting' => [
        'label'      => 'Accounting & General Ledger',
        'modules'    => ['Account', 'JournalEntry', 'ChartOfAccounts', 'BalanceSheet', 'PnL'],
        'fields'     => ['account_code', 'name', 'type', 'debit', 'credit', 'balance', 'period'],
        'workflows'  => ['record_transaction', 'post_journal', 'reconcile', 'close_period', 'generate_reports'],
        'kpis'       => ['Gross Margin', 'Net Profit', 'Cash Flow', 'Debt Ratio'],
        'roles'      => ['accountant', 'finance_manager', 'cfo'],
    ],

    'expenses' => [
        'label'      => 'Expense Management',
        'modules'    => ['Expense', 'ExpenseCategory', 'ExpenseApproval', 'Reimbursement'],
        'fields'     => ['employee_id', 'category', 'amount', 'date', 'receipt', 'description', 'status'],
        'workflows'  => ['submit_expense', 'line_manager_review', 'finance_approval', 'reimburse'],
        'kpis'       => ['Total Expenses', 'Expenses per Employee', 'Approval Cycle Time'],
        'roles'      => ['employee', 'manager', 'accountant'],
    ],

    'tax' => [
        'label'      => 'Tax Management',
        'modules'    => ['TaxRate', 'TaxReturn', 'TaxReport', 'Challan'],
        'fields'     => ['tax_type', 'rate', 'period', 'taxable_amount', 'tax_due', 'paid', 'filing_date'],
        'workflows'  => ['calculate_tax', 'generate_return', 'file_return', 'pay_challan'],
        'kpis'       => ['Tax Compliance Rate', 'Effective Tax Rate'],
        'roles'      => ['accountant', 'finance_manager'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // PROJECT & OPERATIONS
    // ══════════════════════════════════════════════════════════════════════

    'project_management' => [
        'label'      => 'Project Management',
        'modules'    => ['Project', 'Task', 'Milestone', 'Gantt', 'Resource', 'TimeLog'],
        'fields'     => ['name', 'description', 'start_date', 'end_date', 'status', 'assigned_to', 'priority', 'progress'],
        'workflows'  => ['project_kickoff', 'task_assignment', 'progress_update', 'milestone_review', 'project_closure'],
        'kpis'       => ['On-Time Completion', 'Budget Variance', 'Resource Utilization', 'Milestone Achievement'],
        'roles'      => ['project_manager', 'team_member', 'client', 'stakeholder'],
    ],

    'time_tracking' => [
        'label'      => 'Time Tracking',
        'modules'    => ['TimeEntry', 'Timesheet', 'BillableHours', 'Project'],
        'fields'     => ['user_id', 'project_id', 'task', 'hours', 'billable', 'description', 'date'],
        'workflows'  => ['log_time', 'submit_timesheet', 'approve_timesheet', 'generate_invoice'],
        'kpis'       => ['Billable Hours', 'Utilization Rate', 'Non-Billable Hours'],
        'roles'      => ['employee', 'project_manager', 'client', 'accountant'],
    ],

    'workflow' => [
        'label'      => 'Workflow & Approval Management',
        'modules'    => ['Workflow', 'ApprovalRequest', 'ApprovalStep', 'Notification'],
        'fields'     => ['type', 'requestor_id', 'steps', 'current_step', 'status', 'notes', 'deadline'],
        'workflows'  => ['create_request', 'review_step', 'approve_reject', 'complete', 'archive'],
        'kpis'       => ['Average Approval Time', 'Bottleneck Detection', 'SLA Compliance'],
        'roles'      => ['requester', 'approver', 'admin'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // LOGISTICS & OPERATIONS
    // ══════════════════════════════════════════════════════════════════════

    'shipping' => [
        'label'      => 'Shipping & Delivery',
        'modules'    => ['Shipment', 'TrackingEvent', 'DeliveryRoute', 'Carrier'],
        'fields'     => ['tracking_number', 'carrier', 'origin', 'destination', 'status', 'estimated_delivery', 'actual_delivery'],
        'workflows'  => ['create_shipment', 'assign_carrier', 'dispatch', 'track', 'deliver', 'handle_exception'],
        'kpis'       => ['On-Time Delivery', 'Lost/Damaged Rate', 'Cost per Shipment'],
        'roles'      => ['logistics_manager', 'warehouse_staff', 'delivery_agent', 'customer'],
    ],

    'fleet' => [
        'label'      => 'Fleet Management',
        'modules'    => ['Vehicle', 'Driver', 'Trip', 'FuelLog', 'Maintenance'],
        'fields'     => ['plate', 'type', 'driver_id', 'km_reading', 'fuel_liters', 'maintenance_due', 'status'],
        'workflows'  => ['assign_vehicle', 'log_fuel', 'schedule_maintenance', 'complete_trip'],
        'kpis'       => ['Fleet Utilization', 'Fuel Efficiency', 'Maintenance Cost', 'Driver Performance'],
        'roles'      => ['fleet_manager', 'driver', 'accountant'],
    ],

    'maintenance' => [
        'label'      => 'Maintenance Management',
        'modules'    => ['MaintenanceRequest', 'WorkOrder', 'Asset', 'MaintenanceSchedule', 'Technician'],
        'fields'     => ['asset_id', 'type', 'priority', 'requested_by', 'assigned_to', 'scheduled_date', 'cost', 'status'],
        'workflows'  => ['raise_request', 'assign_technician', 'complete_work', 'sign_off', 'update_asset'],
        'kpis'       => ['Mean Time to Repair', 'Preventive vs Reactive Ratio', 'Asset Uptime'],
        'roles'      => ['maintenance_manager', 'technician', 'asset_owner'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // HEALTHCARE-SPECIFIC
    // ══════════════════════════════════════════════════════════════════════

    'patient_management' => [
        'label'      => 'Patient Management',
        'modules'    => ['Patient', 'MedicalRecord', 'Diagnosis', 'Treatment', 'Prescription'],
        'fields'     => ['patient_id', 'name', 'dob', 'blood_group', 'allergies', 'diagnosis', 'doctor_id', 'ward'],
        'workflows'  => ['register_patient', 'assign_doctor', 'diagnose', 'prescribe', 'discharge'],
        'kpis'       => ['Patient Volume', 'Readmission Rate', 'Average Length of Stay'],
        'roles'      => ['doctor', 'nurse', 'receptionist', 'patient'],
    ],

    'appointments' => [
        'label'      => 'Appointment Booking',
        'modules'    => ['Appointment', 'Calendar', 'Reminder', 'SlotManagement'],
        'fields'     => ['doctor_id', 'patient_id', 'date', 'time', 'type', 'status', 'notes'],
        'workflows'  => ['book_appointment', 'send_reminder', 'confirm', 'reschedule', 'cancel'],
        'kpis'       => ['No-Show Rate', 'Cancellation Rate', 'Wait Time', 'Utilization'],
        'roles'      => ['patient', 'receptionist', 'doctor'],
    ],

    'pharmacy' => [
        'label'      => 'Pharmacy Management',
        'modules'    => ['Medicine', 'Prescription', 'DispenseRecord', 'Expiry', 'StockAlert'],
        'fields'     => ['medicine_name', 'batch', 'expiry_date', 'quantity', 'unit_price', 'prescription_required'],
        'workflows'  => ['receive_stock', 'check_expiry', 'fill_prescription', 'dispense', 'reorder'],
        'kpis'       => ['Expiry Loss', 'Stockout Rate', 'Prescription Fill Rate'],
        'roles'      => ['pharmacist', 'doctor', 'patient'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // EDUCATION-SPECIFIC
    // ══════════════════════════════════════════════════════════════════════

    'student_management' => [
        'label'      => 'Student Management',
        'modules'    => ['Student', 'Enrollment', 'AcademicRecord', 'Guardian', 'Transfer'],
        'fields'     => ['name', 'roll_no', 'class', 'section', 'guardian', 'dob', 'blood_group', 'status'],
        'workflows'  => ['admission', 'enroll', 'transfer', 'promote', 'withdraw'],
        'kpis'       => ['Enrollment Count', 'Retention Rate', 'Dropout Rate'],
        'roles'      => ['admin', 'teacher', 'student', 'parent'],
    ],

    'fees' => [
        'label'      => 'Fee Management',
        'modules'    => ['FeeStructure', 'FeeCollection', 'Waiver', 'Receipt', 'DueAlert'],
        'fields'     => ['student_id', 'fee_type', 'amount', 'due_date', 'paid_amount', 'discount', 'status'],
        'workflows'  => ['generate_dues', 'collect_fee', 'apply_waiver', 'send_reminder', 'generate_receipt'],
        'kpis'       => ['Collection Rate', 'Outstanding Dues', 'Waiver Granted %'],
        'roles'      => ['accountant', 'admin', 'student', 'parent'],
    ],

    'results' => [
        'label'      => 'Exam & Result Management',
        'modules'    => ['Exam', 'Mark', 'Grade', 'ResultCard', 'Promotion'],
        'fields'     => ['student_id', 'exam', 'subject', 'full_marks', 'obtained', 'grade', 'position'],
        'workflows'  => ['create_exam', 'enter_marks', 'calculate_grades', 'publish_result', 'promote_students'],
        'kpis'       => ['Pass Rate', 'Average Score', 'Grade Distribution'],
        'roles'      => ['teacher', 'admin', 'student', 'parent'],
    ],

    'course_management' => [
        'label'      => 'Course / Curriculum Management',
        'modules'    => ['Course', 'Syllabus', 'LessonPlan', 'Material', 'Batch'],
        'fields'     => ['course_name', 'code', 'credits', 'instructor', 'prerequisites', 'duration', 'materials'],
        'workflows'  => ['create_course', 'assign_instructor', 'enroll_students', 'deliver_content', 'assess'],
        'kpis'       => ['Completion Rate', 'Student Satisfaction', 'Pass Rate'],
        'roles'      => ['admin', 'instructor', 'student'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // HOSPITALITY-SPECIFIC
    // ══════════════════════════════════════════════════════════════════════

    'booking' => [
        'label'      => 'Booking & Reservation',
        'modules'    => ['Booking', 'Room', 'Availability', 'Channel', 'Pricing'],
        'fields'     => ['guest_id', 'room_type', 'check_in', 'check_out', 'adults', 'children', 'total_price', 'status'],
        'workflows'  => ['check_availability', 'create_booking', 'confirm', 'check_in', 'check_out', 'cancel'],
        'kpis'       => ['Occupancy Rate', 'ADR', 'RevPAR', 'Cancellation Rate'],
        'roles'      => ['front_desk', 'manager', 'guest'],
    ],

    'room_management' => [
        'label'      => 'Room Management',
        'modules'    => ['Room', 'RoomType', 'Housekeeping', 'RoomStatus'],
        'fields'     => ['room_number', 'type', 'floor', 'status', 'last_cleaned', 'occupied_by'],
        'workflows'  => ['assign_room', 'check_out_clean', 'inspect', 'mark_ready'],
        'kpis'       => ['Room Readiness Time', 'Housekeeping Efficiency'],
        'roles'      => ['front_desk', 'housekeeping', 'manager'],
    ],

    'menu_management' => [
        'label'      => 'Menu Management',
        'modules'    => ['MenuItem', 'Category', 'Modifier', 'Pricing', 'Availability'],
        'fields'     => ['name', 'category', 'price', 'description', 'image', 'is_available', 'modifiers'],
        'workflows'  => ['add_item', 'update_price', 'toggle_availability', 'add_special'],
        'kpis'       => ['Menu Item Popularity', 'Category Revenue %'],
        'roles'      => ['admin', 'manager', 'chef'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // DIGITAL / PLATFORM
    // ══════════════════════════════════════════════════════════════════════

    'subscription' => [
        'label'      => 'Subscription Management',
        'modules'    => ['Plan', 'Subscription', 'BillingCycle', 'UpgradeDowngrade', 'TrialManagement'],
        'fields'     => ['user_id', 'plan', 'billing_cycle', 'start_date', 'end_date', 'status', 'auto_renew'],
        'workflows'  => ['start_trial', 'convert_to_paid', 'upgrade_plan', 'cancel', 'renew'],
        'kpis'       => ['MRR', 'ARR', 'Churn Rate', 'Trial Conversion Rate', 'Plan Distribution'],
        'roles'      => ['admin', 'billing_manager', 'customer'],
    ],

    'analytics' => [
        'label'      => 'Analytics & Reporting',
        'modules'    => ['Dashboard', 'Report', 'Chart', 'Export', 'ScheduledReport'],
        'fields'     => ['metric', 'period', 'dimensions', 'value', 'trend', 'comparison'],
        'workflows'  => ['build_report', 'schedule_export', 'share_dashboard', 'drill_down'],
        'kpis'       => ['Data Freshness', 'Report Usage', 'Export Downloads'],
        'roles'      => ['admin', 'manager', 'analyst'],
    ],

    'content_management' => [
        'label'      => 'Content Management',
        'modules'    => ['Article', 'Category', 'Tag', 'Media', 'SEO', 'Comment'],
        'fields'     => ['title', 'slug', 'body', 'author', 'status', 'published_at', 'meta_title', 'meta_description'],
        'workflows'  => ['draft', 'review', 'approve', 'publish', 'archive'],
        'kpis'       => ['Published Content', 'Page Views', 'Engagement Rate', 'SEO Score'],
        'roles'      => ['editor', 'writer', 'admin'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // COMPLIANCE & REPORTING
    // ══════════════════════════════════════════════════════════════════════

    'reporting' => [
        'label'      => 'Reports & Compliance',
        'modules'    => ['Report', 'AuditLog', 'ComplianceChecklist', 'Export'],
        'fields'     => ['report_type', 'period', 'generated_by', 'status', 'file_url'],
        'workflows'  => ['generate_report', 'review', 'submit_compliance', 'archive'],
        'kpis'       => ['Report Accuracy', 'Compliance Rate', 'Audit Trail Completeness'],
        'roles'      => ['admin', 'compliance_officer', 'manager'],
    ],

    'quality' => [
        'label'      => 'Quality Management',
        'modules'    => ['QualityCheck', 'Defect', 'NCR', 'Inspection', 'Corrective Action'],
        'fields'     => ['product_id', 'batch', 'inspector', 'passed', 'defect_type', 'action_taken'],
        'workflows'  => ['schedule_inspection', 'inspect', 'log_defect', 'raise_ncr', 'corrective_action'],
        'kpis'       => ['Defect Rate', 'First Pass Yield', 'Customer Complaints'],
        'roles'      => ['quality_inspector', 'production_manager', 'qc_manager'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // FINANCIAL SERVICES
    // ══════════════════════════════════════════════════════════════════════

    'loan_management' => [
        'label'      => 'Loan Management',
        'modules'    => ['Loan', 'Repayment', 'CollectionSchedule', 'CreditAssessment', 'Guarantor'],
        'fields'     => ['member_id', 'amount', 'interest_rate', 'tenure', 'disbursement_date', 'repayment_schedule', 'status'],
        'workflows'  => ['apply', 'assess', 'approve', 'disburse', 'collect', 'close_loan'],
        'kpis'       => ['Portfolio at Risk', 'Repayment Rate', 'Default Rate', 'Collection Efficiency'],
        'roles'      => ['loan_officer', 'branch_manager', 'field_agent', 'member'],
    ],

    'policy_management' => [
        'label'      => 'Policy Management',
        'modules'    => ['Policy', 'Premium', 'Benefit', 'Endorsement', 'Lapse'],
        'fields'     => ['policy_number', 'holder_id', 'type', 'sum_assured', 'premium', 'start_date', 'end_date', 'status'],
        'workflows'  => ['issue_policy', 'collect_premium', 'endorse', 'lapse', 'renew', 'surrender'],
        'kpis'       => ['Policy Count', 'Premium Collection', 'Lapse Rate', 'Renewal Rate'],
        'roles'      => ['agent', 'underwriter', 'customer'],
    ],

    'claims' => [
        'label'      => 'Claims Management',
        'modules'    => ['Claim', 'ClaimDocument', 'Assessment', 'Settlement', 'FraudFlag'],
        'fields'     => ['policy_id', 'claimant', 'type', 'amount_claimed', 'amount_approved', 'status', 'surveyor_id'],
        'workflows'  => ['file_claim', 'assign_surveyor', 'assess', 'approve', 'settle', 'reject'],
        'kpis'       => ['Settlement Time', 'Claim Rejection Rate', 'Fraud Detection Rate'],
        'roles'      => ['claims_officer', 'surveyor', 'customer'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // COMMUNICATION
    // ══════════════════════════════════════════════════════════════════════

    'communication' => [
        'label'      => 'Communication & Notifications',
        'modules'    => ['Notification', 'EmailTemplate', 'SMSLog', 'Announcement', 'Inbox'],
        'fields'     => ['recipient', 'channel', 'template', 'subject', 'body', 'sent_at', 'status'],
        'workflows'  => ['send_notification', 'schedule_bulk', 'track_delivery', 'handle_reply'],
        'kpis'       => ['Delivery Rate', 'Open Rate', 'Response Rate'],
        'roles'      => ['admin', 'manager', 'hr'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // VENDOR & MULTI-TENANT
    // ══════════════════════════════════════════════════════════════════════

    'vendor_management' => [
        'label'      => 'Vendor / Seller Management',
        'modules'    => ['Vendor', 'VendorProduct', 'Commission', 'VendorPayout', 'VendorReview'],
        'fields'     => ['name', 'business_type', 'commission_rate', 'status', 'verification_status', 'balance'],
        'workflows'  => ['vendor_application', 'verify', 'activate', 'product_listing', 'payout'],
        'kpis'       => ['Active Vendors', 'GMV per Vendor', 'Commission Earned', 'Payout Accuracy'],
        'roles'      => ['admin', 'vendor', 'customer'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SPECIFIC OPERATIONS
    // ══════════════════════════════════════════════════════════════════════

    'shipment_management' => [
        'label'      => 'Shipment Tracking',
        'modules'    => ['Parcel', 'TrackingEvent', 'DeliveryAgent', 'Hub', 'CODCollection'],
        'fields'     => ['tracking_id', 'sender', 'recipient', 'weight', 'status', 'cod_amount', 'agent_id'],
        'workflows'  => ['receive_parcel', 'sort', 'dispatch', 'deliver', 'collect_cod', 'return'],
        'kpis'       => ['Delivery Success Rate', 'COD Collection Rate', 'Return Rate'],
        'roles'      => ['admin', 'operations', 'delivery_agent', 'customer'],
    ],

    'tracking' => [
        'label'      => 'Real-Time Tracking',
        'modules'    => ['TrackingEvent', 'GPSLocation', 'StatusUpdate', 'CustomerNotification'],
        'fields'     => ['entity_type', 'entity_id', 'latitude', 'longitude', 'status', 'timestamp', 'note'],
        'workflows'  => ['start_track', 'update_location', 'status_change', 'notify_customer', 'complete'],
        'kpis'       => ['Tracking Accuracy', 'Update Frequency', 'Customer Satisfaction'],
        'roles'      => ['operations', 'agent', 'customer'],
    ],

    'table_management' => [
        'label'      => 'Table & Floor Management',
        'modules'    => ['Table', 'Reservation', 'FloorPlan', 'TableStatus'],
        'fields'     => ['table_number', 'capacity', 'section', 'status', 'reserved_for', 'reserved_at'],
        'workflows'  => ['reserve', 'seat_customer', 'order', 'bill', 'clear_table'],
        'kpis'       => ['Table Turnover', 'Occupancy Rate', 'Reservation No-Show'],
        'roles'      => ['waiter', 'host', 'manager'],
    ],

    'crop_management' => [
        'label'      => 'Crop & Farm Management',
        'modules'    => ['Farm', 'Crop', 'InputRecord', 'HarvestLog', 'SellRecord'],
        'fields'     => ['crop_name', 'area', 'planting_date', 'expected_harvest', 'actual_yield', 'input_cost'],
        'workflows'  => ['plan_crop', 'apply_inputs', 'monitor_growth', 'harvest', 'sell'],
        'kpis'       => ['Yield per Acre', 'Input Cost per Kg', 'Profit per Acre'],
        'roles'      => ['farm_manager', 'field_worker', 'accountant'],
    ],

    'production' => [
        'label'      => 'Production / Manufacturing',
        'modules'    => ['ProductionOrder', 'BOM', 'WorkCenter', 'ProductionLog', 'WasteRecord'],
        'fields'     => ['product_id', 'quantity', 'bom', 'workcenter', 'start', 'end', 'yield', 'waste'],
        'workflows'  => ['create_order', 'issue_materials', 'start_production', 'quality_check', 'close_order'],
        'kpis'       => ['OEE', 'First Pass Yield', 'Waste %', 'Production Efficiency'],
        'roles'      => ['production_manager', 'machine_operator', 'qc_inspector'],
    ],

    'donor_management' => [
        'label'      => 'Donor Management',
        'modules'    => ['Donor', 'DonationRecord', 'Fund', 'Pledge', 'Receipt'],
        'fields'     => ['donor_name', 'type', 'amount', 'fund', 'date', 'receipt_no', 'notes'],
        'workflows'  => ['record_donation', 'send_receipt', 'pledge_follow_up', 'fund_allocation'],
        'kpis'       => ['Donor Retention', 'Average Donation', 'Total Funds Raised'],
        'roles'      => ['admin', 'fundraiser', 'finance'],
    ],

    'citizen_service' => [
        'label'      => 'Citizen / Public Service',
        'modules'    => ['Application', 'Service', 'Document', 'Queue', 'Certificate'],
        'fields'     => ['applicant_id', 'service_type', 'submitted_at', 'status', 'officer_id', 'notes'],
        'workflows'  => ['apply', 'verify_documents', 'process', 'approve', 'issue_certificate'],
        'kpis'       => ['Processing Time', 'Completion Rate', 'Queue Length'],
        'roles'      => ['citizen', 'officer', 'supervisor', 'admin'],
    ],

    'property_management' => [
        'label'      => 'Property Management',
        'modules'    => ['Property', 'Unit', 'Lease', 'RentCollection', 'Maintenance'],
        'fields'     => ['address', 'type', 'tenant_id', 'rent_amount', 'due_day', 'lease_start', 'lease_end', 'status'],
        'workflows'  => ['list_property', 'tenant_application', 'sign_lease', 'collect_rent', 'maintenance_request'],
        'kpis'       => ['Occupancy Rate', 'Rent Collection Rate', 'Maintenance Response Time'],
        'roles'      => ['landlord', 'property_manager', 'tenant'],
    ],

    'member_management' => [
        'label'      => 'Member Management',
        'modules'    => ['Member', 'Membership', 'Contribution', 'Meeting', 'Benefit'],
        'fields'     => ['member_id', 'name', 'join_date', 'membership_type', 'contribution', 'status'],
        'workflows'  => ['register', 'approve', 'activate', 'collect_contribution', 'suspend', 'exit'],
        'kpis'       => ['Active Members', 'Retention Rate', 'Contribution Collection Rate'],
        'roles'      => ['admin', 'officer', 'member'],
    ],

    'beneficiary_management' => [
        'label'      => 'Beneficiary Management',
        'modules'    => ['Beneficiary', 'Assistance', 'EligibilityCriteria', 'Distribution'],
        'fields'     => ['beneficiary_id', 'name', 'location', 'category', 'status', 'total_received'],
        'workflows'  => ['register', 'assess_eligibility', 'approve', 'distribute', 'follow_up'],
        'kpis'       => ['Beneficiaries Reached', 'Distribution Accuracy', 'Impact Score'],
        'roles'      => ['program_officer', 'field_worker', 'admin'],
    ],

    'case_management' => [
        'label'      => 'Case Management',
        'modules'    => ['Case', 'CaseActivity', 'Document', 'Assignment', 'Resolution'],
        'fields'     => ['case_number', 'type', 'priority', 'assigned_to', 'status', 'deadline', 'resolution'],
        'workflows'  => ['open_case', 'assign', 'investigate', 'escalate', 'resolve', 'close'],
        'kpis'       => ['Resolution Time', 'Open Cases', 'Escalation Rate'],
        'roles'      => ['case_officer', 'supervisor', 'client'],
    ],

    'network_management' => [
        'label'      => 'Network & Infrastructure',
        'modules'    => ['Device', 'Network', 'Monitoring', 'Alert', 'Incident'],
        'fields'     => ['device_id', 'ip', 'type', 'location', 'status', 'last_seen', 'uptime'],
        'workflows'  => ['onboard_device', 'monitor', 'alert_on_failure', 'raise_incident', 'resolve'],
        'kpis'       => ['Network Uptime', 'Mean Time to Recovery', 'Alert Volume'],
        'roles'      => ['network_engineer', 'noc_operator', 'admin'],
    ],

    'meter_management' => [
        'label'      => 'Meter Management',
        'modules'    => ['Meter', 'MeterReading', 'ConsumptionRecord', 'BillGeneration'],
        'fields'     => ['meter_id', 'customer_id', 'previous_reading', 'current_reading', 'consumption', 'bill_amount'],
        'workflows'  => ['assign_meter', 'record_reading', 'calculate_bill', 'send_bill', 'collect_payment'],
        'kpis'       => ['Reading Accuracy', 'Collection Rate', 'Loss Detection'],
        'roles'      => ['meter_reader', 'billing_officer', 'customer'],
    ],

    'sample_management' => [
        'label'      => 'Sample / Specimen Management',
        'modules'    => ['Sample', 'TestOrder', 'Result', 'QualityControl'],
        'fields'     => ['sample_id', 'patient_id', 'test_type', 'collected_at', 'status', 'result', 'validated_by'],
        'workflows'  => ['collect', 'receive_lab', 'process', 'validate_result', 'deliver_result'],
        'kpis'       => ['Turnaround Time', 'Error Rate', 'Sample Volume'],
        'roles'      => ['lab_technician', 'pathologist', 'receptionist'],
    ],

    'export' => [
        'label'      => 'Export / Trade Management',
        'modules'    => ['ExportOrder', 'LC', 'ShippingDocument', 'CustomsClearance'],
        'fields'     => ['order_id', 'buyer', 'country', 'value', 'lc_number', 'shipment_date', 'status'],
        'workflows'  => ['receive_order', 'open_lc', 'produce', 'ship', 'submit_documents', 'realize_payment'],
        'kpis'       => ['On-Time Shipment', 'LC Discrepancy Rate', 'Export Volume'],
        'roles'      => ['merchandiser', 'accounts', 'admin'],
    ],

    'batch_management' => [
        'label'      => 'Batch / Class Management',
        'modules'    => ['Batch', 'BatchStudent', 'Schedule', 'Attendance'],
        'fields'     => ['name', 'course', 'start_date', 'end_date', 'capacity', 'instructor', 'status'],
        'workflows'  => ['create_batch', 'enroll_students', 'schedule_classes', 'run_class', 'complete'],
        'kpis'       => ['Batch Utilization', 'Completion Rate', 'Student Satisfaction'],
        'roles'      => ['admin', 'instructor', 'student'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // DIGITAL & PLATFORM FUNCTIONS
    // ══════════════════════════════════════════════════════════════════════

    'loyalty_rewards' => [
        'label'      => 'Loyalty & Rewards Program',
        'modules'    => ['LoyaltyPoint', 'Tier', 'Redemption', 'Campaign', 'MemberCard'],
        'fields'     => ['customer_id', 'points', 'tier', 'earned_from', 'redeemed_at', 'expiry_date'],
        'workflows'  => ['earn_points', 'tier_upgrade', 'redeem_points', 'expire_points', 'send_reward'],
        'kpis'       => ['Active Members', 'Redemption Rate', 'Points Liability', 'Retention Lift'],
        'roles'      => ['admin', 'marketing_manager', 'customer'],
    ],

    'discount_voucher' => [
        'label'      => 'Discount & Voucher Management',
        'modules'    => ['Coupon', 'Voucher', 'Discount', 'PromoCode', 'UsageLog'],
        'fields'     => ['code', 'type', 'value', 'min_order', 'max_uses', 'expiry', 'applicable_to', 'used_count'],
        'workflows'  => ['create_voucher', 'distribute', 'apply_at_checkout', 'validate', 'expire'],
        'kpis'       => ['Redemption Rate', 'Revenue Impact', 'Unique Users', 'Abuse Rate'],
        'roles'      => ['admin', 'marketing_manager', 'customer'],
    ],

    'document_management' => [
        'label'      => 'Document Management',
        'modules'    => ['Document', 'Folder', 'Version', 'AccessControl', 'Signature'],
        'fields'     => ['title', 'type', 'file_path', 'version', 'owner', 'access_level', 'expiry_date'],
        'workflows'  => ['upload', 'classify', 'approve', 'share', 'archive', 'renew'],
        'kpis'       => ['Storage Used', 'Expiry Alerts', 'Access Violations'],
        'roles'      => ['admin', 'document_manager', 'staff'],
    ],

    'asset_management' => [
        'label'      => 'Asset & Fixed Asset Management',
        'modules'    => ['Asset', 'AssetCategory', 'Depreciation', 'Maintenance', 'Disposal'],
        'fields'     => ['asset_code', 'name', 'category', 'purchase_date', 'cost', 'location', 'assigned_to', 'status'],
        'workflows'  => ['asset_register', 'assign', 'maintain', 'depreciate', 'dispose'],
        'kpis'       => ['Total Asset Value', 'Depreciation Amount', 'Asset Utilization'],
        'roles'      => ['admin', 'asset_manager', 'finance_manager'],
    ],

    'budget_management' => [
        'label'      => 'Budget Planning & Management',
        'modules'    => ['Budget', 'BudgetLine', 'ActualVsBudget', 'BudgetRevision', 'Forecast'],
        'fields'     => ['department', 'period', 'category', 'budgeted_amount', 'actual_amount', 'variance'],
        'workflows'  => ['prepare_budget', 'submit_for_approval', 'approve', 'monitor_monthly', 'revise'],
        'kpis'       => ['Budget Utilization', 'Variance %', 'Department Adherence'],
        'roles'      => ['finance_manager', 'department_head', 'cfo'],
    ],

    'ticket_management' => [
        'label'      => 'Ticketing / Issue Tracker',
        'modules'    => ['Ticket', 'Category', 'Priority', 'SLA', 'EscalationRule'],
        'fields'     => ['ticket_id', 'type', 'priority', 'status', 'assignee', 'requester', 'sla_deadline'],
        'workflows'  => ['raise_ticket', 'auto_assign', 'work_on', 'escalate', 'resolve', 'close', 'reopen'],
        'kpis'       => ['First Response Time', 'Resolution Time', 'SLA Breach Rate', 'Open Tickets'],
        'roles'      => ['support_agent', 'support_manager', 'user'],
    ],

    'complaint_management' => [
        'label'      => 'Complaint / Grievance Management',
        'modules'    => ['Complaint', 'ComplaintCategory', 'Investigation', 'Resolution', 'Feedback'],
        'fields'     => ['reference_no', 'complainant', 'category', 'description', 'status', 'resolution', 'closed_at'],
        'workflows'  => ['file_complaint', 'acknowledge', 'investigate', 'resolve', 'notify_complainant', 'close'],
        'kpis'       => ['Resolution Rate', 'Average Resolution Time', 'Recurrence Rate'],
        'roles'      => ['customer', 'complaint_officer', 'manager'],
    ],

    'survey_feedback' => [
        'label'      => 'Survey & Feedback Collection',
        'modules'    => ['Survey', 'Question', 'Response', 'Analysis', 'NPS'],
        'fields'     => ['title', 'type', 'target_audience', 'start_date', 'end_date', 'anonymous', 'responses'],
        'workflows'  => ['design_survey', 'distribute', 'collect_responses', 'analyze', 'report'],
        'kpis'       => ['Response Rate', 'NPS Score', 'Satisfaction Score', 'Completion Rate'],
        'roles'      => ['admin', 'manager', 'customer', 'employee'],
    ],

    'quiz_assessment' => [
        'label'      => 'Quiz & Assessment Engine',
        'modules'    => ['Quiz', 'Question', 'Answer', 'Result', 'Leaderboard'],
        'fields'     => ['quiz_title', 'time_limit', 'pass_mark', 'questions', 'attempts_allowed', 'shuffle'],
        'workflows'  => ['create_quiz', 'assign', 'attempt', 'auto_grade', 'publish_result'],
        'kpis'       => ['Attempt Rate', 'Pass Rate', 'Average Score', 'Completion Rate'],
        'roles'      => ['instructor', 'admin', 'student'],
    ],

    'certificate_management' => [
        'label'      => 'Certificate & Credential Management',
        'modules'    => ['Certificate', 'Template', 'IssuedCertificate', 'VerificationLink'],
        'fields'     => ['recipient', 'course', 'issue_date', 'expiry_date', 'verification_code', 'template_id'],
        'workflows'  => ['trigger_certificate', 'generate', 'email_delivery', 'verify_online'],
        'kpis'       => ['Certificates Issued', 'Verification Requests', 'Expired Rate'],
        'roles'      => ['admin', 'instructor', 'student'],
    ],

    'map_tracking' => [
        'label'      => 'GPS / Map Tracking',
        'modules'    => ['TrackingDevice', 'LiveLocation', 'GeoFence', 'Route', 'Alert'],
        'fields'     => ['device_id', 'entity_type', 'entity_id', 'lat', 'lng', 'speed', 'timestamp', 'geofence'],
        'workflows'  => ['device_ping', 'live_track', 'geofence_alert', 'route_replay', 'report'],
        'kpis'       => ['Coverage Rate', 'Alert Response Time', 'Geofence Violations'],
        'roles'      => ['admin', 'dispatcher', 'driver', 'operations_manager'],
    ],

    'chat_messaging' => [
        'label'      => 'In-App Chat / Messaging',
        'modules'    => ['Conversation', 'Message', 'Attachment', 'Thread', 'Notification'],
        'fields'     => ['sender_id', 'recipient_id', 'message', 'type', 'read_at', 'attachment_url'],
        'workflows'  => ['start_conversation', 'send_message', 'mark_read', 'archive', 'delete'],
        'kpis'       => ['Response Time', 'Message Volume', 'Resolution via Chat'],
        'roles'      => ['user', 'support_agent', 'admin'],
    ],

    'audit_log' => [
        'label'      => 'Audit Trail / Activity Log',
        'modules'    => ['AuditLog', 'ActivityLog', 'SecurityEvent', 'ChangeHistory'],
        'fields'     => ['user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip', 'timestamp'],
        'workflows'  => ['auto_capture_action', 'flag_suspicious', 'export_logs', 'alert_admin'],
        'kpis'       => ['Actions per Day', 'Suspicious Events', 'Compliance Audit Pass Rate'],
        'roles'      => ['admin', 'compliance_officer', 'security_officer'],
    ],

    'digital_signature' => [
        'label'      => 'Digital Signature & e-Signing',
        'modules'    => ['SignatureRequest', 'Document', 'Signer', 'SignatureLog', 'Certificate'],
        'fields'     => ['document_id', 'signer_email', 'signed_at', 'ip_address', 'certificate', 'status'],
        'workflows'  => ['prepare_document', 'send_for_signature', 'sign', 'countersign', 'finalize'],
        'kpis'       => ['Sign Completion Rate', 'Time to Sign', 'Documents Executed'],
        'roles'      => ['admin', 'contract_manager', 'signer'],
    ],

    'catalog' => [
        'label'      => 'Product / Item Catalog',
        'modules'    => ['Product', 'Category', 'Brand', 'Attribute', 'Variant', 'Image'],
        'fields'     => ['sku', 'name', 'category', 'brand', 'description', 'price', 'variants', 'images', 'status'],
        'workflows'  => ['add_product', 'set_variants', 'set_pricing', 'publish', 'archive'],
        'kpis'       => ['Active Products', 'Out of Stock Rate', 'Catalog Coverage'],
        'roles'      => ['admin', 'catalog_manager', 'vendor'],
    ],

    'booking_management' => [
        'label'      => 'Booking / Reservation System',
        'modules'    => ['Booking', 'Resource', 'Availability', 'Confirmation', 'Cancellation'],
        'fields'     => ['resource_id', 'customer_id', 'date', 'time_slot', 'status', 'payment_status', 'notes'],
        'workflows'  => ['check_availability', 'create_booking', 'send_confirmation', 'remind', 'complete', 'cancel'],
        'kpis'       => ['Booking Rate', 'Cancellation Rate', 'No-Show Rate', 'Utilization'],
        'roles'      => ['admin', 'staff', 'customer'],
    ],

    'helpdesk' => [
        'label'      => 'Help Desk / IT Support',
        'modules'    => ['Incident', 'ServiceRequest', 'CMDB', 'KnowledgeArticle', 'SLA'],
        'fields'     => ['incident_id', 'category', 'priority', 'assigned_to', 'status', 'resolution'],
        'workflows'  => ['log_incident', 'classify', 'assign', 'diagnose', 'resolve', 'post_mortem'],
        'kpis'       => ['MTTR', 'First Call Resolution', 'SLA Compliance', 'Incident Backlog'],
        'roles'      => ['user', 'it_support', 'it_manager'],
    ],

    'task_management' => [
        'label'      => 'Task Management / To-Do',
        'modules'    => ['Task', 'Subtask', 'Label', 'Checklist', 'Attachment', 'Comment'],
        'fields'     => ['title', 'description', 'assignee', 'due_date', 'priority', 'status', 'tags'],
        'workflows'  => ['create_task', 'assign', 'in_progress', 'review', 'complete', 'archive'],
        'kpis'       => ['Task Completion Rate', 'Overdue Tasks', 'Cycle Time'],
        'roles'      => ['admin', 'team_member', 'manager'],
    ],

    'kanban' => [
        'label'      => 'Kanban / Visual Board',
        'modules'    => ['Board', 'Column', 'Card', 'Label', 'WIPLimit'],
        'fields'     => ['board_name', 'column_name', 'card_title', 'assignee', 'priority', 'due_date', 'wip_limit'],
        'workflows'  => ['create_card', 'move_column', 'block', 'unblock', 'archive'],
        'kpis'       => ['Throughput', 'Cycle Time', 'Lead Time', 'WIP Violations'],
        'roles'      => ['admin', 'team_member', 'project_manager'],
    ],

    'multi_tenant' => [
        'label'      => 'Multi-Tenant / SaaS Workspace',
        'modules'    => ['Tenant', 'Workspace', 'TenantUser', 'Plan', 'TenantSetting'],
        'fields'     => ['tenant_id', 'name', 'domain', 'plan', 'users', 'status', 'created_at'],
        'workflows'  => ['tenant_signup', 'workspace_setup', 'invite_users', 'plan_upgrade', 'suspend'],
        'kpis'       => ['Active Tenants', 'Revenue per Tenant', 'Churn Rate', 'Expansion MRR'],
        'roles'      => ['super_admin', 'tenant_admin', 'tenant_user'],
    ],

    'product_catalog' => [
        'label'      => 'Marketplace Product Listing',
        'modules'    => ['Listing', 'Review', 'Rating', 'Flag', 'BuyerProtection'],
        'fields'     => ['vendor_id', 'title', 'category', 'price', 'stock', 'rating', 'status'],
        'workflows'  => ['submit_listing', 'review_listing', 'approve', 'publish', 'flag_report'],
        'kpis'       => ['Listings per Vendor', 'Approval Rate', 'Average Rating', 'Conversion Rate'],
        'roles'      => ['admin', 'vendor', 'buyer', 'moderator'],
    ],

    'stock_management' => [
        'label'      => 'Stock Count / Physical Inventory',
        'modules'    => ['StockCount', 'CountSheet', 'Variance', 'Adjustment', 'CountSchedule'],
        'fields'     => ['location', 'product', 'system_qty', 'counted_qty', 'variance', 'approved_by', 'date'],
        'workflows'  => ['schedule_count', 'assign_counters', 'count', 'variance_review', 'adjust', 'close'],
        'kpis'       => ['Count Accuracy', 'Variance Value', 'Shrinkage Rate'],
        'roles'      => ['warehouse_manager', 'stock_keeper', 'auditor'],
    ],

    'customer_portal' => [
        'label'      => 'Customer Self-Service Portal',
        'modules'    => ['Portal', 'Account', 'OrderHistory', 'InvoiceDownload', 'SupportTicket'],
        'fields'     => ['customer_id', 'email', 'orders', 'invoices', 'tickets', 'profile', 'preferences'],
        'workflows'  => ['portal_login', 'view_orders', 'download_invoice', 'raise_ticket', 'update_profile'],
        'kpis'       => ['Portal Adoption Rate', 'Support Ticket Deflection', 'Self-Service Rate'],
        'roles'      => ['customer', 'admin'],
    ],

    'barcode_qr' => [
        'label'      => 'Barcode / QR Code Management',
        'modules'    => ['Barcode', 'QRCode', 'ScanLog', 'Generator', 'Printer'],
        'fields'     => ['code_type', 'value', 'entity_type', 'entity_id', 'generated_at', 'last_scanned'],
        'workflows'  => ['generate', 'print', 'scan', 'validate', 'log_scan'],
        'kpis'       => ['Codes Generated', 'Scan Accuracy', 'Error Rate'],
        'roles'      => ['admin', 'warehouse_staff', 'cashier'],
    ],

    'valuation' => [
        'label'      => 'Valuation / Appraisal',
        'modules'    => ['ValuationRequest', 'Appraiser', 'Report', 'Certificate'],
        'fields'     => ['item_type', 'description', 'appraiser_id', 'value', 'date', 'method', 'notes'],
        'workflows'  => ['submit_request', 'assign_appraiser', 'inspect', 'calculate_value', 'issue_certificate'],
        'kpis'       => ['Valuations per Month', 'Turnaround Time', 'Accuracy vs Market'],
        'roles'      => ['admin', 'appraiser', 'client'],
    ],

    'gate_pass' => [
        'label'      => 'Gate Pass / Visitor Management',
        'modules'    => ['Visitor', 'GatePass', 'Host', 'Entry', 'Exit', 'Vehicle'],
        'fields'     => ['visitor_name', 'id_type', 'host_id', 'purpose', 'entry_time', 'exit_time', 'badge_no'],
        'workflows'  => ['visitor_registration', 'host_approval', 'badge_issue', 'entry', 'exit', 'report'],
        'kpis'       => ['Visitors per Day', 'Average Visit Duration', 'Unauthorized Entry Rate'],
        'roles'      => ['security', 'receptionist', 'admin', 'visitor', 'host'],
    ],

    'job_card' => [
        'label'      => 'Job Card / Work Order',
        'modules'    => ['JobCard', 'WorkOrder', 'Assignment', 'Parts', 'TimesheetEntry'],
        'fields'     => ['job_no', 'description', 'assigned_to', 'priority', 'start_date', 'due_date', 'status', 'cost'],
        'workflows'  => ['create_job', 'assign', 'start_work', 'log_materials', 'log_time', 'complete', 'invoice'],
        'kpis'       => ['Job Completion Rate', 'On-Time Rate', 'Cost Variance'],
        'roles'      => ['admin', 'supervisor', 'technician', 'client'],
    ],
];
