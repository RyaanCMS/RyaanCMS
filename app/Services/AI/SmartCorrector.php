<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\Project;

/**
 * SmartCorrector — zero-AI domain-aware correction engine.
 *
 * For any project with a known domain, short prompts (≤ 15 words) are
 * intercepted and answered instantly from the domain knowledge base:
 *   - Bug/fix guidance  (intent: fix_issue)
 *   - Validation rules  (intent: add_validation)
 *   - Status flows      (intent: status_flow)
 *   - Business rules    (intent: business_rules)
 *   - How-to guidance   (intent: how_to)
 *   - Search/export     (intent: add_search / add_export)
 *   - Section suggestions (intent: suggest_sections)
 *   - File listing      (intent: list_built)
 *
 * Zero tokens consumed. Instant. Domain-specific.
 */
class SmartCorrector
{
    // ── Intent detection patterns ─────────────────────────────────────────────

    private function intents(): array { return [
        'list_built'       => ['show files', 'list files', 'what was built', 'what have you built', 'what did you build', 'show generated', 'what modules', 'list pages', 'show pages', 'what pages', 'what was generated'],
        'suggest_sections' => ['add more section', 'add more page', 'add more feature', 'add more module', 'suggest section', 'suggest feature', 'more section', 'more page', 'more feature', 'more module', 'what else', 'additional section', 'additional feature', 'relevant section', 'related feature', 'what section', 'what feature', 'show feature'],
        'fix_issue'        => ['fix ', 'broken', 'not working', 'doesnt work', "doesn't work", 'wrong', 'error in', 'bug in', 'issue with', 'problem with', 'failing', 'fails', 'incorrect', 'not correct', 'not saving', 'not loading', 'not showing'],
        'add_validation'   => ['add validation', 'validate ', 'validation for', 'add check', 'add rule', 'missing validation', 'need validation', 'validation missing'],
        'status_flow'      => ['status flow', 'status machine', 'status for', 'workflow for', 'what status', 'order status', 'states for', 'status of', 'add status', 'change status'],
        'business_rules'   => ['what rule', 'business rule', 'rules for', 'business logic', 'logic for', 'how should it work', 'policy for', 'constraint for', 'rules of'],
        'how_to'           => ['how to', 'how do i', 'how can i', 'how should i', 'best way to', 'implement ', 'how implement'],
        'add_search'       => ['add search', 'search for', 'add filter', 'filter by', 'search in', 'enable search', 'add filtering'],
        'add_export'       => ['export ', 'add export', 'download ', 'to excel', 'to pdf', 'to csv', 'generate report', 'export report'],
    ]; }

    // ── Domain keyword → canonical key ───────────────────────────────────────

    private function domainKeywords(): array { return [
        // LMS / Online Education
        'lms' => 'lms', 'learning' => 'lms', 'course' => 'lms', 'elearning' => 'lms', 'e-learning' => 'lms', 'moodle' => 'lms',
        'coaching' => 'lms', 'tutoring' => 'lms', 'training' => 'lms', 'bootcamp' => 'lms', 'vocational' => 'lms',
        'online tutoring' => 'lms', 'e-learning platform' => 'lms', 'teaching' => 'lms',
        // School ERP
        'school' => 'school', 'university' => 'school', 'college' => 'school', 'student' => 'school', 'academy' => 'school',
        'classroom' => 'school', 'kindergarten' => 'school', 'preschool' => 'school', 'daycare' => 'school',
        'driving school' => 'school', 'music school' => 'school', 'art school' => 'school', 'childcare' => 'school',
        // eCommerce
        'ecommerce' => 'ecommerce', 'e-commerce' => 'ecommerce', 'shop' => 'ecommerce', 'store' => 'ecommerce', 'marketplace' => 'ecommerce',
        'boutique' => 'ecommerce', 'online store' => 'ecommerce', 'fashion store' => 'ecommerce',
        'clothing store' => 'ecommerce', 'electronics store' => 'ecommerce', 'furniture store' => 'ecommerce',
        'bookstore' => 'ecommerce', 'toy store' => 'ecommerce', 'sports goods' => 'ecommerce',
        'gift shop' => 'ecommerce', 'pet store' => 'ecommerce', 'multi-vendor' => 'ecommerce',
        // HRM
        'hrm' => 'hrm', 'hr' => 'hrm', 'payroll' => 'hrm', 'employee' => 'hrm', 'human resource' => 'hrm',
        'recruitment' => 'hrm', 'hiring' => 'hrm', 'staffing' => 'hrm', 'workforce' => 'hrm',
        'staff agency' => 'hrm', 'talent management' => 'hrm',
        // Hospital / Healthcare
        'hospital' => 'hospital', 'clinic' => 'hospital', 'medical' => 'hospital', 'patient' => 'hospital', 'healthcare' => 'hospital',
        'dental' => 'hospital', 'dentist' => 'hospital', 'dental clinic' => 'hospital',
        'veterinary' => 'hospital', 'vet' => 'hospital', 'vet clinic' => 'hospital',
        'laboratory' => 'hospital', 'lab' => 'hospital', 'pathology' => 'hospital',
        'telemedicine' => 'hospital', 'telehealth' => 'hospital', 'mental health' => 'hospital',
        'rehabilitation' => 'hospital', 'rehab' => 'hospital', 'nursing home' => 'hospital',
        'eye clinic' => 'hospital', 'optometry' => 'hospital', 'physiotherapy' => 'hospital',
        'blood bank' => 'hospital', 'radiology' => 'hospital', 'orthopedic' => 'hospital', 'dialysis' => 'hospital',
        // CRM
        'crm' => 'crm', 'lead' => 'crm', 'sales' => 'crm', 'pipeline' => 'crm',
        'consulting' => 'crm', 'consultancy' => 'crm', 'marketing agency' => 'crm', 'advertising agency' => 'crm',
        'contact management' => 'crm', 'customer relationship' => 'crm', 'sales pipeline' => 'crm',
        // SaaS
        'saas' => 'saas', 'subscription' => 'saas', 'tenant' => 'saas', 'multi-tenant' => 'saas',
        'helpdesk' => 'saas', 'support ticket' => 'saas', 'ticketing system' => 'saas',
        'project management tool' => 'saas', 'it asset' => 'saas', 'devops' => 'saas',
        'freelance platform' => 'saas', 'api gateway' => 'saas', 'knowledge base' => 'saas',
        // Inventory / Supply Chain
        'inventory' => 'inventory', 'warehouse' => 'inventory', 'stock' => 'inventory',
        'supply chain' => 'inventory', 'wholesale' => 'inventory', 'distribution' => 'inventory',
        'import export' => 'inventory', 'quality control' => 'inventory', 'grn' => 'inventory',
        'purchase order' => 'inventory', 'supplier management' => 'inventory',
        // Restaurant / Food & Beverage
        'restaurant' => 'restaurant', 'food' => 'restaurant', 'kitchen' => 'restaurant',
        'cafe' => 'restaurant', 'coffee shop' => 'restaurant', 'bakery' => 'restaurant',
        'catering' => 'restaurant', 'bar' => 'restaurant', 'pub' => 'restaurant',
        'fast food' => 'restaurant', 'cloud kitchen' => 'restaurant', 'food truck' => 'restaurant',
        'canteen' => 'restaurant', 'mess hall' => 'restaurant', 'pizza' => 'restaurant',
        'food delivery' => 'restaurant', 'online ordering' => 'restaurant',
        // Accounting / Finance
        'accounting' => 'accounting', 'finance' => 'accounting', 'ledger' => 'accounting',
        'invoice' => 'accounting', 'tax management' => 'accounting', 'budget' => 'accounting',
        'fintech' => 'accounting', 'cooperative' => 'accounting', 'credit union' => 'accounting',
        'bookkeeping' => 'accounting', 'accounts payable' => 'accounting', 'accounts receivable' => 'accounting',
        // Real Estate
        'real estate' => 'realestate', 'property' => 'realestate', 'rental' => 'realestate',
        'architect' => 'realestate', 'interior design' => 'realestate', 'land survey' => 'realestate',
        'housing' => 'realestate', 'mortgage broker' => 'realestate', 'property management' => 'realestate',
        // POS / Retail
        'pos' => 'pos', 'retail' => 'pos', 'cashier' => 'pos', 'point of sale' => 'pos',
        'grocery store' => 'pos', 'supermarket' => 'pos', 'jewelry store' => 'pos',
        'hardware store' => 'pos', 'fuel station' => 'pos', 'gas station' => 'pos', 'petrol station' => 'pos',
        'parking' => 'pos', 'print shop' => 'pos', 'repair shop' => 'pos', 'service center' => 'pos',
        // Hotel / Hospitality
        'hotel' => 'hotel', 'resort' => 'hotel', 'hostel' => 'hotel', 'motel' => 'hotel',
        'bed and breakfast' => 'hotel', 'guesthouse' => 'hotel', 'hotel booking' => 'hotel',
        'room booking' => 'hotel', 'housekeeping' => 'hotel', 'hotel management' => 'hotel',
        // Pharmacy
        'pharmacy' => 'pharmacy', 'pharmacist' => 'pharmacy', 'drug store' => 'pharmacy',
        'medicine' => 'pharmacy', 'dispensary' => 'pharmacy', 'chemist' => 'pharmacy',
        'pharmaceutical' => 'pharmacy', 'drug management' => 'pharmacy',
        // Gym / Fitness
        'gym' => 'gym', 'fitness' => 'gym', 'fitness center' => 'gym', 'sports club' => 'gym',
        'swimming pool' => 'gym', 'yoga' => 'gym', 'yoga studio' => 'gym',
        'martial arts' => 'gym', 'personal trainer' => 'gym', 'wellness center' => 'gym',
        'crossfit' => 'gym', 'pilates' => 'gym', 'aerobics' => 'gym', 'health club' => 'gym',
        // Legal / Law Firm
        'law firm' => 'legal', 'legal' => 'legal', 'lawyer' => 'legal', 'attorney' => 'legal',
        'advocate' => 'legal', 'litigation' => 'legal', 'contract management' => 'legal',
        'notary' => 'legal', 'paralegal' => 'legal', 'legal aid' => 'legal',
        // Construction
        'construction' => 'construction', 'contractor' => 'construction', 'builder' => 'construction',
        'civil engineering' => 'construction', 'building management' => 'construction',
        'project site' => 'construction', 'subcontractor' => 'construction', 'site management' => 'construction',
        // Logistics / Transport
        'logistics' => 'logistics', 'courier' => 'logistics', 'fleet' => 'logistics',
        'shipping' => 'logistics', 'freight' => 'logistics', 'transport' => 'logistics',
        'delivery service' => 'logistics', 'dispatch' => 'logistics', 'last mile' => 'logistics',
        'trucking' => 'logistics', 'cargo' => 'logistics', 'parcel' => 'logistics',
        // Manufacturing
        'manufacturing' => 'manufacturing', 'factory' => 'manufacturing', 'production' => 'manufacturing',
        'assembly' => 'manufacturing', 'work order' => 'manufacturing', 'bom' => 'manufacturing',
        'bill of material' => 'manufacturing', 'machine' => 'manufacturing', 'plant' => 'manufacturing',
        'production line' => 'manufacturing',
        // Salon / Beauty
        'salon' => 'salon', 'spa' => 'salon', 'beauty' => 'salon', 'barbershop' => 'salon',
        'nail salon' => 'salon', 'hair salon' => 'salon', 'massage' => 'salon', 'wellness spa' => 'salon',
        'beauty clinic' => 'salon', 'beauty parlour' => 'salon', 'hair studio' => 'salon',
        // Events / Entertainment
        'event' => 'events', 'event management' => 'events', 'wedding' => 'events',
        'conference' => 'events', 'exhibition' => 'events', 'concert' => 'events',
        'venue management' => 'events', 'event planner' => 'events', 'banquet' => 'events',
        'cinema' => 'events', 'theater' => 'events', 'movie theater' => 'events',
        'ticketing' => 'events', 'festival' => 'events', 'seminar' => 'events',
        // NGO / Nonprofit
        'ngo' => 'ngo', 'nonprofit' => 'ngo', 'charity' => 'ngo', 'church' => 'ngo',
        'mosque' => 'ngo', 'temple' => 'ngo', 'foundation' => 'ngo', 'volunteer' => 'ngo',
        'donation' => 'ngo', 'charity management' => 'ngo', 'humanitarian' => 'ngo',
        'religious organization' => 'ngo', 'community org' => 'ngo',
        // Travel / Tourism
        'travel' => 'travel', 'tour' => 'travel', 'tourism' => 'travel', 'car rental' => 'travel',
        'airline' => 'travel', 'travel booking' => 'travel', 'itinerary' => 'travel',
        'visa' => 'travel', 'holiday package' => 'travel', 'tour operator' => 'travel',
        'travel agency' => 'travel', 'safari' => 'travel', 'excursion' => 'travel',
        // Microfinance / Banking
        'microfinance' => 'microfinance', 'banking' => 'microfinance', 'bank' => 'microfinance',
        'loan' => 'microfinance', 'savings' => 'microfinance', 'lending' => 'microfinance',
        'credit' => 'microfinance', 'borrowing' => 'microfinance', 'mfi' => 'microfinance',
        'sacco' => 'microfinance', 'investment' => 'microfinance', 'portfolio' => 'microfinance',
        // Insurance
        'insurance' => 'insurance', 'insurer' => 'insurance', 'claim' => 'insurance',
        'policy' => 'insurance', 'premium' => 'insurance', 'underwriting' => 'insurance',
        'life insurance' => 'insurance', 'health insurance' => 'insurance', 'general insurance' => 'insurance',
        // Library
        'library' => 'library', 'librarian' => 'library', 'book lending' => 'library',
        'book management' => 'library', 'catalog' => 'library', 'borrowing' => 'library',
        'digital library' => 'library',
        // Garage / Automobile Workshop
        'garage' => 'garage', 'automobile' => 'garage', 'workshop' => 'garage',
        'mechanic' => 'garage', 'car service' => 'garage', 'auto repair' => 'garage',
        'vehicle service' => 'garage', 'auto workshop' => 'garage', 'motor garage' => 'garage',
        'vehicle repair' => 'garage', 'car workshop' => 'garage',
        // Laundry
        'laundry' => 'laundry', 'dry cleaning' => 'laundry', 'clothes washing' => 'laundry',
        'laundromat' => 'laundry', 'linen service' => 'laundry',
        // Agriculture / Farm
        'farm' => 'agriculture', 'agriculture' => 'agriculture', 'crop' => 'agriculture',
        'livestock' => 'agriculture', 'agri' => 'agriculture', 'farming' => 'agriculture',
        'nursery' => 'agriculture', 'garden center' => 'agriculture', 'poultry' => 'agriculture',
        'aquaculture' => 'agriculture', 'horticulture' => 'agriculture',
        // Funeral
        'funeral' => 'funeral', 'cemetery' => 'funeral', 'mortuary' => 'funeral',
        'funeral home' => 'funeral', 'cremation' => 'funeral',
    ]; }

