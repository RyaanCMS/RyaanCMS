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

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: HEALTHCARE EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'dental_clinic' => [
        'label'         => 'Dental Clinic',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'inventory'],
        'problems'      => ['appointment_no_shows', 'dental_record_management', 'billing_errors'],
        'roles'         => ['Admin', 'Dentist', 'Dental Assistant', 'Receptionist', 'Patient'],
        'workflows'     => ['patient_registration', 'appointment_booking', 'treatment', 'billing'],
        'integrations'  => ['sms_gateway', 'payment_gateway', 'xray_equipment'],
        'kpis'          => ['No-Show Rate', 'Patient Volume', 'Treatment Completion Rate', 'Revenue per Chair'],
        'synonyms'      => ['dental management', 'dentist software', 'dental office', 'orthodontics'],
    ],

    'eye_care' => [
        'label'         => 'Eye Care / Optical',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'inventory', 'billing', 'pos'],
        'problems'      => ['prescription_tracking', 'frame_inventory', 'lens_order_management'],
        'roles'         => ['Admin', 'Ophthalmologist', 'Optician', 'Receptionist', 'Patient'],
        'workflows'     => ['eye_exam', 'prescription_issue', 'frame_selection', 'lens_order', 'delivery'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'equipment_api'],
        'kpis'          => ['Patient Volume', 'Frame Sales', 'Return Rate', 'Revenue per Visit'],
        'synonyms'      => ['optical shop', 'eye clinic', 'vision center', 'optics management'],
    ],

    'gym_fitness' => [
        'label'         => 'Gym / Fitness Center',
        'sector'        => 'wellness',
        'functions'     => ['member_management', 'attendance', 'billing', 'crm', 'scheduling'],
        'problems'      => ['member_retention', 'attendance_tracking', 'equipment_maintenance', 'churn'],
        'roles'         => ['Admin', 'Manager', 'Trainer', 'Member', 'Receptionist'],
        'workflows'     => ['member_registration', 'class_booking', 'payment', 'trainer_assignment', 'renewal'],
        'integrations'  => ['payment_gateway', 'biometric', 'sms_gateway', 'access_control'],
        'kpis'          => ['Active Members', 'Member Retention Rate', 'Class Attendance', 'Revenue per Member'],
        'synonyms'      => ['gym management', 'fitness center', 'health club', 'sports center', 'crossfit'],
    ],

    'veterinary' => [
        'label'         => 'Veterinary Clinic',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'inventory', 'pharmacy'],
        'problems'      => ['pet_record_management', 'vaccine_schedule', 'billing'],
        'roles'         => ['Admin', 'Veterinarian', 'Vet Tech', 'Receptionist', 'Pet Owner'],
        'workflows'     => ['pet_registration', 'appointment', 'examination', 'treatment', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Patient Count', 'Appointment Fill Rate', 'Revenue per Visit'],
        'synonyms'      => ['vet clinic', 'animal hospital', 'pet clinic', 'pet care management'],
    ],

    'blood_bank' => [
        'label'         => 'Blood Bank',
        'sector'        => 'healthcare',
        'functions'     => ['inventory', 'donor_management', 'patient_management', 'reporting'],
        'problems'      => ['blood_shortage', 'expiry_management', 'donor_retention'],
        'roles'         => ['Admin', 'Lab Technician', 'Donor', 'Hospital', 'Blood Bank Officer'],
        'workflows'     => ['donor_registration', 'blood_donation', 'testing', 'storage', 'issue_to_hospital'],
        'integrations'  => ['hospital_api', 'sms_gateway'],
        'kpis'          => ['Blood Units Available', 'Expiry Loss', 'Donor Retention', 'Requests Fulfilled'],
        'synonyms'      => ['blood bank management', 'blood donation', 'blood inventory'],
    ],

    'rehabilitation' => [
        'label'         => 'Rehabilitation / Physiotherapy Center',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'treatment_plan'],
        'problems'      => ['treatment_progress_tracking', 'session_management', 'insurance_billing'],
        'roles'         => ['Admin', 'Physiotherapist', 'Patient', 'Receptionist'],
        'workflows'     => ['patient_intake', 'assessment', 'treatment_plan', 'sessions', 'discharge'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'insurance_api'],
        'kpis'          => ['Patient Recovery Rate', 'Session Completion', 'Revenue per Session'],
        'synonyms'      => ['physiotherapy', 'rehab center', 'physical therapy', 'occupational therapy'],
    ],

    'mental_health' => [
        'label'         => 'Mental Health Clinic / Counseling',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'reporting'],
        'problems'      => ['patient_confidentiality', 'session_notes', 'progress_tracking'],
        'roles'         => ['Admin', 'Psychiatrist', 'Counselor', 'Psychologist', 'Patient'],
        'workflows'     => ['intake_assessment', 'session_scheduling', 'progress_notes', 'treatment_review'],
        'integrations'  => ['payment_gateway', 'telehealth_api', 'sms_gateway'],
        'kpis'          => ['Patient Retention', 'Session Completion', 'Treatment Outcomes'],
        'synonyms'      => ['counseling center', 'psychology clinic', 'mental health management', 'therapy center'],
    ],

    'telemedicine' => [
        'label'         => 'Telemedicine / Online Doctor',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'billing', 'pharmacy'],
        'problems'      => ['connectivity', 'e_prescription', 'online_consultation'],
        'roles'         => ['Admin', 'Doctor', 'Nurse', 'Patient', 'Pharmacist'],
        'workflows'     => ['book_online', 'video_consult', 'e_prescription', 'online_payment'],
        'integrations'  => ['video_api', 'payment_gateway', 'sms_gateway', 'pharmacy_api'],
        'kpis'          => ['Consultations per Day', 'Patient Satisfaction', 'Prescription Rate'],
        'synonyms'      => ['telehealth', 'virtual clinic', 'online doctor', 'doctor on demand'],
    ],

    'nursing_home' => [
        'label'         => 'Nursing Home / Elder Care',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'billing', 'hrm', 'pharmacy', 'maintenance'],
        'problems'      => ['resident_care_tracking', 'staffing', 'billing'],
        'roles'         => ['Admin', 'Nurse', 'Caregiver', 'Doctor', 'Resident', 'Family'],
        'workflows'     => ['resident_admission', 'daily_care', 'medication', 'family_updates', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Occupancy Rate', 'Resident Satisfaction', 'Care Compliance'],
        'synonyms'      => ['elder care', 'old age home', 'care home', 'assisted living'],
    ],

    'ayurvedic_clinic' => [
        'label'         => 'Ayurvedic / Herbal Clinic',
        'sector'        => 'healthcare',
        'functions'     => ['patient_management', 'appointments', 'inventory', 'billing'],
        'problems'      => ['treatment_tracking', 'herbal_inventory', 'prescription_management'],
        'roles'         => ['Admin', 'Ayurvedic Doctor', 'Therapist', 'Patient', 'Receptionist'],
        'workflows'     => ['patient_registration', 'consultation', 'treatment', 'herbal_dispensing', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Patient Volume', 'Treatment Success Rate', 'Return Rate'],
        'synonyms'      => ['ayurveda clinic', 'herbal clinic', 'traditional medicine', 'homeopathy'],
    ],

    'spa' => [
        'label'         => 'Spa / Wellness Center',
        'sector'        => 'wellness',
        'functions'     => ['appointments', 'billing', 'crm', 'inventory', 'hrm'],
        'problems'      => ['appointment_management', 'therapist_scheduling', 'product_inventory'],
        'roles'         => ['Admin', 'Manager', 'Therapist', 'Receptionist', 'Customer'],
        'workflows'     => ['book_treatment', 'check_in', 'treatment', 'checkout', 'review'],
        'integrations'  => ['payment_gateway', 'booking_api', 'sms_gateway'],
        'kpis'          => ['Bookings per Day', 'Revenue per Treatment', 'Customer Retention', 'Utilization Rate'],
        'synonyms'      => ['spa management', 'wellness center', 'massage parlor', 'beauty spa'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: EDUCATION EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'lms' => [
        'label'         => 'LMS / E-Learning Platform',
        'sector'        => 'education',
        'functions'     => ['course_management', 'student_management', 'payment', 'analytics', 'subscription'],
        'problems'      => ['course_completion', 'student_engagement', 'video_streaming', 'quiz_management'],
        'roles'         => ['Admin', 'Instructor', 'Student', 'Finance'],
        'workflows'     => ['create_course', 'enroll', 'study', 'quiz', 'certificate', 'review'],
        'integrations'  => ['stripe', 'zoom', 'vimeo', 'youtube', 'payment_gateway'],
        'kpis'          => ['Course Completion Rate', 'Student Satisfaction', 'Enrollment Count', 'Revenue per Course'],
        'synonyms'      => ['learning management system', 'online course', 'e-learning', 'MOOC', 'online academy'],
    ],

    'library_management' => [
        'label'         => 'Library Management',
        'sector'        => 'education',
        'functions'     => ['catalog', 'member_management', 'circulation', 'reporting'],
        'problems'      => ['book_tracking', 'overdue_returns', 'catalog_management'],
        'roles'         => ['Admin', 'Librarian', 'Member', 'Student'],
        'workflows'     => ['catalog_book', 'issue_book', 'return_book', 'fine_collection', 'reserve'],
        'integrations'  => ['barcode', 'sms_gateway', 'rfid'],
        'kpis'          => ['Books Issued per Day', 'Overdue Rate', 'Collection Size', 'Member Utilization'],
        'synonyms'      => ['library system', 'book management', 'circulation system', 'library software'],
    ],

    'kindergarten' => [
        'label'         => 'Kindergarten / Daycare',
        'sector'        => 'education',
        'functions'     => ['student_management', 'attendance', 'fees', 'communication'],
        'problems'      => ['child_safety', 'parent_communication', 'daily_activity_reports'],
        'roles'         => ['Admin', 'Teacher', 'Child', 'Parent'],
        'workflows'     => ['admission', 'daily_checkin', 'activity_report', 'fee_collection', 'parent_update'],
        'integrations'  => ['sms_gateway', 'whatsapp', 'payment_gateway'],
        'kpis'          => ['Enrollment', 'Parent Satisfaction', 'Attendance Rate', 'Fee Collection Rate'],
        'synonyms'      => ['daycare management', 'preschool management', 'nursery management', 'child care center'],
    ],

    'language_school' => [
        'label'         => 'Language School',
        'sector'        => 'education',
        'functions'     => ['student_management', 'batch_management', 'fees', 'attendance', 'results'],
        'problems'      => ['batch_management', 'level_assessment', 'teacher_scheduling'],
        'roles'         => ['Admin', 'Teacher', 'Student'],
        'workflows'     => ['enrollment', 'level_test', 'class_schedule', 'progress_assessment', 'certificate'],
        'integrations'  => ['zoom', 'sms_gateway', 'payment_gateway'],
        'kpis'          => ['Student Retention', 'Pass Rate', 'Level Progression Rate'],
        'synonyms'      => ['language institute', 'English school', 'language training', 'IELTS center'],
    ],

    'driving_school' => [
        'label'         => 'Driving School',
        'sector'        => 'education',
        'functions'     => ['student_management', 'scheduling', 'fees', 'instructor', 'reporting'],
        'problems'      => ['lesson_scheduling', 'instructor_availability', 'license_test_tracking'],
        'roles'         => ['Admin', 'Instructor', 'Student'],
        'workflows'     => ['enrollment', 'lesson_booking', 'lesson_completion', 'test_registration', 'license'],
        'integrations'  => ['sms_gateway', 'payment_gateway', 'calendar'],
        'kpis'          => ['Pass Rate', 'Lesson Completion', 'Student Volume'],
        'synonyms'      => ['driving institute', 'driver training', 'motoring school'],
    ],

    'vocational_training' => [
        'label'         => 'Vocational / Skill Training Center',
        'sector'        => 'education',
        'functions'     => ['student_management', 'batch_management', 'fees', 'attendance', 'certificate_management'],
        'problems'      => ['placement_tracking', 'batch_scheduling', 'certification'],
        'roles'         => ['Admin', 'Trainer', 'Student', 'Employer'],
        'workflows'     => ['enrollment', 'batch_assignment', 'training', 'assessment', 'placement'],
        'integrations'  => ['sms_gateway', 'payment_gateway', 'job_portal_api'],
        'kpis'          => ['Placement Rate', 'Completion Rate', 'Employer Satisfaction'],
        'synonyms'      => ['trade school', 'skill center', 'TVET', 'technical training'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: FOOD EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'bakery' => [
        'label'         => 'Bakery / Confectionery',
        'sector'        => 'food',
        'functions'     => ['pos', 'inventory', 'production', 'sales', 'accounting'],
        'problems'      => ['production_planning', 'expiry_waste', 'order_management'],
        'roles'         => ['Admin', 'Baker', 'Cashier', 'Customer', 'Delivery'],
        'workflows'     => ['production_order', 'baking', 'display', 'sale', 'delivery'],
        'integrations'  => ['payment_gateway', 'whatsapp', 'delivery_api'],
        'kpis'          => ['Daily Sales', 'Waste %', 'Custom Order Rate', 'Production Efficiency'],
        'synonyms'      => ['bakery management', 'cake shop', 'patisserie', 'bread shop'],
    ],

    'grocery' => [
        'label'         => 'Grocery / General Store',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'procurement', 'accounting', 'crm'],
        'problems'      => ['expiry_management', 'stockout', 'price_management', 'credit_customers'],
        'roles'         => ['Admin', 'Shop Owner', 'Staff', 'Customer'],
        'workflows'     => ['stock_receipt', 'daily_sales', 'credit_management', 'reorder'],
        'integrations'  => ['bkash', 'nagad', 'payment_gateway', 'supplier_api'],
        'kpis'          => ['Daily Sales', 'Shrinkage Rate', 'Credit Recovery Rate', 'Stock Turnover'],
        'synonyms'      => ['grocery store', 'general store', 'kirana', 'mini mart', 'provision store'],
    ],

    'supermarket' => [
        'label'         => 'Supermarket / Hypermarket',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'procurement', 'crm', 'accounting', 'hrm'],
        'problems'      => ['inventory_mismatch', 'shrinkage', 'cashier_management', 'expiry'],
        'roles'         => ['Admin', 'Store Manager', 'Cashier', 'Stock Boy', 'Customer'],
        'workflows'     => ['stock_receiving', 'shelf_stocking', 'pos_sale', 'daily_reconciliation', 'reorder'],
        'integrations'  => ['barcode', 'payment_gateway', 'loyalty_api', 'supplier_api'],
        'kpis'          => ['Basket Size', 'Footfall', 'Shrinkage %', 'Revenue per SqFt'],
        'synonyms'      => ['supermarket management', 'hypermarket', 'chain store', 'department store'],
    ],

    'food_delivery' => [
        'label'         => 'Food Delivery Platform',
        'sector'        => 'food',
        'functions'     => ['orders', 'vendor_management', 'tracking', 'payment', 'crm'],
        'problems'      => ['delivery_delays', 'order_accuracy', 'driver_management', 'restaurant_onboarding'],
        'roles'         => ['Admin', 'Restaurant Owner', 'Delivery Driver', 'Customer'],
        'workflows'     => ['place_order', 'restaurant_accept', 'prepare', 'assign_driver', 'deliver', 'rate'],
        'integrations'  => ['google_maps', 'payment_gateway', 'sms_gateway', 'push_notification'],
        'kpis'          => ['Orders per Day', 'Delivery Time', 'Order Accuracy Rate', 'Customer Retention'],
        'synonyms'      => ['food delivery', 'meal delivery', 'food ordering', 'online food order'],
    ],

    'catering' => [
        'label'         => 'Catering Service',
        'sector'        => 'food',
        'functions'     => ['orders', 'production', 'inventory', 'billing', 'crm'],
        'problems'      => ['event_planning', 'food_quantity_estimation', 'staff_deployment'],
        'roles'         => ['Admin', 'Manager', 'Chef', 'Service Staff', 'Client'],
        'workflows'     => ['quote_request', 'menu_planning', 'food_preparation', 'delivery', 'billing'],
        'integrations'  => ['payment_gateway', 'whatsapp', 'sms_gateway'],
        'kpis'          => ['Events per Month', 'Repeat Client Rate', 'Waste %', 'Profit per Event'],
        'synonyms'      => ['catering management', 'event catering', 'food service management'],
    ],

    'fast_food' => [
        'label'         => 'Fast Food / Quick Service Restaurant',
        'sector'        => 'food',
        'functions'     => ['pos', 'orders', 'inventory', 'kitchen', 'accounting'],
        'problems'      => ['order_speed', 'food_consistency', 'inventory_management'],
        'roles'         => ['Admin', 'Manager', 'Cashier', 'Kitchen Staff', 'Customer'],
        'workflows'     => ['take_order', 'kitchen_preparation', 'serve', 'payment'],
        'integrations'  => ['pos_hardware', 'payment_gateway', 'kiosk', 'delivery_api'],
        'kpis'          => ['Orders per Hour', 'Average Service Time', 'Waste %', 'Daily Revenue'],
        'synonyms'      => ['QSR', 'quick service restaurant', 'fast food management', 'food kiosk'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: RETAIL STORES EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'jewelry_store' => [
        'label'         => 'Jewelry / Gold Shop',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'accounting', 'crm', 'valuation'],
        'problems'      => ['gold_rate_management', 'old_gold_purchase', 'stock_security'],
        'roles'         => ['Admin', 'Sales Staff', 'Goldsmith', 'Customer', 'Accountant'],
        'workflows'     => ['gold_rate_update', 'sale', 'old_gold_purchase', 'making_order', 'billing'],
        'integrations'  => ['gold_rate_api', 'payment_gateway', 'barcode'],
        'kpis'          => ['Daily Sales', 'Gold Turnover', 'Old Gold Margin', 'Making Charge Revenue'],
        'synonyms'      => ['gold shop management', 'jewellery shop', 'jewelry management', 'gem store'],
    ],

    'electronics_store' => [
        'label'         => 'Electronics / Gadget Store',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'accounting', 'crm', 'warranty'],
        'problems'      => ['warranty_tracking', 'serial_number_management', 'price_updates'],
        'roles'         => ['Admin', 'Sales Staff', 'Technician', 'Customer'],
        'workflows'     => ['sale', 'warranty_registration', 'return', 'repair', 'billing'],
        'integrations'  => ['payment_gateway', 'barcode', 'warranty_api'],
        'kpis'          => ['Sales per Brand', 'Warranty Claim Rate', 'Return Rate', 'Revenue'],
        'synonyms'      => ['electronics shop', 'gadget store', 'mobile shop', 'computer shop'],
    ],

    'furniture_store' => [
        'label'         => 'Furniture / Home Decor Store',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'orders', 'accounting', 'crm'],
        'problems'      => ['custom_order_tracking', 'delivery_management', 'large_item_inventory'],
        'roles'         => ['Admin', 'Sales Staff', 'Delivery Team', 'Customer'],
        'workflows'     => ['showroom_sale', 'custom_order', 'delivery_scheduling', 'installation', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Sales Volume', 'Custom Order Rate', 'Delivery On-Time', 'Revenue'],
        'synonyms'      => ['furniture shop', 'home decor store', 'interior store', 'furniture management'],
    ],

    'clothing_store' => [
        'label'         => 'Clothing / Fashion Store',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'accounting', 'crm', 'marketing'],
        'problems'      => ['size_variant_management', 'seasonal_stock', 'return_management'],
        'roles'         => ['Admin', 'Store Manager', 'Sales Staff', 'Customer'],
        'workflows'     => ['receive_stock', 'display', 'sale', 'return', 'promotion'],
        'integrations'  => ['payment_gateway', 'barcode', 'sms_gateway'],
        'kpis'          => ['Daily Sales', 'Return Rate', 'Stock Turnover', 'Average Basket'],
        'synonyms'      => ['boutique management', 'fashion store', 'apparel store', 'garment store'],
    ],

    'hardware_store' => [
        'label'         => 'Hardware / Building Materials Store',
        'sector'        => 'retail',
        'functions'     => ['pos', 'inventory', 'procurement', 'accounting', 'crm'],
        'problems'      => ['unit_conversion', 'credit_customer_management', 'bulk_order'],
        'roles'         => ['Admin', 'Sales Staff', 'Store Keeper', 'Customer', 'Accountant'],
        'workflows'     => ['stock_receipt', 'sale', 'credit_sale', 'bulk_delivery', 'reorder'],
        'integrations'  => ['payment_gateway', 'bkash', 'nagad'],
        'kpis'          => ['Daily Sales', 'Credit Recovery Rate', 'Stock Value', 'Gross Margin'],
        'synonyms'      => ['hardware shop', 'building materials', 'ironmongery', 'construction supplies'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: LIFESTYLE & SERVICE BUSINESSES
    // ══════════════════════════════════════════════════════════════════════

    'beauty_salon' => [
        'label'         => 'Beauty Salon / Parlour',
        'sector'        => 'wellness',
        'functions'     => ['appointments', 'billing', 'crm', 'inventory', 'hrm'],
        'problems'      => ['appointment_management', 'staff_commission', 'product_inventory'],
        'roles'         => ['Admin', 'Manager', 'Beautician', 'Customer'],
        'workflows'     => ['appointment_booking', 'check_in', 'service', 'billing', 'commission_calc'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'booking_app'],
        'kpis'          => ['Appointments per Day', 'Revenue per Stylist', 'Customer Retention', 'No-Show Rate'],
        'synonyms'      => ['beauty parlour', 'hair salon', 'nail salon', 'beauty center', 'parlor management'],
    ],

    'barbershop' => [
        'label'         => 'Barbershop / Men\'s Grooming',
        'sector'        => 'wellness',
        'functions'     => ['appointments', 'billing', 'crm', 'hrm'],
        'problems'      => ['queue_management', 'appointment_no_shows', 'revenue_tracking'],
        'roles'         => ['Admin', 'Barber', 'Customer'],
        'workflows'     => ['walk_in_queue', 'appointment_booking', 'service', 'payment'],
        'integrations'  => ['payment_gateway', 'booking_app', 'sms_gateway'],
        'kpis'          => ['Customers per Day', 'Revenue per Chair', 'Appointment Rate', 'Repeat Rate'],
        'synonyms'      => ['barber shop', 'barber management', 'men salon', 'grooming center'],
    ],

    'laundry' => [
        'label'         => 'Laundry / Dry Cleaning',
        'sector'        => 'services',
        'functions'     => ['orders', 'billing', 'crm', 'delivery', 'inventory'],
        'problems'      => ['order_tracking', 'garment_tagging', 'delivery_management'],
        'roles'         => ['Admin', 'Manager', 'Counter Staff', 'Delivery Staff', 'Customer'],
        'workflows'     => ['receive_clothes', 'tag_items', 'process', 'ready_notify', 'deliver', 'billing'],
        'integrations'  => ['sms_gateway', 'payment_gateway', 'delivery_tracking'],
        'kpis'          => ['Orders per Day', 'Turnaround Time', 'Delivery Rate', 'Lost Item Rate'],
        'synonyms'      => ['laundry management', 'dry cleaning', 'laundromat', 'wash and fold'],
    ],

    'tailoring' => [
        'label'         => 'Tailoring / Custom Clothing',
        'sector'        => 'services',
        'functions'     => ['orders', 'billing', 'crm', 'inventory', 'production'],
        'problems'      => ['measurement_management', 'delivery_tracking', 'fabric_inventory'],
        'roles'         => ['Admin', 'Master Tailor', 'Tailor', 'Customer'],
        'workflows'     => ['take_measurement', 'fabric_selection', 'production', 'fitting', 'delivery', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Orders per Month', 'On-Time Delivery', 'Rework Rate', 'Revenue per Order'],
        'synonyms'      => ['tailor shop', 'custom clothing', 'alteration shop', 'stitch management'],
    ],

    'photography_studio' => [
        'label'         => 'Photography / Videography Studio',
        'sector'        => 'creative',
        'functions'     => ['booking', 'crm', 'billing', 'project_management'],
        'problems'      => ['booking_conflicts', 'photo_delivery', 'album_management'],
        'roles'         => ['Admin', 'Photographer', 'Videographer', 'Editor', 'Client'],
        'workflows'     => ['inquiry', 'booking', 'shoot', 'editing', 'delivery', 'billing'],
        'integrations'  => ['payment_gateway', 'google_drive', 'dropbox', 'whatsapp'],
        'kpis'          => ['Bookings per Month', 'Delivery Time', 'Client Satisfaction', 'Revenue'],
        'synonyms'      => ['photo studio management', 'photography business', 'videography management'],
    ],

    'event_management' => [
        'label'         => 'Event Management',
        'sector'        => 'services',
        'functions'     => ['booking', 'crm', 'billing', 'vendor_management', 'project_management'],
        'problems'      => ['vendor_coordination', 'budget_tracking', 'event_timeline'],
        'roles'         => ['Admin', 'Event Manager', 'Coordinator', 'Vendor', 'Client'],
        'workflows'     => ['event_inquiry', 'proposal', 'vendor_booking', 'event_day', 'post_event', 'billing'],
        'integrations'  => ['payment_gateway', 'whatsapp', 'sms_gateway', 'google_calendar'],
        'kpis'          => ['Events per Month', 'Client Satisfaction', 'Budget Variance', 'Repeat Clients'],
        'synonyms'      => ['event planner', 'wedding management', 'corporate events', 'party management'],
    ],

    'travel_agency' => [
        'label'         => 'Travel Agency / Tour Operator',
        'sector'        => 'travel',
        'functions'     => ['booking', 'crm', 'billing', 'vendor_management', 'reporting'],
        'problems'      => ['visa_tracking', 'package_customization', 'supplier_management'],
        'roles'         => ['Admin', 'Travel Agent', 'Tour Manager', 'Customer'],
        'workflows'     => ['inquiry', 'quotation', 'booking_confirmation', 'visa_processing', 'travel', 'feedback'],
        'integrations'  => ['flight_api', 'hotel_api', 'payment_gateway', 'visa_api'],
        'kpis'          => ['Bookings per Month', 'Revenue per Package', 'Repeat Rate', 'Visa Approval Rate'],
        'synonyms'      => ['travel management', 'tour operator', 'travel business', 'holiday management'],
    ],

    'car_rental' => [
        'label'         => 'Car Rental / Vehicle Hire',
        'sector'        => 'transport',
        'functions'     => ['fleet', 'booking', 'billing', 'crm', 'maintenance'],
        'problems'      => ['vehicle_availability', 'damage_tracking', 'fuel_management'],
        'roles'         => ['Admin', 'Fleet Manager', 'Driver', 'Customer'],
        'workflows'     => ['book_vehicle', 'vehicle_handover', 'rental_period', 'return', 'inspect', 'billing'],
        'integrations'  => ['payment_gateway', 'google_maps', 'sms_gateway'],
        'kpis'          => ['Fleet Utilization', 'Revenue per Vehicle per Day', 'Damage Rate'],
        'synonyms'      => ['car hire', 'vehicle rental', 'car rental management', 'rent a car'],
    ],

    'auto_repair' => [
        'label'         => 'Auto Repair / Car Workshop',
        'sector'        => 'services',
        'functions'     => ['orders', 'inventory', 'billing', 'crm', 'hrm'],
        'problems'      => ['job_card_management', 'parts_inventory', 'technician_assignment'],
        'roles'         => ['Admin', 'Service Advisor', 'Technician', 'Parts Manager', 'Customer'],
        'workflows'     => ['vehicle_checkin', 'job_card', 'parts_requisition', 'repair', 'qc', 'delivery', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'parts_supplier_api'],
        'kpis'          => ['Job Cards per Day', 'TAT', 'Customer Satisfaction', 'Revenue per Job'],
        'synonyms'      => ['workshop management', 'garage management', 'car service management', 'auto service'],
    ],

    'printing_press' => [
        'label'         => 'Printing Press / Print Shop',
        'sector'        => 'services',
        'functions'     => ['orders', 'production', 'inventory', 'billing', 'crm'],
        'problems'      => ['job_tracking', 'material_management', 'artwork_approval'],
        'roles'         => ['Admin', 'Sales', 'Prepress', 'Machine Operator', 'Customer'],
        'workflows'     => ['order_intake', 'artwork_approval', 'prepress', 'printing', 'finishing', 'delivery', 'billing'],
        'integrations'  => ['payment_gateway', 'file_storage', 'sms_gateway'],
        'kpis'          => ['Jobs per Day', 'Material Waste %', 'Delivery On-Time', 'Revenue'],
        'synonyms'      => ['print shop management', 'printing management', 'digital printing', 'offset printing'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: PROFESSIONAL SERVICES
    // ══════════════════════════════════════════════════════════════════════

    'law_firm' => [
        'label'         => 'Law Firm / Legal Services',
        'sector'        => 'professional',
        'functions'     => ['case_management', 'crm', 'billing', 'document_management', 'time_tracking'],
        'problems'      => ['case_deadline_tracking', 'client_billing', 'document_management'],
        'roles'         => ['Admin', 'Senior Lawyer', 'Associate', 'Paralegal', 'Client'],
        'workflows'     => ['client_intake', 'case_opening', 'hearing_tracking', 'document_filing', 'billing'],
        'integrations'  => ['payment_gateway', 'email', 'court_api', 'document_storage'],
        'kpis'          => ['Cases Won', 'Billable Hours', 'Client Retention', 'Case Closure Time'],
        'synonyms'      => ['legal management', 'law practice management', 'legal case management', 'attorney software'],
    ],

    'audit_firm' => [
        'label'         => 'Audit Firm / CA Firm',
        'sector'        => 'professional',
        'functions'     => ['case_management', 'crm', 'billing', 'document_management', 'reporting'],
        'problems'      => ['deadline_management', 'client_document_collection', 'team_assignment'],
        'roles'         => ['Admin', 'Partner', 'Manager', 'Auditor', 'Client'],
        'workflows'     => ['client_onboarding', 'document_collection', 'audit_fieldwork', 'review', 'report_delivery'],
        'integrations'  => ['payment_gateway', 'email', 'document_storage', 'accounting_api'],
        'kpis'          => ['Client Count', 'On-Time Delivery', 'Billable Hours', 'Client Satisfaction'],
        'synonyms'      => ['accounting firm', 'CA firm', 'audit management', 'chartered accountant'],
    ],

    'recruitment_agency' => [
        'label'         => 'Recruitment Agency / HR Consultancy',
        'sector'        => 'professional',
        'functions'     => ['recruitment', 'crm', 'billing', 'reporting'],
        'problems'      => ['candidate_sourcing', 'client_matching', 'placement_tracking'],
        'roles'         => ['Admin', 'Recruiter', 'Account Manager', 'Client', 'Candidate'],
        'workflows'     => ['job_requirement', 'sourcing', 'screening', 'interview', 'placement', 'billing'],
        'integrations'  => ['linkedin', 'job_portals', 'email', 'payment_gateway'],
        'kpis'          => ['Placement Rate', 'Time to Fill', 'Client Satisfaction', 'Revenue per Placement'],
        'synonyms'      => ['staffing agency', 'headhunter', 'placement agency', 'talent acquisition'],
    ],

    'digital_marketing' => [
        'label'         => 'Digital Marketing Agency',
        'sector'        => 'professional',
        'functions'     => ['project_management', 'crm', 'billing', 'reporting', 'time_tracking'],
        'problems'      => ['campaign_reporting', 'client_approval', 'team_productivity'],
        'roles'         => ['Admin', 'Account Manager', 'Designer', 'Content Writer', 'Developer', 'Client'],
        'workflows'     => ['client_brief', 'strategy', 'content_creation', 'approval', 'publish', 'report'],
        'integrations'  => ['google_ads', 'facebook_ads', 'analytics', 'payment_gateway', 'slack'],
        'kpis'          => ['Client ROI', 'Campaign Performance', 'Retainer Retention', 'Team Utilization'],
        'synonyms'      => ['marketing agency', 'digital agency', 'SEO agency', 'social media agency'],
    ],

    'it_company' => [
        'label'         => 'IT Company / Software House',
        'sector'        => 'technology',
        'functions'     => ['project_management', 'crm', 'billing', 'hrm', 'time_tracking'],
        'problems'      => ['project_delivery', 'resource_allocation', 'scope_creep', 'billing'],
        'roles'         => ['Admin', 'Project Manager', 'Developer', 'Designer', 'QA', 'Client'],
        'workflows'     => ['requirements', 'sprint_planning', 'development', 'testing', 'deployment', 'billing'],
        'integrations'  => ['jira', 'github', 'slack', 'stripe', 'time_tracking_api'],
        'kpis'          => ['Project Delivery Rate', 'Defect Rate', 'Client Satisfaction', 'Revenue per Developer'],
        'synonyms'      => ['software company', 'software house', 'tech startup', 'IT firm', 'web agency'],
    ],

    'cleaning_service' => [
        'label'         => 'Cleaning / Housekeeping Service',
        'sector'        => 'services',
        'functions'     => ['booking', 'hrm', 'billing', 'crm', 'scheduling'],
        'problems'      => ['staff_scheduling', 'quality_control', 'equipment_management'],
        'roles'         => ['Admin', 'Manager', 'Cleaner', 'Supervisor', 'Client'],
        'workflows'     => ['service_request', 'assign_team', 'service_delivery', 'quality_check', 'billing'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'scheduling_api'],
        'kpis'          => ['Jobs per Day', 'Client Satisfaction', 'Staff Productivity', 'Repeat Rate'],
        'synonyms'      => ['cleaning company', 'maid service', 'facility management', 'janitorial service'],
    ],

    'security_service' => [
        'label'         => 'Security Service',
        'sector'        => 'services',
        'functions'     => ['hrm', 'scheduling', 'billing', 'crm', 'reporting'],
        'problems'      => ['guard_scheduling', 'attendance_tracking', 'incident_management'],
        'roles'         => ['Admin', 'Operations Manager', 'Guard Supervisor', 'Guard', 'Client'],
        'workflows'     => ['client_contract', 'roster_planning', 'guard_deployment', 'incident_report', 'billing'],
        'integrations'  => ['biometric', 'sms_gateway', 'payment_gateway'],
        'kpis'          => ['Guard Deployment Rate', 'Incident Response Time', 'Client Retention'],
        'synonyms'      => ['security company', 'guard management', 'security agency', 'facility security'],
    ],

    'pest_control' => [
        'label'         => 'Pest Control Service',
        'sector'        => 'services',
        'functions'     => ['booking', 'crm', 'billing', 'scheduling', 'inventory'],
        'problems'      => ['appointment_management', 'chemical_inventory', 'treatment_records'],
        'roles'         => ['Admin', 'Manager', 'Technician', 'Customer'],
        'workflows'     => ['service_request', 'inspection', 'treatment_plan', 'service', 'follow_up', 'billing'],
        'integrations'  => ['sms_gateway', 'payment_gateway'],
        'kpis'          => ['Jobs per Month', 'Follow-up Completion Rate', 'Customer Satisfaction'],
        'synonyms'      => ['pest control management', 'exterminator', 'fumigation service'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: MANUFACTURING EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'food_processing' => [
        'label'         => 'Food Processing / Packaged Food',
        'sector'        => 'manufacturing',
        'functions'     => ['production', 'inventory', 'procurement', 'quality', 'accounting', 'export'],
        'problems'      => ['batch_traceability', 'expiry_management', 'compliance', 'cold_chain'],
        'roles'         => ['Admin', 'Production Manager', 'QC Inspector', 'Procurement', 'Distribution'],
        'workflows'     => ['raw_material_receipt', 'processing', 'packaging', 'quality_check', 'dispatch'],
        'integrations'  => ['barcode', 'cold_chain_api', 'export_compliance'],
        'kpis'          => ['Yield %', 'Rejection Rate', 'Batch Traceability', 'Expiry Loss'],
        'synonyms'      => ['food manufacturing', 'FMCG', 'packaged food', 'food factory'],
    ],

    'textile_factory' => [
        'label'         => 'Textile / Yarn / Fabric Factory',
        'sector'        => 'manufacturing',
        'functions'     => ['production', 'inventory', 'procurement', 'quality', 'accounting'],
        'problems'      => ['yarn_inventory', 'weaving_efficiency', 'quality_defects'],
        'roles'         => ['Admin', 'Production Manager', 'Quality Inspector', 'Weaver', 'Accountant'],
        'workflows'     => ['yarn_receipt', 'dyeing', 'weaving', 'quality_check', 'dispatch'],
        'integrations'  => ['barcode', 'erp_api'],
        'kpis'          => ['Production Efficiency', 'Defect Rate', 'Yarn Utilization', 'On-Time Delivery'],
        'synonyms'      => ['textile management', 'fabric manufacturing', 'weaving factory', 'yarn factory'],
    ],

    'cold_storage' => [
        'label'         => 'Cold Storage / Warehouse',
        'sector'        => 'manufacturing',
        'functions'     => ['inventory', 'billing', 'crm', 'reporting', 'maintenance'],
        'problems'      => ['temperature_monitoring', 'stock_management', 'expiry_tracking'],
        'roles'         => ['Admin', 'Manager', 'Store Keeper', 'Client'],
        'workflows'     => ['stock_intake', 'storage', 'retrieval', 'dispatch', 'billing'],
        'integrations'  => ['iot_sensors', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['Capacity Utilization', 'Temperature Compliance', 'Stock Accuracy'],
        'synonyms'      => ['cold storage management', 'refrigerated warehouse', 'frozen storage'],
    ],

    'poultry_farm' => [
        'label'         => 'Poultry Farm',
        'sector'        => 'agriculture',
        'functions'     => ['inventory', 'production', 'sales', 'accounting', 'procurement'],
        'problems'      => ['mortality_tracking', 'feed_management', 'disease_management'],
        'roles'         => ['Admin', 'Farm Manager', 'Worker', 'Veterinarian', 'Buyer'],
        'workflows'     => ['chick_purchase', 'feeding_schedule', 'vaccination', 'harvest', 'sale'],
        'integrations'  => ['market_price_api', 'sms_gateway', 'vet_api'],
        'kpis'          => ['FCR (Feed Conversion Ratio)', 'Mortality Rate', 'Revenue per Bird', 'Weight at Harvest'],
        'synonyms'      => ['poultry management', 'chicken farm', 'broiler farm', 'layer farm'],
    ],

    'dairy_farm' => [
        'label'         => 'Dairy Farm',
        'sector'        => 'agriculture',
        'functions'     => ['inventory', 'production', 'sales', 'accounting', 'procurement'],
        'problems'      => ['milk_yield_tracking', 'cattle_health', 'feed_management'],
        'roles'         => ['Admin', 'Farm Manager', 'Worker', 'Veterinarian', 'Buyer'],
        'workflows'     => ['milking', 'quality_test', 'chilling', 'dispatch', 'cattle_health_check'],
        'integrations'  => ['market_price_api', 'sms_gateway'],
        'kpis'          => ['Milk Yield per Cow', 'Fat %', 'Cattle Health Rate', 'Revenue per Liter'],
        'synonyms'      => ['dairy management', 'cattle farm', 'milk production', 'dairy business'],
    ],

    'rice_mill' => [
        'label'         => 'Rice Mill / Flour Mill',
        'sector'        => 'manufacturing',
        'functions'     => ['inventory', 'production', 'procurement', 'sales', 'accounting'],
        'problems'      => ['paddy_procurement', 'milling_efficiency', 'bran_byproduct'],
        'roles'         => ['Admin', 'Mill Manager', 'Operator', 'Accountant', 'Buyer'],
        'workflows'     => ['paddy_purchase', 'milling', 'grading', 'bagging', 'sale'],
        'integrations'  => ['market_price_api', 'payment_gateway'],
        'kpis'          => ['Milling Yield %', 'Wastage %', 'Revenue per Ton', 'Procurement Cost'],
        'synonyms'      => ['rice mill management', 'flour mill', 'grain processing', 'paddy processing'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: FINANCE EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'bank_nbfi' => [
        'label'         => 'Bank / NBFI / Financial Institution',
        'sector'        => 'finance',
        'functions'     => ['loan_management', 'member_management', 'accounting', 'crm', 'reporting'],
        'problems'      => ['kyc_compliance', 'loan_defaults', 'regulatory_reporting'],
        'roles'         => ['Admin', 'Branch Manager', 'Loan Officer', 'Teller', 'Customer', 'Compliance Officer'],
        'workflows'     => ['account_opening', 'kyc', 'loan_application', 'disbursement', 'collection', 'reporting'],
        'integrations'  => ['central_bank_api', 'credit_bureau', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['NPL Ratio', 'Capital Adequacy Ratio', 'Collection Rate', 'Customer Growth'],
        'synonyms'      => ['bank management', 'NBFI', 'finance company', 'credit institution', 'financial institution'],
    ],

    'cooperative_society' => [
        'label'         => 'Cooperative Society / Credit Union',
        'sector'        => 'finance',
        'functions'     => ['member_management', 'loan_management', 'accounting', 'savings', 'reporting'],
        'problems'      => ['share_management', 'dividend_calculation', 'loan_recovery'],
        'roles'         => ['Admin', 'Manager', 'Accountant', 'Member', 'Auditor'],
        'workflows'     => ['member_registration', 'share_purchase', 'savings_deposit', 'loan_application', 'dividend'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'bkash', 'nagad'],
        'kpis'          => ['Active Members', 'Loan Recovery Rate', 'Savings Growth', 'Dividend Rate'],
        'synonyms'      => ['cooperative management', 'samabay', 'credit union', 'thrift society'],
    ],

    'money_exchange' => [
        'label'         => 'Money Exchange / Forex',
        'sector'        => 'finance',
        'functions'     => ['pos', 'accounting', 'crm', 'compliance', 'reporting'],
        'problems'      => ['exchange_rate_management', 'kyc_compliance', 'audit_trail'],
        'roles'         => ['Admin', 'Teller', 'Compliance Officer', 'Customer'],
        'workflows'     => ['rate_update', 'transaction', 'kyc_check', 'receipt', 'daily_close'],
        'integrations'  => ['exchange_rate_api', 'payment_gateway', 'sms_gateway'],
        'kpis'          => ['Daily Volume', 'Margin per Transaction', 'Compliance Rate'],
        'synonyms'      => ['forex management', 'currency exchange', 'remittance', 'money transfer'],
    ],

    'crowdfunding' => [
        'label'         => 'Crowdfunding Platform',
        'sector'        => 'finance',
        'functions'     => ['campaign_management', 'payment', 'crm', 'analytics', 'reporting'],
        'problems'      => ['fraud_prevention', 'payment_distribution', 'campaign_verification'],
        'roles'         => ['Admin', 'Campaign Creator', 'Backer', 'Compliance Officer'],
        'workflows'     => ['campaign_submission', 'verification', 'launch', 'funding', 'milestone_update', 'payout'],
        'integrations'  => ['payment_gateway', 'email', 'social_media_api', 'kyc_api'],
        'kpis'          => ['Campaigns Funded', 'Average Funding %', 'Backer Count', 'Platform Revenue'],
        'synonyms'      => ['crowdfunding management', 'campaign funding', 'donation platform', 'equity crowdfunding'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: TRANSPORT EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'bus_terminal' => [
        'label'         => 'Bus Terminal / Intercity Bus',
        'sector'        => 'transport',
        'functions'     => ['booking', 'fleet', 'billing', 'crm', 'reporting'],
        'problems'      => ['seat_booking', 'route_management', 'driver_management'],
        'roles'         => ['Admin', 'Counter Staff', 'Driver', 'Conductor', 'Passenger'],
        'workflows'     => ['seat_booking', 'ticket_issue', 'boarding', 'trip_completion', 'accounting'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'bkash', 'nagad'],
        'kpis'          => ['Seat Occupancy', 'On-Time Departure', 'Revenue per Route'],
        'synonyms'      => ['bus management', 'bus ticketing', 'coach management', 'intercity transport'],
    ],

    'parking_management' => [
        'label'         => 'Parking Management',
        'sector'        => 'transport',
        'functions'     => ['booking', 'billing', 'fleet', 'reporting'],
        'problems'      => ['slot_availability', 'overstay_management', 'payment_collection'],
        'roles'         => ['Admin', 'Parking Attendant', 'Manager', 'Vehicle Owner'],
        'workflows'     => ['vehicle_entry', 'slot_assignment', 'payment', 'vehicle_exit'],
        'integrations'  => ['payment_gateway', 'lpr_camera', 'sms_gateway', 'barrier_api'],
        'kpis'          => ['Occupancy Rate', 'Revenue per Hour', 'Turnaround Time'],
        'synonyms'      => ['parking system', 'parking lot management', 'multi-story parking'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: COMMUNITY & SOCIAL
    // ══════════════════════════════════════════════════════════════════════

    'sports_club' => [
        'label'         => 'Sports Club / Academy',
        'sector'        => 'sports',
        'functions'     => ['member_management', 'scheduling', 'billing', 'facilities', 'reporting'],
        'problems'      => ['court_booking', 'membership_management', 'coaching_scheduling'],
        'roles'         => ['Admin', 'Manager', 'Coach', 'Member', 'Staff'],
        'workflows'     => ['membership_registration', 'court_booking', 'coaching_session', 'tournament_management'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'booking_api'],
        'kpis'          => ['Active Members', 'Court Utilization', 'Revenue per Facility', 'Member Retention'],
        'synonyms'      => ['sports club management', 'cricket club', 'football club', 'swimming club', 'sports academy'],
    ],

    'cinema_theater' => [
        'label'         => 'Cinema / Theater',
        'sector'        => 'entertainment',
        'functions'     => ['booking', 'pos', 'billing', 'reporting', 'inventory'],
        'problems'      => ['seat_booking', 'concession_management', 'show_scheduling'],
        'roles'         => ['Admin', 'Manager', 'Counter Staff', 'Usher', 'Customer'],
        'workflows'     => ['show_scheduling', 'ticket_booking', 'concession_sale', 'show', 'closing'],
        'integrations'  => ['payment_gateway', 'online_booking_api', 'sms_gateway'],
        'kpis'          => ['Seat Occupancy Rate', 'Concession Revenue per Ticket', 'Revenue per Screen'],
        'synonyms'      => ['cinema management', 'movie theater', 'multiplex management', 'theater ticketing'],
    ],

    'mosque_management' => [
        'label'         => 'Mosque / Islamic Center Management',
        'sector'        => 'religious',
        'functions'     => ['member_management', 'accounting', 'reporting', 'communication'],
        'problems'      => ['donation_tracking', 'zakat_management', 'madrasa_management'],
        'roles'         => ['Admin', 'Imam', 'Committee Member', 'Donor', 'Student'],
        'workflows'     => ['donation_collection', 'zakat_distribution', 'event_announcement', 'expense_approval'],
        'integrations'  => ['payment_gateway', 'sms_gateway', 'bkash', 'nagad'],
        'kpis'          => ['Donations Collected', 'Zakat Distributed', 'Member Count'],
        'synonyms'      => ['masjid management', 'islamic center', 'mosque software', 'madrasa management'],
    ],

    'religious_organization' => [
        'label'         => 'Religious Organization / Temple / Church',
        'sector'        => 'religious',
        'functions'     => ['member_management', 'accounting', 'reporting', 'communication', 'events'],
        'problems'      => ['donation_tracking', 'event_management', 'fund_utilization'],
        'roles'         => ['Admin', 'Religious Leader', 'Committee Member', 'Devotee', 'Volunteer'],
        'workflows'     => ['donation_collection', 'event_planning', 'fund_disbursement', 'annual_report'],
        'integrations'  => ['payment_gateway', 'sms_gateway'],
        'kpis'          => ['Donations Collected', 'Events Conducted', 'Member Engagement'],
        'synonyms'      => ['temple management', 'church management', 'mandir management', 'religious trust'],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SECTOR: DIGITAL PLATFORMS EXTENDED
    // ══════════════════════════════════════════════════════════════════════

    'freelance_platform' => [
        'label'         => 'Freelance Marketplace',
        'sector'        => 'technology',
        'functions'     => ['vendor_management', 'orders', 'payment', 'crm', 'analytics'],
        'problems'      => ['quality_control', 'payment_disputes', 'seller_verification'],
        'roles'         => ['Admin', 'Freelancer', 'Client', 'Dispute Manager'],
        'workflows'     => ['service_listing', 'order_placement', 'delivery', 'review', 'payment_release'],
        'integrations'  => ['payment_gateway', 'email', 'chat_api'],
        'kpis'          => ['GMV', 'Active Freelancers', 'Order Completion Rate', 'Dispute Rate'],
        'synonyms'      => ['gig marketplace', 'freelancer platform', 'online marketplace for services'],
    ],

    'job_portal' => [
        'label'         => 'Job Portal / Career Platform',
        'sector'        => 'technology',
        'functions'     => ['job_posting', 'crm', 'billing', 'analytics', 'matching'],
        'problems'      => ['fake_profiles', 'job_matching', 'employer_retention'],
        'roles'         => ['Admin', 'Employer', 'Job Seeker', 'Recruiter'],
        'workflows'     => ['job_posting', 'candidate_application', 'shortlisting', 'interview_scheduling', 'hiring'],
        'integrations'  => ['payment_gateway', 'email', 'sms_gateway', 'linkedin'],
        'kpis'          => ['Jobs Posted', 'Application Rate', 'Placement Rate', 'Employer Retention'],
        'synonyms'      => ['job board', 'career portal', 'recruitment platform', 'job listing site'],
    ],

    'subscription_box' => [
        'label'         => 'Subscription Box / Membership Box',
        'sector'        => 'retail',
        'functions'     => ['subscription', 'inventory', 'orders', 'billing', 'crm'],
        'problems'      => ['box_curation', 'churn', 'shipping_coordination'],
        'roles'         => ['Admin', 'Curator', 'Subscriber', 'Warehouse Staff'],
        'workflows'     => ['subscription_signup', 'box_curation', 'packing', 'shipping', 'delivery', 'feedback'],
        'integrations'  => ['stripe', 'shipping_api', 'email'],
        'kpis'          => ['Active Subscribers', 'Churn Rate', 'NPS', 'LTV'],
        'synonyms'      => ['subscription box service', 'mystery box', 'curated box', 'monthly box'],
    ],
];
