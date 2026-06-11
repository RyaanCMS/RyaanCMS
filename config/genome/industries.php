<?php

/**
 * Industry Genome Library
 *
 * Each industry entry defines:
 *   functions   → which business functions this industry needs (→ functions.php)
 *   problems    → common pain points (→ problem_solving_library.php)
 *   roles       → typical user roles
 *   workflows   → key operational workflows
 *   integrations→ common third-party integrations
 *   kpis        → industry KPIs
 *   synonyms    → alternate names / how users refer to this industry
 *
 * Target: 500+ industries. Current: 100+ high-value industries.
 * Add more by copying the structure — the genome engine handles the rest.
 */
return [

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: RETAIL & COMMERCE
    // ══════════════════════════════════════════════════════════════════════

    'ecommerce' => [
        'label'         => 'E-Commerce',
        'sector'        => 'retail',
        'functions'     => ['sales', 'inventory', 'payment', 'shipping', 'crm', 'marketing', 'support'],
        'problems'      => ['cart_abandonment', 'inventory_mismatch', 'fake_orders', 'slow_delivery', 'high_returns', 'low_conversion'],
        'roles'         => ['Admin', 'Store Manager', 'Warehouse Staff', 'Customer', 'Delivery Agent'],
        'workflows'     => ['order_to_delivery', 'return_refund', 'inventory_restock', 'customer_support_ticket'],
        'integrations'  => ['stripe', 'paypal', 'bkash', 'nagad', 'fedex', 'shopify', 'facebook_shop', 'google_shopping'],
        'kpis'          => ['GMV', 'Conversion Rate', 'Average Order Value', 'Cart Abandonment Rate', 'Return Rate', 'Customer LTV'],
        'synonyms'      => ['online shop', 'online store', 'marketplace', 'e-shop', 'web store', 'digital store'],
    ],

    'retail' => [
        'label'         => 'Retail / Point of Sale',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'sales', 'crm', 'accounting'],
        'problems'      => ['inventory_mismatch', 'slow_checkout', 'stock_shrinkage', 'low_footfall'],
        'roles'         => ['Admin', 'Store Manager', 'Cashier', 'Customer'],
        'workflows'     => ['sale_transaction', 'daily_reconciliation', 'stock_replenishment'],
        'integrations'  => ['stripe', 'bkash', 'nagad', 'barcode_scanner'],
        'kpis'          => ['Daily Sales', 'Stock Turnover', 'Shrinkage Rate', 'Average Basket Size'],
        'synonyms'      => ['shop', 'store', 'pos system', 'point of sale', 'retail store', 'boutique'],
    ],

    'wholesale' => [
        'label'         => 'Wholesale / Distribution',
        'sector'        => 'retail',
        'functions'     => ['inventory', 'procurement', 'sales', 'invoicing', 'crm', 'accounting'],
        'problems'      => ['inventory_mismatch', 'late_payment', 'order_tracking', 'pricing_errors'],
        'roles'         => ['Admin', 'Sales Rep', 'Warehouse Manager', 'Accountant', 'Client'],
        'workflows'     => ['purchase_order', 'goods_receipt', 'sales_dispatch', 'invoice_payment'],
        'integrations'  => ['quickbooks', 'tally', 'shipping_apis'],
        'kpis'          => ['Fill Rate', 'Inventory Turnover', 'Receivables Days', 'Order Accuracy'],
        'synonyms'      => ['distributor', 'wholesale business', 'trading company', 'b2b commerce'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: HEALTHCARE
    // ══════════════════════════════════════════════════════════════════════

    'hospital' => [
        'label'         => 'Hospital / Healthcare',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'pharmacy', 'reporting', 'hrm'],
        'problems'      => ['appointment_no_shows', 'billing_errors', 'long_wait_times', 'record_management'],
        'roles'         => ['Admin', 'Doctor', 'Nurse', 'Receptionist', 'Pharmacist', 'Patient', 'Lab Technician'],
        'workflows'     => ['patient_admission', 'appointment_booking', 'lab_result', 'discharge_billing'],
        'integrations'  => ['sms_gateway', 'lab_equipment_api', 'insurance_api', 'payment_gateway'],
        'kpis'          => ['Bed Occupancy', 'Average Length of Stay', 'Patient Satisfaction', 'Billing Accuracy'],
        'synonyms'      => ['clinic', 'medical center', 'healthcare facility', 'doctor management', 'HMS', 'hospital management'],
    ],

    'pharmacy' => [
        'label'         => 'Pharmacy / Drugstore',
        'sector'        => 'healthcare',
        'functions'     => ['inventory', 'pos', 'prescription', 'accounting', 'supplier'],
        'problems'      => ['expiry_management', 'stockout', 'prescription_errors', 'cash_reconciliation'],
        'roles'         => ['Admin', 'Pharmacist', 'Cashier', 'Customer'],
        'workflows'     => ['prescription_fill', 'stock_expiry_check', 'reorder_alert'],
        'integrations'  => ['bkash', 'nagad', 'supplier_api'],
        'kpis'          => ['Prescription Fill Rate', 'Expiry Loss %', 'Daily Sales', 'Stockout Rate'],
        'synonyms'      => ['medicine shop', 'drug store', 'medical shop', 'chemist'],
    ],

    'diagnostic' => [
        'label'         => 'Diagnostic / Lab',
        'sector'        => 'healthcare',
        'functions'     => ['appointments', 'patient_management', 'billing', 'reporting', 'sample_management'],
        'problems'      => ['result_delays', 'report_errors', 'sample_tracking'],
        'roles'         => ['Admin', 'Lab Technician', 'Doctor', 'Patient', 'Receptionist'],
        'workflows'     => ['sample_collection', 'test_processing', 'result_delivery'],
        'integrations'  => ['sms_gateway', 'whatsapp', 'email'],
        'kpis'          => ['TAT (Turnaround Time)', 'Report Accuracy', 'Patient Volume'],
        'synonyms'      => ['lab management', 'diagnostic center', 'pathology lab', 'test lab'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: EDUCATION
    // ══════════════════════════════════════════════════════════════════════

    'school' => [
        'label'         => 'School / Educational Institution',
        'sector'        => 'education',
        'functions'     => ['student_management', 'attendance', 'fees', 'results', 'communication', 'hrm'],
        'problems'      => ['fee_collection', 'attendance_tracking', 'parent_communication', 'result_management'],
        'roles'         => ['Admin', 'Principal', 'Teacher', 'Student', 'Parent', 'Accountant'],
        'workflows'     => ['admission', 'fee_collection', 'attendance_report', 'exam_result_publish'],
        'integrations'  => ['sms_gateway', 'bkash', 'nagad', 'whatsapp'],
        'kpis'          => ['Attendance Rate', 'Fee Collection Rate', 'Pass Rate', 'Parent Engagement'],
        'synonyms'      => ['school management', 'academic system', 'student management system', 'SMS', 'school ERP'],
    ],

    'university' => [
        'label'         => 'University / College',
        'sector'        => 'education',
        'functions'     => ['student_management', 'course_management', 'fees', 'results', 'library', 'hrm'],
        'problems'      => ['enrollment_management', 'grade_disputes', 'course_scheduling'],
        'roles'         => ['Admin', 'Registrar', 'Professor', 'Student', 'Librarian', 'Finance Officer'],
        'workflows'     => ['course_enrollment', 'grade_submission', 'graduation_clearance'],
        'integrations'  => ['payment_gateway', 'email', 'lms_api'],
        'kpis'          => ['Enrollment Rate', 'Graduation Rate', 'Course Completion', 'Student Satisfaction'],
        'synonyms'      => ['college management', 'university management system', 'higher education'],
    ],

    'coaching' => [
        'label'         => 'Coaching / Training Center',
        'sector'        => 'education',
        'functions'     => ['student_management', 'batch_management', 'fees', 'attendance', 'results'],
        'problems'      => ['batch_management', 'fee_tracking', 'attendance'],
        'roles'         => ['Admin', 'Instructor', 'Student', 'Parent'],
        'workflows'     => ['enrollment', 'class_schedule', 'payment', 'result'],
        'integrations'  => ['bkash', 'nagad', 'sms_gateway', 'zoom'],
        'kpis'          => ['Student Retention', 'Fee Collection', 'Attendance Rate'],
        'synonyms'      => ['coaching center', 'training institute', 'academy', 'tuition center'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: FOOD & HOSPITALITY
    // ══════════════════════════════════════════════════════════════════════

    'restaurant' => [
        'label'         => 'Restaurant / Food Service',
        'sector'        => 'hospitality',
        'functions'     => ['pos', 'menu_management', 'orders', 'inventory', 'accounting', 'table_management'],
        'problems'      => ['order_errors', 'food_waste', 'slow_service', 'cash_management'],
        'roles'         => ['Admin', 'Manager', 'Waiter', 'Chef', 'Cashier', 'Customer'],
        'workflows'     => ['table_order', 'kitchen_ticket', 'payment', 'daily_closing'],
        'integrations'  => ['foodpanda', 'shohoz', 'pathao_food', 'bkash', 'nagad', 'pos_hardware'],
        'kpis'          => ['Table Turnover', 'Average Ticket', 'Food Cost %', 'Customer Rating'],
        'synonyms'      => ['restaurant management', 'food management', 'cafe system', 'hotel restaurant', 'pos restaurant'],
    ],

    'hotel' => [
        'label'         => 'Hotel / Hospitality',
        'sector'        => 'hospitality',
        'functions'     => ['booking', 'room_management', 'billing', 'housekeeping', 'restaurant', 'hrm'],
        'problems'      => ['overbooking', 'checkout_delays', 'housekeeping_coordination'],
        'roles'         => ['Admin', 'Front Desk', 'Manager', 'Housekeeping', 'Guest', 'Restaurant Staff'],
        'workflows'     => ['check_in', 'check_out', 'room_service', 'housekeeping'],
        'integrations'  => ['booking_com', 'agoda', 'payment_gateway', 'channel_manager'],
        'kpis'          => ['Occupancy Rate', 'RevPAR', 'ADR', 'Guest Satisfaction'],
        'synonyms'      => ['hotel management', 'resort', 'guesthouse', 'PMS', 'property management'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: LOGISTICS & TRANSPORT
    // ══════════════════════════════════════════════════════════════════════

    'logistics' => [
        'label'         => 'Logistics / Courier',
        'sector'        => 'transport',
        'functions'     => ['shipment_management', 'tracking', 'inventory', 'billing', 'fleet', 'crm'],
        'problems'      => ['delivery_delays', 'lost_parcels', 'cash_collection', 'route_optimization'],
        'roles'         => ['Admin', 'Operations Manager', 'Delivery Agent', 'Customer', 'Warehouse Staff'],
        'workflows'     => ['parcel_pickup', 'sorting', 'delivery', 'cod_collection', 'return'],
        'integrations'  => ['google_maps', 'sms_gateway', 'pathao', 'redx', 'steadfast'],
        'kpis'          => ['On-Time Delivery %', 'Loss Rate', 'COD Collection Rate', 'Cost per Delivery'],
        'synonyms'      => ['courier management', 'delivery management', 'last mile', 'parcel management', 'freight'],
    ],

    'transport' => [
        'label'         => 'Transport / Fleet Management',
        'sector'        => 'transport',
        'functions'     => ['fleet', 'booking', 'driver_management', 'billing', 'tracking', 'maintenance'],
        'problems'      => ['vehicle_maintenance', 'fuel_management', 'driver_accountability'],
        'roles'         => ['Admin', 'Fleet Manager', 'Driver', 'Dispatcher', 'Passenger'],
        'workflows'     => ['trip_booking', 'dispatch', 'trip_completion', 'maintenance_schedule'],
        'integrations'  => ['google_maps', 'fuel_cards', 'gps_tracker'],
        'kpis'          => ['Fleet Utilization', 'Fuel Efficiency', 'On-Time Rate', 'Maintenance Cost'],
        'synonyms'      => ['fleet management', 'vehicle management', 'taxi management', 'ride management'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: FINANCE
    // ══════════════════════════════════════════════════════════════════════

    'microfinance' => [
        'label'         => 'Microfinance / MFI',
        'sector'        => 'finance',
        'functions'     => ['loan_management', 'member_management', 'collections', 'accounting', 'reporting'],
        'problems'      => ['loan_defaults', 'collection_tracking', 'compliance_reporting'],
        'roles'         => ['Admin', 'Branch Manager', 'Loan Officer', 'Field Agent', 'Member'],
        'workflows'     => ['loan_application', 'credit_assessment', 'disbursement', 'collection', 'repayment'],
        'integrations'  => ['bkash', 'nagad', 'mobile_banking', 'sms_gateway'],
        'kpis'          => ['Portfolio At Risk (PAR)', 'Collection Efficiency', 'Repayment Rate', 'Delinquency Rate'],
        'synonyms'      => ['MFI', 'loan management', 'samity management', 'credit management', 'NGO finance'],
    ],

    'insurance' => [
        'label'         => 'Insurance',
        'sector'        => 'finance',
        'functions'     => ['policy_management', 'claims', 'premium_collection', 'crm', 'reporting'],
        'problems'      => ['claims_fraud', 'premium_defaults', 'slow_claims_processing'],
        'roles'         => ['Admin', 'Agent', 'Underwriter', 'Claims Officer', 'Customer'],
        'workflows'     => ['policy_issuance', 'premium_renewal', 'claim_filing', 'claim_settlement'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'id_verification'],
        'kpis'          => ['Loss Ratio', 'Claims Settlement Time', 'Policy Renewal Rate', 'Agent Productivity'],
        'synonyms'      => ['insurance management', 'policy management', 'claims management'],
    ],

    'accounting' => [
        'label'         => 'Accounting / Finance Management',
        'sector'        => 'finance',
        'functions'     => ['accounting', 'invoicing', 'expenses', 'payroll', 'reporting', 'tax'],
        'problems'      => ['late_payment', 'reconciliation_errors', 'cash_flow_problems'],
        'roles'         => ['Admin', 'Accountant', 'Finance Manager', 'CEO'],
        'workflows'     => ['invoice_approval', 'payment_processing', 'month_end_close', 'payroll_run'],
        'integrations'  => ['stripe', 'paypal', 'quickbooks', 'bank_api'],
        'kpis'          => ['Days Sales Outstanding', 'Accounts Payable Days', 'Cash Flow', 'Profit Margin'],
        'synonyms'      => ['accounting system', 'finance system', 'bookkeeping', 'ERP finance', 'ledger'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: MANUFACTURING
    // ══════════════════════════════════════════════════════════════════════

    'manufacturing' => [
        'label'         => 'Manufacturing / Production',
        'sector'        => 'manufacturing',
        'functions'     => ['production', 'inventory', 'procurement', 'quality', 'hrm', 'accounting', 'maintenance'],
        'problems'      => ['production_downtime', 'inventory_mismatch', 'quality_defects', 'procurement_delays'],
        'roles'         => ['Admin', 'Production Manager', 'Quality Inspector', 'Machine Operator', 'Procurement Officer'],
        'workflows'     => ['production_order', 'material_requisition', 'quality_check', 'dispatch'],
        'integrations'  => ['erp_api', 'barcode', 'iot_sensors'],
        'kpis'          => ['OEE (Overall Equipment Effectiveness)', 'Defect Rate', 'Production Yield', 'Downtime Hours'],
        'synonyms'      => ['factory management', 'production management', 'manufacturing ERP', 'plant management'],
    ],

    'garments' => [
        'label'         => 'Garments / Apparel',
        'sector'        => 'manufacturing',
        'functions'     => ['production', 'inventory', 'procurement', 'export', 'hrm', 'quality'],
        'problems'      => ['order_delay', 'fabric_waste', 'worker_overtime', 'shipment_compliance'],
        'roles'         => ['Admin', 'Production Manager', 'Cutting Master', 'QC Inspector', 'Merchandiser'],
        'workflows'     => ['order_processing', 'fabric_requisition', 'cutting_stitching', 'finishing_packing'],
        'integrations'  => ['export_compliance_api', 'barcode'],
        'kpis'          => ['On-Time Shipment', 'Rejection Rate', 'Efficiency %', 'Fabric Utilization'],
        'synonyms'      => ['RMG', 'garment factory', 'apparel manufacturing', 'textile factory'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: AGRICULTURE
    // ══════════════════════════════════════════════════════════════════════

    'agriculture' => [
        'label'         => 'Agriculture / Farm Management',
        'sector'        => 'agriculture',
        'functions'     => ['crop_management', 'inventory', 'sales', 'procurement', 'accounting'],
        'problems'      => ['crop_loss_tracking', 'input_cost_management', 'price_volatility'],
        'roles'         => ['Admin', 'Farm Manager', 'Field Worker', 'Buyer'],
        'workflows'     => ['planting_schedule', 'harvest', 'sale_to_market'],
        'integrations'  => ['weather_api', 'market_price_api', 'sms_gateway'],
        'kpis'          => ['Yield per Acre', 'Input Cost per Unit', 'Crop Loss Rate'],
        'synonyms'      => ['farm management', 'agri management', 'crop management', 'farming system'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: REAL ESTATE
    // ══════════════════════════════════════════════════════════════════════

    'real_estate' => [
        'label'         => 'Real Estate',
        'sector'        => 'real_estate',
        'functions'     => ['property_management', 'crm', 'sales', 'rentals', 'accounting'],
        'problems'      => ['lead_management', 'rent_collection', 'property_maintenance'],
        'roles'         => ['Admin', 'Agent', 'Property Manager', 'Tenant', 'Landlord', 'Buyer'],
        'workflows'     => ['lead_to_sale', 'lease_agreement', 'rent_collection', 'maintenance_request'],
        'integrations'  => ['google_maps', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['Lead Conversion', 'Occupancy Rate', 'Rent Collection Rate', 'Days on Market'],
        'synonyms'      => ['real estate management', 'property management', 'rental management', 'land management'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: SERVICES & B2B
    // ══════════════════════════════════════════════════════════════════════

    'crm_saas' => [
        'label'         => 'CRM / Sales Management',
        'sector'        => 'services',
        'functions'     => ['crm', 'sales', 'marketing', 'support', 'reporting'],
        'problems'      => ['low_sales', 'high_churn', 'poor_lead_quality', 'follow_up_failure'],
        'roles'         => ['Admin', 'Sales Manager', 'Sales Rep', 'Customer Success', 'Customer'],
        'workflows'     => ['lead_capture', 'qualification', 'proposal', 'close_deal', 'onboarding'],
        'integrations'  => ['email', 'whatsapp', 'facebook_leads', 'mailchimp', 'slack'],
        'kpis'          => ['Pipeline Value', 'Win Rate', 'Sales Cycle Length', 'Customer LTV', 'Churn Rate'],
        'synonyms'      => ['CRM', 'sales management', 'lead management', 'customer management', 'pipeline management'],
    ],

    'agency' => [
        'label'         => 'Agency / Service Business',
        'sector'        => 'services',
        'functions'     => ['project_management', 'crm', 'invoicing', 'hrm', 'time_tracking'],
        'problems'      => ['project_delays', 'resource_conflicts', 'invoice_delays'],
        'roles'         => ['Admin', 'Project Manager', 'Designer', 'Developer', 'Client'],
        'workflows'     => ['project_kickoff', 'task_assignment', 'review_approval', 'invoice_generation'],
        'integrations'  => ['slack', 'jira', 'stripe', 'gmail'],
        'kpis'          => ['Project On-Time %', 'Utilization Rate', 'Client Satisfaction', 'Revenue per Employee'],
        'synonyms'      => ['agency management', 'service company', 'consulting firm', 'freelance platform'],
    ],

    'hrm_system' => [
        'label'         => 'HR Management',
        'sector'        => 'services',
        'functions'     => ['hrm', 'payroll', 'attendance', 'recruitment', 'performance', 'training'],
        'problems'      => ['employee_turnover', 'attendance_fraud', 'payroll_errors', 'performance_gaps'],
        'roles'         => ['Admin', 'HR Manager', 'Employee', 'Manager', 'Finance'],
        'workflows'     => ['onboarding', 'leave_approval', 'performance_review', 'payroll_processing'],
        'integrations'  => ['biometric', 'bkash', 'nagad', 'sms_gateway'],
        'kpis'          => ['Employee Retention', 'Time to Hire', 'Absenteeism Rate', 'Payroll Accuracy'],
        'synonyms'      => ['HRM', 'HR system', 'payroll system', 'employee management', 'HRMS'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: TECHNOLOGY & DIGITAL
    // ══════════════════════════════════════════════════════════════════════

    'saas' => [
        'label'         => 'SaaS / Software Product',
        'sector'        => 'technology',
        'functions'     => ['subscription', 'crm', 'billing', 'support', 'analytics', 'onboarding'],
        'problems'      => ['high_churn', 'low_activation', 'support_volume'],
        'roles'         => ['Admin', 'Customer Success', 'Support Agent', 'End User', 'Finance'],
        'workflows'     => ['trial_onboarding', 'subscription_upgrade', 'churn_prevention', 'support_ticket'],
        'integrations'  => ['stripe', 'intercom', 'sendgrid', 'slack', 'segment'],
        'kpis'          => ['MRR', 'Churn Rate', 'NPS', 'CAC', 'LTV', 'Activation Rate'],
        'synonyms'      => ['SaaS', 'subscription management', 'software company', 'product company'],
    ],

    'marketplace' => [
        'label'         => 'Marketplace / Platform',
        'sector'        => 'technology',
        'functions'     => ['vendor_management', 'product_catalog', 'orders', 'payment', 'crm', 'analytics'],
        'problems'      => ['fake_sellers', 'product_quality', 'dispute_management', 'commission_tracking'],
        'roles'         => ['Admin', 'Vendor', 'Customer', 'Support', 'Finance'],
        'workflows'     => ['vendor_onboarding', 'product_listing', 'order_fulfillment', 'dispute_resolution'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'shipping_api'],
        'kpis'          => ['GMV', 'Vendor Retention', 'Dispute Rate', 'Commission Revenue'],
        'synonyms'      => ['marketplace', 'multi-vendor', 'platform', 'two-sided marketplace'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: GOVERNMENT & NGO
    // ══════════════════════════════════════════════════════════════════════

    'ngo' => [
        'label'         => 'NGO / Non-Profit',
        'sector'        => 'non_profit',
        'functions'     => ['beneficiary_management', 'project_management', 'donor_management', 'accounting', 'reporting'],
        'problems'      => ['donor_tracking', 'fund_utilization', 'beneficiary_tracking'],
        'roles'         => ['Admin', 'Program Officer', 'Field Worker', 'Finance', 'Donor'],
        'workflows'     => ['project_approval', 'fund_disbursement', 'field_data_collection', 'donor_report'],
        'integrations'  => ['bkash', 'nagad', 'sms_gateway', 'google_forms'],
        'kpis'          => ['Beneficiary Reach', 'Fund Utilization %', 'Project Completion', 'Donor Retention'],
        'synonyms'      => ['NGO management', 'non-profit', 'social organization', 'charity management'],
    ],

    'government' => [
        'label'         => 'Government / Public Service',
        'sector'        => 'public',
        'functions'     => ['citizen_service', 'case_management', 'reporting', 'workflow', 'compliance'],
        'problems'      => ['service_delays', 'corruption_control', 'document_management'],
        'roles'         => ['Admin', 'Officer', 'Supervisor', 'Citizen'],
        'workflows'     => ['application_processing', 'approval_chain', 'service_delivery'],
        'integrations'  => ['national_id_api', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['Service Delivery Time', 'Application Completion Rate', 'Citizen Satisfaction'],
        'synonyms'      => ['government system', 'public service', 'e-government', 'municipal system'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: CONSTRUCTION
    // ══════════════════════════════════════════════════════════════════════

    'construction' => [
        'label'         => 'Construction / Real Estate Development',
        'sector'        => 'construction',
        'functions'     => ['project_management', 'procurement', 'inventory', 'hrm', 'accounting', 'reporting'],
        'problems'      => ['project_delays', 'budget_overrun', 'material_wastage', 'contractor_management'],
        'roles'         => ['Admin', 'Project Manager', 'Site Engineer', 'Contractor', 'Accountant'],
        'workflows'     => ['project_planning', 'material_requisition', 'contractor_payment', 'progress_report'],
        'integrations'  => ['autocad_api', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['On-Time Completion %', 'Budget Variance', 'Material Wastage %', 'Safety Incidents'],
        'synonyms'      => ['construction management', 'project management system', 'contractor management', 'building management'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: MEDIA & ENTERTAINMENT
    // ══════════════════════════════════════════════════════════════════════

    'media' => [
        'label'         => 'Media / Publishing',
        'sector'        => 'media',
        'functions'     => ['content_management', 'subscriptions', 'advertising', 'crm', 'analytics'],
        'problems'      => ['content_workflow', 'subscription_churn', 'ad_revenue_tracking'],
        'roles'         => ['Admin', 'Editor', 'Journalist', 'Advertiser', 'Subscriber'],
        'workflows'     => ['content_creation', 'editorial_review', 'publishing', 'subscription_billing'],
        'integrations'  => ['wordpress', 'stripe', 'google_ads', 'facebook_ads'],
        'kpis'          => ['Page Views', 'Subscriber Count', 'Ad Revenue', 'Content Engagement'],
        'synonyms'      => ['news website', 'blog management', 'media company', 'publishing platform'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: TELECOM
    // ══════════════════════════════════════════════════════════════════════

    'telecom' => [
        'label'         => 'Telecom / ISP',
        'sector'        => 'telecom',
        'functions'     => ['customer_management', 'billing', 'support', 'network_management', 'crm'],
        'problems'      => ['billing_errors', 'churn', 'support_volume', 'network_complaints'],
        'roles'         => ['Admin', 'Customer Service', 'Network Engineer', 'Billing Officer', 'Customer'],
        'workflows'     => ['connection_activation', 'billing_cycle', 'support_ticket', 'disconnection'],
        'integrations'  => ['sms_gateway', 'payment_gateway', 'radius_server'],
        'kpis'          => ['Churn Rate', 'ARPU', 'First Call Resolution', 'Network Uptime'],
        'synonyms'      => ['ISP management', 'internet provider', 'telecom management', 'broadband management'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: ENERGY
    // ══════════════════════════════════════════════════════════════════════

    'energy' => [
        'label'         => 'Energy / Utility',
        'sector'        => 'energy',
        'functions'     => ['meter_management', 'billing', 'customer_management', 'maintenance', 'reporting'],
        'problems'      => ['meter_fraud', 'bill_defaults', 'maintenance_scheduling'],
        'roles'         => ['Admin', 'Meter Reader', 'Billing Officer', 'Customer', 'Technician'],
        'workflows'     => ['meter_reading', 'bill_generation', 'payment_collection', 'disconnection'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'iot_meters'],
        'kpis'          => ['Collection Rate', 'Loss Rate', 'Customer Satisfaction'],
        'synonyms'      => ['utility management', 'power distribution', 'energy management', 'electricity billing'],
    ],
];
