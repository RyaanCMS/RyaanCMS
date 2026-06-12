<?php

namespace App\Services\AI;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Asset Coverage Engine
 *
 * For every generation request:
 *   1. Decompose the prompt into atomic requirements
 *   2. Match each requirement against the existing asset registry
 *   3. Calculate coverage percentage (what we already have)
 *   4. Identify gaps (what's missing)
 *   5. Record gaps to asset_gaps table (demand tracking → data-driven roadmap)
 *   6. Return assembly plan: reuse existing + create only what's missing
 *
 * This answers: "We have intelligence — but what intelligence do we have?"
 * Without this, we rebuild what already exists. With this, we compose.
 */
class AssetCoverageEngine
{
    // ─────────────────────────────────────────────────────────────────────────
    // Universal assets — reusable across ALL domains
    // Every business system needs these. Domain is irrelevant.
    // ─────────────────────────────────────────────────────────────────────────

    private function universalAssets(): array
    {
        return [
            'authentication'      => ['keywords' => ['login', 'auth', 'register', 'password', 'session', 'logout'], 'coverage' => 1.0],
            'rbac_permissions'    => ['keywords' => ['role', 'permission', 'access control', 'acl', 'privilege'], 'coverage' => 1.0],
            'dashboard'           => ['keywords' => ['dashboard', 'overview', 'kpi', 'stats', 'summary', 'home'], 'coverage' => 1.0],
            'reports_analytics'   => ['keywords' => ['report', 'analytics', 'chart', 'graph', 'statistics', 'insight'], 'coverage' => 1.0],
            'export_import'       => ['keywords' => ['export', 'import', 'csv', 'pdf', 'excel', 'download', 'print'], 'coverage' => 1.0],
            'search_filter'       => ['keywords' => ['search', 'filter', 'sort', 'query', 'find'], 'coverage' => 1.0],
            'notifications'       => ['keywords' => ['notification', 'alert', 'email', 'sms', 'push', 'reminder'], 'coverage' => 1.0],
            'audit_log'           => ['keywords' => ['audit', 'log', 'history', 'activity', 'trail', 'changelog'], 'coverage' => 1.0],
            'file_management'     => ['keywords' => ['file', 'document', 'upload', 'attachment', 'media', 'photo'], 'coverage' => 1.0],
            'user_profile'        => ['keywords' => ['profile', 'account', 'user management', 'my account'], 'coverage' => 1.0],
            'settings'            => ['keywords' => ['setting', 'configuration', 'preference', 'config'], 'coverage' => 1.0],
            'approval_workflow'   => ['keywords' => ['approval', 'approve', 'workflow', 'authorize', 'sign off', 'sanction'], 'coverage' => 1.0],
            'billing_invoice'     => ['keywords' => ['billing', 'invoice', 'payment', 'receipt', 'charge', 'fee'], 'coverage' => 0.9],
            'scheduling_calendar' => ['keywords' => ['schedule', 'calendar', 'appointment', 'booking', 'slot', 'time slot'], 'coverage' => 0.9],
            'status_machine'      => ['keywords' => ['status', 'state', 'workflow', 'stage', 'phase', 'pipeline'], 'coverage' => 1.0],
            'api_webhooks'        => ['keywords' => ['api', 'webhook', 'integration', 'connect', 'sync', 'rest'], 'coverage' => 0.85],
            'multi_language'      => ['keywords' => ['language', 'multilingual', 'translation', 'locale', 'i18n'], 'coverage' => 0.9],
            'mobile_responsive'   => ['keywords' => ['mobile', 'responsive', 'pwa', 'app'], 'coverage' => 1.0],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Domain-specific asset packs
    // ─────────────────────────────────────────────────────────────────────────

    private function domainAssets(): array
    {
        return [

            'hospital' => [
                'patient_records'    => ['keywords' => ['patient', 'person record', 'client record', 'subject'], 'coverage' => 0.85],
                'appointment'        => ['keywords' => ['appointment', 'consultation', 'visit', 'booking', 'session'], 'coverage' => 1.0],
                'prescription'       => ['keywords' => ['prescription', 'medication', 'drug', 'medicine', 'rx'], 'coverage' => 0.9],
                'lab_tests'          => ['keywords' => ['lab', 'test', 'sample', 'result', 'diagnosis', 'pathology'], 'coverage' => 0.9],
                'ward_management'    => ['keywords' => ['ward', 'bed', 'admission', 'discharge', 'inpatient', 'room'], 'coverage' => 0.85],
                'pharmacy_stock'     => ['keywords' => ['pharmacy', 'medicine stock', 'dispensary', 'drug store'], 'coverage' => 0.9],
                'doctor_management'  => ['keywords' => ['doctor', 'physician', 'specialist', 'surgeon', 'clinician', 'practitioner'], 'coverage' => 0.95],
                'hospital_billing'   => ['keywords' => ['hospital bill', 'medical bill', 'health invoice', 'treatment cost'], 'coverage' => 1.0],
                'opd_management'     => ['keywords' => ['opd', 'outpatient', 'walk-in', 'out-patient'], 'coverage' => 0.9],
            ],

            'lms' => [
                'course_catalog'     => ['keywords' => ['course', 'curriculum', 'subject', 'class', 'program'], 'coverage' => 1.0],
                'enrollment'         => ['keywords' => ['enrollment', 'enroll', 'registration', 'sign up'], 'coverage' => 1.0],
                'quiz_assessment'    => ['keywords' => ['quiz', 'test', 'assessment', 'exam', 'question bank'], 'coverage' => 1.0],
                'certificate'        => ['keywords' => ['certificate', 'credential', 'diploma', 'badge'], 'coverage' => 1.0],
                'progress_tracking'  => ['keywords' => ['progress', 'completion', 'lesson', 'module completion'], 'coverage' => 1.0],
                'instructor'         => ['keywords' => ['instructor', 'teacher', 'trainer', 'tutor', 'faculty'], 'coverage' => 1.0],
                'assignment'         => ['keywords' => ['assignment', 'homework', 'submission', 'task'], 'coverage' => 1.0],
                'video_lesson'       => ['keywords' => ['video', 'lecture', 'media', 'lesson video'], 'coverage' => 0.9],
            ],

            'school' => [
                'student_management' => ['keywords' => ['student', 'pupil', 'learner', 'child', 'minor'], 'coverage' => 1.0],
                'attendance'         => ['keywords' => ['attendance', 'present', 'absent', 'check-in'], 'coverage' => 1.0],
                'exam_results'       => ['keywords' => ['exam', 'grade', 'result', 'marks', 'score', 'report card'], 'coverage' => 1.0],
                'fee_management'     => ['keywords' => ['fee', 'tuition', 'school fee', 'dues', 'payment'], 'coverage' => 1.0],
                'timetable'          => ['keywords' => ['timetable', 'schedule', 'class schedule', 'period', 'period plan'], 'coverage' => 1.0],
                'parent_portal'      => ['keywords' => ['parent', 'guardian', 'parent portal', 'family'], 'coverage' => 1.0],
                'teacher_management' => ['keywords' => ['teacher', 'staff', 'faculty member'], 'coverage' => 1.0],
            ],

            'ecommerce' => [
                'product_catalog'    => ['keywords' => ['product', 'item', 'catalog', 'listing', 'sku'], 'coverage' => 1.0],
                'shopping_cart'      => ['keywords' => ['cart', 'basket', 'order', 'checkout', 'add to cart'], 'coverage' => 1.0],
                'payment_gateway'    => ['keywords' => ['payment', 'gateway', 'checkout', 'transaction', 'pay online'], 'coverage' => 0.9],
                'inventory'          => ['keywords' => ['inventory', 'stock', 'warehouse', 'quantity'], 'coverage' => 1.0],
                'shipping'           => ['keywords' => ['shipping', 'delivery', 'courier', 'tracking', 'logistics'], 'coverage' => 0.9],
                'reviews_ratings'    => ['keywords' => ['review', 'rating', 'feedback', 'testimonial', 'comment'], 'coverage' => 1.0],
                'coupon_discount'    => ['keywords' => ['coupon', 'discount', 'promo', 'voucher', 'offer'], 'coverage' => 1.0],
                'returns_refunds'    => ['keywords' => ['return', 'refund', 'exchange', 'reverse'], 'coverage' => 0.95],
                'multi_vendor'       => ['keywords' => ['vendor', 'seller', 'marketplace', 'multi-vendor'], 'coverage' => 0.85],
            ],

            'hrm' => [
                'employee_records'   => ['keywords' => ['employee', 'staff', 'personnel', 'worker', 'workforce'], 'coverage' => 1.0],
                'attendance'         => ['keywords' => ['attendance', 'time', 'check-in', 'check-out', 'clock in'], 'coverage' => 1.0],
                'payroll'            => ['keywords' => ['payroll', 'salary', 'wage', 'compensation', 'pay slip'], 'coverage' => 1.0],
                'leave_management'   => ['keywords' => ['leave', 'vacation', 'absence', 'holiday', 'time off'], 'coverage' => 1.0],
                'performance'        => ['keywords' => ['performance', 'appraisal', 'review', 'evaluation', 'kpi'], 'coverage' => 0.9],
                'recruitment'        => ['keywords' => ['recruitment', 'hiring', 'job', 'interview', 'candidate'], 'coverage' => 1.0],
                'department'         => ['keywords' => ['department', 'team', 'unit', 'division', 'branch'], 'coverage' => 1.0],
                'training'           => ['keywords' => ['training', 'development', 'learning', 'certification'], 'coverage' => 0.85],
            ],

            'crm' => [
                'lead_management'    => ['keywords' => ['lead', 'prospect', 'opportunity', 'enquiry'], 'coverage' => 1.0],
                'contact_management' => ['keywords' => ['contact', 'customer', 'client', 'person'], 'coverage' => 1.0],
                'deal_pipeline'      => ['keywords' => ['deal', 'pipeline', 'funnel', 'stage', 'opportunity'], 'coverage' => 1.0],
                'activities'         => ['keywords' => ['activity', 'task', 'follow-up', 'call', 'meeting', 'note'], 'coverage' => 1.0],
                'email_campaign'     => ['keywords' => ['email', 'campaign', 'newsletter', 'marketing', 'broadcast'], 'coverage' => 0.85],
                'sales_forecast'     => ['keywords' => ['forecast', 'revenue', 'projection', 'target', 'quota'], 'coverage' => 0.9],
            ],

            'accounting' => [
                'chart_of_accounts'  => ['keywords' => ['account', 'chart of accounts', 'ledger', 'gl'], 'coverage' => 1.0],
                'journal_entries'    => ['keywords' => ['journal', 'entry', 'debit', 'credit', 'transaction', 'posting'], 'coverage' => 1.0],
                'invoicing'          => ['keywords' => ['invoice', 'billing', 'receivable', 'ar'], 'coverage' => 1.0],
                'expenses'           => ['keywords' => ['expense', 'payable', 'purchase', 'ap', 'bill'], 'coverage' => 1.0],
                'bank_reconciliation'=> ['keywords' => ['bank', 'reconciliation', 'reconcile', 'statement', 'match'], 'coverage' => 1.0],
                'tax'                => ['keywords' => ['tax', 'vat', 'gst', 'withholding', 'tax return'], 'coverage' => 0.9],
                'financial_reports'  => ['keywords' => ['balance sheet', 'income statement', 'p&l', 'cash flow', 'trial balance'], 'coverage' => 1.0],
            ],

            'inventory' => [
                'product_management' => ['keywords' => ['product', 'item', 'sku', 'barcode', 'catalog'], 'coverage' => 1.0],
                'stock_management'   => ['keywords' => ['stock', 'quantity', 'level', 'balance', 'on hand'], 'coverage' => 1.0],
                'purchase_orders'    => ['keywords' => ['purchase order', 'po', 'procurement', 'buying', 'requisition'], 'coverage' => 1.0],
                'supplier_management'=> ['keywords' => ['supplier', 'vendor', 'manufacturer', 'source'], 'coverage' => 1.0],
                'grn'                => ['keywords' => ['grn', 'goods received', 'receiving', 'delivery', 'receipt note'], 'coverage' => 1.0],
                'stock_adjustment'   => ['keywords' => ['adjustment', 'correction', 'write-off', 'count'], 'coverage' => 1.0],
                'warehouse'          => ['keywords' => ['warehouse', 'location', 'bin', 'shelf', 'zone', 'storage'], 'coverage' => 1.0],
            ],

            'restaurant' => [
                'menu_management'    => ['keywords' => ['menu', 'dish', 'food item', 'recipe', 'cuisine'], 'coverage' => 1.0],
                'table_management'   => ['keywords' => ['table', 'seating', 'floor plan', 'section'], 'coverage' => 1.0],
                'order_management'   => ['keywords' => ['order', 'dine-in', 'takeaway', 'delivery', 'food order'], 'coverage' => 1.0],
                'kitchen_display'    => ['keywords' => ['kitchen', 'kot', 'kitchen display', 'preparation', 'cook'], 'coverage' => 1.0],
                'reservation'        => ['keywords' => ['reservation', 'table booking', 'advance booking'], 'coverage' => 1.0],
                'pos_billing'        => ['keywords' => ['pos', 'bill', 'receipt', 'payment', 'settle'], 'coverage' => 1.0],
                'delivery'           => ['keywords' => ['delivery', 'delivery partner', 'online order'], 'coverage' => 0.9],
            ],

            'hotel' => [
                'room_management'    => ['keywords' => ['room', 'suite', 'floor', 'room type', 'accommodation'], 'coverage' => 1.0],
                'reservation'        => ['keywords' => ['reservation', 'check-in', 'check-out', 'booking', 'stay'], 'coverage' => 1.0],
                'guest_management'   => ['keywords' => ['guest', 'visitor', 'customer', 'traveller'], 'coverage' => 1.0],
                'housekeeping'       => ['keywords' => ['housekeeping', 'cleaning', 'maintenance', 'room status'], 'coverage' => 1.0],
                'front_desk'         => ['keywords' => ['front desk', 'reception', 'concierge', 'check-in desk'], 'coverage' => 1.0],
                'folio_billing'      => ['keywords' => ['folio', 'hotel bill', 'checkout bill', 'room charges'], 'coverage' => 1.0],
            ],

            'pos' => [
                'product_catalog'    => ['keywords' => ['product', 'item', 'sku', 'barcode'], 'coverage' => 1.0],
                'sale_transaction'   => ['keywords' => ['sale', 'transaction', 'purchase', 'checkout', 'sell'], 'coverage' => 1.0],
                'payment_methods'    => ['keywords' => ['cash', 'card', 'mobile money', 'payment method', 'split pay'], 'coverage' => 1.0],
                'shift_management'   => ['keywords' => ['shift', 'cashier', 'float', 'drawer', 'opening'], 'coverage' => 1.0],
                'returns'            => ['keywords' => ['return', 'refund', 'exchange', 'reverse sale'], 'coverage' => 1.0],
                'receipt'            => ['keywords' => ['receipt', 'thermal', 'print', 'bill print'], 'coverage' => 1.0],
            ],

            'gym' => [
                'member_management'  => ['keywords' => ['member', 'membership', 'subscriber', 'gym member'], 'coverage' => 1.0],
                'membership_plans'   => ['keywords' => ['plan', 'package', 'tier', 'monthly', 'annual', 'session pack'], 'coverage' => 1.0],
                'trainer_management' => ['keywords' => ['trainer', 'instructor', 'personal trainer', 'coach'], 'coverage' => 1.0],
                'class_scheduling'   => ['keywords' => ['class', 'group class', 'session', 'yoga', 'aerobics', 'schedule'], 'coverage' => 1.0],
                'attendance'         => ['keywords' => ['check-in', 'access', 'gate', 'rfid', 'barcode', 'visit log'], 'coverage' => 1.0],
            ],

            'salon' => [
                'appointment'        => ['keywords' => ['appointment', 'booking', 'slot', 'time slot', 'book'], 'coverage' => 1.0],
                'service_menu'       => ['keywords' => ['service', 'treatment', 'menu', 'price list', 'procedure'], 'coverage' => 1.0],
                'stylist_management' => ['keywords' => ['stylist', 'beautician', 'therapist', 'staff', 'technician'], 'coverage' => 1.0],
                'package_loyalty'    => ['keywords' => ['package', 'loyalty', 'points', 'membership', 'bundle'], 'coverage' => 1.0],
            ],

            'construction' => [
                'project_management' => ['keywords' => ['project', 'site', 'construction project', 'job site'], 'coverage' => 1.0],
                'task_milestone'     => ['keywords' => ['task', 'milestone', 'activity', 'work order', 'wbs'], 'coverage' => 1.0],
                'material_management'=> ['keywords' => ['material', 'resource', 'supply', 'bom', 'material list'], 'coverage' => 1.0],
                'contractor'         => ['keywords' => ['contractor', 'subcontractor', 'worker', 'labour'], 'coverage' => 1.0],
                'progress_tracking'  => ['keywords' => ['progress', 'completion', 'gantt', 'timeline', 'plan'], 'coverage' => 0.9],
                'site_inspection'    => ['keywords' => ['inspection', 'quality check', 'snag', 'punch list'], 'coverage' => 0.9],
            ],

            'logistics' => [
                'shipment'           => ['keywords' => ['shipment', 'parcel', 'package', 'cargo', 'consignment'], 'coverage' => 1.0],
                'driver_management'  => ['keywords' => ['driver', 'courier', 'delivery person', 'rider'], 'coverage' => 1.0],
                'vehicle_management' => ['keywords' => ['vehicle', 'truck', 'fleet', 'transport', 'van'], 'coverage' => 1.0],
                'route_management'   => ['keywords' => ['route', 'destination', 'pickup', 'delivery point', 'waypoint'], 'coverage' => 0.9],
                'live_tracking'      => ['keywords' => ['tracking', 'gps', 'location', 'real-time', 'live'], 'coverage' => 0.85],
                'pod'                => ['keywords' => ['proof of delivery', 'pod', 'signature', 'confirmation'], 'coverage' => 1.0],
            ],

            'manufacturing' => [
                'work_orders'        => ['keywords' => ['work order', 'production order', 'job card', 'wo'], 'coverage' => 1.0],
                'bom'                => ['keywords' => ['bom', 'bill of material', 'recipe', 'formula', 'ingredient'], 'coverage' => 1.0],
                'machine_management' => ['keywords' => ['machine', 'equipment', 'workstation', 'line', 'asset'], 'coverage' => 1.0],
                'quality_control'    => ['keywords' => ['quality', 'inspection', 'qc', 'qa', 'defect', 'rejection'], 'coverage' => 1.0],
                'batch_production'   => ['keywords' => ['batch', 'lot', 'production run', 'shift'], 'coverage' => 1.0],
                'maintenance'        => ['keywords' => ['maintenance', 'downtime', 'repair', 'preventive'], 'coverage' => 0.9],
            ],

            'microfinance' => [
                'client_management'  => ['keywords' => ['client', 'borrower', 'member', 'beneficiary'], 'coverage' => 1.0],
                'loan_management'    => ['keywords' => ['loan', 'credit', 'advance', 'financing'], 'coverage' => 1.0],
                'repayment_tracking' => ['keywords' => ['repayment', 'installment', 'emi', 'collection'], 'coverage' => 1.0],
                'savings_account'    => ['keywords' => ['savings', 'deposit', 'account', 'balance'], 'coverage' => 1.0],
                'group_management'   => ['keywords' => ['group', 'solidarity', 'cluster', 'meeting'], 'coverage' => 0.95],
                'field_agent'        => ['keywords' => ['agent', 'field', 'collector', 'officer'], 'coverage' => 0.9],
            ],

            'realestate' => [
                'property_listing'   => ['keywords' => ['property', 'listing', 'unit', 'apartment', 'house', 'land'], 'coverage' => 1.0],
                'lease_contract'     => ['keywords' => ['lease', 'contract', 'tenancy', 'agreement', 'rental'], 'coverage' => 1.0],
                'maintenance_request'=> ['keywords' => ['maintenance', 'repair request', 'snag', 'issue'], 'coverage' => 1.0],
                'tenant_management'  => ['keywords' => ['tenant', 'renter', 'occupant', 'lessee'], 'coverage' => 1.0],
                'viewing_schedule'   => ['keywords' => ['viewing', 'site visit', 'showing', 'walk-through'], 'coverage' => 1.0],
            ],

            'legal' => [
                'case_management'    => ['keywords' => ['case', 'matter', 'file', 'legal case', 'litigation'], 'coverage' => 1.0],
                'client_portal'      => ['keywords' => ['client', 'party', 'contact', 'legal client'], 'coverage' => 1.0],
                'hearing_schedule'   => ['keywords' => ['hearing', 'court date', 'trial', 'session'], 'coverage' => 1.0],
                'document_management'=> ['keywords' => ['document', 'evidence', 'filing', 'pleading', 'contract'], 'coverage' => 1.0],
                'time_billing'       => ['keywords' => ['time entry', 'billable hours', 'retainer', 'billing'], 'coverage' => 1.0],
            ],

            'ngo' => [
                'donor_management'   => ['keywords' => ['donor', 'contributor', 'supporter', 'funder'], 'coverage' => 1.0],
                'donation_tracking'  => ['keywords' => ['donation', 'contribution', 'fund', 'grant'], 'coverage' => 1.0],
                'program_management' => ['keywords' => ['program', 'project', 'initiative', 'campaign'], 'coverage' => 1.0],
                'beneficiary'        => ['keywords' => ['beneficiary', 'recipient', 'participant', 'target group'], 'coverage' => 1.0],
                'volunteer_management'=> ['keywords' => ['volunteer', 'helper', 'intern', 'community'], 'coverage' => 1.0],
            ],

            'travel' => [
                'package_management' => ['keywords' => ['package', 'tour package', 'deal', 'itinerary'], 'coverage' => 1.0],
                'booking_management' => ['keywords' => ['booking', 'reservation', 'confirmation', 'ticket'], 'coverage' => 1.0],
                'destination'        => ['keywords' => ['destination', 'location', 'place', 'attraction'], 'coverage' => 1.0],
                'guide_management'   => ['keywords' => ['guide', 'tour guide', 'escort', 'companion'], 'coverage' => 1.0],
                'vehicle_transport'  => ['keywords' => ['transport', 'vehicle', 'transfer', 'bus', 'charter'], 'coverage' => 0.9],
            ],

            'library' => [
                'book_catalog'       => ['keywords' => ['book', 'title', 'isbn', 'catalog', 'collection'], 'coverage' => 1.0],
                'member_management'  => ['keywords' => ['member', 'reader', 'borrower', 'library card'], 'coverage' => 1.0],
                'borrow_return'      => ['keywords' => ['borrow', 'return', 'issue', 'checkout', 'check-in'], 'coverage' => 1.0],
                'fine_management'    => ['keywords' => ['fine', 'penalty', 'overdue', 'late fee'], 'coverage' => 1.0],
                'reservation'        => ['keywords' => ['reservation', 'hold', 'queue', 'waitlist'], 'coverage' => 1.0],
            ],

            'insurance' => [
                'policy_management'  => ['keywords' => ['policy', 'cover', 'plan', 'insured', 'coverage'], 'coverage' => 1.0],
                'claim_management'   => ['keywords' => ['claim', 'settlement', 'loss', 'damage report'], 'coverage' => 1.0],
                'premium_billing'    => ['keywords' => ['premium', 'renewal', 'due date', 'installment'], 'coverage' => 1.0],
                'agent_management'   => ['keywords' => ['agent', 'broker', 'advisor', 'sales person'], 'coverage' => 1.0],
                'underwriting'       => ['keywords' => ['underwriting', 'risk', 'assessment', 'quote'], 'coverage' => 0.9],
            ],

            'pharmacy' => [
                'medicine_catalog'   => ['keywords' => ['medicine', 'drug', 'product', 'generic', 'brand'], 'coverage' => 1.0],
                'stock_management'   => ['keywords' => ['stock', 'batch', 'expiry', 'quantity', 'inventory'], 'coverage' => 1.0],
                'prescription'       => ['keywords' => ['prescription', 'rx', 'dispensing', 'prescribed'], 'coverage' => 1.0],
                'purchase_supplier'  => ['keywords' => ['purchase', 'supplier', 'order', 'procurement'], 'coverage' => 1.0],
                'pos_sales'          => ['keywords' => ['sale', 'otc', 'counter sale', 'bill'], 'coverage' => 1.0],
            ],

            'garage' => [
                'job_card'           => ['keywords' => ['job card', 'work order', 'repair job', 'service card'], 'coverage' => 1.0],
                'vehicle_records'    => ['keywords' => ['vehicle', 'car', 'bike', 'service history', 'plate'], 'coverage' => 1.0],
                'parts_inventory'    => ['keywords' => ['parts', 'spare part', 'component', 'stock'], 'coverage' => 1.0],
                'technician'         => ['keywords' => ['technician', 'mechanic', 'worker', 'engineer'], 'coverage' => 1.0],
                'billing'            => ['keywords' => ['invoice', 'bill', 'labour charge', 'parts cost'], 'coverage' => 1.0],
            ],

            'agriculture' => [
                'farm_management'    => ['keywords' => ['farm', 'field', 'plot', 'land', 'acre'], 'coverage' => 1.0],
                'crop_tracking'      => ['keywords' => ['crop', 'plant', 'seed', 'harvest', 'yield'], 'coverage' => 1.0],
                'livestock'          => ['keywords' => ['livestock', 'cattle', 'animal', 'herd', 'flock'], 'coverage' => 0.9],
                'expense_tracking'   => ['keywords' => ['expense', 'input', 'cost', 'fertilizer', 'pesticide'], 'coverage' => 1.0],
                'harvest_sales'      => ['keywords' => ['harvest', 'sale', 'market', 'produce'], 'coverage' => 0.95],
            ],

            'generic' => [
                'entity_management'  => ['keywords' => ['manage', 'record', 'track', 'maintain', 'list'], 'coverage' => 0.85],
                'crud_operations'    => ['keywords' => ['add', 'edit', 'delete', 'view', 'create', 'update'], 'coverage' => 1.0],
                'user_management'    => ['keywords' => ['user', 'admin', 'account'], 'coverage' => 1.0],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Extension packs — sub-domain specializations that extend parent domains
    // Hospital → Veterinary, Dental | School → Kindergarten | etc.
    // ─────────────────────────────────────────────────────────────────────────

    private function extensionPacks(): array
    {
        return [
            // Veterinary extends Hospital
            'veterinary'    => [
                'base_domain'  => 'hospital',
                'base_reuse'   => 0.85,
                'extensions'   => [
                    'animal_records'      => ['keywords' => ['animal', 'pet', 'species'], 'gap' => true],
                    'breed_management'    => ['keywords' => ['breed', 'species', 'type', 'race'], 'gap' => true],
                    'vaccination_tracking'=> ['keywords' => ['vaccine', 'vaccination', 'immunization', 'shot'], 'gap' => true],
                    'weight_tracking'     => ['keywords' => ['weight', 'growth', 'body score'], 'gap' => false],
                ],
            ],
            // Dental extends Hospital
            'dental'        => [
                'base_domain'  => 'hospital',
                'base_reuse'   => 0.9,
                'extensions'   => [
                    'tooth_chart'         => ['keywords' => ['tooth', 'teeth', 'dental chart', 'oral'], 'gap' => true],
                    'treatment_plan'      => ['keywords' => ['treatment plan', 'procedure', 'dental procedure'], 'gap' => true],
                ],
            ],
            // Coaching extends LMS
            'coaching'      => [
                'base_domain'  => 'lms',
                'base_reuse'   => 0.9,
                'extensions'   => [
                    'batch_management'    => ['keywords' => ['batch', 'group', 'intake'], 'gap' => false],
                ],
            ],
            // Driving School extends School
            'driving'       => [
                'base_domain'  => 'school',
                'base_reuse'   => 0.8,
                'extensions'   => [
                    'vehicle_assignment'  => ['keywords' => ['vehicle', 'car', 'driving'], 'gap' => true],
                    'license_test'        => ['keywords' => ['license', 'test', 'permit'], 'gap' => true],
                ],
            ],
            // Bakery / Cafe extends Restaurant
            'bakery'        => [
                'base_domain'  => 'restaurant',
                'base_reuse'   => 0.85,
                'extensions'   => [
                    'production_planning' => ['keywords' => ['production', 'baking', 'recipe', 'batch'], 'gap' => false],
                ],
            ],
            // Cloud Kitchen extends Restaurant
            'cloud kitchen' => [
                'base_domain'  => 'restaurant',
                'base_reuse'   => 0.9,
                'extensions'   => [
                    'multi_brand'         => ['keywords' => ['brand', 'virtual brand', 'multiple brand'], 'gap' => false],
                ],
            ],
            // Salon Spa extends Salon
            'spa'           => [
                'base_domain'  => 'salon',
                'base_reuse'   => 0.95,
                'extensions'   => [],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Domain detection
    // ─────────────────────────────────────────────────────────────────────────

    private function detectDomain(string $lower, Project $project): string
    {
        $bp = $project->blueprint ?? [];
        $appType = strtolower(trim($bp['app_type'] ?? $bp['domain'] ?? ''));
        if ($appType && isset($this->domainAssets()[$appType])) return $appType;

        $domainMap = [
            'hospital'      => ['hospital', 'clinic', 'medical center', 'healthcare', 'health center'],
            'lms'           => ['lms', 'learning management', 'e-learning', 'elearning'],
            'school'        => ['school erp', 'school management', 'university', 'college management'],
            'ecommerce'     => ['ecommerce', 'e-commerce', 'online store', 'online shop', 'marketplace'],
            'hrm'           => ['hrm', 'hris', 'hr management', 'human resource management'],
            'crm'           => ['crm', 'customer relationship', 'sales management', 'lead management'],
            'accounting'    => ['accounting', 'bookkeeping', 'accounts management', 'finance management'],
            'inventory'     => ['inventory management', 'stock management', 'warehouse management'],
            'restaurant'    => ['restaurant', 'food ordering', 'cafe management'],
            'hotel'         => ['hotel management', 'resort management', 'property management'],
            'pos'           => ['pos', 'point of sale', 'retail management'],
            'gym'           => ['gym management', 'fitness center', 'sports club'],
            'salon'         => ['salon management', 'beauty salon', 'spa management'],
            'construction'  => ['construction management', 'project management', 'contractor'],
            'logistics'     => ['logistics', 'fleet management', 'courier management', 'delivery management'],
            'manufacturing' => ['manufacturing', 'factory management', 'production management'],
            'microfinance'  => ['microfinance', 'banking', 'loan management', 'savings management'],
            'realestate'    => ['real estate', 'property management', 'rental management'],
            'legal'         => ['law firm', 'legal management', 'case management'],
            'ngo'           => ['ngo', 'nonprofit', 'charity management'],
            'travel'        => ['travel agency', 'tour management', 'tourism'],
            'library'       => ['library management'],
            'insurance'     => ['insurance management'],
            'pharmacy'      => ['pharmacy management', 'drug store'],
            'garage'        => ['garage', 'automobile workshop', 'vehicle service'],
            'agriculture'   => ['farm management', 'agriculture management'],
        ];

        foreach ($domainMap as $domain => $phrases) {
            foreach ($phrases as $phrase) {
                if (str_contains($lower, $phrase)) return $domain;
            }
        }

        // Check extension packs for sub-domain keywords
        foreach ($this->extensionPacks() as $extKey => $pack) {
            if (str_contains($lower, $extKey)) return $pack['base_domain'];
        }

        return 'generic';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Detect active extension (sub-domain specialization)
    // ─────────────────────────────────────────────────────────────────────────

    private function detectExtension(string $lower): ?string
    {
        foreach ($this->extensionPacks() as $extKey => $pack) {
            if (str_contains($lower, $extKey)) return $extKey;
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Main analysis
    // ─────────────────────────────────────────────────────────────────────────

    public function analyze(string $prompt, Project $project): array
    {
        $lower     = strtolower($prompt);
        $domain    = $this->detectDomain($lower, $project);
        $extension = $this->detectExtension($lower);

        $domainPack   = $this->domainAssets()[$domain] ?? $this->domainAssets()['generic'];
        $universals   = $this->universalAssets();
        $extensionPack= $extension ? ($this->extensionPacks()[$extension] ?? null) : null;

        $matched  = [];
        $gaps     = [];
        $totalW   = 0;
        $coveredW = 0.0;

        // ── Domain-specific requirements ─────────────────────────────────────
        foreach ($domainPack as $name => $asset) {
            $totalW++;
            $extBaseReuse = $extensionPack ? $extensionPack['base_reuse'] : 1.0;
            $effectiveCov = $asset['coverage'] * $extBaseReuse;

            $coveredW += $effectiveCov;
            $matched[] = [
                'name'       => $name,
                'source'     => "domain:{$domain}",
                'coverage'   => (int) round($effectiveCov * 100),
                'reuse_type' => $effectiveCov >= 0.95 ? 'direct' : 'adapt',
            ];
        }

        // ── Always-included universal requirements ────────────────────────────
        $coreUniversals = ['authentication', 'rbac_permissions', 'dashboard', 'reports_analytics', 'search_filter'];
        foreach ($coreUniversals as $name) {
            $totalW++;
            $coveredW += $universals[$name]['coverage'];
            $matched[] = [
                'name'       => $name,
                'source'     => 'universal',
                'coverage'   => (int) round($universals[$name]['coverage'] * 100),
                'reuse_type' => 'direct',
            ];
        }

        // ── Prompt-mentioned universal features ───────────────────────────────
        foreach ($universals as $name => $asset) {
            if (in_array($name, $coreUniversals)) continue;
            foreach ($asset['keywords'] as $kw) {
                if (str_contains($lower, $kw)) {
                    $totalW++;
                    $coveredW += $asset['coverage'];
                    $matched[] = [
                        'name'       => $name,
                        'source'     => 'universal',
                        'coverage'   => (int) round($asset['coverage'] * 100),
                        'reuse_type' => 'direct',
                    ];
                    break;
                }
            }
        }

        // ── Extension-specific requirements (new modules needed) ──────────────
        if ($extensionPack) {
            foreach ($extensionPack['extensions'] as $name => $ext) {
                $totalW++;
                if ($ext['gap']) {
                    $gaps[] = [
                        'name'        => $name,
                        'type'        => 'extension',
                        'description' => "Extend {$domain} base with {$name} (extension module)",
                        'priority'    => 'high',
                        'base_asset'  => "domain:{$domain}",
                        'domain'      => $domain,
                    ];
                    // Extension gaps get partial credit from base domain
                    $coveredW += 0.4;
                } else {
                    $coveredW += 0.9;
                    $matched[] = [
                        'name'       => $name,
                        'source'     => "extension:{$extension}",
                        'coverage'   => 90,
                        'reuse_type' => 'adapt',
                    ];
                }
            }
        }

        // ── Unknown requirements explicitly mentioned in prompt ───────────────
        $this->detectNovelRequirements($lower, $domain, $matched, $gaps, $totalW, $coveredW);

        $coverage = $totalW > 0 ? (int) min(100, round(($coveredW / $totalW) * 100)) : 100;

        return [
            'domain'        => $domain,
            'extension'     => $extension,
            'coverage'      => $coverage,
            'matched'       => $matched,
            'gaps'          => $gaps,
            'matched_count' => count($matched),
            'gap_count'     => count($gaps),
            'total_count'   => $totalW,
            'summary'       => $this->buildSummary($coverage, $matched, $gaps, $domain, $extension),
            'assembly_hint' => $this->buildAssemblyHint($domain, $extension, $matched, $gaps),
        ];
    }

    /**
     * Detect genuinely novel requirements not covered by any domain pack.
     */
    private function detectNovelRequirements(
        string $lower, string $domain,
        array &$matched, array &$gaps,
        float &$totalW, float &$coveredW
    ): void {
        $novelSignals = [
            'blockchain'         => ['blockchain', 'smart contract', 'nft', 'web3', 'crypto'],
            'ai_ml_engine'       => ['machine learning', 'neural network', 'ai model', 'nlp model', 'computer vision'],
            'iot_integration'    => ['iot', 'sensor', 'hardware', 'embedded', 'raspberry'],
            'biometric'          => ['biometric', 'fingerprint', 'face recognition', 'iris scan'],
            'custom_algorithm'   => ['custom algorithm', 'proprietary', 'special formula', 'unique calculation'],
            'external_erp_sync'  => ['sap sync', 'oracle sync', 'salesforce sync', 'quickbooks sync'],
        ];

        foreach ($novelSignals as $name => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $totalW++;
                    $gaps[] = [
                        'name'        => $name,
                        'type'        => 'new',
                        'description' => "Novel capability — no existing asset. Requires AI generation.",
                        'priority'    => 'critical',
                        'base_asset'  => null,
                        'domain'      => $domain,
                    ];
                    break;
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Summary and assembly hint builders
    // ─────────────────────────────────────────────────────────────────────────

    private function buildSummary(int $coverage, array $matched, array $gaps, string $domain, ?string $extension): string
    {
        $direct  = count(array_filter($matched, fn($m) => $m['reuse_type'] === 'direct'));
        $adapted = count(array_filter($matched, fn($m) => $m['reuse_type'] === 'adapt'));
        $new     = count(array_filter($gaps, fn($g) => $g['type'] === 'new'));
        $extend  = count(array_filter($gaps, fn($g) => $g['type'] === 'extension'));

        $domainLabel = $extension ? ucfirst($extension) . ' (' . ucfirst($domain) . ' base)' : ucfirst($domain);
        $parts = ["Domain: {$domainLabel}", "Coverage: {$coverage}%"];
        if ($direct > 0)  $parts[] = "{$direct} direct-reuse";
        if ($adapted > 0) $parts[] = "{$adapted} adapt";
        if ($extend > 0)  $parts[] = "{$extend} extend";
        if ($new > 0)     $parts[] = "{$new} new";

        return implode(' · ', $parts);
    }

    private function buildAssemblyHint(string $domain, ?string $extension, array $matched, array $gaps): string
    {
        if (empty($gaps)) {
            return "Full assembly from existing assets. Zero new modules required.";
        }

        $base    = $extension ? ucfirst($domain) . ' Blueprint + ' . ucfirst($extension) . ' extensions' : ucfirst($domain) . ' Blueprint';
        $newMods = array_filter($gaps, fn($g) => $g['type'] === 'new');
        $extMods = array_filter($gaps, fn($g) => $g['type'] === 'extension');

        $parts = ["Base: {$base}"];
        if (!empty($extMods)) $parts[] = 'Extend: ' . implode(', ', array_column($extMods, 'name'));
        if (!empty($newMods)) $parts[] = 'Create: ' . implode(', ', array_column($newMods, 'name'));

        return implode(' → ', $parts);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Gap registry — demand tracking for data-driven roadmap
    // ─────────────────────────────────────────────────────────────────────────

    public function recordGaps(array $gaps, string $domain, Project $project): void
    {
        foreach ($gaps as $gap) {
            try {
                $exists = DB::table('asset_gaps')
                    ->where('domain', $gap['domain'] ?? $domain)
                    ->where('requirement', $gap['name'])
                    ->exists();

                if ($exists) {
                    DB::table('asset_gaps')
                        ->where('domain', $gap['domain'] ?? $domain)
                        ->where('requirement', $gap['name'])
                        ->increment('demand_count', 1, ['updated_at' => now()]);
                } else {
                    DB::table('asset_gaps')->insert([
                        'domain'       => $gap['domain'] ?? $domain,
                        'requirement'  => $gap['name'],
                        'gap_type'     => $gap['type'],
                        'description'  => $gap['description'],
                        'base_asset'   => $gap['base_asset'],
                        'priority'     => $gap['priority'],
                        'demand_count' => 1,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            } catch (\Throwable) {
                // Gap recording is non-critical — never break generation
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Roadmap intelligence
    // ─────────────────────────────────────────────────────────────────────────

    public function getTopGaps(int $limit = 20): array
    {
        try {
            return DB::table('asset_gaps')
                ->orderByDesc('demand_count')
                ->limit($limit)
                ->get(['domain', 'requirement', 'gap_type', 'priority', 'demand_count'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function getCoverageStats(): array
    {
        try {
            return [
                'total_gaps'    => DB::table('asset_gaps')->count(),
                'total_demand'  => DB::table('asset_gaps')->sum('demand_count'),
                'high_priority' => DB::table('asset_gaps')->where('priority', 'high')->sum('demand_count'),
                'top_domains'   => DB::table('asset_gaps')
                    ->selectRaw('domain, sum(demand_count) as total_demand')
                    ->groupBy('domain')
                    ->orderByDesc('total_demand')
                    ->limit(10)
                    ->pluck('total_demand', 'domain')
                    ->toArray(),
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
