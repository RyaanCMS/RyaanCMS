<?php

/**
 * Intent Patterns Genome Library — Multilingual
 *
 * Maps natural-language phrases (any language/script) to structured intent objects.
 * Each intent maps to: action, industry_hints, function_hints, and confidence weight.
 *
 * Languages covered: English, Bengali (Bangla), Hindi, Arabic, French, Spanish, Portuguese,
 *   Malay/Indonesian, Turkish, Chinese (Pinyin keys), German.
 *
 * The IntentEngine scores each pattern against a user prompt via substring/regex matching,
 * then picks the top-scoring intent to pass to BlueprintGenomeEngine.
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
                'can you make', 'help me build', 'help me create',
                // Bengali
                'বানাও', 'তৈরি কর', 'তৈরি করো', 'বানাতে চাই', 'তৈরি করতে চাই', 'বানান',
                'একটা সিস্টেম', 'একটা অ্যাপ', 'একটি অ্যাপ্লিকেশন', 'দরকার', 'চাই',
                // Hindi
                'बनाओ', 'बनाएं', 'बनाना है', 'चाहिए', 'सिस्टम चाहिए', 'बनाना चाहता',
                // Arabic
                'أنشئ', 'ابن', 'أريد', 'أحتاج', 'إنشاء نظام',
                // French
                'créer', 'construire', 'développer', 'j\'ai besoin', 'faire',
                // Spanish
                'crear', 'construir', 'desarrollar', 'necesito', 'quiero', 'hacer',
                // Portuguese
                'criar', 'construir', 'desenvolver', 'preciso', 'quero',
                // Malay/Indonesian
                'buat', 'bina', 'saya perlu', 'buat sistem', 'develop',
                // Turkish
                'oluştur', 'yap', 'geliştir', 'istiyorum',
            ],
            'weight' => 1.0,
        ],

        'add_feature' => [
            'label'    => 'Add a feature to existing system',
            'patterns' => [
                'add', 'add feature', 'add module', 'extend', 'add to', 'include',
                'যোগ কর', 'যোগ করো', 'যুক্ত কর', 'ফিচার যোগ', 'মডিউল যোগ',
                'जोड़ो', 'जोड़ें', 'फीचर जोड़ो',
                'أضف', 'اضافة',
                'ajouter', 'ajout',
                'agregar', 'añadir',
                'adicionar', 'incluir',
                'tambah', 'tambahkan',
                'ekle', 'eklemek',
            ],
            'weight' => 0.9,
        ],

        'fix' => [
            'label'    => 'Fix / Debug',
            'patterns' => [
                'fix', 'debug', 'error', 'bug', 'not working', 'broken', 'issue', 'problem', 'repair',
                'ঠিক কর', 'ঠিক করো', 'ঠিক করা', 'এরর', 'বাগ', 'সমস্যা', 'কাজ করছে না',
                'ठीक करो', 'ठीक करें', 'बग', 'गलती', 'काम नहीं करता',
                'إصلاح', 'خطأ', 'مشكلة',
                'corriger', 'erreur', 'problème',
                'arreglar', 'error', 'problema',
                'corrigir', 'erro', 'problema',
                'perbaiki', 'error', 'masalah',
                'düzelt', 'hata',
            ],
            'weight' => 0.8,
        ],

        'manage' => [
            'label'    => 'Manage / CRUD operations',
            'patterns' => [
                'manage', 'management', 'track', 'monitor', 'handle', 'control', 'organize',
                'crud', 'list', 'view', 'edit', 'delete', 'update', 'record',
                'ম্যানেজ', 'পরিচালনা', 'ট্র্যাক', 'রেকর্ড', 'নিয়ন্ত্রণ',
                'प्रबंधन', 'मैनेज', 'ट्रैक', 'नियंत्रण',
                'إدارة', 'تتبع', 'سجل',
                'gérer', 'gestion', 'suivi',
                'gestionar', 'administrar', 'seguimiento',
                'gerenciar', 'administrar', 'controlar',
                'kelola', 'pantau', 'atur',
                'yönet', 'takip et',
            ],
            'weight' => 0.9,
        ],

        'automate' => [
            'label'    => 'Automate a process',
            'patterns' => [
                'automate', 'automation', 'automatic', 'auto', 'workflow', 'trigger', 'schedule',
                'অটোমেট', 'স্বয়ংক্রিয়', 'অটোমেশন',
                'स्वचालित', 'ऑटोमेट',
                'أتمتة', 'تلقائي',
                'automatiser', 'automatique',
                'automatizar', 'automático',
                'automatizar', 'automático',
                'otomatisasi', 'otomatis',
                'otomatikleştir',
            ],
            'weight' => 0.85,
        ],

        'report' => [
            'label'    => 'Generate reports / analytics',
            'patterns' => [
                'report', 'reports', 'analytics', 'dashboard', 'chart', 'statistics', 'data',
                'analysis', 'insights', 'summary', 'overview',
                'রিপোর্ট', 'ড্যাশবোর্ড', 'বিশ্লেষণ', 'তথ্য',
                'रिपोर्ट', 'डैशबोर्ड', 'विश्लेषण',
                'تقرير', 'لوحة', 'تحليل',
                'rapport', 'tableau de bord', 'analyse',
                'informe', 'tablero', 'análisis',
                'relatório', 'painel', 'análise',
                'laporan', 'dasbor', 'analisis',
                'rapor', 'analiz',
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
                'product catalog', 'cart', 'checkout', 'shopify', 'woocommerce',
                'ই-কমার্স', 'অনলাইন শপ', 'অনলাইন স্টোর', 'শপিং কার্ট', 'মার্কেটপ্লেস',
                'ऑनलाइन स्टोर', 'ई-कॉमर्स', 'ऑनलाइन शॉप',
                'تجارة إلكترونية', 'متجر إلكتروني',
                'boutique en ligne', 'commerce électronique',
                'tienda online', 'comercio electrónico',
                'loja online', 'e-commerce',
                'toko online', 'belanja online',
                'çevrimiçi mağaza', 'e-ticaret',
            ],
            'maps_to' => 'ecommerce',
            'weight'  => 1.5,
        ],

        'hospital' => [
            'patterns' => [
                'hospital', 'clinic', 'healthcare', 'medical', 'doctor', 'patient', 'health',
                'nursing', 'diagnostic', 'lab', 'pharmacy', 'medicine',
                'হাসপাতাল', 'ক্লিনিক', 'ডাক্তার', 'রোগী', 'স্বাস্থ্যসেবা', 'মেডিকেল',
                'अस्पताल', 'क्लिनिक', 'डॉक्टर', 'मरीज', 'स्वास्थ्य',
                'مستشفى', 'عيادة', 'طبيب', 'مريض', 'صحة',
                'hôpital', 'clinique', 'médecin', 'santé',
                'hospital', 'clínica', 'médico', 'salud',
                'rumah sakit', 'klinik', 'dokter', 'pasien',
                'hastane', 'klinik', 'doktor', 'hasta',
            ],
            'maps_to' => 'hospital',
            'weight'  => 1.5,
        ],

        'school' => [
            'patterns' => [
                'school', 'education', 'student', 'teacher', 'learning', 'class', 'exam',
                'academic', 'university', 'college', 'institute', 'coaching', 'tuition',
                'স্কুল', 'বিদ্যালয়', 'শিক্ষার্থী', 'ছাত্র', 'শিক্ষক', 'পরীক্ষা', 'কলেজ', 'বিশ্ববিদ্যালয়',
                'स्कूल', 'शिक्षा', 'छात्र', 'शिक्षक', 'परीक्षा', 'कॉलेज',
                'مدرسة', 'تعليم', 'طالب', 'معلم',
                'école', 'éducation', 'étudiant', 'enseignant',
                'escuela', 'educación', 'estudiante', 'maestro',
                'sekolah', 'siswa', 'guru', 'pendidikan',
                'okul', 'öğrenci', 'öğretmen', 'eğitim',
            ],
            'maps_to' => 'school',
            'weight'  => 1.5,
        ],

        'restaurant' => [
            'patterns' => [
                'restaurant', 'food', 'menu', 'kitchen', 'dining', 'cafe', 'canteen',
                'takeaway', 'delivery', 'table', 'waiter', 'chef', 'pos restaurant',
                'রেস্টুরেন্ট', 'রেস্তোরাঁ', 'খাবার', 'রান্নাঘর', 'মেনু', 'ওয়েটার',
                'रेस्तरां', 'खाना', 'मेनू', 'रसोई',
                'مطعم', 'طعام', 'قائمة', 'مطبخ',
                'restaurant', 'nourriture', 'menu', 'cuisine',
                'restaurante', 'comida', 'menú', 'cocina',
                'restaurante', 'comida', 'cardápio', 'cozinha',
                'restoran', 'makanan', 'menu', 'dapur',
                'restoran', 'yemek', 'menü', 'mutfak',
            ],
            'maps_to' => 'restaurant',
            'weight'  => 1.5,
        ],

        'hotel' => [
            'patterns' => [
                'hotel', 'resort', 'hospitality', 'accommodation', 'room booking', 'check-in', 'check-out',
                'housekeeping', 'guest', 'front desk',
                'হোটেল', 'রিসোর্ট', 'আবাসন', 'রুম বুকিং', 'অতিথি',
                'होटल', 'रिसोर्ट', 'आवास', 'कमरा बुकिंग',
                'فندق', 'منتجع', 'حجز غرفة',
                'hôtel', 'résort', 'hébergement', 'chambre',
                'hotel', 'resort', 'alojamiento', 'habitación',
                'hotel', 'resort', 'acomodação', 'quarto',
                'hotel', 'resor', 'akomodasi', 'kamar',
                'otel', 'tatil köyü', 'konaklama', 'oda',
            ],
            'maps_to' => 'hotel',
            'weight'  => 1.5,
        ],

        'hrm_system' => [
            'patterns' => [
                'hr', 'hrm', 'employee', 'payroll', 'staff', 'attendance', 'leave',
                'human resource', 'workforce', 'salary',
                'এইচআর', 'কর্মী', 'বেতন', 'হাজিরা', 'ছুটি', 'পেরোল',
                'एचआर', 'कर्मचारी', 'वेतन', 'उपस्थिति', 'छुट्टी',
                'موارد بشرية', 'موظف', 'رواتب', 'حضور', 'إجازة',
                'ressources humaines', 'employé', 'salaire', 'présence',
                'recursos humanos', 'empleado', 'nómina', 'asistencia',
                'recursos humanos', 'funcionário', 'folha de pagamento',
                'SDM', 'karyawan', 'gaji', 'absensi',
                'İK', 'çalışan', 'maaş', 'devam',
            ],
            'maps_to' => 'hrm_system',
            'weight'  => 1.4,
        ],

        'accounting' => [
            'patterns' => [
                'accounting', 'finance', 'ledger', 'bookkeeping', 'invoice', 'expense',
                'accounts', 'profit loss', 'balance sheet', 'tax', 'vat', 'gst',
                'অ্যাকাউন্টিং', 'হিসাব', 'আর্থিক', 'ইনভয়েস', 'খরচ', 'কর',
                'लेखांकन', 'वित्त', 'चालान', 'खर्च', 'कर',
                'محاسبة', 'مالية', 'فاتورة', 'مصروف', 'ضريبة',
                'comptabilité', 'finance', 'facture', 'dépense', 'taxe',
                'contabilidad', 'finanzas', 'factura', 'gasto', 'impuesto',
                'contabilidade', 'finanças', 'fatura', 'despesa', 'imposto',
                'akuntansi', 'keuangan', 'faktur', 'pengeluaran', 'pajak',
                'muhasebe', 'finans', 'fatura', 'gider', 'vergi',
            ],
            'maps_to' => 'accounting',
            'weight'  => 1.4,
        ],

        'logistics' => [
            'patterns' => [
                'logistics', 'courier', 'delivery', 'shipping', 'parcel', 'tracking',
                'dispatch', 'warehouse', 'freight', 'cod', 'last mile',
                'লজিস্টিক', 'কুরিয়ার', 'ডেলিভারি', 'শিপিং', 'পার্সেল', 'ট্র্যাকিং',
                'लॉजिस्टिक्स', 'कूरियर', 'डिलीवरी', 'पार्सल', 'ट्रैकिंग',
                'لوجستيات', 'بريد', 'توصيل', 'شحن', 'تتبع',
                'logistique', 'courrier', 'livraison', 'expédition', 'suivi',
                'logística', 'mensajería', 'entrega', 'envío', 'seguimiento',
                'logistik', 'kurir', 'pengiriman', 'pengiriman', 'pelacakan',
                'lojistik', 'kurye', 'teslimat', 'kargo', 'takip',
            ],
            'maps_to' => 'logistics',
            'weight'  => 1.4,
        ],

        'microfinance' => [
            'patterns' => [
                'microfinance', 'loan', 'mfi', 'credit', 'samity', 'savings', 'collection',
                'member', 'field agent', 'installment', 'ngo finance',
                'মাইক্রোফাইন্যান্স', 'ঋণ', 'সমিতি', 'সঞ্চয়', 'কিস্তি', 'সদস্য', 'এমএফআই',
                'माइक्रोफाइनेंस', 'ऋण', 'बचत', 'किश्त', 'सदस्य',
                'تمويل', 'قرض', 'ادخار', 'أقساط',
                'microfinance', 'prêt', 'épargne', 'versement',
                'microfinanzas', 'préstamo', 'ahorros', 'cuota',
                'koperasi', 'pinjaman', 'tabungan', 'cicilan',
                'mikro finansman', 'kredi', 'tasarruf', 'taksit',
            ],
            'maps_to' => 'microfinance',
            'weight'  => 1.5,
        ],

        'real_estate' => [
            'patterns' => [
                'real estate', 'property', 'rent', 'lease', 'tenant', 'landlord', 'apartment',
                'land', 'flat', 'house rental',
                'রিয়েল এস্টেট', 'বাড়ি ভাড়া', 'জমি', 'সম্পত্তি', 'ভাড়াটে', 'জমির মালিক',
                'रियल एस्टेट', 'संपत्ति', 'किराया', 'किरायेदार', 'जमीन',
                'عقارات', 'ملكية', 'إيجار', 'مستأجر',
                'immobilier', 'propriété', 'loyer', 'locataire',
                'bienes raíces', 'propiedad', 'alquiler', 'inquilino',
                'properti', 'sewa', 'penyewa', 'tanah',
                'gayrimenkul', 'mülk', 'kira', 'kiracı',
            ],
            'maps_to' => 'real_estate',
            'weight'  => 1.4,
        ],

        'crm_saas' => [
            'patterns' => [
                'crm', 'sales', 'lead', 'pipeline', 'deal', 'contact', 'customer management',
                'sales management', 'prospect', 'opportunity',
                'সিআরএম', 'বিক্রয়', 'লিড', 'পাইপলাইন', 'গ্রাহক ব্যবস্থাপনা',
                'सीआरएम', 'बिक्री', 'लीड', 'पाइपलाइन',
                'إدارة علاقات العملاء', 'مبيعات', 'عملاء محتملون',
                'gestion des ventes', 'prospects', 'pipeline commercial',
                'gestión de ventas', 'clientes potenciales',
                'manajemen pelanggan', 'penjualan', 'prospek',
                'satış yönetimi', 'müşteri adayı', 'boru hattı',
            ],
            'maps_to' => 'crm_saas',
            'weight'  => 1.4,
        ],

        'manufacturing' => [
            'patterns' => [
                'manufacturing', 'factory', 'production', 'assembly', 'plant', 'machine',
                'quality control', 'bom', 'bill of materials', 'work order',
                'উৎপাদন', 'কারখানা', 'ফ্যাক্টরি', 'মেশিন', 'মান নিয়ন্ত্রণ',
                'निर्माण', 'कारखाना', 'उत्पादन', 'मशीन', 'गुणवत्ता नियंत्रण',
                'تصنيع', 'مصنع', 'إنتاج', 'جودة',
                'fabrication', 'usine', 'production', 'qualité',
                'fabricación', 'fábrica', 'producción', 'calidad',
                'manufaktur', 'pabrik', 'produksi', 'kualitas',
                'imalat', 'fabrika', 'üretim', 'kalite',
            ],
            'maps_to' => 'manufacturing',
            'weight'  => 1.4,
        ],

        'ngo' => [
            'patterns' => [
                'ngo', 'non-profit', 'nonprofit', 'charity', 'social', 'beneficiary',
                'donor', 'fund', 'aid', 'development organization',
                'এনজিও', 'দাতব্য', 'সুবিধাভোগী', 'দাতা', 'তহবিল',
                'एनजीओ', 'गैर-लाभकारी', 'दान', 'सामाजिक',
                'منظمة غير ربحية', 'خيري', 'مانح', 'مستفيد',
                'ONG', 'association', 'bénéficiaire', 'donateur',
                'ONG', 'sin fines de lucro', 'beneficiario', 'donante',
                'LSM', 'nirlaba', 'penerima manfaat', 'donor',
                'STK', 'kar amacı gütmeyen', 'yararlanıcı', 'bağışçı',
            ],
            'maps_to' => 'ngo',
            'weight'  => 1.4,
        ],

        'saas' => [
            'patterns' => [
                'saas', 'subscription', 'software as a service', 'product', 'tenant', 'trial',
                'plan', 'billing cycle', 'upgrade', 'churn',
                'সাস', 'সাবস্ক্রিপশন', 'প্ল্যান', 'বিলিং',
                'सॉफ्टवेयर', 'सदस्यता', 'योजना', 'बिलिंग',
                'برنامج', 'اشتراك', 'خطة', 'فاتورة',
                'logiciel', 'abonnement', 'plan', 'facturation',
                'software', 'suscripción', 'plan', 'facturación',
                'perangkat lunak', 'langganan', 'paket', 'tagihan',
                'yazılım', 'abonelik', 'plan', 'faturalama',
            ],
            'maps_to' => 'saas',
            'weight'  => 1.3,
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // FUNCTION/FEATURE PATTERNS — what capability the user needs
    // ══════════════════════════════════════════════════════════════════════

    'features' => [

        'authentication' => [
            'patterns' => [
                'login', 'register', 'auth', 'authentication', 'user management', 'sign in', 'sign up',
                'password', 'role', 'permission', 'rbac',
                'লগইন', 'রেজিস্ট্রেশন', 'অথেন্টিকেশন', 'পাসওয়ার্ড', 'ভূমিকা',
                'लॉगिन', 'पंजीकरण', 'पासवर्ड', 'भूमिका',
                'تسجيل الدخول', 'مصادقة', 'كلمة مرور',
                'connexion', 'authentification', 'mot de passe',
                'inicio de sesión', 'autenticación', 'contraseña',
            ],
            'maps_to' => 'auth',
            'weight'  => 1.2,
        ],

        'notification' => [
            'patterns' => [
                'notification', 'sms', 'email', 'alert', 'reminder', 'push notification',
                'নোটিফিকেশন', 'এসএমএস', 'ইমেইল', 'রিমাইন্ডার',
                'अधिसूचना', 'एसएमएस', 'ईमेल', 'अनुस्मारक',
                'إشعار', 'رسالة', 'تذكير',
                'notification', 'email', 'rappel',
                'notificación', 'correo', 'recordatorio',
                'notifikasi', 'email', 'pengingat',
                'bildirim', 'e-posta', 'hatırlatıcı',
            ],
            'maps_to' => 'communication',
            'weight'  => 1.1,
        ],

        'reporting_feature' => [
            'patterns' => [
                'report', 'chart', 'graph', 'dashboard', 'analytics', 'statistics', 'export', 'pdf',
                'রিপোর্ট', 'চার্ট', 'গ্রাফ', 'ড্যাশবোর্ড', 'পিডিএফ',
                'रिपोर्ट', 'चार्ट', 'डैशबोर्ड', 'पीडीएफ',
                'تقرير', 'مخطط', 'لوحة',
                'rapport', 'graphique', 'tableau de bord',
                'informe', 'gráfico', 'tablero',
                'laporan', 'grafik', 'dasbor',
                'rapor', 'grafik', 'panel',
            ],
            'maps_to' => 'reporting',
            'weight'  => 1.1,
        ],

        'payment_feature' => [
            'patterns' => [
                'payment', 'pay', 'bkash', 'nagad', 'stripe', 'paypal', 'credit card', 'transaction',
                'online payment', 'mobile banking', 'gateway',
                'পেমেন্ট', 'বিকাশ', 'নগদ', 'পরিশোধ', 'টাকা',
                'भुगतान', 'पेमेंट', 'क्रेडिट कार्ड', 'लेनदेन',
                'دفع', 'بطاقة', 'معاملة',
                'paiement', 'carte de crédit', 'transaction',
                'pago', 'tarjeta', 'transacción',
                'pembayaran', 'kartu kredit', 'transaksi',
                'ödeme', 'kredi kartı', 'işlem',
            ],
            'maps_to' => 'payment',
            'weight'  => 1.2,
        ],

        'inventory_feature' => [
            'patterns' => [
                'inventory', 'stock', 'warehouse', 'product', 'item', 'barcode', 'sku',
                'ইনভেন্টরি', 'স্টক', 'গুদাম', 'পণ্য', 'বারকোড',
                'इन्वेंटरी', 'स्टॉक', 'गोदाम', 'उत्पाद', 'बारकोड',
                'مخزون', 'مستودع', 'منتج', 'باركود',
                'inventaire', 'stock', 'entrepôt', 'produit',
                'inventario', 'stock', 'almacén', 'producto',
                'inventaris', 'stok', 'gudang', 'produk',
                'envanter', 'stok', 'depo', 'ürün',
            ],
            'maps_to' => 'inventory',
            'weight'  => 1.1,
        ],

        'multi_user' => [
            'patterns' => [
                'multi user', 'multiple users', 'team', 'role based', 'access control', 'permission',
                'multi-tenant', 'branch', 'multi-branch', 'franchise',
                'মাল্টি ইউজার', 'দল', 'ব্রাঞ্চ', 'অ্যাক্সেস কন্ট্রোল',
                'बहु उपयोगकर्ता', 'टीम', 'शाखा', 'अनुमति',
                'متعدد المستخدمين', 'فريق', 'فرع', 'إذن',
                'multi-utilisateur', 'équipe', 'agence', 'permission',
                'multiusuario', 'equipo', 'sucursal', 'permiso',
                'multi pengguna', 'tim', 'cabang', 'izin',
                'çok kullanıcılı', 'ekip', 'şube', 'izin',
            ],
            'maps_to' => 'hrm',
            'weight'  => 1.0,
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // SCALE SIGNALS — hints about system size / complexity
    // ══════════════════════════════════════════════════════════════════════

    'scale' => [

        'small' => [
            'patterns' => [
                'small', 'simple', 'basic', 'tiny', 'starter', 'just', 'only', 'single',
                'ছোট', 'সাধারণ', 'বেসিক', 'সহজ',
                'छोटा', 'सरल', 'बुनियादी',
                'صغير', 'بسيط',
                'petit', 'simple', 'basique',
                'pequeño', 'simple', 'básico',
                'kecil', 'sederhana', 'dasar',
                'küçük', 'basit', 'temel',
            ],
            'complexity' => 'small',
            'weight'     => 1.0,
        ],

        'large' => [
            'patterns' => [
                'enterprise', 'large', 'full', 'complete', 'advanced', 'comprehensive',
                'multi-branch', 'scalable', 'production ready', 'full featured',
                'এন্টারপ্রাইজ', 'বড়', 'পূর্ণ', 'সম্পূর্ণ', 'মাল্টি-ব্রাঞ্চ',
                'एंटरप्राइज़', 'बड़ा', 'पूर्ण', 'पूर्ण विशेषताओं',
                'مؤسسة', 'كبير', 'كامل', 'شامل',
                'entreprise', 'grand', 'complet', 'avancé',
                'empresa', 'grande', 'completo', 'avanzado',
                'enterprise', 'besar', 'lengkap', 'canggih',
                'kurumsal', 'büyük', 'tam', 'gelişmiş',
            ],
            'complexity' => 'large',
            'weight'     => 1.0,
        ],
    ],
];
