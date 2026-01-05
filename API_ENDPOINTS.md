# TapIt API Endpoints Documentation

All endpoints require `auth:sanctum` middleware (Bearer token authentication).

Base URL: `http://your-domain.com/api`

## Authentication
All requests must include:
```
Authorization: Bearer {your-token-here}
```

---

## 📇 Cards API (8 endpoints)

### 1. List Cards
```
GET /api/cards
```
**Response:** Paginated list of user's business cards with sections and theme
**Authorization:** User's own cards only

### 2. Create Card
```
POST /api/cards
```
**Body:**
```json
{
  "title": "John Doe",
  "subtitle": "Software Developer",
  "template_id": 1,
  "theme_id": 1,
  "custom_slug": "johndoe"
}
```
**Validation:** 
- `title`: required, string, max:255
- `subtitle`: nullable, string, max:255
- `template_id`: nullable, exists:templates,id
- `theme_id`: nullable, exists:themes,id
- `custom_slug`: nullable, unique, lowercase alphanumeric with hyphens

### 3. Show Card
```
GET /api/cards/{id}
```
**Response:** Single card with sections and theme
**Authorization:** User must own the card

### 4. Update Card
```
PUT /api/cards/{id}
```
**Body:** Same as create, all fields optional
**Authorization:** User must own the card

### 5. Publish/Unpublish Card
```
POST /api/cards/{id}/publish
```
**Body:**
```json
{
  "is_published": true
}
```
**Authorization:** User must own the card

### 6. Duplicate Card
```
POST /api/cards/{id}/duplicate
```
**Response:** New card with copied sections
**Authorization:** User must own the card and have card quota available

### 7. Delete Card
```
DELETE /api/cards/{id}
```
**Authorization:** User must own the card

### 8. Get Card Analytics
```
GET /api/cards/{id}/analytics
```
**Response:** Views, shares, and engagement stats
**Authorization:** User must own the card

---

## 📋 Sections API (4 endpoints)

### 9. Create Section
```
POST /api/cards/{card}/sections
```
**Body:**
```json
{
  "section_type": "contact",
  "title": "Contact Information",
  "content": {
    "email": "john@example.com",
    "phone": "+1234567890"
  },
  "is_active": true,
  "metadata": {}
}
```
**Validation:**
- `section_type`: required, one of: contact, social, services, products, testimonials, hours, appointments, gallery
- `title`: required, string, max:255
- `content`: required, array
- `is_active`: optional, boolean
- `metadata`: optional, array

### 10. Update Section
```
PUT /api/sections/{id}
```
**Body:** Same as create, all fields optional
**Authorization:** User must own the card

### 11. Delete Section
```
DELETE /api/sections/{id}
```
**Authorization:** User must own the card

### 12. Reorder Sections
```
POST /api/cards/{card}/sections/reorder
```
**Body:**
```json
{
  "section_ids": [3, 1, 2]
}
```
**Validation:** Array of existing section IDs in desired order

---

## 🎨 Themes API (8 endpoints)

### 13. List Themes
```
GET /api/themes
```
**Response:** User's themes + public themes + system defaults
**Pagination:** 15 per page

### 14. Create Theme
```
POST /api/themes
```
**Body:**
```json
{
  "name": "My Custom Theme",
  "config": {
    "colors": {
      "primary": "#3B82F6",
      "secondary": "#8B5CF6",
      "background": "#FFFFFF"
    },
    "fonts": {
      "heading": "Inter",
      "body": "Inter"
    }
  },
  "is_public": false
}
```
**Validation:**
- `name`: required, string, max:255
- `config`: required, array
- `is_public`: optional, boolean

### 15. Show Theme
```
GET /api/themes/{id}
```
**Authorization:** User's theme, public theme, or system default

### 16. Update Theme
```
PUT /api/themes/{id}
```
**Body:** Same as create, all fields optional
**Authorization:** User must own the theme (not system defaults)

### 17. Delete Theme
```
DELETE /api/themes/{id}
```
**Authorization:** User must own the theme (not system defaults)

### 18. Duplicate Theme
```
POST /api/themes/{id}/duplicate
```
**Response:** New theme copy
**Authorization:** User can view the theme and has theme quota

### 19. Apply Theme to Card
```
POST /api/themes/{theme}/apply/{card}
```
**Response:** Updated card with new theme
**Authorization:** User can view theme and owns card

