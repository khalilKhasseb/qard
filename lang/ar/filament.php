<?php

return [
    // Navigation Groups
    'navigation' => [
        'groups' => [
            'user_management' => 'إدارة المستخدمين',
            'finance' => 'المالية',
            'cards' => 'البطاقات',
            'system_management' => 'إدارة النظام',
            'management' => 'الإدارة',
            'settings' => 'الإعدادات',
        ],
    ],

    // Common Labels
    'common' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'password' => 'كلمة المرور',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'actions' => 'الإجراءات',
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'create' => 'إنشاء',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'confirm' => 'تأكيد',
        'search' => 'بحث',
        'filter' => 'تصفية',
        'all' => 'الكل',
        'yes' => 'نعم',
        'no' => 'لا',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'enabled' => 'مفعل',
        'disabled' => 'معطل',
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتمل',
        'failed' => 'فاشل',
        'user' => 'المستخدم',
        'date' => 'التاريخ',
        'amount' => 'المبلغ',
        'description' => 'الوصف',
        'notes' => 'ملاحظات',
        'type' => 'النوع',
        'key' => 'المفتاح',
        'value' => 'القيمة',
        'verified' => 'موثق',
        'password' => 'كلمة المرور',
    ],

    // User Resource
    'users' => [
        'label' => 'مستخدم',
        'plural' => 'المستخدمون',
        'navigation_label' => 'المستخدمون',

        'sections' => [
            'user_information' => 'معلومات المستخدم',
            'subscription' => 'الاشتراك',
            'preferences' => 'التفضيلات',
        ],

        'fields' => [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'is_admin' => 'مدير',
            'admin_helper' => 'المدراء لديهم صلاحية كاملة للوصول إلى لوحة التحكم',
            'email_verified_at' => 'تاريخ التحقق من البريد',
            'email_verified_helper' => 'اضبط هذا للتحقق يدويًا من بريد المستخدم',
            'subscription_tier' => 'مستوى الاشتراك',
            'subscription_status' => 'حالة الاشتراك',
            'subscription_expires_at' => 'تاريخ انتهاء الاشتراك',
            'language' => 'اللغة',
            'cards_count' => 'البطاقات',
            'last_login' => 'آخر تسجيل دخول',
        ],

        'tiers' => [
            'free' => 'مجاني',
            'pro' => 'احترافي',
            'business' => 'أعمال',
        ],

        'statuses' => [
            'pending' => 'قيد الانتظار',
            'active' => 'نشط',
            'canceled' => 'ملغي',
            'expired' => 'منتهي',
        ],

        'filters' => [
            'unverified' => 'مستخدمون غير موثقين',
            'verified' => 'مستخدمون موثقون',
        ],

        'actions' => [
            'verify' => 'التحقق من البريد',
            'unverify' => 'إلغاء التحقق',
            'verify_selected' => 'التحقق من المحددين',
            'grant_addon' => 'منح إضافة',
        ],

        'notifications' => [
            'verified' => 'تم التحقق من المستخدم بنجاح',
            'verified_body' => 'تم التحقق من بريد :name.',
            'unverified' => 'تم إلغاء التحقق من المستخدم',
            'unverified_body' => 'تم إزالة التحقق من البريد لـ :name.',
            'bulk_verified' => 'تم التحقق من المستخدمين',
            'bulk_verified_body' => 'تم التحقق من :count مستخدم.',
            'addon_granted' => 'تم منح الإضافة بنجاح',
            'addon_granted_body' => 'تم منح :addon لـ :user.',
        ],
    ],

    // Payment Resource
    'payments' => [
        'label' => 'دفعة',
        'plural' => 'المدفوعات',
        'navigation_label' => 'المدفوعات',

        'sections' => [
            'payment_details' => 'تفاصيل الدفعة',
            'payment_method' => 'طريقة الدفع',
            'transaction_information' => 'معلومات المعاملة',
        ],

        'fields' => [
            'transaction_id' => 'رقم المعاملة',
            'user' => 'المستخدم',
            'plan' => 'الخطة',
            'amount' => 'المبلغ',
            'currency' => 'العملة',
            'payment_method' => 'طريقة الدفع',
            'status' => 'الحالة',
            'gateway_reference' => 'مرجع لحظة',
            'notes' => 'ملاحظات',
            'paid_at' => 'تاريخ الدفع',
            'metadata' => 'البيانات الإضافية',
            'not_paid' => 'لم يتم الدفع',
        ],

        'methods' => [
            'cash' => 'نقدي',
            'lahza' => 'بوابة لحظة',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
        ],

        'statuses' => [
            'pending' => 'قيد الانتظار',
            'completed' => 'مكتمل',
            'failed' => 'فاشل',
            'refunded' => 'مسترجع',
        ],

        'actions' => [
            'verify' => 'التحقق',
            'verify_with_lahza' => 'التحقق من لحظة',
            'verify_heading' => 'التحقق من الدفعة مع لحظة',
            'verify_description' => 'سيتم التحقق من حالة الدفعة مع لحظة وتفعيل الاشتراك إذا نجحت.',
            'confirm' => 'تأكيد',
            'confirm_heading' => 'تأكيد الدفعة',
            'confirm_description' => 'هل أنت متأكد من تأكيد هذه الدفعة؟ سيتم تفعيل اشتراك المستخدم.',
            'refund' => 'استرجاع',
            'refund_heading' => 'استرجاع الدفعة',
            'refund_description' => 'سيتم استرجاع الدفعة وإلغاء اشتراك المستخدم.',
            'refund_amount' => 'مبلغ الاسترجاع',
            'refund_reason' => 'سبب الاسترجاع',
        ],

        'notifications' => [
            'verified' => 'تم التحقق من الدفعة وتفعيل الاشتراك',
            'verification_failed' => 'فشل التحقق',
            'confirmed' => 'تم تأكيد الدفعة',
            'refunded' => 'تم استرجاع الدفعة',
            'refund_failed' => 'فشل الاسترجاع',
        ],
    ],

    // Subscription Plan Resource
    'subscription_plans' => [
        'label' => 'خطة اشتراك',
        'plural' => 'خطط الاشتراك',
        'navigation_label' => 'خطط الاشتراك',

        'sections' => [
            'plan_details' => 'تفاصيل الخطة',
            'pricing' => 'التسعير',
            'limits' => 'الحدود',
            'ai_translation' => 'الترجمة بالذكاء الاصطناعي',
            'features' => 'الميزات',
            'additional_features' => 'ميزات إضافية',
        ],

        'fields' => [
            'name' => 'الاسم',
            'slug' => 'المعرف',
            'description' => 'الوصف',
            'price' => 'السعر',
            'billing_cycle' => 'دورة الفوترة',
            'cards_limit' => 'حد البطاقات',
            'themes_limit' => 'حد السمات',
            'translation_credits_monthly' => 'رصيد الترجمة الشهري',
            'translation_credits_helper' => 'عدد الترجمات المجانية شهريًا.',
            'unlimited_translations' => 'ترجمات غير محدودة',
            'per_credit_cost' => 'تكلفة الرصيد الواحد',
            'per_credit_helper' => 'تكلفة الترجمات بعد استخدام الرصيد المجاني.',
            'custom_css_allowed' => 'CSS مخصص',
            'analytics_enabled' => 'التحليلات مفعلة',
            'nfc_enabled' => 'دعم NFC',
            'custom_domain_allowed' => 'نطاق مخصص',
            'is_active' => 'نشط',
            'features' => 'الميزات',
            'feature' => 'الميزة',
            'add_feature' => 'إضافة ميزة',
            'subscribers' => 'المشتركون',
            'ai_credits' => 'رصيد الذكاء الاصطناعي',
            'unlimited_ai' => 'ذكاء اصطناعي غير محدود',
        ],

        'billing_cycles' => [
            'monthly' => 'شهري',
            'yearly' => 'سنوي',
            'lifetime' => 'مدى الحياة',
        ],
    ],

    // User Subscription Resource
    'user_subscriptions' => [
        'label' => 'اشتراك مستخدم',
        'plural' => 'اشتراكات المستخدمين',
        'navigation_label' => 'اشتراكات المستخدمين',

        'sections' => [
            'subscription_details' => 'تفاصيل الاشتراك',
            'dates' => 'التواريخ',
            'settings' => 'الإعدادات',
        ],

        'fields' => [
            'user' => 'المستخدم',
            'plan' => 'الخطة',
            'status' => 'الحالة',
            'starts_at' => 'تاريخ البدء',
            'ends_at' => 'تاريخ الانتهاء',
            'trial_ends_at' => 'انتهاء الفترة التجريبية',
            'canceled_at' => 'تاريخ الإلغاء',
            'auto_renew' => 'تجديد تلقائي',
        ],

        'statuses' => [
            'active' => 'نشط',
            'pending' => 'قيد الانتظار',
            'canceled' => 'ملغي',
            'expired' => 'منتهي',
        ],
    ],

    // Business Card Resource
    'business_cards' => [
        'label' => 'بطاقة عمل',
        'plural' => 'بطاقات العمل',
        'navigation_label' => 'بطاقات العمل',

        'sections' => [
            'card_information' => 'معلومات البطاقة',
            'card_description' => 'تفاصيل البطاقة الأساسية والملكية',
            'media_images' => 'الوسائط والصور',
            'media_description' => 'رفع صور ووسائط البطاقة',
            'design_appearance' => 'التصميم والمظهر',
            'design_description' => 'تخصيص مظهر البطاقة',
            'urls_access' => 'الروابط والوصول',
            'urls_description' => 'إعدادات روابط البطاقة والوصول',
            'status_visibility' => 'الحالة والظهور',
            'status_description' => 'التحكم في ظهور وحالة البطاقة',
        ],

        'fields' => [
            'owner' => 'المالك',
            'user' => 'المستخدم',
            'language' => 'اللغة',
            'primary_language' => 'اللغة الأساسية',
            'title' => 'العنوان',
            'subtitle' => 'العنوان الفرعي',
            'cover_image' => 'صورة الغلاف',
            'cover_helper' => 'الحجم الموصى به: 1200×675 بكسل (16:9) للعرض الأفضل',
            'profile_image' => 'الصورة الشخصية',
            'profile_helper' => 'الحجم الموصى به: 400×400 بكسل (1:1) شكل مربع',
            'avatar' => 'الصورة الرمزية',
            'template' => 'القالب',
            'theme' => 'السمة',
            'theme_customizations' => 'تخصيصات السمة',
            'css_property' => 'خاصية CSS',
            'custom_slug' => 'الرابط المخصص',
            'slug_helper' => 'فقط الحروف والأرقام والشرطات والشرطات السفلية مسموحة',
            'share_url' => 'رابط المشاركة',
            'share_helper' => 'معرف فريد يتم إنشاؤه تلقائيًا',
            'nfc_identifier' => 'معرف NFC',
            'nfc_helper' => 'معرف بطاقة NFC للمشاركة اللاسلكية',
            'full_url_preview' => 'معاينة رابط البطاقة',
            'url_generated_after_save' => 'سيتم إنشاؤه بعد الحفظ',
            'is_published' => 'منشورة',
            'published_helper' => 'جعل هذه البطاقة متاحة للعامة',
            'is_primary' => 'البطاقة الأساسية',
            'primary_helper' => 'تعيين كالبطاقة الافتراضية لهذا المستخدم',
            'views_count' => 'إجمالي المشاهدات',
            'shares_count' => 'إجمالي المشاركات',
            'sections_count' => 'عدد الأقسام',
            'published' => 'منشورة',
            'primary' => 'أساسية',
            'views' => 'المشاهدات',
            'shares' => 'المشاركات',
            'sections' => 'الأقسام',
        ],

        'filters' => [
            'published_status' => 'حالة النشر',
            'published' => 'منشورة',
            'draft' => 'مسودة',
            'primary_card' => 'البطاقة الأساسية',
            'primary_cards' => 'البطاقات الأساسية',
            'secondary_cards' => 'البطاقات الثانوية',
            'has_cover_image' => 'لديها صورة غلاف',
            'popular' => 'البطاقات الشائعة (10+ مشاهدات)',
        ],

        'actions' => [
            'preview' => 'معاينة',
            'preview_card' => 'معاينة البطاقة',
            'duplicate' => 'نسخ',
            'duplicate_card' => 'نسخ البطاقة',
            'publish' => 'نشر',
            'unpublish' => 'إلغاء النشر',
        ],
    ],

    // Theme Resource
    'themes' => [
        'label' => 'سمة',
        'plural' => 'السمات',
        'navigation_label' => 'السمات',

        'sections' => [
            'theme_details' => 'تفاصيل السمة',
            'colors' => 'الألوان',
            'typography' => 'الخطوط',
            'layout' => 'التخطيط',
            'custom_css' => 'CSS مخصص',
            'preview' => 'المعاينة',
        ],

        'fields' => [
            'name' => 'الاسم',
            'owner' => 'المالك',
            'system' => 'النظام',
            'is_system_default' => 'سمة النظام الافتراضية',
            'system_helper' => 'سمات النظام متاحة لجميع المستخدمين',
            'is_public' => 'عامة',
            'public_helper' => 'السمات العامة يمكن استخدامها من قبل مستخدمين آخرين',
            'primary_color' => 'اللون الأساسي',
            'secondary_color' => 'اللون الثانوي',
            'background_color' => 'لون الخلفية',
            'text_color' => 'لون النص',
            'card_bg' => 'خلفية البطاقة',
            'border_color' => 'لون الحدود',
            'heading_font' => 'خط العنوان',
            'body_font' => 'خط النص',
            'card_style' => 'نمط البطاقة',
            'border_radius' => 'نصف قطر الحدود',
            'alignment' => 'المحاذاة',
            'spacing' => 'المسافات',
            'custom_css' => 'CSS مخصص',
            'css_placeholder' => '/* أضف CSS المخصص هنا */',
            'preview_image' => 'صورة المعاينة',
            'used_by' => 'مستخدمة بواسطة',
        ],

        'card_styles' => [
            'elevated' => 'مرفوعة (ظل)',
            'outlined' => 'محددة (حدود)',
            'filled' => 'ممتلئة (مسطحة)',
        ],

        'alignments' => [
            'left' => 'يسار',
            'center' => 'وسط',
            'right' => 'يمين',
        ],

        'spacings' => [
            'compact' => 'مضغوط',
            'normal' => 'عادي',
            'relaxed' => 'مريح',
        ],

        'filters' => [
            'system_default' => 'سمة النظام الافتراضية',
            'public' => 'عامة',
        ],
    ],

    // Language Resource
    'languages' => [
        'label' => 'لغة',
        'plural' => 'اللغات',
        'navigation_label' => 'اللغات',

        'sections' => [
            'language_information' => 'معلومات اللغة',
            'ui_labels' => 'تسميات الواجهة',
            'labels_description' => 'إدارة ترجمات التسميات لهذه اللغة',
        ],

        'fields' => [
            'name' => 'الاسم',
            'code' => 'الكود',
            'direction' => 'الاتجاه',
            'is_active' => 'نشطة',
            'is_default' => 'افتراضية',
            'labels' => 'التسميات',
        ],

        'directions' => [
            'ltr' => 'من اليسار إلى اليمين',
            'rtl' => 'من اليمين إلى اليسار',
        ],
    ],

    // Translation History Resource
    'translation_history' => [
        'label' => 'سجل ترجمة',
        'plural' => 'سجل الترجمات',
        'navigation_label' => 'سجل الترجمات',

        'sections' => [
            'translation_details' => 'تفاصيل الترجمة',
            'quality_status' => 'الجودة والحالة',
            'additional_information' => 'معلومات إضافية',
        ],

        'fields' => [
            'user' => 'المستخدم',
            'business_card' => 'بطاقة العمل',
            'card' => 'البطاقة',
            'source_language' => 'اللغة المصدر',
            'source' => 'المصدر',
            'target_language' => 'اللغة الهدف',
            'target' => 'الهدف',
            'source_text' => 'النص المصدر',
            'translated_text' => 'النص المترجم',
            'quality_score' => 'درجة الجودة',
            'verification_status' => 'حالة التحقق',
            'cost' => 'التكلفة',
            'content_hash' => 'بصمة المحتوى',
            'metadata' => 'البيانات الإضافية',
            'error_message' => 'رسالة الخطأ',
        ],

        'verification_statuses' => [
            'pending' => 'قيد الانتظار',
            'auto_verified' => 'تحقق تلقائي',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'needs_review' => 'يحتاج مراجعة',
        ],

        'filters' => [
            'created_from' => 'من تاريخ',
            'created_until' => 'حتى تاريخ',
        ],

        'actions' => [
            'approve' => 'موافقة',
            'reject' => 'رفض',
        ],
    ],

    // Settings Page
    'settings' => [
        'title' => 'إدارة الإعدادات',
        'navigation_label' => 'الإعدادات',

        'sections' => [
            'general' => 'الإعدادات العامة',
            'authentication' => 'إعدادات المصادقة',
            'authentication_description' => 'إعدادات التحقق وطرق تسجيل الدخول.',
            'mail' => 'إعدادات البريد',
            'payment' => 'إعدادات الدفع',
            'ai_translation' => 'إعدادات ترجمة الذكاء الاصطناعي',
        ],

        'fields' => [
            'site_name' => 'اسم الموقع',
            'site_description' => 'وصف الموقع',
            'meta_keywords' => 'الكلمات المفتاحية',
            'meta_description' => 'وصف الميتا',
            'logo' => 'الشعار',
            'favicon' => 'أيقونة الموقع',
            'verification_method' => 'طريقة التحقق',
            'verification_helper' => 'اختر كيفية تحقق المستخدمين من حساباتهم بعد التسجيل.',
            'email_verification' => 'التحقق بالبريد الإلكتروني',
            'phone_verification' => 'التحقق بالهاتف (SMS)',
            'allow_email_login' => 'السماح بتسجيل الدخول بالبريد',
            'email_login_helper' => 'يمكن للمستخدمين تسجيل الدخول باستخدام بريدهم الإلكتروني.',
            'allow_phone_login' => 'السماح بتسجيل الدخول بالهاتف',
            'phone_login_helper' => 'يمكن للمستخدمين تسجيل الدخول باستخدام رقم هاتفهم.',
            'mailer' => 'المرسل',
            'host' => 'المضيف',
            'port' => 'المنفذ',
            'username' => 'اسم المستخدم',
            'encryption' => 'التشفير',
            'from_address' => 'عنوان المرسل',
            'from_name' => 'اسم المرسل',
            'default_gateway' => 'بوابة الدفع الافتراضية',
            'lahza_public_key' => 'المفتاح العام لـ لحظة',
            'lahza_secret_key' => 'المفتاح السري لـ لحظة',
            'lahza_test_mode' => 'وضع الاختبار لـ لحظة',
            'lahza_currency' => 'عملة لحظة',
            'openrouter_api_key' => 'مفتاح OpenRouter API',
            'openrouter_url' => 'رابط OpenRouter',
            'translation_model' => 'نموذج الترجمة',
            'request_timeout' => 'مهلة الطلب',
        ],

        'notifications' => [
            'saved' => 'تم حفظ الإعدادات بنجاح.',
        ],
    ],

    // Widgets
    'widgets' => [
        'stats' => [
            'total_users' => 'إجمالي المستخدمين',
            'registered_users' => 'المستخدمون المسجلون',
            'active_subscriptions' => 'الاشتراكات النشطة',
            'paid_subscribers' => 'المشتركون المدفوعون',
            'published_cards' => 'البطاقات المنشورة',
            'active_cards' => 'بطاقات العمل النشطة',
            'weekly_views' => 'المشاهدات الأسبوعية',
            'views_this_week' => 'مشاهدات البطاقات هذا الأسبوع',
        ],

        'latest_payments' => [
            'heading' => 'آخر المدفوعات',
            'transaction' => 'المعاملة',
            'user' => 'المستخدم',
            'plan' => 'الخطة',
            'amount' => 'المبلغ',
            'status' => 'الحالة',
            'date' => 'التاريخ',
        ],

        'unverified_users' => [
            'heading' => 'المستخدمون غير الموثقين',
        ],

        'revenue_chart' => [
            'heading' => 'الإيرادات',
        ],

        'card_views_chart' => [
            'heading' => 'مشاهدات البطاقات',
        ],

        'event_types_chart' => [
            'heading' => 'أنواع الأحداث',
        ],
    ],

    // Relation Managers
    // Addon Resource
    'addons' => [
        'label' => 'إضافة',
        'plural' => 'الإضافات',
        'navigation_label' => 'الإضافات',

        'sections' => [
            'addon_details' => 'تفاصيل الإضافة',
            'type_config' => 'النوع والإعدادات',
            'pricing' => 'التسعير',
        ],

        'fields' => [
            'name' => 'الاسم',
            'slug' => 'الرابط المختصر',
            'description' => 'الوصف',
            'type' => 'النوع',
            'feature_key' => 'مفتاح الميزة',
            'value' => 'القيمة',
            'value_helper' => 'عدد فتحات البطاقات الإضافية (لنوع البطاقات الإضافية)',
            'price' => 'السعر',
            'currency' => 'العملة',
            'sort_order' => 'ترتيب العرض',
            'is_active' => 'نشط',
            'purchases' => 'المشتريات',
        ],

        'types' => [
            'extra_cards' => 'فتحات بطاقات إضافية',
            'feature_unlock' => 'فتح ميزة',
        ],

        'feature_keys' => [
            'nfc' => 'NFC',
            'custom_domain' => 'نطاق مخصص',
            'analytics' => 'التحليلات',
            'custom_css' => 'CSS مخصص',
        ],
    ],

    // User Addon Resource
    'user_addons' => [
        'label' => 'إضافة المستخدم',
        'plural' => 'إضافات المستخدمين',
        'navigation_label' => 'إضافات المستخدمين',

        'fields' => [
            'user' => 'المستخدم',
            'addon' => 'الإضافة',
            'granted_by' => 'ممنوح بواسطة',
            'transaction_id' => 'رقم المعاملة',
        ],

        'granted_types' => [
            'purchase' => 'شراء',
            'admin_grant' => 'منحة إدارية',
            'promo' => 'ترويجي',
        ],
    ],

    'relations' => [
        'sections' => [
            'label' => 'قسم',
            'plural' => 'الأقسام',
        ],
        'subscriptions' => [
            'label' => 'اشتراك',
            'plural' => 'الاشتراكات',
        ],
        'addons' => [
            'label' => 'إضافة',
            'plural' => 'الإضافات',
        ],
    ],
];
