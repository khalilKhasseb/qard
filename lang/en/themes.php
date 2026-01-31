<?php

return [
    'title' => 'Themes',
    'create' => 'Create Theme',
    'edit' => 'Edit Theme',
    'delete' => 'Delete Theme',
    'back_to_themes' => 'Back to Themes',

    // Create page
    'create_page' => [
        'title' => 'Create New Theme',
        'description' => 'Design a custom theme for your business cards.',
        'info' => 'After creating your theme, you\'ll be able to customize colors, fonts, images, and more in the theme editor.',
        'creating' => 'Creating...',
        'usage' => ':count / :limit themes used',
        'remaining' => ':count remaining',
    ],

    // Edit page
    'edit_page' => [
        'title' => 'Edit Theme: :name',
        'basic_info' => 'Basic Information',
        'colors' => 'Colors',
        'typography' => 'Typography',
        'heading_font' => 'Heading Font',
        'body_font' => 'Body Font',
        'images' => 'Images',
        'background_image' => 'Background Image',
        'header_image' => 'Header Image',
        'logo_image' => 'Logo Image',
        'uploading' => 'Uploading...',
        'layout' => 'Layout',
        'card_style' => 'Card Style',
        'border_radius' => 'Border Radius',
        'text_alignment' => 'Text Alignment',
        'custom_css' => 'Custom CSS',
        'css_placeholder' => '/* Your custom CSS here */',
        'save_theme' => 'Save Theme',
        'saving' => 'Saving...',
        'live_preview' => 'Live Preview',
        'desktop' => 'Desktop',
        'mobile' => 'Mobile',
    ],

    // Card styles
    'card_styles' => [
        'elevated' => 'Elevated (Shadow)',
        'outlined' => 'Outlined (Border)',
        'filled' => 'Filled (Solid)',
    ],

    // Text alignments
    'alignments' => [
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
    ],

    // Fields
    'fields' => [
        'name' => 'Theme Name',
        'name_required' => 'Theme Name *',
        'name_placeholder' => 'My Awesome Theme',
        'primary_color' => 'Primary Color',
        'secondary_color' => 'Secondary Color',
        'background_color' => 'Background Color',
        'text_color' => 'Text Color',
        'card_bg_color' => 'Card Background',
        'font_family' => 'Font Family',
        'is_default' => 'Default Theme',
        'is_public' => 'Public Theme',
        'make_public' => 'Make this theme public (others can use it)',
    ],

    // Actions
    'actions' => [
        'preview' => 'Preview',
        'apply' => 'Apply to Card',
        'duplicate' => 'Duplicate',
    ],

    // Messages
    'messages' => [
        'created' => 'Theme created successfully.',
        'updated' => 'Theme updated successfully.',
        'deleted' => 'Theme deleted successfully.',
        'applied' => 'Theme applied to card.',
        'upload_failed' => 'Upload failed',
        'preview_failed' => 'Preview failed',
    ],

    // Empty states
    'empty' => [
        'title' => 'No themes yet',
        'description' => 'Create a custom theme for your cards.',
        'action' => 'Create Theme',
    ],
];