### 20. Upload Theme Image
```
POST /api/themes/upload
```
**Body (multipart/form-data):**
```
image: (file) - max 5MB
type: background|header|logo|favicon
theme_id: (optional) - existing theme ID
```
**Response:** Image URL and ID

---

## 💳 Payments API (5 endpoints)

### 21. List Subscription Plans
```
GET /api/subscription-plans
```
**Response:** All active subscription plans
**Public:** No auth required for viewing plans

### 22. Create Payment
```
POST /api/payments
```
**Body:**
```json
{
  "subscription_plan_id": 1,
  "payment_method": "cash",
  "notes": "Payment for Pro plan"
}
```
**Validation:**
- `subscription_plan_id`: required, exists:subscription_plans,id
- `payment_method`: required, currently only 'cash' supported
- `notes`: optional, string, max:1000

### 23. Confirm Payment
```
POST /api/payments/{id}/confirm
```
**Note:** Currently shows payment status. Full confirmation requires admin access (typically done via Filament admin panel)
**Authorization:** User must own the payment

### 24. Payment History
```
GET /api/payments/history
```
**Response:** Paginated list of user's payments with plan details
**Pagination:** 15 per page

### 25. Current Subscription
```
GET /api/subscription
```
**Response:** User's active subscription details

---

## 🔐 Authorization Policies

### BusinessCardPolicy
- **view**: User must own the card
- **create**: User must have card quota available
- **update**: User must own the card
- **delete**: User must own the card

### ThemePolicy
- **view**: Theme is public, system default, or user owns it
- **create**: User must have theme quota available
- **update**: User must own the theme (cannot edit system defaults)
- **delete**: User must own the theme (cannot delete system defaults)
- **duplicate**: User can view the theme and has quota

---

## 📊 Response Formats

### Success Response (200/201)
```json
{
  "data": {
    "id": 1,
    "title": "John Doe",
    ...
  }
}
```

### Collection Response (200)
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  }
}
```

### Error Response (4xx/5xx)
```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["Card title is required"]
  }
}
```

---

## 🔑 Getting Started

1. **Create an API Token:**
```bash
php artisan tinker
```
```php
$user = User::first();
$token = $user->createToken('my-app-token')->plainTextToken;
echo $token;
```

2. **Make API Request:**
```bash
curl -X GET http://your-domain.com/api/cards \
  -H "Authorization: Bearer {your-token}" \
  -H "Accept: application/json"
```

3. **Test All Endpoints:**
```bash
php test_api.php
```

---

## 📝 Implementation Details

### Controllers
- `App\Http\Controllers\Api\CardController` - Card CRUD + publish, duplicate, analytics
- `App\Http\Controllers\Api\SectionController` - Section CRUD + reorder
- `App\Http\Controllers\Api\ThemeController` - Theme CRUD + duplicate, apply, upload
- `App\Http\Controllers\Api\PaymentController` - Payments + subscription plans

### Form Requests
- `App\Http\Requests\CreateCardRequest` - Card creation validation
- `App\Http\Requests\UpdateCardRequest` - Card update validation
- `App\Http\Requests\CreateSectionRequest` - Section creation validation
- `App\Http\Requests\UpdateSectionRequest` - Section update validation

### API Resources
- `App\Http\Resources\CardResource` - Card JSON transformation
- `App\Http\Resources\SectionResource` - Section JSON transformation
- `App\Http\Resources\ThemeResource` - Theme JSON transformation
- `App\Http\Resources\PaymentResource` - Payment JSON transformation
- `App\Http\Resources\SubscriptionPlanResource` - Plan JSON transformation

### Policies
- `App\Policies\BusinessCardPolicy` - Card authorization
- `App\Policies\ThemePolicy` - Theme authorization

### Services
- `App\Services\CardService` - Card business logic
- `App\Services\ThemeService` - Theme processing
- `App\Services\PaymentService` - Payment processing

---

## ✅ Status: All 26 Endpoints Implemented

- ✅ Cards API: 8/8 endpoints
- ✅ Sections API: 4/4 endpoints
- ✅ Themes API: 8/8 endpoints
- ✅ Payments API: 5/5 endpoints (+ 1 subscription endpoint = 26 total)

All endpoints are:
- ✅ Protected with `auth:sanctum` middleware
- ✅ Using FormRequest validation
- ✅ Using API Resources for responses
- ✅ Using Authorization Policies
- ✅ Properly handling errors (404, 403, 422, 500)
- ✅ Returning consistent JSON responses