    // ── Domain knowledge base ─────────────────────────────────────────────────
    // Each domain: label, entities[], corrections{}, status_flows{}, sections{}

    private function domains(): array { return [

        // ════════════════════════════════════════════════════════════════
        'lms' => [
            'label'    => 'LMS',
            'entities' => ['course', 'enrollment', 'quiz', 'certificate', 'lesson', 'assignment', 'progress', 'grade', 'student', 'instructor', 'category', 'module'],
            'corrections' => [
                'enrollment' => [
                    'rules'    => "- Student cannot enroll in the same course twice\n- Check course capacity before enrolling\n- Validate prerequisites are completed\n- Enrollment must be within course start/end dates",
                    'validate' => "```php\n// EnrollmentRequest\npublic function rules(): array {\n    return [\n        'course_id' => [\n            'required',\n            Rule::unique('enrollments')\n                ->where('student_id', auth()->id())\n                ->whereNull('deleted_at'),\n        ],\n    ];\n}\n```",
                    'fix'      => "Common enrollment bugs:\n1. **Duplicate enrollment** → add unique constraint: `courses_students` pivot or `enrollments` table on `[student_id, course_id]`\n2. **Capacity not enforced** → check `course->max_students` before insert\n3. **Status not updating** → ensure `enrolled_at` timestamp is set on creation",
                ],
                'quiz' => [
                    'rules'    => "- Attempt limit must be enforced server-side\n- Time limit tracked from server timestamp (not client)\n- Auto-grade MCQ on submission\n- Pass mark must be defined per quiz\n- Show results only after all attempts used or quiz closed",
                    'validate' => "```php\n// QuizAttemptController\n$attempts = QuizAttempt::where('student_id', auth()->id())\n    ->where('quiz_id', \$quiz->id)->count();\nif (\$attempts >= \$quiz->max_attempts) {\n    abort(422, 'Maximum attempts reached.');\n}\n```",
                    'fix'      => "Common quiz bugs:\n1. **Score not saving** → ensure `QuizAttempt::updateOrCreate` uses `student_id + quiz_id`\n2. **Timer bypass** → validate `submitted_at - started_at <= time_limit` on backend\n3. **Wrong score** → count only `is_correct = 1` answers; score = (correct / total) * 100",
                ],
                'certificate' => [
                    'rules'    => "- Issue only when enrollment status = 'completed'\n- Completion = all lessons watched + quiz passed (if applicable)\n- Certificate number must be unique and verifiable\n- Store issued_at timestamp",
                    'validate' => "```php\n// CertificateController\n\$enrollment = Enrollment::where('student_id', auth()->id())\n    ->where('course_id', \$course->id)\n    ->where('status', 'completed')\n    ->firstOrFail();\n```",
                    'fix'      => "Common certificate bugs:\n1. **Issued before completion** → gate behind `enrollment->status === 'completed'`\n2. **Duplicate certificates** → `Certificate::firstOrCreate(['enrollment_id' => \$id])`\n3. **QR not verifying** → store `uuid` on certificate; verify endpoint: `/verify/{uuid}`",
                ],
                'progress' => [
                    'rules'    => "- Progress = completed lessons / total lessons * 100\n- Update progress on every lesson completion\n- Enrollment status → 'completed' when progress = 100 and all quizzes passed",
                    'validate' => "```php\n// After marking lesson complete:\n\$progress = LessonCompletion::where('enrollment_id', \$enrollment->id)->count();\n\$total = \$course->lessons()->count();\n\$enrollment->update(['progress' => round(\$progress / \$total * 100)]);\nif (\$enrollment->progress >= 100) {\n    \$enrollment->update(['status' => 'completed', 'completed_at' => now()]);\n}\n```",
                    'fix'      => "Common progress bugs:\n1. **Progress stuck** → ensure `LessonCompletion` has unique `[enrollment_id, lesson_id]`\n2. **Progress over 100** → use `min(100, ...)` in calculation\n3. **Not triggering completion** → add Observer on `LessonCompletion::created`",
                ],
                'grade' => [
                    'rules'    => "- Grade = weighted average of all quiz scores in course\n- Store raw score and max score, calculate percentage at query time\n- Letter grade thresholds must be configurable",
                    'fix'      => "```php\n// Correct grade calculation\n\$grade = QuizAttempt::where('enrollment_id', \$id)\n    ->selectRaw('SUM(score) as total, SUM(max_score) as max')\n    ->first();\n\$percentage = \$grade->max > 0 ? (\$grade->total / \$grade->max) * 100 : 0;\n```",
                ],
            ],
            'status_flows' => [
                'enrollment'   => "`pending` → `active` → `completed` | `dropped` | `expired`",
                'course'       => "`draft` → `published` → `archived`",
                'quiz_attempt' => "`in_progress` → `submitted` → `graded`",
                'assignment'   => "`assigned` → `submitted` → `graded` | `late`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📚 Course Catalog' => 'Browse, search and filter courses by category, level and duration',
                    '🎓 My Enrollments' => 'Student dashboard — enrolled courses, completion %, resume points',
                    '📝 Quiz Builder' => 'MCQ, true/false, fill-in-the-blank with auto-grading',
                    '🏆 Certificate Manager' => 'Auto-issue certificates with QR verification',
                    '📊 Progress Tracker' => 'Visual progress bars, time-spent analytics, milestone badges',
                    '👨‍🏫 Instructor Dashboard' => 'Course creation, student roster, grading center',
                ],
                'Advanced Modules' => [
                    '💬 Discussion Forums' => 'Per-lesson threaded discussions with instructor replies',
                    '📹 Video Lessons' => 'Upload/embed with watch-time tracking',
                    '📁 Assignment Submissions' => 'File-upload tasks with rubric-based grading',
                    '🔔 Smart Notifications' => 'New lesson, due date, quiz result alerts',
                    '💳 Course Payments' => 'One-time purchase, subscriptions, coupons',
                    '🏅 Gamification' => 'XP points, leaderboards, streak rewards',
                    '📈 Analytics Dashboard' => 'Enrollment trends, completion rates, revenue per course',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'hospital' => [
            'label'    => 'Hospital Management',
            'entities' => ['patient', 'appointment', 'doctor', 'prescription', 'billing', 'invoice', 'lab', 'medicine', 'ward', 'bed', 'nurse', 'admission', 'discharge'],
            'corrections' => [
                'appointment' => [
                    'rules'    => "- One doctor cannot have two appointments at same time\n- Check doctor's schedule/availability before booking\n- Patient must be registered before booking\n- Appointment cannot be in the past\n- Cancellation must trigger slot release",
                    'validate' => "```php\nRule::unique('appointments')\n    ->where('doctor_id', \$request->doctor_id)\n    ->where('appointment_date', \$request->date)\n    ->where('appointment_time', \$request->time)\n    ->whereNotIn('status', ['cancelled'])\n```",
                    'fix'      => "Common appointment bugs:\n1. **Double booking** → unique constraint on `[doctor_id, date, time]` excluding cancelled\n2. **Past dates allowed** → `'date' => 'required|date|after_or_equal:today'`\n3. **Status not releasing slot** → on cancel: fire event to free the slot",
                ],
                'billing' => [
                    'rules'    => "- Patient cannot be discharged with unpaid bill (unless insurance/credit)\n- Bill = consultation + lab + pharmacy + room charges\n- Partial payment allowed — track balance\n- Insurance claims tracked separately\n- All billing changes audit-logged",
                    'validate' => "```php\n// Before discharge\nif (\$patient->invoices()->where('status', 'unpaid')->exists()) {\n    return response()->json(['error' => 'Unpaid invoices must be settled before discharge.'], 422);\n}\n```",
                    'fix'      => "Common billing bugs:\n1. **Charges missing** → ensure all service types post to `invoice_items` on creation\n2. **Total wrong** → `Invoice::sum('amount')` on related items; never store static total\n3. **Discount not applying** → apply discount before tax calculation, not after",
                ],
                'prescription' => [
                    'rules'    => "- Only licensed doctors can create prescriptions\n- Check drug allergies before saving\n- Check basic drug interactions\n- Prescription linked to specific visit/encounter\n- Pharmacy can only dispense against approved prescription",
                    'validate' => "```php\n// Gate::allows check or Policy\nif (auth()->user()->role !== 'doctor') {\n    abort(403, 'Only doctors can issue prescriptions.');\n}\n// Allergy check\nif (\$patient->allergies()->where('drug_id', \$drugId)->exists()) {\n    return back()->with('warning', 'Patient is allergic to this drug.');\n}\n```",
                    'fix'      => "Common prescription bugs:\n1. **Non-doctors can prescribe** → add policy `PrescriptionPolicy::create` checking role\n2. **Allergy not checked** → query `patient_allergies` on drug selection\n3. **Linked to wrong visit** → always pass `encounter_id` when creating",
                ],
                'patient' => [
                    'rules'    => "- Patient data accessible only to treating team\n- Medical record number (MRN) must be unique and auto-generated\n- Emergency contact required\n- Blood type stored and verified before transfusion",
                    'validate' => "```php\n'mrn' => 'required|unique:patients|alpha_num',\n'blood_type' => 'required|in:A+,A-,B+,B-,O+,O-,AB+,AB-',\n'emergency_contact_phone' => 'required|string',\n```",
                    'fix'      => "Common patient bugs:\n1. **MRN duplicates** → auto-generate: `'P' . str_pad(Patient::max('id') + 1, 6, '0', STR_PAD_LEFT)`\n2. **Privacy leak** → scope all patient queries with `PatientPolicy`\n3. **Missing blood type** → make it required at registration",
                ],
                'lab' => [
                    'rules'    => "- Lab test ordered by doctor only\n- Results entered by lab technician\n- Doctor notified when results ready\n- Pending lab results flagged on discharge screen",
                    'fix'      => "```php\n// Check pending labs before discharge\n\$pending = LabOrder::where('patient_id', \$id)\n    ->where('status', 'pending')\n    ->exists();\nif (\$pending) {\n    session()->flash('warning', 'Patient has pending lab results.');\n}\n```",
                ],
            ],
            'status_flows' => [
                'appointment' => "`scheduled` → `confirmed` → `in_progress` → `completed` | `cancelled` | `no_show`",
                'patient'     => "`registered` → `admitted` → `under_treatment` → `discharged`",
                'invoice'     => "`draft` → `issued` → `partial` → `paid` | `overdue` | `insurance_pending`",
                'lab_order'   => "`ordered` → `sample_collected` → `processing` → `result_ready` → `reviewed`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🔬 Lab Module' => 'Test orders, results entry, printable reports',
                    '💊 Pharmacy' => 'Drug inventory, prescription fulfillment, expiry alerts',
                    '🛏️ Ward Management' => 'Bed allocation, admission, discharge workflows',
                    '🩺 Telemedicine' => 'Video consultations with online prescriptions',
                    '🔗 Insurance Claims' => 'Pre-auth requests, claim submission, settlement',
                ],
                'Advanced Modules' => [
                    '🏥 OT Scheduling' => 'Operation theater booking and surgical team roster',
                    '📊 Hospital Analytics' => 'Bed occupancy, revenue per department',
                    '📱 Patient App' => 'Patient-facing app for appointments and reports',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'hrm' => [
            'label'    => 'HRM',
            'entities' => ['employee', 'attendance', 'payroll', 'leave', 'department', 'designation', 'salary', 'overtime', 'loan', 'performance', 'appraisal'],
            'corrections' => [
                'payroll' => [
                    'rules'    => "- Published payroll cannot be modified — void and republish\n- Attendance data must be finalized before payroll processing\n- Overtime = hours worked - standard hours (min 0)\n- Loan/advance deductions applied per installment schedule\n- Tax slabs applied per current fiscal year",
                    'validate' => "```php\n// Prevent editing published payroll\nif (\$payroll->status === 'published') {\n    abort(422, 'Published payroll cannot be modified. Void it first.');\n}\n// Check attendance complete\n\$missing = Employee::active()\n    ->whereDoesntHave('attendances', fn(\$q) =>\n        \$q->whereMonth('date', \$month)->whereYear('date', \$year)\n    )->count();\nif (\$missing > 0) {\n    return back()->with('error', \"{$missing} employees missing attendance data.\");\n}\n```",
                    'fix'      => "Common payroll bugs:\n1. **Wrong net salary** → net = gross - deductions (tax + loan + absence_deduction)\n2. **Overtime miscalculation** → `max(0, \$workedHours - \$standardHours) * \$hourlyRate`\n3. **Published payroll edited** → add `if (\$payroll->status === 'published') abort(422)`",
                ],
                'attendance' => [
                    'rules'    => "- Cannot mark attendance for future dates\n- Late arrival tracked if check-in after schedule start\n- Absent deduction = daily_salary * absent_days\n- Half-day logic: < 4 hours = half day\n- Weekend/holiday attendance = overtime",
                    'validate' => "```php\n'date'     => 'required|date|before_or_equal:today',\n'check_in' => 'required|date_format:H:i',\n'check_out' => 'nullable|date_format:H:i|after:check_in',\n```",
                    'fix'      => "Common attendance bugs:\n1. **Future dates allowed** → `'date' => 'before_or_equal:today'`\n2. **Duplicate entries** → unique constraint on `[employee_id, date]`\n3. **Hours wrong** → use `Carbon::parse(\$checkOut)->diffInMinutes(\$checkIn) / 60`",
                ],
                'leave' => [
                    'rules'    => "- Check available balance before approving\n- Probation employees not eligible for annual leave\n- Overlapping leave requests must be rejected\n- Cancel leave only if not started\n- Balance restored on cancellation or rejection",
                    'validate' => "```php\n\$balance = LeaveBalance::where('employee_id', \$id)\n    ->where('leave_type_id', \$request->leave_type_id)\n    ->value('remaining_days');\n\$requested = Carbon::parse(\$request->from)->diffInDays(\$request->to) + 1;\nif (\$requested > \$balance) {\n    abort(422, 'Insufficient leave balance.');\n}\n// Overlap check\nif (Leave::where('employee_id', \$id)\n    ->where('status', 'approved')\n    ->where('from_date', '<=', \$request->to)\n    ->where('to_date', '>=', \$request->from)->exists()) {\n    abort(422, 'Overlapping leave exists.');\n}\n```",
                    'fix'      => "Common leave bugs:\n1. **Balance not deducted** → in `Leave::approved` observer, decrement balance\n2. **Overlap allowed** → add date range overlap query before saving\n3. **Balance not restored on cancel** → in `Leave::cancelled` observer, increment balance",
                ],
                'salary' => [
                    'rules'    => "- Basic + allowances = gross; gross - deductions = net\n- Salary revision creates new record (don't overwrite history)\n- Store currency explicitly on each record",
                    'fix'      => "```php\n// Correct salary structure\n\$gross = \$employee->basic_salary + \$employee->allowances->sum('amount');\n\$deductions = \$employee->deductions->sum('amount') + \$taxAmount;\n\$net = \$gross - \$deductions;\n```",
                ],
            ],
            'status_flows' => [
                'leave'      => "`pending` → `approved` | `rejected` | `cancelled`",
                'payroll'    => "`draft` → `processed` → `published` | `voided`",
                'employee'   => "`probation` → `permanent` | `resigned` | `terminated`",
                'appraisal'  => "`draft` → `self_review` → `manager_review` → `hr_review` → `completed`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📋 Recruitment Module' => 'Job postings, applicant tracking, interview scheduling',
                    '🎯 Performance Reviews' => '360° appraisals, KPI tracking, goal setting',
                    '📚 Training & Learning' => 'Course assignments, completion tracking, skill matrix',
                    '🏢 Asset Management' => 'Assign devices and equipment to employees',
                    '📅 Shift Scheduling' => 'Roster planner with conflict detection',
                ],
                'Advanced Modules' => [
                    '📊 HR Analytics' => 'Turnover rate, headcount trends, cost-per-hire',
                    '💼 Document Management' => 'Contract storage, expiry alerts, e-signature',
                    '🏅 Awards & Recognition' => 'Peer nominations, monthly awards',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'ecommerce' => [
            'label'    => 'eCommerce',
            'entities' => ['order', 'product', 'cart', 'payment', 'inventory', 'coupon', 'shipping', 'return', 'refund', 'customer', 'category', 'review'],
            'corrections' => [
                'order' => [
                    'rules'    => "- Status flow: `pending → confirmed → processing → shipped → delivered → completed`\n- Cannot cancel a delivered/completed order directly (requires return)\n- Payment must be confirmed before dispatching (except COD)\n- Store item price AT time of order — never recalculate",
                    'validate' => "```php\n// Valid status transitions\n\$allowed = [\n    'pending'    => ['confirmed', 'cancelled'],\n    'confirmed'  => ['processing', 'cancelled'],\n    'processing' => ['shipped'],\n    'shipped'    => ['delivered'],\n    'delivered'  => ['completed'],\n];\nif (!in_array(\$newStatus, \$allowed[\$order->status] ?? [])) {\n    abort(422, \"Cannot transition from {$order->status} to {$newStatus}.\");\n}\n```",
                    'fix'      => "Common order bugs:\n1. **Price changes after order** → store `unit_price` on `order_items` at checkout time\n2. **Invalid status jump** → enforce state machine (see above)\n3. **Stock not locked** → decrement stock in `Order::confirmed` observer",
                ],
                'payment' => [
                    'rules'    => "- Never trust redirect params — use webhook for confirmation\n- Payment initiation must be idempotent (same request = no double charge)\n- Refund requires: reason, approver, reference to original payment\n- Verify webhook signature before processing",
                    'validate' => "```php\n// Webhook verification (example: Stripe)\n\$sig = \$request->header('Stripe-Signature');\ntry {\n    \$event = Webhook::constructEvent(\$payload, \$sig, config('services.stripe.webhook_secret'));\n} catch (\\Exception \$e) {\n    abort(400, 'Invalid webhook signature.');\n}\n```",
                    'fix'      => "Common payment bugs:\n1. **Double charge** → check `Payment::where('reference', \$ref)->exists()` before processing\n2. **Stock not restored on fail** → listen to `Payment::failed` event, restore stock\n3. **Wrong amount** → always recalculate total server-side; never trust client total",
                ],
                'inventory' => [
                    'rules'    => "- Reserve stock on order placement\n- Decrement permanently on payment confirmation\n- Restore on order cancellation (atomically)\n- Alert on low stock (< reorder_level)",
                    'validate' => "```php\n// Atomic stock check + reserve\nDB::transaction(function () use (\$product, \$qty) {\n    \$product = Product::lockForUpdate()->find(\$product->id);\n    if (\$product->stock < \$qty) {\n        throw new \\Exception('Insufficient stock.');\n    }\n    \$product->decrement('stock', \$qty);\n});\n```",
                    'fix'      => "Common inventory bugs:\n1. **Race condition (oversell)** → use `lockForUpdate()` in transaction\n2. **Stock not restored on cancel** → `Order::cancelled` observer increments stock\n3. **Negative stock** → add DB check constraint or validate before decrement",
                ],
                'coupon' => [
                    'rules'    => "- Single-use coupons: check usage before applying\n- Validate min order value\n- Check expiry date\n- Check usage limit (global and per-user)",
                    'validate' => "```php\n\$coupon = Coupon::where('code', \$code)->firstOrFail();\nif (\$coupon->expires_at && \$coupon->expires_at->isPast()) {\n    abort(422, 'Coupon has expired.');\n}\nif (\$coupon->usage_limit && \$coupon->times_used >= \$coupon->usage_limit) {\n    abort(422, 'Coupon usage limit reached.');\n}\nif (CouponUsage::where('coupon_id', \$coupon->id)->where('user_id', auth()->id())->exists()) {\n    abort(422, 'You have already used this coupon.');\n}\n```",
                    'fix'      => "Common coupon bugs:\n1. **Used more than once** → check `coupon_usages` table per user\n2. **Expired coupon accepted** → add `->where('expires_at', '>', now())` or check after fetch\n3. **Discount applied to shipping** → discount should apply to subtotal only",
                ],
            ],
            'status_flows' => [
                'order'   => "`pending` → `confirmed` → `processing` → `shipped` → `delivered` → `completed` | `cancelled`",
                'payment' => "`pending` → `processing` → `paid` | `failed` | `refunded`",
                'return'  => "`requested` → `approved` → `item_received` → `refunded` | `rejected`",
                'product' => "`draft` → `active` | `out_of_stock` | `discontinued`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '⭐ Product Reviews' => 'Star ratings and written reviews with photo uploads',
                    '❤️ Wishlist' => 'Save products for later, share wishlists',
                    '🏷️ Coupons & Discounts' => 'Percentage, fixed, free-shipping codes',
                    '📦 Shipment Tracking' => 'Real-time order tracking with carrier integration',
                ],
                'Advanced Modules' => [
                    '🔄 Subscription Products' => 'Recurring orders with flexible billing',
                    '🤖 AI Recommendations' => '"You may also like" engine',
                    '💰 Loyalty Points' => 'Earn and redeem points on every purchase',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'crm' => [
            'label'    => 'CRM',
            'entities' => ['lead', 'deal', 'contact', 'company', 'pipeline', 'activity', 'task', 'email', 'call', 'note'],
            'corrections' => [
                'lead' => [
                    'rules'    => "- Lead score updated on every activity\n- Uncontacted leads auto-remind after X days\n- Duplicate leads detected by email/phone\n- Lead source must be tracked",
                    'validate' => "```php\n'email' => 'required|email|unique:leads,email',\n'phone' => 'nullable|unique:leads,phone',\n'source' => 'required|in:web,referral,cold_call,social,email',\n```",
                    'fix'      => "Common lead bugs:\n1. **Duplicates** → check by email+phone before creating; offer merge UI\n2. **Score not updating** → add Observer on `Activity::created` to recalculate score\n3. **No follow-up** → create scheduled task on lead assignment",
                ],
                'deal' => [
                    'rules'    => "- Deal must be linked to contact and company\n- Pipeline stage change logged\n- Expected close date required for active deals\n- Won/lost deals are immutable (archive, don't delete)",
                    'validate' => "```php\n'pipeline_stage_id' => 'required|exists:pipeline_stages,id',\n'expected_close_date' => 'required_if:status,open|date|after:today',\n'value' => 'required|numeric|min:0',\n```",
                    'fix'      => "Common deal bugs:\n1. **Stage not logging** → in `Deal::updated` observer, check `isDirty('stage_id')` and log\n2. **Won deal edited** → gate behind `if (\$deal->status !== 'won')` check\n3. **Forecast wrong** → only sum deals where `status = 'open'` and `expected_close = this_month`",
                ],
            ],
            'status_flows' => [
                'lead'     => "`new` → `contacted` → `qualified` → `converted` | `lost`",
                'deal'     => "`open` → `[pipeline stages]` → `won` | `lost`",
                'activity' => "`planned` → `completed` | `cancelled`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📧 Email Integration' => 'Two-way email sync, templates, open/click tracking',
                    '📞 Call Logging' => 'Log calls, record notes, set follow-up reminders',
                    '🤖 Lead Scoring' => 'Auto-score leads based on behavior',
                    '📊 Sales Forecasting' => 'Pipeline value projection by month and rep',
                ],
                'Advanced Modules' => [
                    '🗓️ Meeting Scheduler' => 'Calendly-style booking linked to CRM contacts',
                    '🔗 API Integrations' => 'Zapier, Slack, WhatsApp webhooks',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'saas' => [
            'label'    => 'SaaS',
            'entities' => ['tenant', 'subscription', 'plan', 'billing', 'user', 'team', 'feature', 'limit', 'onboarding', 'webhook'],
            'corrections' => [
                'tenant' => [
                    'rules'    => "- Every user-created resource must carry `tenant_id` — no exceptions\n- All queries scoped by global tenant scope — never bypassed\n- Deleting a tenant cascades or archives all data\n- Super-admin and tenant-admin use separate guards",
                    'validate' => "```php\n// Global scope on every model\nprotected static function booted(): void {\n    static::addGlobalScope('tenant', function (Builder \$q) {\n        if (auth()->check() && auth()->user()->tenant_id) {\n            \$q->where('tenant_id', auth()->user()->tenant_id);\n        }\n    });\n}\n```",
                    'fix'      => "Common tenant bugs:\n1. **Cross-tenant data leak** → add global scope (see above) to every tenant-owned model\n2. **Missing tenant_id** → add `'tenant_id' => auth()->user()->tenant_id` to every `create()`\n3. **Super-admin blocked** → skip global scope when `auth()->user()->is_super_admin`",
                ],
                'subscription' => [
                    'rules'    => "- Plan limits enforced server-side (never client)\n- Grace period on payment failure (3-7 days) before suspending\n- Trial does NOT auto-convert — require explicit payment method\n- Cancellation → access until period end, not immediate\n- Upgrade prorated; downgrade at period end",
                    'validate' => '```php' . "\n// Feature limit check\n\$limit = \$tenant->plan->limits[\$feature] ?? 0;\n\$current = \$tenant->{\$feature.'_count'};\nif (\$current >= \$limit) {\n    abort(403, \"Your plan allows {\$limit} {\$feature}. Upgrade to add more.\");\n}\n```",
                    'fix'      => "Common subscription bugs:\n1. **Feature limit bypass** → always check server-side; never trust client feature flag\n2. **Immediate cutoff on cancel** → set `ends_at = current_period_end`, not `now()`\n3. **Trial auto-converted** → require `payment_method` before trial ends",
                ],
            ],
            'status_flows' => [
                'subscription' => "`trialing` → `active` → `past_due` → `cancelled` | `suspended`",
                'tenant'       => "`onboarding` → `active` → `suspended` | `cancelled`",
                'invoice'      => "`draft` → `open` → `paid` | `void` | `uncollectible`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🔑 SSO / OAuth' => 'Google, GitHub, Microsoft single sign-on',
                    '📊 Usage Analytics' => 'Feature adoption, active users per tenant',
                    '🎨 White-label' => 'Custom domain, logo, color per tenant',
                    '🔗 Public API' => 'REST API with key management for tenants',
                ],
                'Advanced Modules' => [
                    '🤝 Affiliate System' => 'Referral links, commission tracking',
                    '📋 Audit Logs' => 'Per-tenant activity history for compliance',
                    '🧪 Feature Flags' => 'A/B test features per tenant or plan tier',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'inventory' => [
            'label'    => 'Inventory',
            'entities' => ['product', 'stock', 'purchase_order', 'supplier', 'warehouse', 'grn', 'transfer', 'adjustment', 'category', 'batch'],
            'corrections' => [
                'stock' => [
                    'rules'    => "- Stock cannot go negative — reject or backorder\n- Only increase stock after GRN creation\n- Manual adjustments require reason code and approver\n- Track batch/lot for expiry-sensitive items",
                    'validate' => "```php\nDB::transaction(function () use (\$product, \$qty) {\n    \$stock = Stock::lockForUpdate()->where('product_id', \$product->id)->first();\n    if (\$stock->quantity < \$qty) {\n        throw new \\Exception('Insufficient stock for ' . \$product->name);\n    }\n    \$stock->decrement('quantity', \$qty);\n    StockMovement::create(['type' => 'out', 'qty' => \$qty, 'product_id' => \$product->id]);\n});\n```",
                    'fix'      => "Common stock bugs:\n1. **Negative stock** → use `lockForUpdate` in DB transaction\n2. **Stock increased without GRN** → gate `StockIn` behind `GRN::where('status','received')`\n3. **Adjustment without reason** → `'reason' => 'required|string|min:5'`",
                ],
                'purchase_order' => [
                    'rules'    => "- PO requires approval before sending to supplier\n- Received quantity cannot exceed ordered quantity\n- Partial delivery creates partial GRN\n- PO cannot be edited after supplier confirmation",
                    'fix'      => "Common PO bugs:\n1. **Overshipping allowed** → validate `received_qty <= ordered_qty` on GRN\n2. **Approved PO edited** → check `status !== 'approved'` before allowing edits\n3. **Price mismatch** → store unit price on PO line items at creation time",
                ],
            ],
            'status_flows' => [
                'purchase_order' => "`draft` → `pending_approval` → `approved` → `sent` → `partial` → `received` | `cancelled`",
                'stock_adjustment' => "`pending` → `approved` → `applied` | `rejected`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📦 Purchase Orders' => 'Raise POs, approval workflow, delivery tracking',
                    '🏭 Multi-warehouse' => 'Stock levels per location with transfer requests',
                    '🔔 Reorder Alerts' => 'Auto-alert when stock falls below minimum',
                    '📋 Barcode Scanning' => 'Scan-based stock-in, stock-out, counting',
                ],
                'Advanced Modules' => [
                    '🔗 Supplier Portal' => 'Supplier login to view POs and update delivery',
                    '📈 Demand Forecasting' => 'Reorder quantity suggestions from sales history',
                    '🔄 Batch/Lot Tracking' => 'Trace batches from receipt to sale',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'accounting' => [
            'label'    => 'Accounting',
            'entities' => ['journal', 'ledger', 'invoice', 'payment', 'expense', 'account', 'period', 'tax', 'bank', 'reconciliation'],
            'corrections' => [
                'journal' => [
                    'rules'    => "- Every transaction: debit total = credit total (double-entry)\n- Posted entries are immutable — void + repost to correct\n- All changes audit-logged with user, time, reason\n- Closed accounting periods are read-only",
                    'validate' => "```php\n\$totalDebit  = collect(\$lines)->sum('debit');\n\$totalCredit = collect(\$lines)->sum('credit');\nif (round(\$totalDebit, 4) !== round(\$totalCredit, 4)) {\n    abort(422, 'Journal entry is not balanced: debits != credits.');\n}\nif (AccountingPeriod::isClosed(\$date)) {\n    abort(422, 'Accounting period is closed. No entries allowed.');\n}\n```",
                    'fix'      => "Common journal bugs:\n1. **Unbalanced entry** → always validate `sum(debit) == sum(credit)` before saving\n2. **Editing posted entry** → check `status !== 'posted'` or use void-and-repost flow\n3. **Period not checked** → add `AccountingPeriod::isClosed(\$date)` check",
                ],
                'invoice' => [
                    'rules'    => "- Invoice total = line items sum + tax - discount\n- Tax stored as DECIMAL(15,4) — never rounded during calculation\n- Invoice number auto-generated and sequential\n- Paid invoices cannot be edited",
                    'validate' => "```php\n// Correct total calculation\n\$subtotal  = \$lines->sum(fn(\$l) => \$l['qty'] * \$l['unit_price']);\n\$discount  = \$request->discount ?? 0;\n\$taxable   = \$subtotal - \$discount;\n\$tax       = round(\$taxable * (\$request->tax_rate / 100), 4);\n\$total     = \$taxable + \$tax;\n```",
                    'fix'      => "Common invoice bugs:\n1. **Total wrong** → never store static total; recalculate from items\n2. **Duplicate invoice numbers** → use DB `AUTOINCREMENT` or locked sequence\n3. **Tax rounding error** → use `DECIMAL(15,4)` and round only at display",
                ],
            ],
            'status_flows' => [
                'invoice'      => "`draft` → `issued` → `partial` → `paid` | `overdue` | `void`",
                'journal'      => "`draft` → `posted` | `voided`",
                'period'       => "`open` → `closed` → `locked`",
                'bank_recon'   => "`in_progress` → `reconciled`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📊 Financial Statements' => 'P&L, Balance Sheet, Cash Flow auto-generated',
                    '🧾 Expense Claims' => 'Staff expense submission with receipt and approval',
                    '🏦 Bank Reconciliation' => 'Match bank statement lines to journal entries',
                    '📅 Budgeting' => 'Annual budget vs actual variance tracking',
                ],
                'Advanced Modules' => [
                    '🔗 Tax Filing' => 'VAT/GST return preparation and e-filing',
                    '📋 Audit Trail' => 'Immutable log of every financial entry change',
                    '📈 CFO Dashboard' => 'Cash runway, burn rate, AR/AP aging',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'school' => [
            'label'    => 'School Management',
            'entities' => ['student', 'class', 'subject', 'attendance', 'exam', 'result', 'fee', 'teacher', 'timetable', 'notice'],
            'corrections' => [
                'attendance' => [
                    'rules'    => "- Cannot mark attendance for future dates\n- Teacher can only mark attendance for their class\n- Late arrival tracked (e.g., > 15 min after start)\n- Absent alert sent to parent same day",
                    'validate' => "```php\n'date'       => 'required|date|before_or_equal:today',\n'student_id' => 'required|exists:students,id',\n'status'     => 'required|in:present,absent,late,half_day',\n// Unique per student per date:\nRule::unique('attendances')->where(fn(\$q) =>\n    \$q->where('student_id', \$request->student_id)\n      ->where('date', \$request->date)\n),\n```",
                    'fix'      => "Common attendance bugs:\n1. **Future date allowed** → `before_or_equal:today`\n2. **Duplicate entries** → unique constraint on `[student_id, date]`\n3. **Parent not notified** → dispatch `StudentAbsent` job in `Attendance::created` observer",
                ],
                'fee' => [
                    'rules'    => "- Fee structure assigned per class/section\n- Late fee applied after due date\n- Partial payment allowed — track balance\n- Fee receipt auto-generated on payment",
                    'validate' => "```php\n\$invoice = FeeInvoice::findOrFail(\$request->invoice_id);\n\$maxPayable = \$invoice->total - \$invoice->paid_amount;\nif (\$request->amount > \$maxPayable) {\n    abort(422, \"Maximum payable amount is {$maxPayable}.\");\n}\n```",
                    'fix'      => "Common fee bugs:\n1. **Overpayment allowed** → validate `amount <= (total - paid)`\n2. **Late fee not applied** → add `if (now() > \$invoice->due_date) \$invoice->addLateFee()`\n3. **Receipt not generated** → fire `FeePaymentReceived` event → queue PDF generation",
                ],
                'exam' => [
                    'rules'    => "- Result entry only after exam date passed\n- Pass/fail computed from pass_mark\n- Grade auto-calculated from marks\n- Publish results separately from entry",
                    'fix'      => "```php\n// Grade calculation\nfunction calculateGrade(float \$percentage): string {\n    return match(true) {\n        \$percentage >= 80 => 'A+',\n        \$percentage >= 70 => 'A',\n        \$percentage >= 60 => 'B',\n        \$percentage >= 50 => 'C',\n        \$percentage >= 40 => 'D',\n        default           => 'F',\n    };\n}\n```",
                ],
            ],
            'status_flows' => [
                'admission' => "`applied` → `shortlisted` → `enrolled` | `rejected`",
                'exam'      => "`scheduled` → `ongoing` → `completed` → `results_published`",
                'fee'       => "`pending` → `partial` → `paid` | `overdue`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📚 Library Management' => 'Book inventory, issue/return, overdue fines',
                    '🚌 Transport Management' => 'Route planning, vehicle tracking, bus passes',
                    '💻 Online Classes' => 'Live class links, recording archive',
                    '📱 Parent Portal' => 'Real-time attendance, results, fee status',
                ],
                'Advanced Modules' => [
                    '📝 Online Exams' => 'MCQ-based with auto-grading and SMS results',
                    '📊 School Analytics' => 'Pass rates, attendance trends, fee collection',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'restaurant' => [
            'label'    => 'Restaurant',
            'entities' => ['order', 'table', 'menu', 'kitchen', 'reservation', 'bill', 'payment', 'rider', 'recipe', 'ingredient'],
            'corrections' => [
                'order' => [
                    'rules'    => "- Kitchen ticket created immediately on order confirmation\n- Table orders linked to table session\n- Modifier/add-on prices added to item price\n- Order cannot be voided after food is prepared",
                    'validate' => "```php\n'table_id'  => 'required_if:type,dine_in|exists:tables,id',\n'items'     => 'required|array|min:1',\n'items.*.menu_item_id' => 'required|exists:menu_items,id',\n'items.*.quantity'     => 'required|integer|min:1',\n```",
                    'fix'      => "Common order bugs:\n1. **Kitchen not notified** → fire `OrderPlaced` event → broadcast to kitchen screen\n2. **Modifiers not priced** → sum `menu_item.price + modifiers.sum('price')`\n3. **Void after cooking** → check `status not in ('preparing', 'ready')`",
                ],
                'bill' => [
                    'rules'    => "- Bill = all order items for the table session\n- Tax applied per bill, not per item\n- Split bill tracks each person's items\n- Service charge optional, configurable",
                    'fix'      => "```php\n\$subtotal = \$order->items->sum(fn(\$i) => \$i->unit_price * \$i->quantity);\n\$tax      = \$subtotal * (config('restaurant.tax_rate') / 100);\n\$service  = \$subtotal * (config('restaurant.service_charge') / 100);\n\$total    = \$subtotal + \$tax + \$service;\n```",
                ],
            ],
            'status_flows' => [
                'order'       => "`pending` → `confirmed` → `preparing` → `ready` → `served` | `delivered`",
                'table'       => "`available` → `occupied` → `reserved` → `cleaning`",
                'reservation' => "`pending` → `confirmed` → `arrived` | `no_show` | `cancelled`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📲 Online Ordering' => 'Customer-facing menu, cart and checkout',
                    '🛵 Delivery Tracking' => 'Real-time rider location for delivery',
                    '⭐ Customer Loyalty' => 'Points, stamps and rewards',
                    '📅 Reservations' => 'Table booking with confirmation SMS/email',
                ],
                'Advanced Modules' => [
                    '🍽️ Recipe Costing' => 'Ingredient cost per dish, margin tracking',
                    '🔗 Food Aggregators' => 'Sync with Uber Eats, Foodpanda',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'realestate' => [
            'label'    => 'Real Estate',
            'entities' => ['property', 'tenant', 'lease', 'rent', 'maintenance', 'viewing', 'offer', 'owner', 'agent', 'contract'],
            'corrections' => [
                'lease' => [
                    'rules'    => "- Lease cannot start before property is available\n- Overlapping leases rejected\n- Rent amount fixed for lease duration (unless rent-review clause)\n- Security deposit tracked separately from rent",
                    'validate' => "```php\n// Overlap check\nif (Lease::where('property_id', \$id)\n    ->where('status', 'active')\n    ->where('start_date', '<=', \$request->end_date)\n    ->where('end_date', '>=', \$request->start_date)->exists()) {\n    abort(422, 'Property is already leased for this period.');\n}\n```",
                    'fix'      => "Common lease bugs:\n1. **Overlapping leases** → overlap query (see above)\n2. **Deposit mixed with rent** → separate `deposits` table from `rent_payments`\n3. **Expired lease still active** → scheduled job: `Lease::where('end_date','<',today())->update(['status','expired'])`",
                ],
                'maintenance' => [
                    'rules'    => "- Maintenance request linked to property and tenant\n- Priority: urgent/high/normal\n- Contractor assigned with estimated cost\n- Tenant notified on each status change",
                    'fix'      => "Common maintenance bugs:\n1. **No notification** → observer on status change → notify tenant\n2. **Cost not tracked** → add `estimated_cost` and `actual_cost` fields\n3. **Urgent not prioritized** → sort by `priority DESC, created_at ASC` in listing",
                ],
            ],
            'status_flows' => [
                'property'    => "`available` → `viewing` → `under_offer` → `leased` | `sold`",
                'lease'       => "`draft` → `active` → `expired` | `terminated`",
                'maintenance' => "`open` → `assigned` → `in_progress` → `resolved` | `closed`",
                'viewing'     => "`scheduled` → `completed` → `offer_made` | `not_interested`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🏠 Property Listings' => 'Searchable catalog with photos, floor plans, map view',
                    '📅 Viewing Appointments' => 'Schedule and track property viewings',
                    '💳 Rent Collection' => 'Recurring rent invoicing, payment tracking',
                    '📋 Tenancy Contracts' => 'Digital lease generation, e-signature',
                ],
                'Advanced Modules' => [
                    '🔧 Maintenance Requests' => 'Tenant fault reporting with contractor assignment',
                    '📊 Portfolio Analytics' => 'Yield, occupancy rate, expense per property',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'pos' => [
            'label'    => 'POS (Point of Sale)',
            'entities' => ['sale', 'product', 'payment', 'cashier', 'shift', 'receipt', 'return', 'discount', 'customer', 'drawer'],
            'corrections' => [
                'sale' => [
                    'rules'    => "- Sale linked to open cashier shift\n- Cannot process sale with closed drawer\n- Discount capped by product min_price or max_discount_pct\n- Void/refund requires supervisor PIN",
                    'validate' => "```php\n\$shift = CashierShift::where('cashier_id', auth()->id())\n    ->where('status', 'open')->first();\nif (!\$shift) {\n    abort(422, 'No open shift. Open your cash drawer first.');\n}\n```",
                    'fix'      => "Common POS bugs:\n1. **Sale without open shift** → check shift before allowing transaction\n2. **Discount above max** → validate `discount <= product->max_discount_pct`\n3. **Receipt not printing** → fire `SaleCompleted` event → broadcast to receipt printer",
                ],
                'payment' => [
                    'rules'    => "- Accept multiple payment methods in one sale (split payment)\n- Change = cash tendered - total (only for cash payments)\n- Card payments: do not store raw card data\n- Mobile money: verify callback before marking paid",
                    'fix'      => "```php\n// Split payment calculation\n\$totalPaid = collect(\$payments)->sum('amount');\nif (\$totalPaid < \$sale->total) {\n    abort(422, \"Underpaid by \" . (\$sale->total - \$totalPaid));\n}\n\$change = \$totalPaid - \$sale->total; // return to customer\n```",
                ],
            ],
            'status_flows' => [
                'sale'  => "`in_progress` → `completed` | `voided`",
                'shift' => "`open` → `closed` → `reconciled`",
                'return'=> "`requested` → `approved` → `refunded`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '💳 Multiple Payment' => 'Cash, card, mobile money, split payment',
                    '🔄 Returns & Refunds' => 'Easy return workflow with inventory restock',
                    '📦 Stock Alerts' => 'Low-stock notification at point of sale',
                    '🧾 Receipt Printing' => 'Thermal printer integration',
                ],
                'Advanced Modules' => [
                    '📊 POS Analytics' => 'Hourly sales, cashier performance, product mix',
                    '🔗 E-commerce Sync' => 'Unified stock between physical and online store',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'hotel' => [
            'label'    => 'Hotel Management',
            'entities' => ['room', 'reservation', 'guest', 'check_in', 'check_out', 'housekeeping', 'amenity', 'invoice', 'staff', 'floor'],
            'corrections' => [
                'reservation' => [
                    'rules'    => "- No double-booking: room+date range must be unique\n- Check-in date must be before check-out date\n- Minimum 1 night stay\n- Room status must be 'available' before confirming reservation\n- Block room immediately on reservation confirmation",
                    'fix'      => "Common reservation bugs:\n1. **Double booking** → check `reservations` overlap before confirming: `whereBetween` or `NOT (check_out <= ? OR check_in >= ?)`\n2. **Room not blocked** → set `rooms.status = 'occupied'` on check-in\n3. **Late checkout handling** → add `late_checkout` flag with extra charge rule",
                ],
                'invoice' => [
                    'rules'    => "- No check-out with unpaid balance\n- Include: room rate × nights + extras (minibar, laundry, room service)\n- Apply taxes after discounts\n- Invoice must be printable as PDF",
                    'fix'      => "Common invoice bugs:\n1. **Nights miscalculated** → use `Carbon::parse(check_in)->diffInDays(check_out)`\n2. **Extras not included** → join `room_charges` table on invoice generation\n3. **Tax on discounted amount** → apply `tax = (subtotal - discount) * tax_rate`",
                ],
            ],
            'status_flows' => [
                'reservation' => "`pending` → `confirmed` → `checked_in` → `checked_out` | `cancelled` | `no_show`",
                'room'        => "`available` → `occupied` → `checkout` → `housekeeping` → `available`",
                'housekeeping'=> "`pending` → `in_progress` → `inspected` → `ready`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🏨 Room Management' => 'Floor plans, room types, pricing by season',
                    '📅 Reservations' => 'Online booking, walk-in, group bookings',
                    '🛎️ Front Desk' => 'Check-in/out, room assignment, guest profile',
                    '🧹 Housekeeping' => 'Task assignment, room status board, inspection',
                    '🧾 Billing & Invoices' => 'Folio management, split billing, tax receipts',
                ],
                'Advanced Modules' => [
                    '🍽️ Restaurant POS' => 'Room service charges linked to guest folio',
                    '🎰 Amenities Booking' => 'Spa, gym, conference room reservations',
                    '📊 Occupancy Reports' => 'RevPAR, ADR, occupancy rate dashboards',
                    '🔑 Loyalty Program' => 'Points per night, tier upgrades, redemption',
                    '📱 Guest App' => 'Mobile check-in, room key, service requests',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'pharmacy' => [
            'label'    => 'Pharmacy Management',
            'entities' => ['medicine', 'stock', 'prescription', 'purchase', 'supplier', 'sale', 'customer', 'batch', 'expiry'],
            'corrections' => [
                'stock' => [
                    'rules'    => "- Never dispense expired medicine (check batch expiry before sale)\n- FIFO/FEFO: sell oldest batch first\n- Reorder point triggers purchase order automatically\n- Controlled substances require doctor prescription + ID record\n- Track by batch number and expiry date",
                    'fix'      => "Common stock bugs:\n1. **Expired medicine sold** → always check `batches.expiry_date > today()` before dispensing\n2. **Wrong FIFO** → order batch selection by `expiry_date ASC`\n3. **Negative stock** → add DB constraint `CHECK (quantity >= 0)` and `lockForUpdate()` on sale",
                ],
                'prescription' => [
                    'rules'    => "- Verify prescriber is a licensed doctor\n- Controlled drugs: check prescription is within validity period (usually 30 days)\n- Record patient name, age, doctor name on each prescription\n- Partial dispensing allowed — track remaining quantity",
                    'fix'      => "Common prescription bugs:\n1. **Dispensed without validation** → require `prescription.status = 'verified'` before dispensing\n2. **Controlled drug tracking** → log to `controlled_drug_log` with pharmacist ID\n3. **Partial fill not tracked** → store `dispensed_qty` per prescription line item",
                ],
            ],
            'status_flows' => [
                'prescription' => "`received` → `verified` → `dispensed` | `rejected`",
                'purchase_order' => "`draft` → `sent` → `partial` → `received` | `cancelled`",
                'batch'        => "`active` → `expired` | `recalled`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '💊 Medicine Catalog' => 'Drug database with generic/brand names, categories',
                    '📋 Prescription Management' => 'Receive, verify and dispense prescriptions',
                    '📦 Stock Management' => 'Batch tracking, expiry alerts, reorder automation',
                    '🛒 Point of Sale' => 'OTC sales, insurance billing, receipts',
                    '🚚 Purchase Orders' => 'Supplier orders, GRN, batch registration',
                ],
                'Advanced Modules' => [
                    '🔔 Expiry Alerts' => '30/60/90 day expiry warnings with disposal workflow',
                    '💉 Controlled Drugs Register' => 'Schedule H/X drug log with regulatory reports',
                    '📊 Sales Analytics' => 'Top drugs, supplier performance, margin reports',
                    '🏥 Insurance Claims' => 'Insurer integration, claim submission and tracking',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'gym' => [
            'label'    => 'Gym / Fitness Center',
            'entities' => ['member', 'membership', 'session', 'trainer', 'attendance', 'payment', 'equipment', 'locker', 'schedule'],
            'corrections' => [
                'membership' => [
                    'rules'    => "- Expire membership on end_date; block access after expiry\n- Freeze requests pause expiry and extend end_date accordingly\n- No double active membership for same member\n- Renewal creates new membership (do not extend old one)\n- Refund policy: only within 3 days of purchase if not used",
                    'fix'      => "Common membership bugs:\n1. **Access after expiry** → gate entry on `membership.end_date >= today AND status = 'active'`\n2. **Freeze not extending end_date** → `end_date = end_date + frozen_days` on unfreeze\n3. **Duplicate active** → unique constraint on `[member_id, status='active']`",
                ],
                'attendance' => [
                    'rules'    => "- Log entry and exit time\n- Validate active membership on check-in\n- Flag visits when membership is near expiry (5 days)\n- Trainer sessions deduct from session pack balance",
                    'fix'      => "Common attendance bugs:\n1. **Entry without active membership** → validate in middleware before logging\n2. **Duplicate check-in** → check last entry has an exit before logging new entry\n3. **Session balance not deducting** → decrement `session_packs.remaining` on trainer session completion",
                ],
            ],
            'status_flows' => [
                'membership' => "`pending` → `active` → `frozen` | `expired` | `cancelled`",
                'session'    => "`scheduled` → `in_progress` → `completed` | `cancelled` | `no_show`",
                'payment'    => "`pending` → `paid` → `refunded`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '👤 Member Management' => 'Profile, photo, health history, emergency contact',
                    '🏋️ Membership Plans' => 'Monthly, quarterly, annual, day pass, session packs',
                    '✅ Attendance Tracking' => 'Barcode/RFID check-in, visit history',
                    '👨‍🏫 Trainer Management' => 'Trainer profiles, session scheduling, client assignment',
                    '💰 Billing & Payments' => 'Auto-renewal reminders, payment plans, receipts',
                ],
                'Advanced Modules' => [
                    '🔒 Access Control' => 'Turnstile integration, membership gate',
                    '📅 Class Scheduling' => 'Group classes with capacity, waitlist, booking',
                    '🥗 Diet & Nutrition' => 'Meal plans linked to fitness goals',
                    '📊 Member Analytics' => 'Retention rate, revenue per member, peak hours',
                    '📱 Member App' => 'Self check-in, class booking, progress tracking',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'legal' => [
            'label'    => 'Law Firm / Legal Services',
            'entities' => ['case', 'client', 'hearing', 'document', 'billing', 'lawyer', 'task', 'court', 'invoice', 'retainer'],
            'corrections' => [
                'case' => [
                    'rules'    => "- Case number must be unique and auto-generated\n- All case updates must be timestamped with lawyer ID\n- Conflict of interest check: client cannot appear on opposing side\n- Closed cases are read-only — reopen with workflow approval\n- Hearing dates must be tracked against court calendar",
                    'fix'      => "Common case bugs:\n1. **No conflict check** → before opening case, check if `client_id` exists in opposing parties of open cases\n2. **Missing audit trail** → add `case_activities` log: `[case_id, user_id, action, created_at]`\n3. **Closed case edits** → gate all mutations: `if (\$case->status === 'closed') abort(403, 'Case is closed')`",
                ],
                'billing' => [
                    'rules'    => "- Retainer balance must not go below zero without authorization\n- Time entries must specify billable vs non-billable\n- Invoice must show: hours × rate, disbursements, retainer applied, balance due\n- Trust account funds must be segregated from operating funds",
                    'fix'      => "Common billing bugs:\n1. **Retainer overdraft** → check `retainer_balance >= amount` before billing\n2. **Billable hours miscalculated** → store start/end time; calculate `diffInMinutes / 60 * rate`\n3. **Disbursements missing** → include `expenses` table join in invoice generation",
                ],
            ],
            'status_flows' => [
                'case'    => "`inquiry` → `open` → `in_progress` → `judgment` → `closed` | `appealed`",
                'invoice' => "`draft` → `sent` → `partially_paid` → `paid` | `overdue`",
                'task'    => "`todo` → `in_progress` → `review` → `done`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '⚖️ Case Management' => 'Case files, parties, opposing counsel, court details',
                    '👤 Client Portal' => 'Secure document sharing, case status updates',
                    '📅 Court Calendar' => 'Hearing dates, deadlines, reminders',
                    '📄 Document Management' => 'Version control, e-signature, secure storage',
                    '💰 Time & Billing' => 'Time tracking, invoices, retainer management',
                ],
                'Advanced Modules' => [
                    '🔍 Conflict Checker' => 'Automated conflict of interest detection',
                    '📝 Contract Builder' => 'Template-based legal document drafting',
                    '📊 Firm Analytics' => 'Revenue per lawyer, billable hours, case win rate',
                    '🔒 Trust Accounting' => 'Client fund management with three-way reconciliation',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'construction' => [
            'label'    => 'Construction / Project Management',
            'entities' => ['project', 'task', 'contractor', 'material', 'milestone', 'budget', 'invoice', 'worker', 'site', 'equipment'],
            'corrections' => [
                'project' => [
                    'rules'    => "- Budget cannot be exceeded without change order approval\n- Milestone completion requires PM sign-off\n- Material requests need stock check before approval\n- Subcontractor payments released only after milestone sign-off\n- All site incidents must be logged same day",
                    'fix'      => "Common project bugs:\n1. **Budget overrun not flagged** → compare `actual_cost` vs `budget` on every expense entry; alert at 80% and block at 100%\n2. **Milestone dependency** → enforce `predecessor_milestone.status = 'completed'` before starting dependent task\n3. **Unapproved change order** → require `change_orders` approval workflow before budget update",
                ],
                'material' => [
                    'rules'    => "- Issue materials against work orders only\n- Track material wastage per project\n- Supplier invoices matched against purchase orders (3-way match)\n- Return excess material to central store after project completion",
                    'fix'      => "Common material bugs:\n1. **Over-issue** → check `material_request.approved_qty` vs `issued_qty` before issuing\n2. **3-way match failing** → compare `PO qty`, `GRN qty`, and `invoice qty` before approving payment\n3. **Wastage not tracked** → add `material_wastage` log per project per material",
                ],
            ],
            'status_flows' => [
                'project'   => "`tendering` → `awarded` → `planning` → `in_progress` → `punch_list` → `completed` | `on_hold`",
                'task'      => "`todo` → `in_progress` → `inspection` → `done` | `rework`",
                'invoice'   => "`draft` → `submitted` → `certified` → `paid` | `disputed`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🏗️ Project Dashboard' => 'Timeline, Gantt chart, milestones, % complete',
                    '📦 Material Management' => 'Requisitions, purchase orders, site stock',
                    '👷 Workforce Management' => 'Worker attendance, daily progress reports',
                    '💰 Budget Tracking' => 'Planned vs actual cost, change orders',
                    '🔧 Equipment Management' => 'Utilization log, maintenance schedule',
                ],
                'Advanced Modules' => [
                    '📸 Site Progress Photos' => 'Geo-tagged daily site photos with reports',
                    '📄 Document Control' => 'Drawings, specs, RFIs, submittals versioning',
                    '⚠️ Safety & Incidents' => 'Hazard register, incident log, toolbox talks',
                    '📊 Project Analytics' => 'Earned value analysis, delay reports, margin tracking',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'logistics' => [
            'label'    => 'Logistics / Fleet Management',
            'entities' => ['shipment', 'driver', 'vehicle', 'route', 'customer', 'delivery', 'manifest', 'invoice', 'tracking'],
            'corrections' => [
                'shipment' => [
                    'rules'    => "- Shipment weight + dimensions must match vehicle capacity\n- Driver must have valid license before assignment\n- Real-time tracking: update GPS coordinates every 5 minutes\n- Failed delivery requires reattempt scheduling, not auto-cancel\n- Proof of delivery (POD): signature or photo required",
                    'fix'      => "Common shipment bugs:\n1. **Overloaded vehicle** → validate `sum(shipment_weights) <= vehicle.max_load` on manifest\n2. **Missing POD** → block `delivered` status until `pod_type` + `pod_reference` are saved\n3. **Tracking not updating** → ensure GPS webhook updates `tracking_events` table with timestamp+location",
                ],
            ],
            'status_flows' => [
                'shipment' => "`created` → `assigned` → `picked_up` → `in_transit` → `out_for_delivery` → `delivered` | `failed` | `returned`",
                'vehicle'  => "`available` → `in_use` → `maintenance` | `inactive`",
                'driver'   => "`available` → `on_duty` → `off_duty`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📦 Shipment Management' => 'Create, assign, track shipments end-to-end',
                    '🚚 Fleet Management' => 'Vehicle profiles, maintenance, utilization',
                    '👨‍✈️ Driver Management' => 'License tracking, performance scores, assignments',
                    '🗺️ Route Planning' => 'Optimized route with multi-stop manifests',
                    '🧾 Billing' => 'Per-shipment invoicing, weight-based pricing',
                ],
                'Advanced Modules' => [
                    '📍 Live Tracking' => 'GPS map view, ETA estimation, geofence alerts',
                    '📱 Driver App' => 'Mobile POD capture, turn-by-turn navigation',
                    '📊 Delivery Analytics' => 'On-time rate, cost per km, SLA compliance',
                    '🔔 Customer Notifications' => 'SMS/email updates on each status change',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'manufacturing' => [
            'label'    => 'Manufacturing / Factory ERP',
            'entities' => ['product', 'work_order', 'material', 'machine', 'batch', 'quality_check', 'purchase', 'supplier', 'warehouse'],
            'corrections' => [
                'work_order' => [
                    'rules'    => "- Cannot start work order if raw materials are insufficient\n- Machine must be available and not under maintenance\n- Work order completion requires quality check sign-off\n- Actual quantity produced must be recorded vs planned\n- Reject/rework items tracked separately",
                    'fix'      => "Common work order bugs:\n1. **Material shortage not checked** → validate BOM requirements against warehouse stock before releasing WO\n2. **Machine double-booking** → check `machine_schedule` conflicts before WO start\n3. **No quality gate** → require `quality_checks.status = 'passed'` before marking WO complete",
                ],
                'batch' => [
                    'rules'    => "- Each batch has unique batch number (traceable)\n- Record: production date, expiry date (if applicable), operator ID\n- Quality control: samples tested per batch (AQL standard)\n- Batch recall must trace all distribution channels",
                    'fix'      => "Common batch bugs:\n1. **Non-unique batch number** → use `YYYYMMDD-SEQ` format with DB unique constraint\n2. **Recall can't trace** → link `batch_id` to `sales_order_items` and `shipment_items`\n3. **Yield not calculated** → store `planned_qty` and `actual_qty`; yield = actual/planned * 100",
                ],
            ],
            'status_flows' => [
                'work_order'    => "`draft` → `released` → `in_progress` → `quality_check` → `completed` | `cancelled`",
                'batch'         => "`produced` → `qc_pending` → `approved` → `dispatched` | `rejected` | `recalled`",
                'purchase_order'=> "`draft` → `sent` → `partial` → `received` | `cancelled`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🏭 Work Orders' => 'Production scheduling, BOM explosion, job cards',
                    '📦 Raw Material Management' => 'Stock control, reorder alerts, consumption tracking',
                    '🔧 Machine Management' => 'Downtime log, maintenance schedule, OEE tracking',
                    '✅ Quality Control' => 'In-process and final inspection, rejection log',
                    '🚚 Finished Goods' => 'Batch tracking, warehouse putaway, dispatch',
                ],
                'Advanced Modules' => [
                    '📊 Production Analytics' => 'OEE, yield analysis, downtime reports',
                    '🔁 Material Requirement Planning' => 'Auto-generate POs based on production plan',
                    '⚠️ Defect Tracking' => 'Root cause analysis, corrective actions',
                    '📋 Batch Traceability' => 'Forward/backward trace for recall management',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'salon' => [
            'label'    => 'Salon / Spa / Beauty Center',
            'entities' => ['appointment', 'service', 'stylist', 'customer', 'payment', 'product', 'package', 'inventory'],
            'corrections' => [
                'appointment' => [
                    'rules'    => "- No double-booking: stylist+timeslot must be unique\n- Duration auto-calculated from service duration\n- Buffer time between appointments (configurable, e.g., 15 min)\n- Advance booking limit (configurable, e.g., 30 days)\n- Cancellation must free the slot immediately",
                    'fix'      => "Common appointment bugs:\n1. **Double-booking** → check `appointments` where `stylist_id = X AND status NOT IN ('cancelled') AND timeslot overlaps`\n2. **Duration not blocking slot** → end_time = start_time + service.duration_minutes\n3. **Cancelled slot not freed** → update status immediately; include in slot availability query",
                ],
                'payment' => [
                    'rules'    => "- Package balance deducted on each visit use\n- Tips tracked separately from service revenue\n- Membership/loyalty points earned on payment\n- No-show fee can be charged from saved card",
                    'fix'      => "Common payment bugs:\n1. **Package over-use** → check `packages.remaining_sessions > 0` before applying\n2. **Tips not tracked** → separate `tip_amount` column in `payments` table\n3. **Points miscalculated** → award points only on `payment.status = 'completed'` (not pending)",
                ],
            ],
            'status_flows' => [
                'appointment' => "`booked` → `confirmed` → `checked_in` → `in_service` → `completed` | `cancelled` | `no_show`",
                'package'     => "`active` → `exhausted` | `expired`",
                'payment'     => "`pending` → `completed` → `refunded`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📅 Online Booking' => 'Service + stylist + timeslot selection with real-time availability',
                    '💇 Service Menu' => 'Services, duration, pricing by stylist level',
                    '👩‍🦰 Stylist Management' => 'Profiles, specializations, calendar, commission',
                    '💳 Billing & POS' => 'Packages, memberships, tips, split payment',
                    '📦 Product Inventory' => 'Retail and professional product stock',
                ],
                'Advanced Modules' => [
                    '🎁 Packages & Memberships' => 'Session packs with expiry and carry-over rules',
                    '🌟 Loyalty Program' => 'Points, VIP tiers, birthday offers',
                    '📱 Customer App' => 'Booking, history, offers, feedback',
                    '📊 Salon Analytics' => 'Stylist revenue, service popularity, retention rate',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'events' => [
            'label'    => 'Event Management',
            'entities' => ['event', 'booking', 'venue', 'guest', 'ticket', 'vendor', 'budget', 'staff', 'setup'],
            'corrections' => [
                'ticket' => [
                    'rules'    => "- Ticket quantity cannot exceed venue capacity\n- Early-bird pricing has strict date cutoff\n- Each ticket has unique QR code for gate validation\n- Group bookings require minimum quantity threshold\n- Refund policy must be enforced based on event type",
                    'fix'      => "Common ticket bugs:\n1. **Oversell** → use `lockForUpdate()` on ticket inventory; decrement atomically\n2. **QR not unique** → generate `uuid()` per ticket; store in `tickets.qr_code`\n3. **Early-bird after cutoff** → always check `early_bird_end_date >= now()` server-side",
                ],
                'booking' => [
                    'rules'    => "- Venue must be available for requested date/time\n- Deposit required before confirming booking\n- Setup/breakdown time must be included in venue block\n- Cancellation policy: tiered refund based on notice period",
                    'fix'      => "Common booking bugs:\n1. **Venue double-booking** → check `bookings` where `venue_id = X AND status != 'cancelled' AND date overlaps`\n2. **Setup time not blocked** → block `event_date - setup_hours` to `event_date + breakdown_hours`\n3. **Deposit not tracked** → store `deposit_amount`, `deposit_paid_at` separately from final payment",
                ],
            ],
            'status_flows' => [
                'event'   => "`draft` → `published` → `registration_open` → `ongoing` → `completed` | `cancelled`",
                'booking' => "`inquiry` → `quotation_sent` → `deposit_paid` → `confirmed` → `completed` | `cancelled`",
                'ticket'  => "`active` → `used` | `cancelled` | `transferred`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🎪 Event Builder' => 'Event details, schedule, agenda, speakers',
                    '🎟️ Ticket Management' => 'Types, pricing tiers, QR codes, capacity',
                    '🏛️ Venue Management' => 'Floor plans, capacity, availability calendar',
                    '👥 Guest Management' => 'Registrations, seating, dietary preferences',
                    '💰 Budget Management' => 'Budget vs actual, vendor payments',
                ],
                'Advanced Modules' => [
                    '📱 Mobile Check-in' => 'QR scanner app for gate validation',
                    '🎙️ Speaker Portal' => 'Bio, session materials, schedule management',
                    '🤝 Sponsor Management' => 'Sponsorship packages, logo placement, reports',
                    '📊 Event Analytics' => 'Ticket sales, attendance, revenue breakdown',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'ngo' => [
            'label'    => 'NGO / Nonprofit Management',
            'entities' => ['donor', 'donation', 'program', 'beneficiary', 'volunteer', 'grant', 'expense', 'report'],
            'corrections' => [
                'donation' => [
                    'rules'    => "- Issue tax receipt immediately upon confirmed donation\n- Recurring donations: track next due date; alert on failed charge\n- Designated donations must only be spent on stated purpose\n- Refunds require executive approval + donor communication",
                    'fix'      => "Common donation bugs:\n1. **Receipt not issued** → trigger `DonationReceiptMail` on `donation.status = 'confirmed'`\n2. **Undesignated fund mixing** → use `fund_id` to separate restricted vs unrestricted funds\n3. **Recurring failure** → log `payment_failures` and notify donor + finance team",
                ],
                'program' => [
                    'rules'    => "- Program budget cannot exceed approved grant amount\n- Beneficiary enrollment must be within program capacity\n- Expenses require program manager approval + receipts\n- Impact metrics must be recorded: reach, outcomes achieved",
                    'fix'      => "Common program bugs:\n1. **Over-enrollment** → check `beneficiaries.count < program.capacity` before adding\n2. **Budget overrun** → sum `expenses.amount` per program; alert at 80%, block at 100%\n3. **Missing impact data** → add `program_outcomes` table: `[program_id, metric, value, period]`",
                ],
            ],
            'status_flows' => [
                'donation'    => "`pending` → `confirmed` → `allocated` | `refunded`",
                'grant'       => "`applied` → `under_review` → `awarded` → `reporting` → `closed` | `rejected`",
                'volunteer'   => "`applied` → `approved` → `active` → `inactive`",
                'beneficiary' => "`applied` → `screened` → `enrolled` → `graduated` | `exited`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '💝 Donor Management' => 'Profiles, giving history, segmentation, receipts',
                    '📋 Program Management' => 'Goals, activities, budget, timeline, impact',
                    '👥 Beneficiary Management' => 'Registration, eligibility, case management',
                    '🙋 Volunteer Management' => 'Recruitment, hours tracking, assignments',
                    '💰 Fund Accounting' => 'Restricted/unrestricted funds, expense tracking',
                ],
                'Advanced Modules' => [
                    '📊 Impact Dashboard' => 'KPIs, stories, before/after comparisons',
                    '📄 Grant Management' => 'Applications, compliance, reporting deadlines',
                    '🌐 Donation Portal' => 'Online giving page, campaign thermometers',
                    '📧 Communication' => 'Newsletters, event invites, impact reports',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'travel' => [
            'label'    => 'Travel Agency / Tour Operator',
            'entities' => ['booking', 'package', 'destination', 'tour', 'customer', 'vehicle', 'guide', 'payment', 'itinerary'],
            'corrections' => [
                'booking' => [
                    'rules'    => "- Package availability must be checked before confirming booking\n- Seat/room must be reserved on supplier immediately on booking\n- Partial payment: record deposit; auto-remind on balance due date\n- Passport and visa validity must be validated before international tours\n- Cancellation charges are tiered by days before departure",
                    'fix'      => "Common booking bugs:\n1. **Overbooking** → check `package.available_seats > 0` with `lockForUpdate()`\n2. **Visa not validated** → store `visa_required` flag per destination; alert agent at booking\n3. **Cancellation refund miscalculated** → use tiered policy: e.g., 100% if > 30 days, 50% if 15-30 days, 0% < 15 days",
                ],
            ],
            'status_flows' => [
                'booking'  => "`inquiry` → `quotation` → `deposit_paid` → `confirmed` → `in_progress` → `completed` | `cancelled`",
                'tour'     => "`draft` → `published` → `departed` → `completed`",
                'payment'  => "`deposit_pending` → `deposit_paid` → `balance_pending` → `fully_paid` | `refunded`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '✈️ Package Management' => 'Tours, prices, itineraries, availability calendar',
                    '📅 Booking Management' => 'Reservations, confirmations, amendments',
                    '👤 Customer Profiles' => 'Passport details, visa status, travel history',
                    '🗺️ Itinerary Builder' => 'Day-by-day schedule with hotel + transport',
                    '💰 Payment Plans' => 'Deposits, balance reminders, refund tracking',
                ],
                'Advanced Modules' => [
                    '🏨 Hotel Contracts' => 'B2B rates, allotment management, room blocks',
                    '🚌 Transport Management' => 'Charter buses, transfers, driver assignment',
                    '👨‍🦯 Guide Management' => 'Guide profiles, language skills, tour assignment',
                    '📊 Revenue Analytics' => 'Booking trends, top destinations, margin reports',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'microfinance' => [
            'label'    => 'Microfinance / Banking',
            'entities' => ['client', 'loan', 'repayment', 'savings', 'group', 'branch', 'collector', 'disbursement'],
            'corrections' => [
                'loan' => [
                    'rules'    => "- Credit scoring must be done before approval (not after)\n- Disbursement only after all documents verified and loan approved\n- Repayment schedule generated at disbursement (fixed installments)\n- Late payment triggers penalty calculation automatically\n- Loan cannot be disbursed if client has another active loan (configurable)\n- Group loans: all members must be active before group loan approved",
                    'fix'      => "Common loan bugs:\n1. **Disbursement before approval** → gate disbursement on `loan.status = 'approved'` only\n2. **Schedule not generated** → auto-create `repayment_schedule` records (installment × tenure) on disbursement\n3. **Penalty not applied** → run scheduled job: flag overdue schedules, add `penalty_amount` row\n4. **Double active loan** → check `Loan::where('client_id', \$id)->where('status', 'active')->exists()` before approving",
                ],
                'savings' => [
                    'rules'    => "- Withdrawal cannot exceed available balance\n- Minimum balance rule must be enforced\n- Compulsory savings locked until loan clearance\n- Interest accrual runs on month-end batch",
                    'fix'      => "Common savings bugs:\n1. **Overdraft** → check `savings.balance - minimum_balance >= withdrawal_amount` before processing\n2. **Compulsory savings unlocked early** → check `client.active_loans_count = 0` before releasing compulsory\n3. **Interest not credited** → run `SavingsInterestBatch` on month-end, credit to each account",
                ],
            ],
            'status_flows' => [
                'loan'     => "`applied` → `under_review` → `approved` → `disbursed` → `active` → `closed` | `defaulted` | `written_off`",
                'savings'  => "`active` → `dormant` → `closed`",
                'group'    => "`forming` → `active` → `dissolved`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '👤 Client Management' => 'KYC, credit scoring, group assignment',
                    '💵 Loan Management' => 'Application, appraisal, approval, disbursement',
                    '📅 Repayment Tracking' => 'Schedule, collections, penalties, receipts',
                    '🏦 Savings Accounts' => 'Compulsory, voluntary, interest accrual',
                    '👥 Group Management' => 'Group loans, meeting records, group dynamics',
                ],
                'Advanced Modules' => [
                    '📊 Portfolio Dashboard' => 'PAR, NPL ratio, collection efficiency',
                    '📱 Mobile Collections' => 'Field collector app with offline sync',
                    '🔔 SMS Reminders' => 'Due date, overdue, and payment confirmation alerts',
                    '📋 Regulatory Reports' => 'Central bank reports, audit trails',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'insurance' => [
            'label'    => 'Insurance Management',
            'entities' => ['policy', 'claim', 'premium', 'customer', 'agent', 'coverage', 'underwriting', 'payment'],
            'corrections' => [
                'claim' => [
                    'rules'    => "- Claim cannot be filed for expired policy\n- Claim amount cannot exceed sum insured\n- Required documents must be submitted before claim processing\n- Waiting period must be checked for health/life claims\n- No claim for exclusions listed in policy schedule",
                    'fix'      => "Common claim bugs:\n1. **Claim on expired policy** → validate `policy.end_date >= claim.incident_date`\n2. **Over-claim** → check `claim.amount <= policy.sum_insured - previous_claims_paid`\n3. **Missing document validation** → define `required_documents[]` per claim type; block submission until all uploaded",
                ],
                'premium' => [
                    'rules'    => "- Grace period: 15-30 days after due date (configurable by policy type)\n- Policy lapses if premium unpaid after grace period\n- Reinstatement requires medical checkup for life policies\n- Premium receipt must be issued within 24 hours of payment",
                    'fix'      => "Common premium bugs:\n1. **Grace period not respected** → check `due_date + grace_days >= today` before marking lapsed\n2. **Receipt not generated** → trigger `PremiumReceiptJob` on `payment.status = 'confirmed'`\n3. **Lapse not communicated** → send SMS/email at 7 days, 3 days, and 0 days before grace expiry",
                ],
            ],
            'status_flows' => [
                'policy'       => "`quoted` → `active` → `lapsed` → `reinstated` | `expired` | `cancelled`",
                'claim'        => "`filed` → `under_review` → `investigation` → `approved` → `settled` | `rejected` | `withdrawn`",
                'premium'      => "`due` → `paid` | `overdue` → `grace` → `lapsed`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📋 Policy Management' => 'Life, health, motor, property policy issuance',
                    '💰 Premium Collection' => 'Due reminders, grace period, receipt generation',
                    '🔍 Claims Processing' => 'FNOL, document collection, assessment, settlement',
                    '👨‍💼 Agent Management' => 'Commission tracking, portfolio, performance',
                    '📄 Underwriting' => 'Risk assessment, quote generation, exclusions',
                ],
                'Advanced Modules' => [
                    '🏥 Cashless Claims' => 'Hospital network, pre-authorization, direct settlement',
                    '📊 Actuarial Reports' => 'Loss ratio, claims frequency, reserve calculations',
                    '📱 Customer Portal' => 'Policy details, claims status, premium payment',
                    '🔔 Renewal Automation' => 'Auto-renewal notices, lapse prevention campaigns',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'library' => [
            'label'    => 'Library Management',
            'entities' => ['book', 'member', 'borrow', 'return', 'fine', 'catalog', 'shelf', 'reservation'],
            'corrections' => [
                'borrow' => [
                    'rules'    => "- Member cannot borrow if they have overdue items\n- Maximum borrow limit per member (configurable)\n- Due date = borrow_date + loan_period_days\n- Renewal allowed only if no reservations waiting for the book\n- Fine accrues per day after due date",
                    'fix'      => "Common borrowing bugs:\n1. **Overdue member borrows** → check `borrows.where('member_id', \$id)->where('status', 'overdue')->exists()`\n2. **Limit not enforced** → count `active_borrows` before new issue; compare against `member_type.max_books`\n3. **Fine not calculated** → `fine = max(0, today - due_date) * daily_fine_rate`",
                ],
            ],
            'status_flows' => [
                'borrow'      => "`issued` → `renewed` | `returned` | `overdue` → `returned_with_fine`",
                'reservation' => "`pending` → `available` → `issued` | `cancelled` | `expired`",
                'book'        => "`available` → `borrowed` → `reserved` | `maintenance` | `lost`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📚 Book Catalog' => 'ISBN search, subject classification, multi-copy tracking',
                    '👤 Member Management' => 'Student/staff/public membership, photo ID',
                    '📖 Borrowing & Returns' => 'Issue, return, renewal, due date tracking',
                    '💰 Fine Management' => 'Overdue fines, waiver workflow, payment receipt',
                    '🔖 Reservation' => 'Reserve unavailable books, email on availability',
                ],
                'Advanced Modules' => [
                    '📱 OPAC (Self-Search)' => 'Online catalog with availability status',
                    '🔊 Barcode Scanner' => 'Barcode/RFID based issue and return',
                    '📊 Usage Analytics' => 'Top borrowed books, active members, collection gaps',
                    '🌐 Digital Library' => 'E-book lending with DRM and download limits',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'garage' => [
            'label'    => 'Automobile Workshop / Garage',
            'entities' => ['vehicle', 'job_card', 'technician', 'part', 'invoice', 'customer', 'appointment', 'service'],
            'corrections' => [
                'job_card' => [
                    'rules'    => "- Customer must sign job card before work begins\n- Pre-work vehicle condition photos required\n- Estimate must be approved before ordering additional parts\n- Parts replaced must be listed with OEM/after-market distinction\n- Warranty work tracked separately; do not charge customer",
                    'fix'      => "Common job card bugs:\n1. **Work started without approval** → require `job_card.customer_approval_at IS NOT NULL` before technician can update status to 'in_progress'\n2. **Parts not deducted from stock** → trigger `StockDeduction` on `job_card_parts::created`\n3. **Labour time not tracked** → store `started_at` and `completed_at` per job card; calculate labour cost from technician rate",
                ],
                'invoice' => [
                    'rules'    => "- Invoice = labour charges + parts cost + tax\n- Tax applied on parts only or total (configurable)\n- Warranty repairs: zero invoice but full cost tracked internally\n- Payment required before vehicle release",
                    'fix'      => "Common invoice bugs:\n1. **Vehicle released without payment** → check `invoice.balance_due = 0` before updating job_card status to 'delivered'\n2. **Warranty not separated** → add `is_warranty` flag on job_card; bypass customer invoice but create internal cost record\n3. **Tax miscalculation** → `tax_amount = (labour + parts) * tax_rate / 100`",
                ],
            ],
            'status_flows' => [
                'job_card'    => "`received` → `estimated` → `approved` → `in_progress` → `quality_check` → `ready` → `delivered`",
                'appointment' => "`booked` → `confirmed` → `arrived` → `completed` | `cancelled` | `no_show`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🔧 Job Card Management' => 'Vehicle intake, work orders, technician assignment',
                    '🚗 Vehicle History' => 'Full service history, mileage tracking per vehicle',
                    '🔩 Parts Inventory' => 'OEM/aftermarket parts, stock, reorder alerts',
                    '💰 Invoicing' => 'Labour + parts billing, tax, payment tracking',
                    '📅 Appointment Booking' => 'Online booking with service type selection',
                ],
                'Advanced Modules' => [
                    '📸 Pre/Post Photos' => 'Vehicle condition documentation with timestamps',
                    '📱 Customer Portal' => 'Job status tracking, digital invoice, history',
                    '📊 Workshop Analytics' => 'Revenue per technician, common repairs, bay utilization',
                    '🔔 Service Reminders' => 'Mileage/time based service due notifications',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'laundry' => [
            'label'    => 'Laundry Management',
            'entities' => ['order', 'customer', 'garment', 'staff', 'payment', 'delivery'],
            'corrections' => [
                'order' => [
                    'rules'    => "- Each garment tagged with unique QR/barcode on intake\n- Stain and damage noted on intake (customer signs)\n- Order promise time must be realistic (based on workload)\n- Delivery address verified before dispatching\n- Lost/damaged item compensation workflow required",
                    'fix'      => "Common order bugs:\n1. **Item mix-up** → generate unique barcode per garment on intake; scan at each stage\n2. **Garment count mismatch** → record `intake_count` and `delivery_count`; alert if different\n3. **Overdue orders** → add `promised_at` column; flag orders past promise time on dashboard",
                ],
            ],
            'status_flows' => [
                'order'   => "`received` → `tagged` → `washing` → `drying` → `pressing` → `ready` → `delivered` | `picked_up`",
                'garment' => "`intake` → `processing` → `ready` | `damaged` | `lost`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '👕 Order Management' => 'Intake, tagging, processing stages, delivery',
                    '👤 Customer Management' => 'Profiles, order history, loyalty',
                    '💰 Billing & POS' => 'Per-piece pricing, packages, receipts',
                    '🚚 Pickup & Delivery' => 'Scheduled pickup, delivery route, driver tracking',
                ],
                'Advanced Modules' => [
                    '📱 Customer App' => 'Book pickup, track order, pay online',
                    '📊 Laundry Analytics' => 'Daily volume, revenue, staff productivity',
                    '🔔 SMS Notifications' => 'Order ready, driver en-route, delivery confirmation',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'agriculture' => [
            'label'    => 'Agriculture / Farm Management',
            'entities' => ['farm', 'crop', 'livestock', 'harvest', 'expense', 'worker', 'equipment', 'sale'],
            'corrections' => [
                'crop' => [
                    'rules'    => "- Planting date determines expected harvest window\n- Pest/disease alerts require immediate action log\n- Input usage (seeds, fertilizer, pesticide) tracked per plot\n- Yield recorded in standard units per hectare\n- Weather events must be logged against crop losses",
                    'fix'      => "Common crop bugs:\n1. **Harvest date not calculated** → `expected_harvest = planted_at + crop_type.maturity_days`\n2. **Input cost not per plot** → link `inputs` to `plot_id`, not just `farm_id`\n3. **Yield not normalized** → store yield in kg; calculate per-hectare at reporting time",
                ],
            ],
            'status_flows' => [
                'crop'     => "`land_prep` → `planted` → `growing` → `ready` → `harvested` | `failed`",
                'livestock'=> "`healthy` → `sick` → `treated` → `healthy` | `sold` | `deceased`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '🌾 Farm Management' => 'Plot mapping, soil data, irrigation schedule',
                    '🌱 Crop Tracking' => 'Planting, growth stages, harvest, yield records',
                    '🐄 Livestock Management' => 'Herd register, health records, production log',
                    '💰 Farm Expenses' => 'Input costs, labour, equipment per season',
                    '📦 Harvest & Sales' => 'Yield recording, grading, market pricing',
                ],
                'Advanced Modules' => [
                    '📡 Weather Integration' => 'Real-time alerts, historical weather correlation',
                    '🗺️ Field Mapping' => 'GPS plot boundaries, zone management',
                    '📊 Farm Analytics' => 'Profit per crop, input ROI, yield trends',
                    '🔔 Advisory Alerts' => 'Pest warnings, input recommendations by growth stage',
                ],
            ],
        ],

        // ════════════════════════════════════════════════════════════════
        'funeral' => [
            'label'    => 'Funeral Home / Cemetery Management',
            'entities' => ['case', 'deceased', 'service', 'package', 'payment', 'family', 'grave', 'document'],
            'corrections' => [
                'case' => [
                    'rules'    => "- Death certificate required before service scheduling\n- Next-of-kin authorization required for all decisions\n- Grave plot must be available before burial scheduling\n- All services delivered must match signed package\n- Financial arrangements must be approved before service",
                    'fix'      => "Common case bugs:\n1. **Service without authorization** → require `case.nok_signature_at IS NOT NULL` before proceeding\n2. **Plot double-booking** → check `graves.status = 'available'` before assignment; set to 'reserved' immediately\n3. **Package vs delivered mismatch** → track each service item delivery status in `case_services` table",
                ],
            ],
            'status_flows' => [
                'case'    => "`opened` → `arrangement` → `service_scheduled` → `service_complete` → `closed`",
                'grave'   => "`available` → `reserved` → `occupied` | `exhumed`",
                'payment' => "`pending` → `partial` → `paid` | `payment_plan`",
            ],
            'sections' => [
                'Core Extensions' => [
                    '📋 Case Management' => 'Deceased records, next-of-kin, documents',
                    '⚰️ Service Planning' => 'Funeral arrangements, schedules, venue',
                    '🪦 Cemetery Management' => 'Plot inventory, grave assignment, maps',
                    '📦 Packages & Pricing' => 'Service packages, itemized billing',
                    '💰 Billing & Payments' => 'Payment plans, receipts, pre-need contracts',
                ],
                'Advanced Modules' => [
                    '👨‍👩‍👧 Family Portal' => 'Memorial pages, tributes, death notices',
                    '🗺️ Cemetery Map' => 'Interactive grave location finder',
                    '📊 Operations Reports' => 'Monthly cases, revenue, service type breakdown',
                ],
            ],
        ],
    ]; }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Returns true if this prompt can be fully answered from the knowledge base.
     */
    public function canHandle(string $prompt, Project $project): bool
    {
        $lower = strtolower(trim($prompt));
        if (str_word_count($lower) > 15) return false;

        $intent = $this->detectIntent($lower);
        if (!$intent) return false;

        if ($intent === 'list_built') return true;

        $domain = $this->detectDomain($lower, $project);
        return $domain !== null;
    }

    /**
     * Handle the prompt using domain knowledge. Saves to conversation and emits SSE events.
     */
    public function handle(string $prompt, Project $project, AIConversation $conversation, callable $emit): void
    {
        $lower  = strtolower(trim($prompt));
        $intent = $this->detectIntent($lower);
        $domain = $this->detectDomain($lower, $project);
        $kb     = $domain ? ($this->domains()[$domain] ?? null) : null;

        $conversation->addMessage('user', $prompt, []);

        $response = match ($intent) {
            'list_built'       => $this->respondListBuilt($project),
            'suggest_sections' => $this->respondSuggestSections($kb),
            'fix_issue'        => $this->respondFix($lower, $kb),
            'add_validation'   => $this->respondValidation($lower, $kb),
            'status_flow'      => $this->respondStatusFlow($lower, $kb),
            'business_rules'   => $this->respondBusinessRules($domain),
            'how_to'           => $this->respondHowTo($lower, $kb),
            'add_search'       => $this->respondSearch($lower, $kb),
            'add_export'       => $this->respondExport($lower, $kb),
            default            => null,
        };

        if (!$response) {
            $emit(['type' => 'error', 'message' => 'Smart KB could not answer this — routing to AI.']);
            return;
        }

        // Stream in small chunks for a natural feel
        $words  = explode(' ', $response);
        $buffer = '';
        foreach ($words as $i => $word) {
            $buffer .= ($i === 0 ? '' : ' ') . $word;
            if (($i + 1) % 10 === 0) {
                $emit(['type' => 'chunk', 'content' => $buffer]);
                $buffer = '';
            }
        }
        if ($buffer !== '') {
            $emit(['type' => 'chunk', 'content' => $buffer]);
        }

        $conversation->addMessage('assistant', $response, ['tokens_used' => 0, 'model' => 'smart-kb']);
        $emit(['type' => 'done', 'message' => $response, 'model' => 'smart-kb', 'files' => [], 'tokens_used' => 0]);
    }

    // ── Intent detection ──────────────────────────────────────────────────────

    private function detectIntent(string $lower): ?string
    {
        foreach ($this->intents() as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($lower, $pattern)) return $intent;
            }
        }
        return null;
    }

    // ── Domain detection ──────────────────────────────────────────────────────

    private function detectDomain(string $lower, Project $project): ?string
    {
        // 1. Project blueprint is the primary source
        $bp      = $project->blueprint ?? [];
        $appType = strtolower($bp['app_type'] ?? $bp['domain'] ?? '');
        if ($appType && isset($this->domains()[$appType])) return $appType;
        if ($appType && isset($this->domainKeywords()[$appType])) {
            $mapped = $this->domainKeywords()[$appType];
            if (isset($this->domains()[$mapped])) return $mapped;
        }

        // 2. Prompt keywords as fallback
        foreach ($this->domainKeywords() as $kw => $domain) {
            if (str_contains($lower, $kw) && isset($this->domains()[$domain])) return $domain;
        }

        return null;
    }

    // ── Entity detection ──────────────────────────────────────────────────────

    private function detectEntity(string $lower, ?array $kb): ?string
    {
        if (!$kb) return null;
        foreach ($kb['entities'] ?? [] as $entity) {
            if (str_contains($lower, $entity)) return $entity;
        }
        return null;
    }

    // ── Response builders ─────────────────────────────────────────────────────

    private function respondListBuilt(Project $project): string
    {
        $files = $project->files()->orderBy('path')->get(['path']);
        if ($files->isEmpty()) {
            return "No files generated yet. Describe what you want to build and I'll get started!";
        }
        $grouped = $files->groupBy(fn($f) => explode('/', $f->path)[0] ?? 'root');
        $lines   = ["Here's what's been built (**{$files->count()} files**):\n"];
        foreach ($grouped as $dir => $group) {
            $lines[] = "\n**{$dir}/** ({$group->count()} files)";
            foreach ($group as $file) {
                $lines[] = "  - `{$file->path}`";
            }
        }
        return implode("\n", $lines);
    }

    private function respondSuggestSections(?array $kb): ?string
    {
        if (!$kb || empty($kb['sections'])) return null;
        $lines = ["Here are additional sections you can add to your **{$kb['label']}** project:\n"];
        foreach ($kb['sections'] as $group => $items) {
            $lines[] = "\n### {$group}";
            foreach ($items as $title => $desc) {
                $lines[] = "\n**{$title}**\n{$desc}";
            }
        }
        $lines[] = "\n\n---\nType **\"build [section name]\"** to add any of these to your project.";
        return implode("\n", $lines);
    }

    private function respondFix(string $lower, ?array $kb): ?string
    {
        if (!$kb) return null;
        $entity = $this->detectEntity($lower, $kb);

        if ($entity && isset($kb['corrections'][$entity]['fix'])) {
            $label = ucfirst($entity);
            return "**{$kb['label']} — {$label} Fix Guide**\n\n" . $kb['corrections'][$entity]['fix'];
        }

        // Generic domain-level fix guidance
        $lines = ["**{$kb['label']} — Common Fix Patterns**\n"];
        foreach (array_keys($kb['corrections'] ?? []) as $e) {
            if (isset($kb['corrections'][$e]['fix'])) {
                $lines[] = "\n### " . ucfirst($e);
                $lines[] = $kb['corrections'][$e]['fix'];
            }
        }
        return count($lines) > 1 ? implode("\n", $lines) : null;
    }

    private function respondValidation(string $lower, ?array $kb): ?string
    {
        if (!$kb) return null;
        $entity = $this->detectEntity($lower, $kb);

        if ($entity && isset($kb['corrections'][$entity])) {
            $c     = $kb['corrections'][$entity];
            $label = ucfirst($entity);
            $out   = "**{$kb['label']} — {$label} Validation**\n\n";
            if (!empty($c['rules'])) {
                $out .= "**Business Rules:**\n" . $c['rules'] . "\n\n";
            }
            if (!empty($c['validate'])) {
                $out .= "**Validation Code:**\n" . $c['validate'];
            }
            return $out;
        }

        // All validations for domain
        $lines = ["**{$kb['label']} — Validation Rules**\n"];
        foreach ($kb['corrections'] ?? [] as $e => $c) {
            if (!empty($c['rules'])) {
                $lines[] = "\n### " . ucfirst($e);
                $lines[] = $c['rules'];
            }
        }
        return count($lines) > 1 ? implode("\n", $lines) : null;
    }

    private function respondStatusFlow(string $lower, ?array $kb): ?string
    {
        if (!$kb || empty($kb['status_flows'])) return null;
        $entity = $this->detectEntity($lower, $kb);

        if ($entity && isset($kb['status_flows'][$entity])) {
            return "**{$kb['label']} — " . ucfirst($entity) . " Status Flow**\n\n" . $kb['status_flows'][$entity];
        }

        $lines = ["**{$kb['label']} — All Status Flows**\n"];
        foreach ($kb['status_flows'] as $e => $flow) {
            $lines[] = "\n**" . ucfirst(str_replace('_', ' ', $e)) . ":**\n" . $flow;
        }
        return implode("\n", $lines);
    }

    private function respondBusinessRules(string $domain): ?string
    {
        $rules = config("kb.business_rules.{$domain}", []);
        $kb    = $this->domains()[$domain] ?? null;
        $label = $kb['label'] ?? ucfirst($domain);

        if (empty($rules)) {
            // Use inline correction rules from DOMAIN_KNOWLEDGE
            if (!$kb) return null;
            $lines = ["**{$label} — Business Rules**\n"];
            foreach ($kb['corrections'] ?? [] as $entity => $c) {
                if (!empty($c['rules'])) {
                    $lines[] = "\n### " . ucfirst($entity);
                    $lines[] = $c['rules'];
                }
            }
            return count($lines) > 1 ? implode("\n", $lines) : null;
        }

        $lines = ["**{$label} — Business Rules** _(enforced at application layer)_\n"];
        foreach ($rules as $key => $description) {
            $title = ucwords(str_replace('_', ' ', $key));
            $lines[] = "\n**{$title}**\n{$description}";
        }
        return implode("\n", $lines);
    }

    private function respondHowTo(string $lower, ?array $kb): ?string
    {
        if (!$kb) return null;
        $entity = $this->detectEntity($lower, $kb);

        if ($entity && isset($kb['corrections'][$entity])) {
            $c   = $kb['corrections'][$entity];
            $out = "**{$kb['label']} — How to implement " . ucfirst($entity) . "**\n\n";
            if (!empty($c['rules']))    $out .= "**Rules to follow:**\n" . $c['rules'] . "\n\n";
            if (!empty($c['validate'])) $out .= "**Validation:**\n" . $c['validate'] . "\n\n";
            if (!empty($c['fix']))      $out .= "**Common issues & patterns:**\n" . $c['fix'];
            return $out;
        }

        // General domain how-to
        return "**{$kb['label']} — Key implementation areas:**\n\n" .
               implode("\n", array_map(
                   fn($e) => "- **" . ucfirst($e) . "**",
                   $kb['entities'] ?? []
               )) .
               "\n\nAsk about a specific entity — e.g., _\"how to implement " . ($kb['entities'][0] ?? 'enrollment') . "\"_";
    }

    private function respondSearch(string $lower, ?array $kb): ?string
    {
        $entity = $kb ? ($this->detectEntity($lower, $kb) ?? ($kb['entities'][0] ?? 'records')) : 'records';
        $model  = ucfirst($entity);
        $label  = $kb ? $kb['label'] : 'App';

        return "**{$label} — Add Search to {$model}**\n\n" .
               "```php\n// Controller\n\$query = {$model}::query();\nif (\$search = request('search')) {\n    \$query->where(function (\$q) use (\$search) {\n        \$q->where('name', 'like', \"%{\$search}%\")\n          ->orWhere('email', 'like', \"%{\$search}%\");\n    });\n}\n\$items = \$query->latest()->paginate(20)->withQueryString();\n```\n\n" .
               "```blade\n{{-- Blade --}}\n<input type=\"text\" name=\"search\" value=\"{{ request('search') }}\" placeholder=\"Search {$entity}...\" class=\"input\">\n```";
    }

    private function respondExport(string $lower, ?array $kb): ?string
    {
        $entity = $kb ? ($this->detectEntity($lower, $kb) ?? ($kb['entities'][0] ?? 'records')) : 'records';
        $model  = ucfirst($entity);
        $label  = $kb ? $kb['label'] : 'App';

        return "**{$label} — Export {$model} to Excel/PDF**\n\n" .
               "Install: `composer require maatwebsite/excel`\n\n" .
               "```php\n// app/Exports/{$model}sExport.php\nuse Maatwebsite\\Excel\\Concerns\\FromCollection;\nuse Maatwebsite\\Excel\\Concerns\\WithHeadings;\n\nclass {$model}sExport implements FromCollection, WithHeadings {\n    public function collection() {\n        return {$model}::select(['id', 'name', 'created_at'])->get();\n    }\n    public function headings(): array {\n        return ['ID', 'Name', 'Created At'];\n    }\n}\n```\n\n" .
               "```php\n// Controller\nuse Maatwebsite\\Excel\\Facades\\Excel;\nreturn Excel::download(new {$model}sExport, '{$entity}s.xlsx');\n```";
    }
}
