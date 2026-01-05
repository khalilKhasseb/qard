# Phase 2 - API Layer Agent Tasks

## Context
TapIt digital business card application at C:\Users\user\Herd\qard
- Laravel 12, FilamentPHP, Inertia.js, Vue 3, MySQL
- Phase 1 complete: Models, migrations, services, admin panel, basic auth
- Current API endpoints: Theme upload, preview CSS, preview (in app/Http/Controllers/Api/ThemeController.php)
- Services available: CardService, ThemeService, PaymentService, AnalyticsService

## Objective
Create comprehensive RESTful API endpoints for:
1. Business Cards CRUD
2. Themes CRUD
3. Card Sections management
4. Payment/Subscription flows

## Task 1: Business Cards API

### Endpoints to Create
File: `app/Http/Controllers/Api/CardController.php`

```php
POST   /api/cards              - Create new card
GET    /api/cards              - List user's cards
GET    /api/cards/{id}         - Get single card
PUT    /api/cards/{id}         - Update card
DELETE /api/cards/{id}         - Delete card
POST   /api/cards/{id}/publish - Publish/unpublish card
POST   /api/cards/{id}/duplicate - Duplicate card
GET    /api/cards/{id}/analytics - Get card analytics
```

### Implementation Requirements

**Controller**: `app/Http/Controllers/Api/CardController.php`

- Use CardService for business logic
- Implement proper authorization (user can only access their own cards)
- Return JSON responses with proper status codes
- Include card with relations: sections, theme, user

**Request Validation**: Create FormRequests
- `app/Http/Requests/CreateCardRequest.php`
- `app/Http/Requests/UpdateCardRequest.php`

**Resources**: Create API Resources
- `app/Http/Resources/CardResource.php`
- `app/Http/Resources/CardCollection.php`

Include in resource:
- id, title, subtitle, template_id, theme_id
- custom_slug, share_url, qr_code_url
- is_published, views_count, shares_count
- full_url (accessor)
- sections (nested), theme (nested)
- created_at, updated_at

## Task 2: Card Sections API

### Endpoints to Create
File: `app/Http/Controllers/Api/SectionController.php`

```php
POST   /api/cards/{card}/sections       - Add section to card
PUT    /api/sections/{section}          - Update section
DELETE /api/sections/{section}          - Delete section
POST   /api/cards/{card}/sections/reorder - Reorder sections
```

### Implementation Requirements

**Controller**: `app/Http/Controllers/Api/SectionController.php`

- Use CardService methods (addSection, updateSection, reorderSections, deleteSection)
- Validate section_type enum: contact, social, services, products, testimonials, hours, appointments, gallery
- Return section with card relationship

**Request Validation**:
- `app/Http/Requests/CreateSectionRequest.php`
- `app/Http/Requests/UpdateSectionRequest.php`
- `app/Http/Requests/ReorderSectionsRequest.php`

**Resource**:
- `app/Http/Resources/SectionResource.php`

## Task 3: Themes API Enhancement

### Endpoints to Create
File: `app/Http/Controllers/Api/ThemeController.php` (enhance existing)

```php
// Already exists:
POST /api/themes/upload
POST /api/themes/preview-css
POST /api/themes/preview

// Add these:
GET    /api/themes              - List user's themes
POST   /api/themes              - Create theme
GET    /api/themes/{id}         - Get single theme
PUT    /api/themes/{id}         - Update theme
DELETE /api/themes/{id}         - Delete theme
POST   /api/themes/{id}/duplicate - Duplicate theme
POST   /api/themes/{id}/apply/{card} - Apply theme to card
```

### Implementation Requirements

- Enhance existing ThemeController
- Use ThemeService for all operations
- Authorization: user can access own themes + public themes + system themes
- Check user permissions for custom_css (canUseCustomCss())

**Request Validation**:
- `app/Http/Requests/CreateThemeRequest.php`
- `app/Http/Requests/UpdateThemeRequest.php`

**Resource**:
- `app/Http/Resources/ThemeResource.php`
- Include: id, name, user_id, is_system_default, is_public, config, preview_image, used_by_cards_count

## Task 4: Payment & Subscription API

### Endpoints to Create
File: `app/Http/Controllers/Api/PaymentController.php`

```php
GET  /api/subscription-plans      - List available plans
POST /api/payments                - Create payment for subscription
POST /api/payments/{id}/confirm   - Confirm payment (cash gateway)
GET  /api/payments/history        - Get user's payment history
GET  /api/payments/pending        - Get pending payments
```

File: `app/Http/Controllers/Api/SubscriptionController.php`

