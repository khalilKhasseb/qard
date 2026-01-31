# RTL (Right-to-Left) Implementation Guide

## Overview

This guide explains how RTL (Right-to-Left) language support is implemented in the Qard application.

## Supported Languages

The system supports both LTR (Left-to-Right) and RTL languages:

**LTR Languages:**
- English (`en`)
- French (`fr`)
- Spanish (`es`)
- German (`de`)
- Italian (`it`)

**RTL Languages:**
- Arabic (`ar`)
- Hebrew (`he`)
- Persian/Farsi (`fa`)
- Urdu (`ur`)

## Implementation Details

### 1. Database Structure

The `languages` table includes a `direction` field:
- `ltr` - Left-to-Right
- `rtl` - Right-to-Left

### 2. Middleware

The `SetLanguageDirection` middleware automatically sets the application direction based on the current language:

```php
// app/Http/Middleware/SetLanguageDirection.php
$languageCode = app()->getLocale();
$language = Language::where('code', $languageCode)->first();

if ($language && $language->direction === 'rtl') {
    config(['app.direction' => 'rtl']);
} else {
    config(['app.direction' => 'ltr']);
}
```

### 3. CSS Implementation

The `resources/css/rtl.css` file contains all RTL-specific styles:

```css
[dir="rtl"] {
    direction: rtl;
    unicode-bidi: embed;
}

[dir="rtl"] .text-left {
    text-align: right !important;
}

[dir="rtl"] .text-right {
    text-align: left !important;
}

/* ... more RTL styles ... */
```

### 4. Blade Template Integration

In Blade templates, use the `dir` attribute:

```html
<html lang="{{ app()->getLocale() }}" dir="{{ config('app.direction', 'ltr') }}">
    <!-- Content -->
</html>
```

### 5. Vue Component Integration

In Vue components, use computed properties:

```javascript
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const direction = computed(() => page.props.appDirection || 'ltr');
```

## Testing RTL

### Manual Testing

1. **Switch to an RTL language** (e.g., Arabic)
2. **Verify layout**: All elements should be mirrored
3. **Check text alignment**: Text should be right-aligned
4. **Test forms**: Form elements should work correctly
5. **Navigation**: Menus and dropdowns should open from the right

### Automated Testing

Use the provided test files:
- `tests/Feature/RTLTest.php`
- `tests/Feature/RTLWorkingFinalTest.php`

```bash
php artisan test --filter RTLTest
```

## Common RTL Issues and Solutions

### 1. Layout Issues

**Problem**: Elements not properly mirrored

**Solution**: Use directional classes and test with RTL CSS

```css
/* Before */
.element {
    margin-left: 10px;
    float: left;
}

/* After */
.element {
    margin-left: 10px;
    float: left;
}

[dir="rtl"] .element {
    margin-right: 10px;
    margin-left: 0;
    float: right;
}
```

### 2. Form Input Issues

**Problem**: Input fields not properly aligned

```css
[dir="rtl"] input,
[dir="rtl"] select,
[dir="rtl"] textarea {
    direction: rtl;
    text-align: right;
}
```

### 3. Icon Positioning

**Problem**: Icons appear on the wrong side

```css
[dir="rtl"] .icon-left {
    margin-right: 8px;
    margin-left: 0;
    order: 1;
}

[dir="rtl"] .icon-right {
    margin-left: 8px;
    margin-right: 0;
    order: -1;
}
```

### 4. Dropdown Menus

**Problem**: Dropdowns open on the wrong side

```css
[dir="rtl"] .dropdown-menu {
    right: 0;
    left: auto;
}
```

### 5. Pagination

**Problem**: Pagination arrows reversed

```css
[dir="rtl"] .pagination {
    flex-direction: row-reverse;
}
```

## Best Practices

### 1. Use Relative Positioning

Avoid absolute positioning when possible. Use flexbox and grid layouts.

### 2. Test Early and Often

Test RTL support during development, not just at the end.

### 3. Use Directional Classes

Use classes like `.ml-4` (margin-left) instead of inline styles.

### 4. Mirror Layouts

Design layouts to be easily mirrored for RTL support.

### 5. Icon Consistency

Use icons that work well in both LTR and RTL contexts.

### 6. Form Validation

Ensure form validation works correctly with RTL text.

## Performance Considerations

1. **CSS Specificity**: RTL styles should override LTR styles
2. **Caching**: Cache RTL CSS separately if needed
3. **Lazy Loading**: Load RTL CSS only when needed
4. **Minification**: Minify RTL CSS for production

## Browser Support

Test RTL support in all major browsers:
- Chrome
- Firefox
- Safari
- Edge
- Mobile browsers

## Debugging Tools

1. **Browser DevTools**: Check computed styles
2. **RTL Tester**: Chrome extension for RTL testing
3. **Language Switcher**: Quickly switch between languages
4. **Console Logging**: Debug direction changes

## Future Enhancements

1. **Automatic RTL Detection**: Detect RTL languages automatically
2. **Dynamic CSS Loading**: Load RTL CSS only when needed
3. **RTL Preview Mode**: Preview RTL without changing language
4. **RTL Theme Support**: Different themes for RTL languages
5. **RTL Font Support**: Better font support for RTL languages
