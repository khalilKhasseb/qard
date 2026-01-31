<?php

return [
    'title' => 'הכרטיסים שלי',
    'create' => 'צור כרטיס',
    'edit' => 'ערוך כרטיס',
    'delete' => 'מחק כרטיס',

    // Create page
    'create_page' => [
        'title' => 'צור כרטיס ביזנס',
        'heading' => 'צור כרטיס ביזנס חדש',
        'description' => 'מלא את המידע הבסיסי עבור הכרטיס שלך.',
        'usage' => 'שימוש: :count / :limit כרטיסים',
        'remaining' => ':count נותרו',
        'primary_language_hint' => 'זוהי השפה הראשית של הכרטיס שלך. תוכל להוסיף תוכן בשפות נוספות לאחר יצירת הכרטיס!',
        'custom_url_hint' => 'השאר ריק לשימוש בקישור אוטומטי',
        'publish_immediately' => 'פרסם מיד',
        'creating' => 'יוצר...',
        'default_theme' => 'ערכת עיצוב ברירת מחדל',
    ],

    // Fields
    'fields' => [
        'title' => 'כותרת הכרטיס',
        'title_required' => 'כותרת הכרטיס *',
        'title_placeholder' => 'שמך או שם העסק',
        'subtitle' => 'כותרת משנה',
        'subtitle_placeholder' => 'התפקיד או הסלוגן שלך',
        'description' => 'תיאור',
        'slug' => 'קישור מקוצר',
        'custom_url' => 'קישור מותאם (אופציונלי)',
        'custom_url_placeholder' => 'הקישור-המותאם-שלך',
        'theme' => 'ערכת עיצוב',
        'primary_language' => 'שפה ראשית *',
        'published' => 'מפורסם',
        'cover_image' => 'תמונת כיסוי',
        'profile_image' => 'תמונת פרופיל',
    ],

    // Actions
    'actions' => [
        'publish' => 'פרסם',
        'unpublish' => 'בטל פרסום',
        'preview' => 'תצוגה מקדימה',
        'share' => 'שתף',
        'duplicate' => 'שכפל',
        'view_public' => 'הצג כרטיס ציבורי',
    ],

    // Status
    'status' => [
        'published' => 'מפורסם',
        'draft' => 'טיוטה',
        'views' => ':count צפיות',
    ],

    // Messages
    'messages' => [
        'created' => 'הכרטיס נוצר בהצלחה.',
        'updated' => 'הכרטיס עודכן בהצלחה.',
        'deleted' => 'הכרטיס נמחק בהצלחה.',
        'published' => 'הכרטיס פורסם בהצלחה.',
        'unpublished' => 'פרסום הכרטיס בוטל.',
        'duplicated' => 'הכרטיס שוכפל בהצלחה.',
    ],

    // Empty states
    'empty' => [
        'title' => 'אין עדיין כרטיסים',
        'description' => 'התחל על ידי יצירת כרטיס הביזנס הדיגיטלי הראשון שלך.',
        'action' => 'צור את הכרטיס הראשון שלך',
    ],

    // Sections
    'sections' => [
        'title' => 'מקטעים',
        'add' => 'הוסף מקטע',
        'edit' => 'ערוך מקטע',
        'delete' => 'מחק מקטע',
        'reorder' => 'סדר מחדש מקטעים',
        'types' => [
            'text' => 'טקסט',
            'contact' => 'פרטי קשר',
            'social' => 'קישורים חברתיים',
            'gallery' => 'גלריה',
            'services' => 'שירותים',
            'products' => 'מוצרים',
            'testimonials' => 'המלצות',
            'hours' => 'שעות פעילות',
            'appointments' => 'פגישות',
        ],
    ],
];
