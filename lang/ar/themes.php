<?php

return [
    'title' => 'السمات',
    'create' => 'إنشاء سمة',
    'edit' => 'تعديل السمة',
    'delete' => 'حذف السمة',
    'back_to_themes' => 'العودة إلى السمات',

    // Create page
    'create_page' => [
        'title' => 'إنشاء سمة جديدة',
        'description' => 'صمم سمة مخصصة لبطاقات العمل الخاصة بك.',
        'info' => 'بعد إنشاء السمة، ستتمكن من تخصيص الألوان والخطوط والصور والمزيد في محرر السمات.',
        'creating' => 'جاري الإنشاء...',
        'usage' => ':count / :limit سمات مستخدمة',
        'remaining' => ':count متبقية',
    ],

    // Edit page
    'edit_page' => [
        'title' => 'تعديل السمة: :name',
        'basic_info' => 'المعلومات الأساسية',
        'colors' => 'الألوان',
        'typography' => 'الخطوط',
        'heading_font' => 'خط العناوين',
        'body_font' => 'خط النص',
        'images' => 'الصور',
        'background_image' => 'صورة الخلفية',
        'header_image' => 'صورة الرأس',
        'logo_image' => 'صورة الشعار',
        'uploading' => 'جاري الرفع...',
        'layout' => 'التخطيط',
        'card_style' => 'نمط البطاقة',
        'border_radius' => 'نصف قطر الحافة',
        'text_alignment' => 'محاذاة النص',
        'custom_css' => 'CSS مخصص',
        'css_placeholder' => '/* كود CSS المخصص هنا */',
        'save_theme' => 'حفظ السمة',
        'saving' => 'جاري الحفظ...',
        'live_preview' => 'معاينة حية',
        'desktop' => 'سطح المكتب',
        'mobile' => 'الجوال',
    ],

    // Card styles
    'card_styles' => [
        'elevated' => 'مرتفع (ظل)',
        'outlined' => 'محدد (حدود)',
        'filled' => 'ممتلئ (صلب)',
    ],

    // Text alignments
    'alignments' => [
        'left' => 'يسار',
        'center' => 'وسط',
        'right' => 'يمين',
    ],

    // Fields
    'fields' => [
        'name' => 'اسم السمة',
        'name_required' => 'اسم السمة *',
        'name_placeholder' => 'السمة الرائعة',
        'primary_color' => 'اللون الأساسي',
        'secondary_color' => 'اللون الثانوي',
        'background_color' => 'لون الخلفية',
        'text_color' => 'لون النص',
        'card_bg_color' => 'خلفية البطاقة',
        'font_family' => 'نوع الخط',
        'is_default' => 'السمة الافتراضية',
        'is_public' => 'سمة عامة',
        'make_public' => 'اجعل هذه السمة عامة (يمكن للآخرين استخدامها)',
    ],

    // Actions
    'actions' => [
        'preview' => 'معاينة',
        'apply' => 'تطبيق على البطاقة',
        'duplicate' => 'نسخ',
    ],

    // Messages
    'messages' => [
        'created' => 'تم إنشاء السمة بنجاح.',
        'updated' => 'تم تحديث السمة بنجاح.',
        'deleted' => 'تم حذف السمة بنجاح.',
        'applied' => 'تم تطبيق السمة على البطاقة.',
        'upload_failed' => 'فشل الرفع',
        'preview_failed' => 'فشلت المعاينة',
    ],

    // Empty states
    'empty' => [
        'title' => 'لا توجد سمات بعد',
        'description' => 'أنشئ سمة مخصصة لبطاقاتك.',
        'action' => 'إنشاء سمة',
    ],
];
