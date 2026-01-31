<?php

return [
    'title' => 'ערכות עיצוב',
    'create' => 'צור ערכת עיצוב',
    'edit' => 'ערוך ערכת עיצוב',
    'delete' => 'מחק ערכת עיצוב',
    'back_to_themes' => 'חזרה לערכות העיצוב',

    // Create page
    'create_page' => [
        'title' => 'צור ערכת עיצוב חדשה',
        'description' => 'עצב ערכת עיצוב מותאמת אישית לכרטיסי הביזנס שלך.',
        'info' => 'לאחר יצירת ערכת העיצוב, תוכל להתאים אישית צבעים, גופנים, תמונות ועוד בעורך ערכת העיצוב.',
        'creating' => 'יוצר...',
        'usage' => ':count / :limit ערכות עיצוב בשימוש',
        'remaining' => ':count נותרו',
    ],

    // Edit page
    'edit_page' => [
        'title' => 'ערוך ערכת עיצוב: :name',
        'basic_info' => 'מידע בסיסי',
        'colors' => 'צבעים',
        'typography' => 'טיפוגרפיה',
        'heading_font' => 'גופן כותרות',
        'body_font' => 'גופן גוף',
        'images' => 'תמונות',
        'background_image' => 'תמונת רקע',
        'header_image' => 'תמונת כותרת',
        'logo_image' => 'תמונת לוגו',
        'uploading' => 'מעלה...',
        'layout' => 'פריסה',
        'card_style' => 'סגנון כרטיס',
        'border_radius' => 'רדיוס פינות',
        'text_alignment' => 'יישור טקסט',
        'custom_css' => 'CSS מותאם אישית',
        'css_placeholder' => '/* ה-CSS המותאם שלך כאן */',
        'save_theme' => 'שמור ערכת עיצוב',
        'saving' => 'שומר...',
        'live_preview' => 'תצוגה מקדימה',
        'desktop' => 'מחשב',
        'mobile' => 'נייד',
    ],

    // Card styles
    'card_styles' => [
        'elevated' => 'מורם (צל)',
        'outlined' => 'עם מסגרת',
        'filled' => 'מלא (מוצק)',
    ],

    // Text alignments
    'alignments' => [
        'left' => 'שמאל',
        'center' => 'מרכז',
        'right' => 'ימין',
    ],

    // Fields
    'fields' => [
        'name' => 'שם ערכת העיצוב',
        'name_required' => 'שם ערכת העיצוב *',
        'name_placeholder' => 'ערכת העיצוב המדהימה שלי',
        'primary_color' => 'צבע ראשי',
        'secondary_color' => 'צבע משני',
        'background_color' => 'צבע רקע',
        'text_color' => 'צבע טקסט',
        'card_bg_color' => 'רקע כרטיס',
        'font_family' => 'גופן',
        'is_default' => 'ערכת עיצוב ברירת מחדל',
        'is_public' => 'ערכת עיצוב ציבורית',
        'make_public' => 'הפוך ערכת עיצוב זו לציבורית (אחרים יכולים להשתמש בה)',
    ],

    // Actions
    'actions' => [
        'preview' => 'תצוגה מקדימה',
        'apply' => 'החל על כרטיס',
        'duplicate' => 'שכפל',
    ],

    // Messages
    'messages' => [
        'created' => 'ערכת העיצוב נוצרה בהצלחה.',
        'updated' => 'ערכת העיצוב עודכנה בהצלחה.',
        'deleted' => 'ערכת העיצוב נמחקה בהצלחה.',
        'applied' => 'ערכת העיצוב הוחלה על הכרטיס.',
        'upload_failed' => 'ההעלאה נכשלה',
        'preview_failed' => 'התצוגה המקדימה נכשלה',
    ],

    // Empty states
    'empty' => [
        'title' => 'אין עדיין ערכות עיצוב',
        'description' => 'צור ערכת עיצוב מותאמת אישית לכרטיסים שלך.',
        'action' => 'צור ערכת עיצוב',
    ],
];
