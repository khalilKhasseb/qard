@php
    $primaryLang = $card->language?->code ?? 'en';

    $getTranslated = function($field, $lang) {
        if (!is_array($field)) return $field;
        return $field[$lang] ?? ($field[array_key_first($field)] ?? '');
    };

    $metaTitle = $getTranslated($card->title, $primaryLang);
    $metaDescription = $getTranslated($card->subtitle, $primaryLang);

    // Pass card and sections data to Vue
    $cardData = [
        'id' => $card->id,
        'title' => $card->title,
        'subtitle' => $card->subtitle,
        'cover_image_url' => $card->cover_image_url,
        'profile_image_url' => $card->profile_image_url,
        'qr_code_url' => $card->qr_code_url,
        'full_url' => $card->full_url,
        'primary_language' => $primaryLang,
        'theme' => [
            'config' => $card->getEffectiveThemeConfig()
        ]
    ];

    $sectionsData = $sections->map(function($section) {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'section_type' => $section->section_type,
            'content' => $section->content,
            'image_url' => $section->image_url
        ];
    })->toArray();

    $languagesData = $languages->map(function($lang) {
        return [
            'id' => $lang->id,
            'name' => $lang->name,
            'code' => $lang->code,
            'direction' => $lang->direction,
            'labels' => $lang->labels,
        ];
    })->toArray();
@endphp

<!DOCTYPE html>
<html lang="{{ $primaryLang }}" dir="{{ $card->language?->direction ?? 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }} - TapIt</title>
    <meta name="description" content="{{ $metaDescription }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
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
            $css = $theme ? $themeService->generateCSS($theme, $card->theme_overrides) : '';
        @endphp
        {!! $css !!}
    </style>

    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app"
         data-card="{{ json_encode($cardData) }}"
         data-sections="{{ json_encode($sectionsData) }}"
         data-languages="{{ json_encode($languagesData) }}">
        <public-card></public-card>
    </div>
</body>
</html>
