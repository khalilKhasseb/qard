<?php

return [
    'title' => 'بطاقاتي',
    'create' => 'إنشاء بطاقة',
    'edit' => 'تعديل البطاقة',
    'delete' => 'حذف البطاقة',

    // Create page
    'create_page' => [
        'title' => 'إنشاء بطاقة عمل',
        'heading' => 'إنشاء بطاقة عمل جديدة',
        'description' => 'أدخل المعلومات الأساسية لبطاقتك.',
        'usage' => 'الاستخدام: :count / :limit بطاقة',
        'remaining' => ':count متبقية',
        'primary_language_hint' => 'هذه هي اللغة الرئيسية لبطاقتك. يمكنك إضافة محتوى بلغات أخرى بعد إنشاء البطاقة!',
        'custom_url_hint' => 'اتركه فارغاً لاستخدام رابط مُنشأ تلقائياً',
        'publish_immediately' => 'نشر فوراً',
        'creating' => 'جاري الإنشاء...',
        'default_theme' => 'السمة الافتراضية',
    ],

    // Fields
    'fields' => [
        'title' => 'عنوان البطاقة',
        'title_required' => 'عنوان البطاقة *',
        'title_placeholder' => 'اسمك أو اسم نشاطك التجاري',
        'subtitle' => 'العنوان الفرعي',
        'subtitle_placeholder' => 'لقبك أو شعارك',
        'description' => 'الوصف',
        'slug' => 'الرابط المختصر',
        'custom_url' => 'رابط مخصص (اختياري)',
        'custom_url_placeholder' => 'الرابط-المخصص',
        'theme' => 'السمة',
        'primary_language' => 'اللغة الرئيسية *',
        'published' => 'منشورة',
        'cover_image' => 'صورة الغلاف',
        'profile_image' => 'الصورة الشخصية',
    ],

    // Actions
    'actions' => [
        'publish' => 'نشر',
        'unpublish' => 'إلغاء النشر',
        'preview' => 'معاينة',
        'share' => 'مشاركة',
        'duplicate' => 'نسخ',
        'view_public' => 'عرض البطاقة العامة',
    ],

    // Status
    'status' => [
        'published' => 'منشورة',
        'draft' => 'مسودة',
        'views' => ':count مشاهدة',
    ],

    // Messages
    'messages' => [
        'created' => 'تم إنشاء البطاقة بنجاح.',
        'updated' => 'تم تحديث البطاقة بنجاح.',
        'deleted' => 'تم حذف البطاقة بنجاح.',
        'published' => 'تم نشر البطاقة بنجاح.',
        'unpublished' => 'تم إلغاء نشر البطاقة.',
        'duplicated' => 'تم نسخ البطاقة بنجاح.',
    ],

    // Empty states
    'empty' => [
        'title' => 'لا توجد بطاقات بعد',
        'description' => 'ابدأ بإنشاء أول بطاقة عمل رقمية لك.',
        'action' => 'أنشئ بطاقتك الأولى',
    ],

    // Sections
    'sections' => [
        'title' => 'الأقسام',
        'add' => 'إضافة قسم',
        'edit' => 'تعديل القسم',
        'delete' => 'حذف القسم',
        'reorder' => 'إعادة ترتيب الأقسام',
        'types' => [
            'text' => 'نص',
            'contact' => 'معلومات الاتصال',
            'social' => 'روابط التواصل',
            'gallery' => 'معرض الصور',
            'services' => 'الخدمات',
            'products' => 'المنتجات',
            'testimonials' => 'الشهادات',
            'hours' => 'ساعات العمل',
            'appointments' => 'المواعيد',
        ],
    ],
];
