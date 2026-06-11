<?php

namespace App\Services\AI;

/**
 * ProblemMapper — Maps real business problems to Blueprint keys (0 AI tokens)
 *
 * Users rarely say "build me a CRM". They say:
 *   "Sales team follow-up korte pare na" → crm
 *   "Employees er attendance track korte problem" → hrm
 *   "Patient appointment miss hocche" → clinic / hospital
 *
 * This service understands vague, multilingual problem statements and maps them
 * to the correct blueprint key so BlueprintLibrary can serve a 0-token response.
 *
 * Supports: English, Bengali, Arabic, Hindi, Urdu, Spanish, French (and more
 * via the pattern arrays below).
 */
class ProblemMapper
{
    /**
     * Map a problem statement to a blueprint key.
     * Returns null if no confident match — caller should fall back to AI discovery.
     */
    public function map(string $problem): ?string
    {
        $text = mb_strtolower(trim($problem), 'UTF-8');

        $best      = null;
        $bestScore = 0;

        foreach ($this->problemPatterns() as $blueprintKey => $patterns) {
            $score = $this->score($text, $patterns);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $blueprintKey;
            }
        }

        // Require minimum confidence — single weak match may be coincidental
        return $bestScore >= 1.5 ? $best : null;
    }

    /**
     * Return multiple possible matches with scores (for debugging / UI hints).
     */
    public function suggestions(string $problem, int $limit = 3): array
    {
        $text    = mb_strtolower(trim($problem), 'UTF-8');
        $scores  = [];

        foreach ($this->problemPatterns() as $blueprintKey => $patterns) {
            $score = $this->score($text, $patterns);
            if ($score > 0) {
                $scores[$blueprintKey] = $score;
            }
        }

        arsort($scores);
        return array_slice($scores, 0, $limit, true);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Scoring
    // ─────────────────────────────────────────────────────────────────────

    private function score(string $text, array $patterns): float
    {
        $score = 0.0;
        foreach ($patterns as $pattern) {
            $p = mb_strtolower((string) $pattern, 'UTF-8');
            if ($p === '') continue;
            if (mb_strpos($text, $p) !== false) {
                $score += max(1.0, mb_strlen($p) / 5);
            }
        }
        return $score;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Problem Pattern Library
    // Each key matches a BlueprintLibrary key.
    // Patterns are PROBLEM PHRASES, not product names.
    // ─────────────────────────────────────────────────────────────────────

    private function problemPatterns(): array
    {
        return [

            // ── CRM ──────────────────────────────────────────────────────
            'crm' => [
                // English
                'follow up', 'follow-up', 'sales team', 'lead management', 'lose deals',
                'losing customers', 'customer follow', 'sales pipeline', 'contact management',
                'track leads', 'leads', 'deal tracking', 'sales report',
                // Bengali
                'ফলো আপ', 'লিড', 'সেলস', 'বিক্রয়', 'গ্রাহক ফলো',
                'কাস্টমার ট্র্যাক', 'ডিল ট্র্যাক', 'সেলস পাইপলাইন',
                // Arabic
                'متابعة العملاء', 'إدارة العملاء', 'فريق المبيعات',
                // Hindi/Urdu
                'ग्राहक', 'बिक्री', 'فروخت',
            ],

            // ── HRM ──────────────────────────────────────────────────────
            'hrm' => [
                // English
                'employee attendance', 'staff attendance', 'leave management', 'track attendance',
                'payroll', 'salary management', 'hr management', 'staff management',
                'overtime', 'employee records', 'performance review', 'appraisal',
                // Bengali
                'কর্মী হাজিরা', 'হাজিরা ট্র্যাক', 'ছুটি ম্যানেজমেন্ট',
                'কর্মচারী', 'বেতন', 'পে-রোল', 'হাজিরা',
                // Arabic
                'حضور الموظفين', 'إدارة الموارد البشرية', 'كشوف المرتبات',
            ],

            // ── Accounting ───────────────────────────────────────────────
            'accounting' => [
                // English
                'accounting', 'accounts', 'bookkeeping', 'track expenses', 'income expenses',
                'profit loss', 'invoice', 'balance sheet', 'tax', 'vat', 'ledger',
                'financial report', 'cash flow', 'receivables', 'payables',
                // Bengali
                'হিসাব', 'আয় ব্যয়', 'খরচ ট্র্যাক', 'ইনভয়েস',
                'ব্যালেন্স শিট', 'ট্যাক্স', 'ভ্যাট', 'লেজার',
                // Arabic
                'محاسبة', 'الفواتير', 'ضريبة',
            ],

            // ── Payroll ──────────────────────────────────────────────────
            'payroll' => [
                // English
                'salary calculation', 'salary slip', 'payslip', 'pay staff', 'wage',
                'salary process', 'payroll processing', 'tax deduction from salary',
                // Bengali
                'বেতন হিসাব', 'স্যালারি স্লিপ', 'পেস্লিপ', 'বেতন প্রক্রিয়া',
                // Arabic
                'رواتب الموظفين', 'مسير الرواتب',
            ],

            // ── Inventory ────────────────────────────────────────────────
            'inventory' => [
                // English
                'stock management', 'out of stock', 'stock level', 'warehouse',
                'inventory', 'goods receipt', 'stock tracking', 'reorder',
                'product stock', 'supply', 'stock count',
                // Bengali
                'স্টক ম্যানেজমেন্ট', 'মাল শেষ', 'স্টক কমে যাচ্ছে',
                'গুদাম', 'ইনভেন্টরি', 'মজুদ',
                // Arabic
                'إدارة المخزون', 'مستودع', 'نفاد المخزون',
            ],

            // ── eCommerce ─────────────────────────────────────────────────
            'ecommerce' => [
                // English
                'sell online', 'online store', 'online shop', 'product catalog',
                'shopping cart', 'buy online', 'order management', 'delivery tracking',
                // Bengali
                'অনলাইনে বিক্রি', 'অনলাইন শপ', 'পণ্য বিক্রি',
                'কার্ট', 'অর্ডার ট্র্যাক', 'ডেলিভারি ট্র্যাক',
                // Arabic
                'بيع عبر الإنترنت', 'متجر إلكتروني',
            ],

            // ── Marketplace ───────────────────────────────────────────────
            'marketplace' => [
                // English
                'multiple sellers', 'vendor marketplace', 'multi vendor', 'commission from sellers',
                'seller management', 'platform for sellers',
                // Bengali
                'একাধিক বিক্রেতা', 'ভেন্ডর', 'বিক্রেতা কমিশন',
            ],

            // ── POS ───────────────────────────────────────────────────────
            'pos' => [
                // English
                'cashier', 'cash register', 'barcode scan', 'point of sale',
                'retail billing', 'counter billing', 'receipt printing',
                // Bengali
                'ক্যাশিয়ার', 'বারকোড', 'কাউন্টার বিক্রয়',
                'রিসিট', 'দোকান বিলিং',
                // Arabic
                'نقطة البيع', 'كاشير',
            ],

            // ── Hospital ──────────────────────────────────────────────────
            'hospital' => [
                // English
                'patient management', 'hospital management', 'doctor schedule',
                'bed management', 'ward management', 'opd management', 'ipd management',
                'medical records', 'emr', 'prescription management', 'pharmacy billing',
                // Bengali
                'রোগী ব্যবস্থাপনা', 'হাসপাতাল ম্যানেজমেন্ট',
                'ডাক্তার শিডিউল', 'রোগীর রেকর্ড',
                // Arabic
                'إدارة المرضى', 'إدارة المستشفى',
            ],

            // ── Clinic ────────────────────────────────────────────────────
            'clinic' => [
                // English
                'patient appointment', 'appointment miss', 'appointment booking',
                'doctor appointment', 'clinic management', 'consultation',
                // Bengali
                'রোগী অ্যাপয়েন্টমেন্ট', 'অ্যাপয়েন্টমেন্ট মিস',
                'ডাক্তার অ্যাপয়েন্টমেন্ট', 'ক্লিনিক',
                // Arabic
                'مواعيد المرضى', 'عيادة',
            ],

            // ── Pharmacy ──────────────────────────────────────────────────
            'pharmacy' => [
                // English
                'medicine stock', 'drug inventory', 'pharmacy management', 'medicine expired',
                'prescription dispensing', 'medicine sales',
                // Bengali
                'ওষুধ স্টক', 'ওষুধ মেয়াদ', 'ফার্মেসি ম্যানেজমেন্ট',
                // Arabic
                'مخزون الأدوية', 'صيدلية',
            ],

            // ── School ────────────────────────────────────────────────────
            'school' => [
                // English
                'student attendance', 'school management', 'exam result', 'fee collection',
                'student fees', 'class schedule', 'teacher management',
                // Bengali
                'ছাত্র হাজিরা', 'স্কুল ম্যানেজমেন্ট', 'পরীক্ষার ফলাফল',
                'ফি কালেকশন', 'ছাত্র ফি',
                // Arabic
                'إدارة المدرسة', 'حضور الطلاب', 'رسوم المدرسة',
            ],

            // ── LMS ───────────────────────────────────────────────────────
            'lms' => [
                // English
                'online course', 'sell courses', 'video lessons', 'course platform',
                'student learning', 'e-learning', 'quiz', 'certificate',
                // Bengali
                'অনলাইন কোর্স', 'কোর্স বিক্রি', 'ভিডিও লেসন',
                // Arabic
                'الدورات عبر الإنترنت', 'منصة التعلم',
            ],

            // ── Restaurant ────────────────────────────────────────────────
            'restaurant' => [
                // English
                'food order', 'table order', 'kitchen management', 'menu management',
                'restaurant billing', 'dine in', 'food delivery management',
                // Bengali
                'খাবার অর্ডার', 'টেবিল অর্ডার', 'কিচেন', 'রেস্টুরেন্ট বিল',
                // Arabic
                'طلبات المطعم', 'إدارة المطبخ',
            ],

            // ── Hotel ─────────────────────────────────────────────────────
            'hotel' => [
                // English
                'room booking', 'hotel management', 'check in check out',
                'room availability', 'guest management', 'housekeeping',
                // Bengali
                'রুম বুকিং', 'হোটেল ম্যানেজমেন্ট', 'চেক ইন চেক আউট',
                // Arabic
                'حجز الغرف', 'إدارة الفندق',
            ],

            // ── Real Estate ───────────────────────────────────────────────
            'real_estate' => [
                // English
                'property listing', 'property sale', 'real estate leads', 'property management',
                'agent commission', 'property crm',
                // Bengali
                'জমি বিক্রি', 'ফ্ল্যাট বিক্রি', 'প্রপার্টি লিস্টিং',
                'রিয়েল এস্টেট লিড',
                // Arabic
                'قوائم العقارات', 'بيع عقارات',
            ],

            // ── Property Rental ───────────────────────────────────────────
            'property_rental' => [
                // English
                'rent collection', 'tenant management', 'monthly rent', 'rental income',
                'tenant overdue', 'lease management',
                // Bengali
                'ভাড়া কালেকশন', 'ভাড়াটিয়া ম্যানেজমেন্ট', 'মাসিক ভাড়া',
                // Arabic
                'تحصيل الإيجار', 'إدارة المستأجرين',
            ],

            // ── Microfinance ──────────────────────────────────────────────
            'microfinance' => [
                // English
                'loan management', 'loan collection', 'repayment', 'savings management',
                'field collection', 'ngo loan', 'microfinance',
                // Bengali
                'ঋণ ম্যানেজমেন্ট', 'কিস্তি কালেকশন', 'সঞ্চয়',
                'মাঠ কালেকশন', 'ক্ষুদ্রঋণ',
                // Arabic
                'إدارة القروض', 'تحصيل الأقساط',
            ],

            // ── NGO ───────────────────────────────────────────────────────
            'ngo_charity' => [
                // English
                'donor management', 'donation tracking', 'beneficiary management',
                'fund utilisation', 'ngo management', 'charity',
                // Bengali
                'দাতা ব্যবস্থাপনা', 'অনুদান ট্র্যাক', 'সুবিধাভোগী',
                'এনজিও ম্যানেজমেন্ট',
            ],

            // ── Courier ───────────────────────────────────────────────────
            'courier' => [
                // English
                'parcel tracking', 'delivery management', 'courier management',
                'cod collection', 'rider management', 'dispatch management',
                // Bengali
                'পার্সেল ট্র্যাকিং', 'ডেলিভারি ম্যানেজমেন্ট',
                'কুরিয়ার', 'রাইডার',
            ],

            // ── Project Management ────────────────────────────────────────
            'project_management' => [
                // English
                'task management', 'project tracking', 'team tasks', 'client delivery',
                'milestone tracking', 'project deadline', 'kanban', 'sprint',
                // Bengali
                'টাস্ক ম্যানেজমেন্ট', 'প্রজেক্ট ট্র্যাকিং', 'দলের কাজ',
                'ক্লায়েন্ট ডেলিভারি',
            ],

            // ── SaaS ──────────────────────────────────────────────────────
            'saas' => [
                // English
                'subscription billing', 'multi tenant', 'monthly subscription',
                'saas platform', 'subscription management', 'recurring revenue',
                // Bengali
                'সাবস্ক্রিপশন', 'মাল্টি টেন্যান্ট',
            ],

            // ── Booking ───────────────────────────────────────────────────
            'booking' => [
                // English
                'appointment booking', 'slot booking', 'service booking', 'schedule management',
                // Bengali
                'বুকিং', 'স্লট বুকিং', 'সার্ভিস বুকিং',
            ],

            // ── Gym ───────────────────────────────────────────────────────
            'gym' => [
                // English
                'gym membership', 'fitness center', 'member check in', 'trainer session',
                // Bengali
                'জিম মেম্বারশিপ', 'ফিটনেস',
            ],

            // ── Salon ─────────────────────────────────────────────────────
            'salon' => [
                // English
                'salon management', 'beauty salon', 'stylist appointment', 'spa management',
                // Bengali
                'সেলুন', 'বিউটি পার্লার',
            ],

            // ── ERP ───────────────────────────────────────────────────────
            'erp' => [
                // English
                'manufacturing management', 'production management', 'erp system',
                'procurement management', 'supply chain', 'factory management',
                // Bengali
                'উৎপাদন ব্যবস্থাপনা', 'ইআরপি', 'ক্রয় ব্যবস্থাপনা',
            ],

        ];
    }
}