```php
GET  /api/subscription            - Get current subscription
POST /api/subscription/cancel     - Cancel subscription
```

### Implementation Requirements

**PaymentController**:
- Use PaymentService
- Use CashPaymentGateway (already configured in PaymentService)
- Return Payment resource with subscription plan

**SubscriptionController**:
- Return UserSubscription resource
- Handle cancellation logic

**Models to consider**:
- SubscriptionPlan (has price, billing_cycle, features)
- Payment (has status: pending, completed, failed, refunded)
- UserSubscription (has status: active, expired, canceled)

**Resources**:
- `app/Http/Resources/SubscriptionPlanResource.php`
- `app/Http/Resources/PaymentResource.php`
- `app/Http/Resources/UserSubscriptionResource.php`

## Task 5: Routes Configuration

### Update `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    
    // Cards
    Route::apiResource('cards', CardController::class);
    Route::post('cards/{card}/publish', [CardController::class, 'publish']);
    Route::post('cards/{card}/duplicate', [CardController::class, 'duplicate']);
    Route::get('cards/{card}/analytics', [CardController::class, 'analytics']);
    
    // Sections
    Route::post('cards/{card}/sections', [SectionController::class, 'store']);
    Route::put('sections/{section}', [SectionController::class, 'update']);
    Route::delete('sections/{section}', [SectionController::class, 'destroy']);
    Route::post('cards/{card}/sections/reorder', [SectionController::class, 'reorder']);
    
    // Themes (enhance existing)
    Route::apiResource('themes', ThemeController::class);
    Route::post('themes/{theme}/duplicate', [ThemeController::class, 'duplicate']);
    Route::post('themes/{theme}/apply/{card}', [ThemeController::class, 'apply']);
    Route::post('themes/upload', [ThemeController::class, 'upload']);
    Route::post('themes/preview-css', [ThemeController::class, 'previewCss']);
    Route::post('themes/preview', [ThemeController::class, 'preview']);
    
    // Payments & Subscriptions
    Route::get('subscription-plans', [PaymentController::class, 'plans']);
    Route::post('payments', [PaymentController::class, 'create']);
    Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm']);
    Route::get('payments/history', [PaymentController::class, 'history']);
    Route::get('payments/pending', [PaymentController::class, 'pending']);
    
    Route::get('subscription', [SubscriptionController::class, 'show']);
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel']);
});

// Public analytics (no auth)
Route::post('analytics/track', [AnalyticsController::class, 'track']);
```

## Task 6: Authorization Policies

### Create Policies
File: `app/Policies/BusinessCardPolicy.php`
```php
- view: user owns card
- update: user owns card
- delete: user owns card
- publish: user owns card
```

File: `app/Policies/ThemePolicy.php`
```php
- view: user owns OR is_public OR is_system_default
- update: user owns
- delete: user owns AND NOT is_system_default
- useCustomCss: user->canUseCustomCss()
```

File: `app/Policies/CardSectionPolicy.php`
```php
- update: user owns card
- delete: user owns card
```

### Register Policies
In `app/Providers/AuthServiceProvider.php`:
```php
protected $policies = [
    BusinessCard::class => BusinessCardPolicy::class,
    Theme::class => ThemePolicy::class,
    CardSection::class => CardSectionPolicy::class,
];
```

## Task 7: Error Handling

Create `app/Exceptions/Handler.php` enhancements:
- Handle ModelNotFoundException → 404 JSON response
- Handle AuthorizationException → 403 JSON response
- Handle ValidationException → 422 JSON response
- Handle generic exceptions → 500 JSON response

## Testing Requirements

After implementation, create API tests:
- `tests/Feature/Api/CardApiTest.php`
- `tests/Feature/Api/ThemeApiTest.php`
- `tests/Feature/Api/SectionApiTest.php`
- `tests/Feature/Api/PaymentApiTest.php`

Test scenarios:
- Authenticated user can perform authorized actions
- Unauthorized access returns 403
- Validation errors return 422
- Not found returns 404
- Success responses have correct structure

## Success Criteria

✅ All endpoints return proper JSON responses
✅ Authorization policies enforced
✅ Validation rules applied
✅ Services used for business logic (no logic in controllers)
✅ API Resources used for consistent response structure
✅ Error handling provides clear messages
✅ Tests pass for all endpoints
✅ Postman/API documentation generated

## Dependencies

- Existing services: CardService, ThemeService, PaymentService, AnalyticsService
- Existing models: BusinessCard, Theme, CardSection, Payment, UserSubscription, SubscriptionPlan
- Authentication: Laravel Sanctum (already configured)
- Must run `php artisan test` to verify no regressions
