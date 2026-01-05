@php
    // Pass card and sections data to Vue
    $cardData = [
        'id' => $card->id,
        'title' => $card->title,
        'subtitle' => $card->subtitle,
        'full_url' => $card->full_url,
        'theme' => $theme ? [
            'config' => $theme->config
        ] : null
    ];
    
    $sectionsData = $sections->map(function($section) {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'section_type' => $section->section_type,
            'content' => $section->content,
            'image_url' => $section->image_url // Explicitly add this here
        ];
    })->toArray();
@endphp

<!DOCTYPE html>
<html lang="en" dir="{{ $card->user->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $card->title }} - TapIt</title>
    <meta name="description" content="{{ $card->subtitle }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $card->title }}">
    <meta property="og:description" content="{{ $card->subtitle }}">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ $card->full_url }}">
    
    <!-- Fonts -->
    @if($theme?->config['fonts']['heading_url'] ?? false)
        <link href="{{ $theme->config['fonts']['heading_url'] }}" rel="stylesheet">
    @endif
    @if($theme?->config['fonts']['body_url'] ?? false)
        <link href="{{ $theme->config['fonts']['body_url'] }}" rel="stylesheet">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Theme CSS -->
    <style>
        @php
            $themeService = app(\App\Services\ThemeService::class);
            $css = $theme ? $themeService->generateCSS($theme) : '';
        @endphp
        {!! $css !!}
    </style>
    
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app" data-card="{{ json_encode($cardData) }}" data-sections="{{ json_encode($sectionsData) }}">
        <public-card></public-card>
    </div>
</body>
</html>
