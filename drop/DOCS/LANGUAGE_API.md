# Language API Documentation

## Overview

The Language API provides endpoints for managing languages and translations in the Qard application.

## Base URL

All API endpoints are relative to: `https://yourdomain.com/api`

## Authentication

Most endpoints require authentication using Laravel Sanctum. Include the `Authorization: Bearer {token}` header.

## Endpoints

### GET `/language` - List Available Languages

Returns a list of all active languages.

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "English",
            "code": "en",
            "direction": "ltr",
            "is_active": true,
            "is_default": true,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Arabic",
            "code": "ar",
            "direction": "rtl",
            "is_active": true,
            "is_default": false,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ]
}
```

### GET `/language/{language}` - Get Specific Language

Returns details for a specific language.

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "English",
        "code": "en",
        "direction": "ltr",
        "is_active": true,
        "is_default": true,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

### POST `/language/switch` - Switch User Language

Switches the current user's language preference.

**Request Body:**
```json
{
    "language_code": "ar"
}
```

**Response:**
```json
{
    "message": "Language switched successfully",
    "language": {
        "data": {
            "id": 2,
            "name": "Arabic",
            "code": "ar",
            "direction": "rtl",
            "is_active": true,
            "is_default": false,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    }
}
```

## Translation Endpoints (Authenticated)

### GET `/translations` - List Translations

Returns all translations, optionally filtered by language.

**Query Parameters:**
- `language_code` (optional): Filter by language code

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "key": "welcome.message",
            "language_code": "en",
            "value": "Welcome to Qard!",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ]
}
```

### POST `/translations` - Create Translation

Creates a new translation.

**Request Body:**
```json
{
    "key": "welcome.message",
    "language_code": "ar",
    "value": "مرحبا بك في Qard!"
}
```

**Response:**
```json
{
    "data": {
        "id": 2,
        "key": "welcome.message",
        "language_code": "ar",
        "value": "مرحبا بك في Qard!",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

### PUT `/translations/{translation}` - Update Translation

Updates an existing translation.

**Request Body:**
```json
{
    "key": "welcome.message",
    "language_code": "ar",
    "value": "مرحبا بك في Qard - نسخة محسنة!"
}
```

**Response:**
```json
{
    "data": {
        "id": 2,
        "key": "welcome.message",
        "language_code": "ar",
        "value": "مرحبا بك في Qard - نسخة محسنة!",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

### DELETE `/translations/{translation}` - Delete Translation

Deletes a translation.

**Response:**
```json
{
    "message": "Translation deleted successfully"
}
```

## Error Responses

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 404 Not Found

```json
{
    "message": "No query results for model [App\\Models\\Language]."
}
```

### 422 Validation Error

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "language_code": [
            "The selected language code is invalid."
        ]
    }
}
```

## Language Codes

The system supports ISO 639-1 language codes:
- `en` - English
- `ar` - Arabic
- `fr` - French
- `es` - Spanish
- `de` - German
- `it` - Italian
- `pt` - Portuguese
- `ru` - Russian
- `zh` - Chinese
- `ja` - Japanese

## Direction Support

- `ltr` - Left-to-Right (English, French, etc.)
- `rtl` - Right-to-Left (Arabic, Hebrew, etc.)

## Integration Guide

### Frontend Integration

```javascript
// Switch language
import { router } from '@inertiajs/vue3';

const switchLanguage = (languageCode) => {
    router.post('/api/language/switch', {
        language_code: languageCode
    }, {
        onSuccess: () => {
            // Reload page to apply language changes
            window.location.reload();
        }
    });
};
```

### Backend Integration

```php
// Get current language
$currentLanguage = app()->getLocale();

// Get language direction
$language = \App\Models\Language::where('code', $currentLanguage)->first();
$direction = $language ? $language->direction : 'ltr';
```

## Best Practices

1. **Cache language data**: Cache the list of available languages to reduce database queries
2. **Fallback language**: Always provide a fallback to the default language
3. **RTL testing**: Test RTL languages thoroughly as layout issues may occur
4. **Translation keys**: Use consistent naming conventions for translation keys
5. **Performance**: Batch translation requests when possible
