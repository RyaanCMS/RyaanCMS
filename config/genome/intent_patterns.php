<?php

/**
 * Intent Patterns Genome Library — All Major World Languages
 *
 * Covers 25+ languages / scripts:
 *   English, Bengali (বাংলা), Hindi (हिन्दी), Arabic (العربية),
 *   French (Français), Spanish (Español), Portuguese (Português),
 *   Malay/Indonesian, Turkish (Türkçe), Chinese (中文/Pinyin),
 *   Russian (Русский), German (Deutsch), Japanese (日本語),
 *   Korean (한국어), Urdu (اردو), Persian/Farsi (فارسی),
 *   Vietnamese (Tiếng Việt), Thai (ภาษาไทย), Italian (Italiano),
 *   Dutch (Nederlands), Swahili (Kiswahili), Tagalog/Filipino,
 *   Tamil (தமிழ்), Ukrainian (Українська), Polish (Polski)
 *
 * The IntentEngine scores each pattern against a user prompt via substring matching.
 */
return [

    // ══════════════════════════════════════════════════════════════════════
    // ACTION PATTERNS — what the user wants to DO
    // ══════════════════════════════════════════════════════════════════════

    'actions' => [

        'build' => [
            'label'    => 'Build / Create a system',
            'patterns' => [
                // English
                'build', 'create', 'make', 'develop', 'design', 'implement', 'setup', 'set up',
                'start', 'launch', 'generate', 'code', 'write code', 'i need', 'i want',
                'can you make', 'help me build', 'help me create', 'i would like',
                // Bengali
                'বানাও', 'তৈরি কর', 'তৈরি করো', 'বানাতে চাই', 'তৈরি করতে চাই', 'বানান',
                'একটা সিস্টেম', 'একটা অ্যাপ', 'একটি অ্যাপ্লিকেশন', 'দরকার', 'চাই',
                'তৈরি', 'বানাতে', 'করতে চাই', 'লাগবে',
                // Hindi
                'बनाओ', 'बनाएं', 'बनाना है', 'चाहिए', 'सिस्टम चाहिए', 'बनाना चाहता',
                'तैयार करो', 'विकसित करो', 'मुझे चाहिए', 'बनवाना है',
                // Arabic
                'أنشئ', 'ابن', 'أريد', 'أحتاج', 'إنشاء نظام', 'أريد بناء', 'انشاء',
                'اصنع', 'طور', 'أريد إنشاء',
                // French
                'créer', 'construire', 'développer', "j'ai besoin", 'faire', 'mettre en place',
                'je veux créer', 'je souhaite', 'fabriquer',
                // Spanish
                'crear', 'construir', 'desarrollar', 'necesito', 'quiero', 'hacer',
                'quiero crear', 'necesito crear', 'implementar',
                // Portuguese
                'criar', 'construir', 'desenvolver', 'preciso', 'quero', 'fazer',
                'quero criar', 'preciso criar', 'implementar',
                // Malay/Indonesian
                'buat', 'bina', 'saya perlu', 'buat sistem', 'develop', 'mau buat', 'ingin buat',
                'saya ingin', 'tolong buat', 'kembangkan',
                // Turkish
                'oluştur', 'yap', 'geliştir', 'istiyorum', 'yapmak istiyorum', 'oluşturmak',
                'kurmak', 'ihtiyacım var',
                // Chinese
                '创建', '制作', '开发', '构建', '我需要', '我想要', '做一个',
                '建立', '设计', '我想创建',
                // Russian
                'создать', 'сделать', 'разработать', 'построить', 'мне нужно', 'хочу создать',
                'нужна система', 'нужно приложение', 'разработка',
                // German
                'erstellen', 'bauen', 'entwickeln', 'ich brauche', 'ich möchte', 'ich will',
                'erstelle', 'aufbauen', 'entwickle',
                // Japanese
                '作る', '作成する', '開発する', '構築する', '必要です', '作りたい',
                'システムが必要', 'アプリを作る', '欲しい',
                // Korean
                '만들다', '만들어', '개발하다', '구축하다', '필요합니다', '만들고 싶다',
                '시스템 만들기', '앱 만들기', '원합니다',
                // Urdu
                'بنانا', 'بنائیں', 'تیار کریں', 'چاہیے', 'سسٹم چاہیے', 'بنانا ہے',
                'تیار کرنا', 'بنانا چاہتا',
                // Persian/Farsi
                'ایجاد کن', 'بساز', 'توسعه بده', 'نیاز دارم', 'می‌خواهم', 'سیستم بساز',
                'برنامه بساز', 'ساخت',
                // Vietnamese
                'tạo', 'xây dựng', 'phát triển', 'tôi cần', 'tôi muốn', 'làm',
                'muốn tạo', 'cần tạo',
                // Thai
                'สร้าง', 'พัฒนา', 'ต้องการ', 'อยากได้', 'ทำ', 'สร้างระบบ',
                // Italian
                'creare', 'costruire', 'sviluppare', 'ho bisogno', 'voglio', 'fare',
                'voglio creare', 'mi serve',
                // Dutch
                'maken', 'bouwen', 'ontwikkelen', 'ik wil', 'ik heb nodig', 'aanmaken',
                'ik wil maken',
                // Swahili
                'jenga', 'tengeneza', 'ninahitaji', 'nataka', 'unda', 'fanya',
                // Tagalog
                'gumawa', 'bumuo', 'kailangan ko', 'gusto ko', 'gawin', 'mag-create',
                // Tamil
                'உருவாக்கு', 'கட்டமைக்க', 'தேவை', 'வேண்டும்', 'செய்யுங்கள்',
                // Ukrainian
                'створити', 'зробити', 'розробити', 'мені потрібно', 'хочу створити',
                // Polish
                'stwórz', 'zbuduj', 'opracuj', 'potrzebuję', 'chcę', 'zrób',
            ],
            'weight' => 1.0,
        ],

        'add_feature' => [
            'label'    => 'Add a feature to existing system',
            'patterns' => [
                'add', 'add feature', 'add module', 'extend', 'add to', 'include', 'append',
                'যোগ কর', 'যোগ করো', 'যুক্ত কর', 'ফিচার যোগ', 'মডিউল যোগ', 'যুক্ত করো',
                'जोड़ो', 'जोड़ें', 'फीचर जोड़ो', 'मॉड्यूल जोड़ें',
                'أضف', 'اضافة', 'أضف ميزة',
                'ajouter', 'ajout', 'ajouter une fonctionnalité',
                'agregar', 'añadir', 'agregar función',
                'adicionar', 'incluir', 'adicionar módulo',
                'tambah', 'tambahkan', 'tambah fitur',
                'ekle', 'eklemek', 'özellik ekle',
                '添加', '增加', '添加功能',
                'добавить', 'добавить функцию',
                'hinzufügen', 'ergänzen', 'Funktion hinzufügen',
                '追加する', '追加',
                '추가하다', '추가',
                'شامل کریں', 'اضافہ کریں',
                'اضافه کن', 'اضافه',
                'thêm', 'thêm tính năng',
                'เพิ่ม', 'เพิ่มฟีเจอร์',
                'aggiungere', 'aggiungere una funzionalità',
                'toevoegen', 'functie toevoegen',
                'ongeza', 'ongeza kipengele',
                'magdagdag', 'idagdag',
                'சேர்க்கவும்', 'சேர்',
                'додати', 'додати функцію',
                'dodać', 'dodaj funkcję',
            ],
            'weight' => 0.9,
        ],

        'fix' => [
            'label'    => 'Fix / Debug',
            'patterns' => [
                'fix', 'debug', 'error', 'bug', 'not working', 'broken', 'issue', 'problem', 'repair',
                'ঠিক কর', 'ঠিক করো', 'ঠিক করা', 'এরর', 'বাগ', 'সমস্যা', 'কাজ করছে না',
                'ठीक करो', 'ठीक करें', 'बग', 'गलती', 'काम नहीं करता',
                'إصلاح', 'خطأ', 'مشكلة', 'لا يعمل',
                'corriger', 'erreur', 'problème', 'ne fonctionne pas',
                'arreglar', 'error', 'problema', 'no funciona',
                'corrigir', 'erro', 'problema', 'não funciona',
                'perbaiki', 'error', 'masalah', 'tidak bekerja',
                'düzelt', 'hata', 'sorun', 'çalışmıyor',
                '修复', '错误', '问题', '不工作',
                'исправить', 'ошибка', 'проблема', 'не работает',
                'reparieren', 'Fehler', 'Problem', 'funktioniert nicht',
                '修正する', 'エラー', '問題',
                '수정하다', '오류', '문제', '작동안해',
                'ٹھیک کریں', 'غلطی', 'مسئلہ',
                'رفع اشکال', 'خطا', 'مشکل',
                'sửa', 'lỗi', 'vấn đề', 'không hoạt động',
                'แก้ไข', 'ข้อผิดพลาด', 'ปัญหา',
                'correggere', 'errore', 'problema', 'non funziona',
                'repareren', 'fout', 'probleem',
                'rekebisha', 'hitilafu', 'tatizo',
                'ayusin', 'error', 'problema',
                'சரிசெய்', 'பிழை', 'சிக்கல்',
                'виправити', 'помилка', 'проблема',
                'naprawić', 'błąd', 'problem',
            ],
            'weight' => 0.8,
        ],

        'manage' => [
            'label'    => 'Manage / CRUD operations',
            'patterns' => [
                'manage', 'management', 'track', 'monitor', 'handle', 'control', 'organize',
                'crud', 'list', 'view', 'edit', 'delete', 'update', 'record', 'dashboard',
                'ম্যানেজ', 'পরিচালনা', 'ট্র্যাক', 'রেকর্ড', 'নিয়ন্ত্রণ', 'ব্যবস্থাপনা',
                'प्रबंधन', 'मैनेज', 'ट्रैक', 'नियंत्रण', 'व्यवस्थापन',
                'إدارة', 'تتبع', 'سجل', 'رصد', 'تحكم',
                'gérer', 'gestion', 'suivi', 'contrôler',
                'gestionar', 'administrar', 'seguimiento', 'controlar',
                'gerenciar', 'administrar', 'controlar', 'acompanhar',
                'kelola', 'pantau', 'atur', 'kelola',
                'yönet', 'takip et', 'kontrol et', 'yönetim',
                '管理', '跟踪', '监控', '控制',
                'управлять', 'управление', 'отслеживать', 'контролировать',
                'verwalten', 'Verwaltung', 'verfolgen', 'steuern',
                '管理する', '追跡する', '監視する',
                '관리하다', '추적하다', '모니터링',
                'منظم کریں', 'ٹریک کریں', 'انتظام',
                'مدیریت کن', 'پیگیری', 'کنترل',
                'quản lý', 'theo dõi', 'kiểm soát',
                'จัดการ', 'ติดตาม', 'ควบคุม',
                'gestire', 'gestione', 'monitorare',
                'beheren', 'beheer', 'bijhouden',
                'simamia', 'fuatilia', 'dhibiti',
                'pamahalaan', 'subaybayan', 'kontrolin',
                'நிர்வகி', 'கண்காணி', 'கட்டுப்படுத்து',
                'керувати', 'управляти', 'відстежувати',
                'zarządzać', 'zarządzanie', 'śledzić',
            ],
            'weight' => 0.9,
        ],

        'automate' => [
            'label'    => 'Automate a process',
            'patterns' => [
                'automate', 'automation', 'automatic', 'auto', 'workflow', 'trigger', 'schedule',
                'অটোমেট', 'স্বয়ংক্রিয়', 'অটোমেশন',
                'स्वचालित', 'ऑटोमेट', 'स्वचालन',
                'أتمتة', 'تلقائي', 'أوتوماتيكي',
                'automatiser', 'automatique', 'automatisation',
                'automatizar', 'automático', 'automatización',
                'automatizar', 'automático', 'automatização',
                'otomatisasi', 'otomatis', 'otomatis',
                'otomatikleştir', 'otomatik', 'otomasyon',
                '自动化', '自动', '自动处理',
                'автоматизировать', 'автоматический', 'автоматизация',
                'automatisieren', 'automatisch', 'Automatisierung',
                '自動化', '自動', '自動化する',
                '자동화', '자동', '워크플로우',
                'خودکار', 'آٹومیشن',
                'خودکار کن', 'اتوماسیون',
                'tự động', 'tự động hóa',
                'อัตโนมัติ', 'ออโตเมชัน',
                'automatizzare', 'automatico', 'automazione',
                'automatiseren', 'automatisch', 'automatisering',
                'fanya kiotomatiki', 'otomatiki',
                'i-automate', 'awtomatiko',
                'தானியங்கு', 'தானியக்கம்',
                'автоматизувати', 'автоматичний',
                'automatyzować', 'automatyczny',
            ],
            'weight' => 0.85,
        ],

        'report' => [
            'label'    => 'Generate reports / analytics',
            'patterns' => [
                'report', 'reports', 'analytics', 'dashboard', 'chart', 'statistics', 'data',
                'analysis', 'insights', 'summary', 'overview', 'kpi',
                'রিপোর্ট', 'ড্যাশবোর্ড', 'বিশ্লেষণ', 'তথ্য', 'পরিসংখ্যান',
                'रिपोर्ट', 'डैशबोर्ड', 'विश्लेषण', 'आंकड़े',
                'تقرير', 'لوحة', 'تحليل', 'إحصائيات',
                'rapport', 'tableau de bord', 'analyse', 'statistiques',
                'informe', 'tablero', 'análisis', 'estadísticas',
                'relatório', 'painel', 'análise', 'estatísticas',
                'laporan', 'dasbor', 'analisis', 'statistik',
                'rapor', 'analiz', 'gösterge paneli', 'istatistik',
                '报告', '仪表板', '分析', '统计',
                'отчёт', 'дашборд', 'аналитика', 'статистика',
                'Bericht', 'Dashboard', 'Analyse', 'Statistiken',
                'レポート', 'ダッシュボード', '分析', '統計',
                '보고서', '대시보드', '분석', '통계',
                'رپورٹ', 'ڈیش بورڈ', 'تجزیہ',
                'گزارش', 'داشبورد', 'تحلیل',
                'báo cáo', 'bảng điều khiển', 'phân tích',
                'รายงาน', 'แดชบอร์ด', 'วิเคราะห์',
                'rapporto', 'cruscotto', 'analisi',
                'rapport', 'dashboard', 'analyse',
                'ripoti', 'dashibodi', 'uchambuzi',
                'ulat', 'dashboard', 'pagsusuri',
                'அறிக்கை', 'டாஷ்போர்டு', 'பகுப்பாய்வு',
                'звіт', 'інформаційна панель', 'аналіз',
                'raport', 'panel', 'analiza',
            ],
            'weight' => 0.85,
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // INDUSTRY PATTERNS — what domain the user is describing
    // ══════════════════════════════════════════════════════════════════════

    'industries' => [

        'ecommerce' => [
            'patterns' => [
                'ecommerce', 'e-commerce', 'online shop', 'online store', 'marketplace', 'sell online',
                'product catalog', 'cart', 'checkout', 'shopify', 'woocommerce', 'web store',
                // Bengali
                'ই-কমার্স', 'অনলাইন শপ', 'অনলাইন স্টোর', 'শপিং কার্ট', 'মার্কেটপ্লেস',
                // Hindi
                'ऑनलाइन स्टोर', 'ई-कॉमर्स', 'ऑनलाइन शॉप', 'ऑनलाइन दुकान',
                // Arabic
                'تجارة إلكترونية', 'متجر إلكتروني', 'التسوق الإلكتروني',
                // French
                'boutique en ligne', 'commerce électronique', 'e-commerce',
                // Spanish
                'tienda online', 'comercio electrónico', 'tienda virtual',
                // Portuguese
                'loja online', 'comércio eletrônico', 'loja virtual',
                // Malay/Indonesian
                'toko online', 'belanja online', 'toko daring', 'jualan online',
                // Turkish
                'çevrimiçi mağaza', 'e-ticaret', 'online alışveriş',
                // Chinese
                '电商', '网上商店', '在线购物', '电子商务',
                // Russian
                'интернет-магазин', 'онлайн-магазин', 'электронная коммерция',
                // German
                'Online-Shop', 'Online-Store', 'E-Commerce',
                // Japanese
                'ネットショップ', 'オンラインショップ', 'EC サイト', '通販',
                // Korean
                '온라인 쇼핑몰', '이커머스', '온라인 상점',
                // Urdu
                'آن لائن شاپ', 'آن لائن اسٹور', 'ای کامرس',
                // Persian
                'فروشگاه آنلاین', 'تجارت الکترونیک',
                // Vietnamese
                'thương mại điện tử', 'cửa hàng trực tuyến',
                // Thai
                'ร้านออนไลน์', 'อีคอมเมิร์ซ',
                // Italian
                'negozio online', 'commercio elettronico',
                // Dutch
                'online winkel', 'e-commerce',
                // Swahili
                'biashara ya mtandaoni', 'duka la mtandaoni',
                // Tagalog
                'online tindahan', 'e-commerce',
                // Tamil
                'ஆன்லைன் கடை', 'மின்வணிகம்',
                // Ukrainian
                'інтернет-магазин', 'електронна комерція',
                // Polish
                'sklep internetowy', 'e-commerce',
            ],
            'maps_to' => 'ecommerce',
            'weight'  => 1.5,
        ],

        'hospital' => [
            'patterns' => [
                'hospital', 'clinic', 'healthcare', 'medical', 'doctor', 'patient', 'health',
                'nursing', 'diagnostic', 'lab', 'pharmacy', 'medicine', 'hms',
                // Bengali
                'হাসপাতাল', 'ক্লিনিক', 'ডাক্তার', 'রোগী', 'স্বাস্থ্যসেবা', 'মেডিকেল', 'চিকিৎসা',
                // Hindi
                'अस्पताल', 'क्लिनिक', 'डॉक्टर', 'मरीज', 'स्वास्थ्य', 'चिकित्सा',
                // Arabic
                'مستشفى', 'عيادة', 'طبيب', 'مريض', 'صحة', 'طب',
                // French
                'hôpital', 'clinique', 'médecin', 'santé', 'patient',
                // Spanish
                'hospital', 'clínica', 'médico', 'salud', 'paciente',
                // Portuguese
                'hospital', 'clínica', 'médico', 'saúde', 'paciente',
                // Malay/Indonesian
                'rumah sakit', 'klinik', 'dokter', 'pasien', 'kesehatan',
                // Turkish
                'hastane', 'klinik', 'doktor', 'hasta', 'sağlık',
                // Chinese
                '医院', '诊所', '医生', '病人', '医疗', '健康',
                // Russian
                'больница', 'клиника', 'врач', 'пациент', 'здоровье',
                // German
                'Krankenhaus', 'Klinik', 'Arzt', 'Patient', 'Gesundheit',
                // Japanese
                '病院', 'クリニック', '医師', '患者', '医療',
                // Korean
                '병원', '클리닉', '의사', '환자', '의료',
                // Urdu
                'ہسپتال', 'کلینک', 'ڈاکٹر', 'مریض', 'صحت',
                // Persian
                'بیمارستان', 'کلینیک', 'دکتر', 'بیمار', 'سلامت',
                // Vietnamese
                'bệnh viện', 'phòng khám', 'bác sĩ', 'bệnh nhân',
                // Thai
                'โรงพยาบาล', 'คลินิก', 'แพทย์', 'ผู้ป่วย',
                // Italian
                'ospedale', 'clinica', 'medico', 'paziente', 'salute',
                // Dutch
                'ziekenhuis', 'kliniek', 'dokter', 'patiënt', 'gezondheid',
                // Swahili
                'hospitali', 'kliniki', 'daktari', 'mgonjwa', 'afya',
                // Tagalog
                'ospital', 'klinika', 'doktor', 'pasyente', 'kalusugan',
                // Tamil
                'மருத்துவமனை', 'கிளினிக்', 'மருத்துவர்', 'நோயாளி',
                // Ukrainian
                'лікарня', 'клініка', 'лікар', 'пацієнт',
                // Polish
                'szpital', 'klinika', 'lekarz', 'pacjent', 'zdrowie',
            ],
            'maps_to' => 'hospital',
            'weight'  => 1.5,
        ],

        'school' => [
            'patterns' => [
                'school', 'education', 'student', 'teacher', 'learning', 'class', 'exam',
                'academic', 'university', 'college', 'institute', 'coaching', 'tuition',
                // Bengali
                'স্কুল', 'বিদ্যালয়', 'শিক্ষার্থী', 'ছাত্র', 'শিক্ষক', 'পরীক্ষা', 'কলেজ', 'বিশ্ববিদ্যালয়',
                // Hindi
                'स्कूल', 'शिक्षा', 'छात्र', 'शिक्षक', 'परीक्षा', 'कॉलेज', 'विश्वविद्यालय',
                // Arabic
                'مدرسة', 'تعليم', 'طالب', 'معلم', 'مدرس', 'جامعة',
                // French
                'école', 'éducation', 'étudiant', 'enseignant', 'université',
                // Spanish
                'escuela', 'educación', 'estudiante', 'maestro', 'universidad',
                // Portuguese
                'escola', 'educação', 'estudante', 'professor', 'universidade',
                // Malay/Indonesian
                'sekolah', 'siswa', 'guru', 'pendidikan', 'universitas',
                // Turkish
                'okul', 'öğrenci', 'öğretmen', 'eğitim', 'üniversite',
                // Chinese
                '学校', '教育', '学生', '老师', '大学', '学院',
                // Russian
                'школа', 'образование', 'студент', 'учитель', 'университет',
                // German
                'Schule', 'Bildung', 'Schüler', 'Lehrer', 'Universität',
                // Japanese
                '学校', '教育', '学生', '先生', '大学',
                // Korean
                '학교', '교육', '학생', '선생님', '대학교',
                // Urdu
                'اسکول', 'تعلیم', 'طالب علم', 'استاد', 'یونیورسٹی',
                // Persian
                'مدرسه', 'آموزش', 'دانش‌آموز', 'معلم', 'دانشگاه',
                // Vietnamese
                'trường học', 'giáo dục', 'học sinh', 'giáo viên', 'đại học',
                // Thai
                'โรงเรียน', 'การศึกษา', 'นักเรียน', 'ครู', 'มหาวิทยาลัย',
                // Italian
                'scuola', 'educazione', 'studente', 'insegnante', 'università',
                // Dutch
                'school', 'onderwijs', 'student', 'leraar', 'universiteit',
                // Swahili
                'shule', 'elimu', 'mwanafunzi', 'mwalimu', 'chuo kikuu',
                // Tagalog
                'paaralan', 'edukasyon', 'estudyante', 'guro', 'unibersidad',
                // Tamil
                'பள்ளி', 'கல்வி', 'மாணவர்', 'ஆசிரியர்', 'பல்கலைக்கழகம்',
                // Ukrainian
                'школа', 'освіта', 'студент', 'вчитель', 'університет',
                // Polish
                'szkoła', 'edukacja', 'uczeń', 'nauczyciel', 'uniwersytet',
            ],
            'maps_to' => 'school',
            'weight'  => 1.5,
        ],

        'restaurant' => [
            'patterns' => [
                'restaurant', 'food', 'menu', 'kitchen', 'dining', 'cafe', 'canteen',
                'takeaway', 'delivery', 'table', 'waiter', 'chef', 'pos restaurant',
                // Bengali
                'রেস্টুরেন্ট', 'রেস্তোরাঁ', 'খাবার', 'রান্নাঘর', 'মেনু', 'ওয়েটার',
                // Hindi
                'रेस्तरां', 'खाना', 'मेनू', 'रसोई', 'खाद्य',
                // Arabic
                'مطعم', 'طعام', 'قائمة', 'مطبخ', 'وجبة',
                // French
                'restaurant', 'nourriture', 'menu', 'cuisine', 'café',
                // Spanish
                'restaurante', 'comida', 'menú', 'cocina', 'cafetería',
                // Portuguese
                'restaurante', 'comida', 'cardápio', 'cozinha',
                // Malay/Indonesian
                'restoran', 'makanan', 'menu', 'dapur', 'warung',
                // Turkish
                'restoran', 'yemek', 'menü', 'mutfak', 'kafe',
                // Chinese
                '餐厅', '餐馆', '菜单', '食物', '厨房',
                // Russian
                'ресторан', 'еда', 'меню', 'кухня', 'кафе',
                // German
                'Restaurant', 'Speisekarte', 'Küche', 'Café',
                // Japanese
                'レストラン', 'メニュー', '食べ物', '厨房',
                // Korean
                '레스토랑', '메뉴', '음식', '주방',
                // Urdu
                'ریستوران', 'کھانا', 'مینو', 'کچن',
                // Persian
                'رستوران', 'غذا', 'منو', 'آشپزخانه',
                // Vietnamese
                'nhà hàng', 'thức ăn', 'menu', 'bếp',
                // Thai
                'ร้านอาหาร', 'อาหาร', 'เมนู', 'ครัว',
                // Italian
                'ristorante', 'cibo', 'menu', 'cucina',
                // Dutch
                'restaurant', 'eten', 'menu', 'keuken',
                // Swahili
                'mkahawa', 'chakula', 'menyu', 'jiko',
                // Tagalog
                'restaurant', 'pagkain', 'menu', 'kusina',
                // Tamil
                'உணவகம்', 'உணவு', 'மெனு', 'சமையலறை',
                // Ukrainian
                'ресторан', 'їжа', 'меню', 'кухня',
                // Polish
                'restauracja', 'jedzenie', 'menu', 'kuchnia',
            ],
            'maps_to' => 'restaurant',
            'weight'  => 1.5,
        ],

        'hotel' => [
            'patterns' => [
                'hotel', 'resort', 'hospitality', 'accommodation', 'room booking', 'check-in', 'check-out',
                'housekeeping', 'guest', 'front desk', 'pms',
                // Bengali
                'হোটেল', 'রিসোর্ট', 'আবাসন', 'রুম বুকিং', 'অতিথি',
                // Hindi
                'होटल', 'रिसोर्ट', 'आवास', 'कमरा बुकिंग',
                // Arabic
                'فندق', 'منتجع', 'حجز غرفة', 'ضيف',
                // French
                'hôtel', 'résort', 'hébergement', 'chambre',
                // Spanish
                'hotel', 'resort', 'alojamiento', 'habitación',
                // Portuguese
                'hotel', 'resort', 'acomodação', 'quarto',
                // Malay/Indonesian
                'hotel', 'resor', 'penginapan', 'kamar',
                // Turkish
                'otel', 'tatil köyü', 'konaklama', 'oda',
                // Chinese
                '酒店', '旅馆', '度假村', '住宿',
                // Russian
                'отель', 'гостиница', 'курорт', 'размещение',
                // German
                'Hotel', 'Resort', 'Unterkunft', 'Zimmer',
                // Japanese
                'ホテル', 'リゾート', '宿泊',
                // Korean
                '호텔', '리조트', '숙박',
                // Urdu
                'ہوٹل', 'رزورٹ', 'قیام',
                // Persian
                'هتل', 'ریزورت', 'اقامتگاه',
                // Vietnamese
                'khách sạn', 'khu nghỉ dưỡng', 'lưu trú',
                // Thai
                'โรงแรม', 'รีสอร์ท', 'ที่พัก',
                // Italian
                'albergo', 'hotel', 'resort', 'alloggio',
                // Dutch
                'hotel', 'resort', 'accommodatie', 'kamer',
                // Swahili
                'hoteli', 'mapumziko', 'bweni',
                // Tagalog
                'hotel', 'resort', 'tirahan',
                // Tamil
                'ஹோட்டல்', 'விடுதி',
                // Ukrainian
                'готель', 'курорт', 'розміщення',
                // Polish
                'hotel', 'resort', 'zakwaterowanie',
            ],
            'maps_to' => 'hotel',
            'weight'  => 1.5,
        ],

        'hrm_system' => [
            'patterns' => [
                'hr', 'hrm', 'employee', 'payroll', 'staff', 'attendance', 'leave',
                'human resource', 'workforce', 'salary', 'hrms',
                // Bengali
                'এইচআর', 'কর্মী', 'বেতন', 'হাজিরা', 'ছুটি', 'পেরোল', 'কর্মচারী',
                // Hindi
                'एचआर', 'कर्मचारी', 'वेतन', 'उपस्थिति', 'छुट्टी', 'एचआरएम',
                // Arabic
                'موارد بشرية', 'موظف', 'رواتب', 'حضور', 'إجازة',
                // French
                'ressources humaines', 'employé', 'salaire', 'présence',
                // Spanish
                'recursos humanos', 'empleado', 'nómina', 'asistencia',
                // Portuguese
                'recursos humanos', 'funcionário', 'folha de pagamento',
                // Malay/Indonesian
                'SDM', 'karyawan', 'gaji', 'absensi', 'sumber daya manusia',
                // Turkish
                'İK', 'çalışan', 'maaş', 'devam', 'insan kaynakları',
                // Chinese
                '人力资源', '员工', '薪资', '考勤', '假期',
                // Russian
                'кадры', 'сотрудник', 'зарплата', 'посещаемость',
                // German
                'Personalwesen', 'Mitarbeiter', 'Gehalt', 'Anwesenheit',
                // Japanese
                '人事', '従業員', '給与', '勤怠', '休暇',
                // Korean
                '인사', '직원', '급여', '출근', '휴가',
                // Urdu
                'ایچ آر', 'ملازم', 'تنخواہ', 'حاضری', 'چھٹی',
                // Persian
                'منابع انسانی', 'کارمند', 'حقوق', 'حضور',
                // Vietnamese
                'nhân sự', 'nhân viên', 'lương', 'chấm công',
                // Thai
                'ทรัพยากรบุคคล', 'พนักงาน', 'เงินเดือน', 'การเข้างาน',
                // Italian
                'risorse umane', 'dipendente', 'stipendio', 'presenze',
                // Dutch
                'HRM', 'werknemer', 'salaris', 'aanwezigheid',
                // Swahili
                'rasilimali watu', 'mfanyakazi', 'mshahara', 'mahudhurio',
                // Tagalog
                'HR', 'empleyado', 'sahod', 'attendance',
                // Tamil
                'மனித வளம்', 'பணியாளர்', 'சம்பளம்', 'வருகை',
                // Ukrainian
                'HR', 'персонал', 'зарплата', 'відвідуваність',
                // Polish
                'HR', 'pracownik', 'wynagrodzenie', 'frekwencja',
            ],
            'maps_to' => 'hrm_system',
            'weight'  => 1.4,
        ],

        'accounting' => [
            'patterns' => [
                'accounting', 'finance', 'ledger', 'bookkeeping', 'invoice', 'expense',
                'accounts', 'profit loss', 'balance sheet', 'tax', 'vat', 'gst',
                // Bengali
                'অ্যাকাউন্টিং', 'হিসাব', 'আর্থিক', 'ইনভয়েস', 'খরচ', 'কর', 'হিসাবরক্ষণ',
                // Hindi
                'लेखांकन', 'वित्त', 'चालान', 'खर्च', 'कर', 'हिसाब',
                // Arabic
                'محاسبة', 'مالية', 'فاتورة', 'مصروف', 'ضريبة',
                // French
                'comptabilité', 'finance', 'facture', 'dépense', 'taxe',
                // Spanish
                'contabilidad', 'finanzas', 'factura', 'gasto', 'impuesto',
                // Portuguese
                'contabilidade', 'finanças', 'fatura', 'despesa', 'imposto',
                // Malay/Indonesian
                'akuntansi', 'keuangan', 'faktur', 'pengeluaran', 'pajak',
                // Turkish
                'muhasebe', 'finans', 'fatura', 'gider', 'vergi',
                // Chinese
                '会计', '财务', '发票', '税', '财务管理',
                // Russian
                'бухгалтерия', 'финансы', 'счёт', 'расходы', 'налог',
                // German
                'Buchhaltung', 'Finanzen', 'Rechnung', 'Ausgaben', 'Steuer',
                // Japanese
                '会計', '財務', '請求書', '経費', '税金',
                // Korean
                '회계', '재무', '세금', '청구서', '지출',
                // Urdu
                'اکاؤنٹنگ', 'مالیات', 'انوائس', 'خرچ', 'ٹیکس',
                // Persian
                'حسابداری', 'مالی', 'فاکتور', 'هزینه', 'مالیات',
                // Vietnamese
                'kế toán', 'tài chính', 'hóa đơn', 'chi phí', 'thuế',
                // Thai
                'บัญชี', 'การเงิน', 'ใบแจ้งหนี้', 'ค่าใช้จ่าย', 'ภาษี',
                // Italian
                'contabilità', 'finanza', 'fattura', 'spesa', 'tasse',
                // Dutch
                'boekhouding', 'financiën', 'factuur', 'kosten', 'belasting',
                // Swahili
                'uhasibu', 'fedha', 'ankara', 'gharama', 'ushuru',
                // Tagalog
                'accounting', 'finance', 'invoice', 'gastos', 'buwis',
                // Tamil
                'கணக்கியல்', 'நிதி', 'விலைப்பட்டியல்', 'வரி',
                // Ukrainian
                'бухгалтерія', 'фінанси', 'рахунок', 'витрати', 'податок',
                // Polish
                'księgowość', 'finanse', 'faktura', 'wydatki', 'podatek',
            ],
            'maps_to' => 'accounting',
            'weight'  => 1.4,
        ],

        'logistics' => [
            'patterns' => [
                'logistics', 'courier', 'delivery', 'shipping', 'parcel', 'tracking',
                'dispatch', 'warehouse', 'freight', 'cod', 'last mile',
                // Bengali
                'লজিস্টিক', 'কুরিয়ার', 'ডেলিভারি', 'শিপিং', 'পার্সেল', 'ট্র্যাকিং',
                // Hindi
                'लॉजिस्टिक्स', 'कूरियर', 'डिलीवरी', 'पार्सल', 'ट्रैकिंग',
                // Arabic
                'لوجستيات', 'بريد', 'توصيل', 'شحن', 'تتبع',
                // French
                'logistique', 'courrier', 'livraison', 'expédition', 'suivi',
                // Spanish
                'logística', 'mensajería', 'entrega', 'envío', 'seguimiento',
                // Portuguese
                'logística', 'entrega', 'envio', 'rastreamento',
                // Malay/Indonesian
                'logistik', 'kurir', 'pengiriman', 'pelacakan',
                // Turkish
                'lojistik', 'kurye', 'teslimat', 'kargo', 'takip',
                // Chinese
                '物流', '快递', '配送', '运输', '追踪',
                // Russian
                'логистика', 'курьер', 'доставка', 'отслеживание',
                // German
                'Logistik', 'Kurier', 'Lieferung', 'Sendungsverfolgung',
                // Japanese
                '物流', '宅配', '配送', '追跡',
                // Korean
                '물류', '배송', '택배', '배달', '추적',
                // Urdu
                'لاجسٹکس', 'کوریئر', 'ڈیلیوری', 'ٹریکنگ',
                // Persian
                'لجستیک', 'پیک', 'تحویل', 'ردیابی',
                // Vietnamese
                'hậu cần', 'giao hàng', 'vận chuyển', 'theo dõi',
                // Thai
                'โลจิสติกส์', 'ส่งของ', 'การจัดส่ง', 'ติดตาม',
                // Italian
                'logistica', 'corriere', 'consegna', 'tracciamento',
                // Dutch
                'logistiek', 'koerier', 'bezorging', 'tracking',
                // Swahili
                'usafirishaji', 'ujumbe', 'utoaji', 'ufuatiliaji',
                // Tagalog
                'logistics', 'courier', 'delivery', 'pagsubaybay',
                // Tamil
                'தளவாட', 'கூரியர்', 'டெலிவரி', 'கண்காணிப்பு',
                // Ukrainian
                'логістика', 'кур\'єр', 'доставка', 'відстеження',
                // Polish
                'logistyka', 'kurier', 'dostawa', 'śledzenie',
            ],
            'maps_to' => 'logistics',
            'weight'  => 1.4,
        ],

        'microfinance' => [
            'patterns' => [
                'microfinance', 'loan', 'mfi', 'credit', 'samity', 'savings', 'collection',
                'member', 'field agent', 'installment', 'ngo finance', 'microloan',
                // Bengali
                'মাইক্রোফাইন্যান্স', 'ঋণ', 'সমিতি', 'সঞ্চয়', 'কিস্তি', 'সদস্য', 'এমএফআই',
                // Hindi
                'माइक्रोफाइनेंस', 'ऋण', 'बचत', 'किश्त', 'सदस्य',
                // Arabic
                'تمويل أصغر', 'قرض', 'ادخار', 'أقساط',
                // French
                'microfinance', 'prêt', 'épargne', 'versement',
                // Spanish
                'microfinanzas', 'préstamo', 'ahorros', 'cuota',
                // Portuguese
                'microfinanças', 'empréstimo', 'poupança',
                // Malay/Indonesian
                'koperasi', 'pinjaman', 'tabungan', 'cicilan',
                // Turkish
                'mikro finansman', 'kredi', 'tasarruf', 'taksit',
                // Chinese
                '小额贷款', '微金融', '储蓄', '分期',
                // Russian
                'микрофинансирование', 'кредит', 'сбережения', 'взнос',
                // German
                'Mikrofinanzierung', 'Kredit', 'Ersparnisse',
                // Japanese
                'マイクロファイナンス', 'ローン', '貯蓄',
                // Korean
                '마이크로 파이낸스', '대출', '저축', '할부',
                // Urdu
                'مائیکرو فنانس', 'قرضہ', 'بچت', 'قسط',
                // Persian
                'خرد مالی', 'وام', 'پس‌انداز', 'قسط',
                // Vietnamese
                'tài chính vi mô', 'vay', 'tiết kiệm', 'trả góp',
                // Thai
                'ไมโครไฟแนนซ์', 'สินเชื่อ', 'เงินออม',
                // Italian
                'microfinanza', 'prestito', 'risparmio',
                // Dutch
                'microfinanciering', 'lening', 'spaargeld',
                // Swahili
                'fedha ndogo', 'mkopo', 'akiba', 'awamu',
                // Tagalog
                'microfinance', 'pautang', 'ipon', 'hulog',
                // Tamil
                'நுண்கடன்', 'கடன்', 'சேமிப்பு',
                // Ukrainian
                'мікрофінансування', 'кредит', 'заощадження',
                // Polish
                'mikrofinanse', 'kredyt', 'oszczędności',
            ],
            'maps_to' => 'microfinance',
            'weight'  => 1.5,
        ],

        'real_estate' => [
            'patterns' => [
                'real estate', 'property', 'rent', 'lease', 'tenant', 'landlord', 'apartment',
                'land', 'flat', 'house rental', 'real estate management',
                // Bengali
                'রিয়েল এস্টেট', 'বাড়ি ভাড়া', 'জমি', 'সম্পত্তি', 'ভাড়াটে', 'জমির মালিক', 'ফ্ল্যাট',
                // Hindi
                'रियल एस्टेट', 'संपत्ति', 'किराया', 'किरायेदार', 'जमीन',
                // Arabic
                'عقارات', 'ملكية', 'إيجار', 'مستأجر', 'أرض',
                // French
                'immobilier', 'propriété', 'loyer', 'locataire', 'terrain',
                // Spanish
                'bienes raíces', 'propiedad', 'alquiler', 'inquilino',
                // Portuguese
                'imóveis', 'propriedade', 'aluguel', 'inquilino',
                // Malay/Indonesian
                'properti', 'sewa', 'penyewa', 'tanah', 'real estate',
                // Turkish
                'gayrimenkul', 'mülk', 'kira', 'kiracı', 'arazi',
                // Chinese
                '房地产', '租房', '房屋', '土地', '租客',
                // Russian
                'недвижимость', 'аренда', 'арендатор', 'земля',
                // German
                'Immobilien', 'Miete', 'Grundstück', 'Mieter',
                // Japanese
                '不動産', '賃貸', '土地', '家賃',
                // Korean
                '부동산', '임대', '토지', '세입자',
                // Urdu
                'جائیداد', 'کرایہ', 'کرایہ دار', 'زمین',
                // Persian
                'مسکن', 'اجاره', 'مستأجر', 'زمین',
                // Vietnamese
                'bất động sản', 'thuê nhà', 'cho thuê', 'đất',
                // Thai
                'อสังหาริมทรัพย์', 'เช่า', 'ผู้เช่า', 'ที่ดิน',
                // Italian
                'immobiliare', 'affitto', 'inquilino', 'terreno',
                // Dutch
                'vastgoed', 'huur', 'huurder', 'grond',
                // Swahili
                'mali isiyohamishika', 'kukodi', 'mkodishaji',
                // Tagalog
                'real estate', 'upa', 'nangungupahan', 'lupa',
                // Tamil
                'ரியல் எஸ்டேட்', 'வாடகை', 'குத்தகை',
                // Ukrainian
                'нерухомість', 'оренда', 'орендар',
                // Polish
                'nieruchomości', 'wynajem', 'najemca', 'ziemia',
            ],
            'maps_to' => 'real_estate',
            'weight'  => 1.4,
        ],

        'crm_saas' => [
            'patterns' => [
                'crm', 'sales', 'lead', 'pipeline', 'deal', 'contact', 'customer management',
                'sales management', 'prospect', 'opportunity', 'sales crm',
                // Bengali
                'সিআরএম', 'বিক্রয়', 'লিড', 'পাইপলাইন', 'গ্রাহক ব্যবস্থাপনা',
                // Hindi
                'सीआरएम', 'बिक्री', 'लीड', 'पाइपलाइन',
                // Arabic
                'إدارة علاقات العملاء', 'مبيعات', 'عملاء محتملون',
                // French
                'gestion des ventes', 'prospects', 'pipeline commercial',
                // Spanish
                'gestión de ventas', 'clientes potenciales', 'pipeline',
                // Portuguese
                'gestão de vendas', 'leads', 'pipeline',
                // Malay/Indonesian
                'manajemen pelanggan', 'penjualan', 'prospek', 'crm',
                // Turkish
                'satış yönetimi', 'müşteri adayı', 'boru hattı', 'crm',
                // Chinese
                '客户关系管理', '销售', '线索', '商机',
                // Russian
                'CRM', 'продажи', 'лиды', 'воронка продаж',
                // German
                'CRM', 'Vertrieb', 'Leads', 'Vertriebspipeline',
                // Japanese
                'CRM', '営業', 'リード', 'パイプライン',
                // Korean
                'CRM', '영업', '리드', '파이프라인',
                // Urdu
                'سی آر ایم', 'فروخت', 'لیڈ', 'گاہک انتظام',
                // Persian
                'CRM', 'فروش', 'سرنخ', 'خط لوله',
                // Vietnamese
                'CRM', 'bán hàng', 'khách hàng tiềm năng',
                // Thai
                'CRM', 'การขาย', 'ลีด',
                // Italian
                'CRM', 'vendite', 'lead', 'pipeline',
                // Dutch
                'CRM', 'verkoop', 'leads', 'pijplijn',
                // Swahili
                'CRM', 'mauzo', 'mteja anayeweza',
                // Tagalog
                'CRM', 'benta', 'lead',
                // Tamil
                'CRM', 'விற்பனை', 'தகவல்',
                // Ukrainian
                'CRM', 'продажі', 'ліди',
                // Polish
                'CRM', 'sprzedaż', 'leady', 'potok sprzedaży',
            ],
            'maps_to' => 'crm_saas',
            'weight'  => 1.4,
        ],

        'manufacturing' => [
            'patterns' => [
                'manufacturing', 'factory', 'production', 'assembly', 'plant', 'machine',
                'quality control', 'bom', 'bill of materials', 'work order',
                // Bengali
                'উৎপাদন', 'কারখানা', 'ফ্যাক্টরি', 'মেশিন', 'মান নিয়ন্ত্রণ',
                // Hindi
                'निर्माण', 'कारखाना', 'उत्पादन', 'मशीन', 'गुणवत्ता नियंत्रण',
                // Arabic
                'تصنيع', 'مصنع', 'إنتاج', 'جودة', 'آلة',
                // French
                'fabrication', 'usine', 'production', 'qualité',
                // Spanish
                'fabricación', 'fábrica', 'producción', 'calidad',
                // Portuguese
                'manufatura', 'fábrica', 'produção', 'qualidade',
                // Malay/Indonesian
                'manufaktur', 'pabrik', 'produksi', 'kualitas',
                // Turkish
                'imalat', 'fabrika', 'üretim', 'kalite',
                // Chinese
                '制造', '工厂', '生产', '质量控制',
                // Russian
                'производство', 'завод', 'качество',
                // German
                'Fertigung', 'Fabrik', 'Produktion', 'Qualitätskontrolle',
                // Japanese
                '製造', '工場', '生産', '品質管理',
                // Korean
                '제조', '공장', '생산', '품질 관리',
                // Urdu
                'مینوفیکچرنگ', 'فیکٹری', 'پیداوار', 'معیار',
                // Persian
                'تولید', 'کارخانه', 'ساخت', 'کنترل کیفیت',
                // Vietnamese
                'sản xuất', 'nhà máy', 'chất lượng',
                // Thai
                'การผลิต', 'โรงงาน', 'การควบคุมคุณภาพ',
                // Italian
                'produzione', 'fabbrica', 'qualità',
                // Dutch
                'productie', 'fabriek', 'kwaliteitscontrole',
                // Swahili
                'utengenezaji', 'kiwanda', 'uzalishaji',
                // Tagalog
                'manufacturing', 'pabrika', 'produksyon',
                // Tamil
                'உற்பத்தி', 'தொழிற்சாலை', 'தர கட்டுப்பாடு',
                // Ukrainian
                'виробництво', 'завод', 'якість',
                // Polish
                'produkcja', 'fabryka', 'kontrola jakości',
            ],
            'maps_to' => 'manufacturing',
            'weight'  => 1.4,
        ],

        'ngo' => [
            'patterns' => [
                'ngo', 'non-profit', 'nonprofit', 'charity', 'social', 'beneficiary',
                'donor', 'fund', 'aid', 'development organization',
                // Bengali
                'এনজিও', 'দাতব্য', 'সুবিধাভোগী', 'দাতা', 'তহবিল', 'সমাজ সেবা',
                // Hindi
                'एनजीओ', 'गैर-लाभकारी', 'दान', 'सामाजिक',
                // Arabic
                'منظمة غير ربحية', 'خيري', 'مانح', 'مستفيد',
                // French
                'ONG', 'association', 'bénéficiaire', 'donateur',
                // Spanish
                'ONG', 'sin fines de lucro', 'beneficiario', 'donante',
                // Portuguese
                'ONG', 'sem fins lucrativos', 'beneficiário', 'doador',
                // Malay/Indonesian
                'LSM', 'nirlaba', 'penerima manfaat', 'donor',
                // Turkish
                'STK', 'kar amacı gütmeyen', 'yararlanıcı', 'bağışçı',
                // Chinese
                '非政府组织', '非营利', '受益人', '捐助者',
                // Russian
                'НКО', 'некоммерческий', 'благополучатель',
                // German
                'NGO', 'gemeinnützig', 'Begünstigter', 'Spender',
                // Japanese
                'NGO', '非営利', '受益者', '寄付者',
                // Korean
                'NGO', '비영리', '수혜자', '기부자',
                // Urdu
                'این جی او', 'غیر منافع بخش', 'مستفید', 'عطیہ دہندہ',
                // Persian
                'سازمان غیردولتی', 'غیرانتفاعی', 'بهره‌مند',
                // Vietnamese
                'NGO', 'phi lợi nhuận', 'người thụ hưởng', 'nhà tài trợ',
                // Thai
                'NGO', 'ไม่แสวงหากำไร', 'ผู้รับประโยชน์',
                // Italian
                'ONG', 'senza scopo di lucro', 'beneficiario', 'donatore',
                // Dutch
                'NGO', 'non-profit', 'begunstigde', 'donateur',
                // Swahili
                'NGO', 'si ya faida', 'mnufaika', 'mchangiaji',
                // Tagalog
                'NGO', 'non-profit', 'benepisyaryo', 'donor',
                // Tamil
                'தன்னார்வ நிறுவனம்', 'தொண்டு', 'பயனாளி',
                // Ukrainian
                'НГО', 'некомерційний', 'бенефіціар',
                // Polish
                'NGO', 'non-profit', 'beneficjent', 'darczyńca',
            ],
            'maps_to' => 'ngo',
            'weight'  => 1.4,
        ],

        'saas' => [
            'patterns' => [
                'saas', 'subscription', 'software as a service', 'product', 'tenant', 'trial',
                'plan', 'billing cycle', 'upgrade', 'churn', 'multi-tenant',
                // Bengali
                'সাস', 'সাবস্ক্রিপশন', 'প্ল্যান', 'বিলিং', 'মাল্টি-টেন্যান্ট',
                // Hindi
                'सॉफ्टवेयर', 'सदस्यता', 'योजना', 'बिलिंग',
                // Arabic
                'برنامج', 'اشتراك', 'خطة', 'فاتورة',
                // French
                'logiciel', 'abonnement', 'plan', 'facturation',
                // Spanish
                'software', 'suscripción', 'plan', 'facturación',
                // Portuguese
                'software', 'assinatura', 'plano',
                // Malay/Indonesian
                'perangkat lunak', 'langganan', 'paket', 'tagihan',
                // Turkish
                'yazılım', 'abonelik', 'plan', 'faturalama',
                // Chinese
                'SaaS', '订阅', '计划', '多租户',
                // Russian
                'SaaS', 'подписка', 'план',
                // German
                'SaaS', 'Abonnement', 'Plan',
                // Japanese
                'SaaS', 'サブスクリプション', 'プラン',
                // Korean
                'SaaS', '구독', '플랜',
                // Urdu
                'سافٹ ویئر سروس', 'سبسکرپشن', 'پلان',
                // Persian
                'نرم‌افزار سرویس', 'اشتراک', 'طرح',
                // Vietnamese
                'SaaS', 'đăng ký', 'gói dịch vụ',
                // Thai
                'SaaS', 'บริการซอฟต์แวร์', 'แผน',
                // Italian
                'SaaS', 'abbonamento', 'piano',
                // Dutch
                'SaaS', 'abonnement', 'plan',
                // Swahili
                'SaaS', 'usajili', 'mpango',
                // Tagalog
                'SaaS', 'subscription', 'plan',
                // Tamil
                'SaaS', 'சந்தா', 'திட்டம்',
                // Ukrainian
                'SaaS', 'підписка', 'план',
                // Polish
                'SaaS', 'subskrypcja', 'plan',
            ],
            'maps_to' => 'saas',
            'weight'  => 1.3,
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // FEATURE PATTERNS — what capability the user needs
    // ══════════════════════════════════════════════════════════════════════

    'features' => [

        'authentication' => [
            'patterns' => [
                'login', 'register', 'auth', 'authentication', 'user management', 'sign in', 'sign up',
                'password', 'role', 'permission', 'rbac', 'access control',
                // Bengali
                'লগইন', 'রেজিস্ট্রেশন', 'অথেন্টিকেশন', 'পাসওয়ার্ড', 'ভূমিকা',
                // Hindi
                'लॉगिन', 'पंजीकरण', 'पासवर्ड', 'भूमिका', 'प्रमाणीकरण',
                // Arabic
                'تسجيل الدخول', 'مصادقة', 'كلمة مرور', 'دور',
                // French
                'connexion', 'authentification', 'mot de passe', 'rôle',
                // Spanish
                'inicio de sesión', 'autenticación', 'contraseña', 'rol',
                // Portuguese
                'login', 'autenticação', 'senha', 'função',
                // Malay/Indonesian
                'masuk', 'autentikasi', 'kata sandi', 'peran',
                // Turkish
                'giriş', 'kimlik doğrulama', 'şifre', 'rol',
                // Chinese
                '登录', '认证', '密码', '角色', '权限',
                // Russian
                'вход', 'аутентификация', 'пароль', 'роль',
                // German
                'Anmeldung', 'Authentifizierung', 'Passwort', 'Rolle',
                // Japanese
                'ログイン', '認証', 'パスワード', '役割',
                // Korean
                '로그인', '인증', '비밀번호', '역할',
                // Urdu
                'لاگ ان', 'تصدیق', 'پاسورڈ', 'کردار',
                // Persian
                'ورود', 'احراز هویت', 'رمز عبور', 'نقش',
                // Vietnamese
                'đăng nhập', 'xác thực', 'mật khẩu', 'vai trò',
                // Thai
                'เข้าสู่ระบบ', 'การยืนยันตัวตน', 'รหัสผ่าน',
                // Italian
                'accesso', 'autenticazione', 'password', 'ruolo',
                // Dutch
                'inloggen', 'authenticatie', 'wachtwoord', 'rol',
                // Swahili
                'kuingia', 'uthibitishaji', 'nywila', 'jukumu',
                // Tagalog
                'login', 'authentication', 'password', 'role',
                // Tamil
                'உள்நுழைவு', 'அங்கீகாரம்', 'கடவுச்சொல்',
                // Ukrainian
                'вхід', 'аутентифікація', 'пароль', 'роль',
                // Polish
                'logowanie', 'uwierzytelnianie', 'hasło', 'rola',
            ],
            'maps_to' => 'auth',
            'weight'  => 1.2,
        ],

        'notification' => [
            'patterns' => [
                'notification', 'sms', 'email', 'alert', 'reminder', 'push notification',
                // Bengali
                'নোটিফিকেশন', 'এসএমএস', 'ইমেইল', 'রিমাইন্ডার', 'বিজ্ঞপ্তি',
                // Hindi
                'अधिसूचना', 'एसएमएस', 'ईमेल', 'अनुस्मारक',
                // Arabic
                'إشعار', 'رسالة', 'تذكير', 'تنبيه',
                // French
                'notification', 'email', 'rappel', 'alerte',
                // Spanish
                'notificación', 'correo', 'recordatorio', 'alerta',
                // Portuguese
                'notificação', 'e-mail', 'lembrete', 'alerta',
                // Malay/Indonesian
                'notifikasi', 'email', 'pengingat', 'pemberitahuan',
                // Turkish
                'bildirim', 'e-posta', 'hatırlatıcı', 'uyarı',
                // Chinese
                '通知', '短信', '提醒', '邮件',
                // Russian
                'уведомление', 'СМС', 'напоминание', 'оповещение',
                // German
                'Benachrichtigung', 'E-Mail', 'Erinnerung',
                // Japanese
                '通知', 'メール', 'リマインダー',
                // Korean
                '알림', '이메일', '리마인더',
                // Urdu
                'اطلاع', 'ایس ایم ایس', 'یاد دہانی',
                // Persian
                'اعلان', 'پیامک', 'یادآوری',
                // Vietnamese
                'thông báo', 'email', 'nhắc nhở',
                // Thai
                'การแจ้งเตือน', 'อีเมล', 'เตือนความจำ',
                // Italian
                'notifica', 'email', 'promemoria',
                // Dutch
                'melding', 'e-mail', 'herinnering',
                // Swahili
                'arifa', 'barua pepe', 'ukumbusho',
                // Tagalog
                'notification', 'email', 'paalala',
                // Tamil
                'அறிவிப்பு', 'மின்னஞ்சல்', 'நினைவூட்டல்',
                // Ukrainian
                'сповіщення', 'електронна пошта', 'нагадування',
                // Polish
                'powiadomienie', 'email', 'przypomnienie',
            ],
            'maps_to' => 'communication',
            'weight'  => 1.1,
        ],

        'payment_feature' => [
            'patterns' => [
                'payment', 'pay', 'bkash', 'nagad', 'stripe', 'paypal', 'credit card', 'transaction',
                'online payment', 'mobile banking', 'gateway', 'checkout',
                // Bengali
                'পেমেন্ট', 'বিকাশ', 'নগদ', 'পরিশোধ', 'টাকা', 'অর্থ প্রদান',
                // Hindi
                'भुगतान', 'पेमेंट', 'क्रेडिट कार्ड', 'लेनदेन', 'पेटीएम',
                // Arabic
                'دفع', 'بطاقة', 'معاملة', 'مدفوع',
                // French
                'paiement', 'carte de crédit', 'transaction', 'payer',
                // Spanish
                'pago', 'tarjeta', 'transacción', 'pagar',
                // Portuguese
                'pagamento', 'cartão', 'transação', 'pagar',
                // Malay/Indonesian
                'pembayaran', 'kartu kredit', 'transaksi', 'bayar',
                // Turkish
                'ödeme', 'kredi kartı', 'işlem', 'ödemek',
                // Chinese
                '支付', '付款', '信用卡', '微信支付', '支付宝',
                // Russian
                'оплата', 'платёж', 'кредитная карта',
                // German
                'Zahlung', 'Kreditkarte', 'Bezahlung',
                // Japanese
                '支払い', 'クレジットカード', '決済',
                // Korean
                '결제', '지불', '신용카드',
                // Urdu
                'ادائیگی', 'کریڈٹ کارڈ', 'لین دین',
                // Persian
                'پرداخت', 'کارت اعتباری', 'تراکنش',
                // Vietnamese
                'thanh toán', 'thẻ tín dụng', 'giao dịch',
                // Thai
                'ชำระเงิน', 'บัตรเครดิต', 'การชำระเงิน',
                // Italian
                'pagamento', 'carta di credito', 'transazione',
                // Dutch
                'betaling', 'creditcard', 'transactie',
                // Swahili
                'malipo', 'kadi ya mkopo', 'muamala',
                // Tagalog
                'bayad', 'credit card', 'transaksyon',
                // Tamil
                'கட்டணம்', 'கடன் அட்டை', 'பரிவர்த்தனை',
                // Ukrainian
                'оплата', 'кредитна картка', 'транзакція',
                // Polish
                'płatność', 'karta kredytowa', 'transakcja',
            ],
            'maps_to' => 'payment',
            'weight'  => 1.2,
        ],

        'inventory_feature' => [
            'patterns' => [
                'inventory', 'stock', 'warehouse', 'product', 'item', 'barcode', 'sku',
                // Bengali
                'ইনভেন্টরি', 'স্টক', 'গুদাম', 'পণ্য', 'বারকোড',
                // Hindi
                'इन्वेंटरी', 'स्टॉक', 'गोदाम', 'उत्पाद', 'बारकोड',
                // Arabic
                'مخزون', 'مستودع', 'منتج', 'باركود',
                // French
                'inventaire', 'stock', 'entrepôt', 'produit',
                // Spanish
                'inventario', 'stock', 'almacén', 'producto',
                // Portuguese
                'inventário', 'estoque', 'armazém', 'produto',
                // Malay/Indonesian
                'inventaris', 'stok', 'gudang', 'produk',
                // Turkish
                'envanter', 'stok', 'depo', 'ürün',
                // Chinese
                '库存', '仓库', '产品', '库存管理',
                // Russian
                'инвентарь', 'склад', 'товар',
                // German
                'Inventar', 'Lager', 'Produkt',
                // Japanese
                '在庫', '倉庫', '製品',
                // Korean
                '재고', '창고', '제품',
                // Urdu
                'انوینٹری', 'گودام', 'مصنوعات',
                // Persian
                'موجودی', 'انبار', 'محصول',
                // Vietnamese
                'hàng tồn kho', 'kho hàng', 'sản phẩm',
                // Thai
                'สินค้าคงคลัง', 'คลังสินค้า', 'สินค้า',
                // Italian
                'inventario', 'magazzino', 'prodotto',
                // Dutch
                'inventaris', 'magazijn', 'product',
                // Swahili
                'hesabu ya bidhaa', 'ghala', 'bidhaa',
                // Tagalog
                'imbentaryo', 'bodega', 'produkto',
                // Tamil
                'சரக்கு', 'கிடங்கு', 'தயாரிப்பு',
                // Ukrainian
                'інвентар', 'склад', 'товар',
                // Polish
                'inwentarz', 'magazyn', 'produkt',
            ],
            'maps_to' => 'inventory',
            'weight'  => 1.1,
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SCALE SIGNALS — hints about system size / complexity
    // ══════════════════════════════════════════════════════════════════════

    'scale' => [

        'small' => [
            'patterns' => [
                'small', 'simple', 'basic', 'tiny', 'starter', 'just', 'only', 'single', 'lite',
                // Bengali
                'ছোট', 'সাধারণ', 'বেসিক', 'সহজ', 'সরল',
                // Hindi
                'छोटा', 'सरल', 'बुनियादी', 'साधारण',
                // Arabic
                'صغير', 'بسيط', 'أساسي',
                // French
                'petit', 'simple', 'basique',
                // Spanish
                'pequeño', 'simple', 'básico', 'sencillo',
                // Portuguese
                'pequeno', 'simples', 'básico',
                // Malay/Indonesian
                'kecil', 'sederhana', 'dasar',
                // Turkish
                'küçük', 'basit', 'temel',
                // Chinese
                '简单', '基础', '小型',
                // Russian
                'простой', 'маленький', 'базовый',
                // German
                'einfach', 'klein', 'grundlegend',
                // Japanese
                'シンプル', '小さい', '基本的',
                // Korean
                '간단한', '작은', '기본',
                // Urdu
                'سادہ', 'چھوٹا', 'بنیادی',
                // Persian
                'ساده', 'کوچک', 'پایه',
                // Vietnamese
                'đơn giản', 'nhỏ', 'cơ bản',
                // Thai
                'เล็ก', 'ง่าย', 'พื้นฐาน',
                // Italian
                'semplice', 'piccolo', 'di base',
                // Dutch
                'eenvoudig', 'klein', 'basis',
                // Swahili
                'ndogo', 'rahisi', 'msingi',
                // Tagalog
                'maliit', 'simple', 'basic',
                // Tamil
                'எளிய', 'சிறிய', 'அடிப்படை',
                // Ukrainian
                'простий', 'маленький', 'базовий',
                // Polish
                'prosty', 'mały', 'podstawowy',
            ],
            'complexity' => 'small',
            'weight'     => 1.0,
        ],

        'large' => [
            'patterns' => [
                'enterprise', 'large', 'full', 'complete', 'advanced', 'comprehensive',
                'multi-branch', 'scalable', 'production ready', 'full featured',
                // Bengali
                'এন্টারপ্রাইজ', 'বড়', 'পূর্ণ', 'সম্পূর্ণ', 'মাল্টি-ব্রাঞ্চ',
                // Hindi
                'एंटरप्राइज़', 'बड़ा', 'पूर्ण', 'उन्नत',
                // Arabic
                'مؤسسة', 'كبير', 'كامل', 'شامل',
                // French
                'entreprise', 'grand', 'complet', 'avancé',
                // Spanish
                'empresa', 'grande', 'completo', 'avanzado',
                // Portuguese
                'empresa', 'grande', 'completo', 'avançado',
                // Malay/Indonesian
                'enterprise', 'besar', 'lengkap', 'canggih',
                // Turkish
                'kurumsal', 'büyük', 'tam', 'gelişmiş',
                // Chinese
                '企业', '大型', '全功能', '高级',
                // Russian
                'предприятие', 'большой', 'полный', 'корпоративный',
                // German
                'Unternehmen', 'groß', 'vollständig', 'erweitert',
                // Japanese
                'エンタープライズ', '大規模', '完全', '高度',
                // Korean
                '엔터프라이즈', '대규모', '완전한', '고급',
                // Urdu
                'انٹرپرائز', 'بڑا', 'مکمل', 'جدید',
                // Persian
                'سازمانی', 'بزرگ', 'کامل', 'پیشرفته',
                // Vietnamese
                'doanh nghiệp', 'lớn', 'đầy đủ', 'nâng cao',
                // Thai
                'องค์กร', 'ใหญ่', 'สมบูรณ์', 'ขั้นสูง',
                // Italian
                'impresa', 'grande', 'completo', 'avanzato',
                // Dutch
                'onderneming', 'groot', 'volledig', 'geavanceerd',
                // Swahili
                'biashara kubwa', 'kubwa', 'kamili', 'ya juu',
                // Tagalog
                'enterprise', 'malaki', 'kumpleto', 'advanced',
                // Tamil
                'நிறுவன', 'பெரிய', 'முழுமையான', 'மேம்பட்ட',
                // Ukrainian
                'підприємство', 'великий', 'повний', 'корпоративний',
                // Polish
                'przedsiębiorstwo', 'duży', 'pełny', 'zaawansowany',
            ],
            'complexity' => 'large',
            'weight'     => 1.0,
        ],
    ],
];
