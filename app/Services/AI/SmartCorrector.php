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
