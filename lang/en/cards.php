<?php

return [
    'title' => 'My Cards',
    'create' => 'Create Card',
    'edit' => 'Edit Card',
    'delete' => 'Delete Card',

    // Create page
    'create_page' => [
        'title' => 'Create Business Card',
        'heading' => 'Create New Business Card',
        'description' => 'Fill in the basic information for your card.',
        'usage' => 'Usage: :count / :limit cards',
        'remaining' => ':count remaining',
        'primary_language_hint' => 'This is the main language of your card. You can add content in other languages once the card is created!',
        'custom_url_hint' => 'Leave empty to use a generated URL',
        'publish_immediately' => 'Publish immediately',
        'creating' => 'Creating...',
        'default_theme' => 'Default Theme',
    ],

    // Fields
    'fields' => [
        'title' => 'Card Title',
        'title_required' => 'Card Title *',
        'title_placeholder' => 'Your Name or Business Name',
        'subtitle' => 'Subtitle',
        'subtitle_placeholder' => 'Your Title or Tagline',
        'description' => 'Description',
        'slug' => 'URL Slug',
        'custom_url' => 'Custom URL (optional)',
        'custom_url_placeholder' => 'your-custom-url',
        'theme' => 'Theme',
        'primary_language' => 'Primary Language *',
        'published' => 'Published',
        'cover_image' => 'Cover Image',
        'profile_image' => 'Profile Image',
    ],

    // Actions
    'actions' => [
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
        'preview' => 'Preview',
        'share' => 'Share',
        'duplicate' => 'Duplicate',
        'view_public' => 'View Public Card',
    ],

    // Status
    'status' => [
        'published' => 'Published',
        'draft' => 'Draft',
        'views' => ':count views',
    ],

    // Messages
    'messages' => [
        'created' => 'Card created successfully.',
        'updated' => 'Card updated successfully.',
        'deleted' => 'Card deleted successfully.',
        'published' => 'Card published successfully.',
        'unpublished' => 'Card unpublished.',
        'duplicated' => 'Card duplicated successfully.',
    ],

    // Empty states
    'empty' => [
        'title' => 'No cards yet',
        'description' => 'Get started by creating your first digital business card.',
        'action' => 'Create Your First Card',
    ],

    // Sections
    'sections' => [
        'title' => 'Sections',
        'add' => 'Add Section',
        'edit' => 'Edit Section',
        'delete' => 'Delete Section',
        'reorder' => 'Reorder Sections',
        'types' => [
            'text' => 'Text',
            'contact' => 'Contact Info',
            'social' => 'Social Links',
            'gallery' => 'Gallery',
            'services' => 'Services',
            'products' => 'Products',
            'testimonials' => 'Testimonials',
            'hours' => 'Business Hours',
            'appointments' => 'Appointments',
        ],
    ],
];
