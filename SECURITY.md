# TapIt Application Security Documentation

## Security Measures Implemented

### 1. Authorization & Access Control ✅

#### Policies Implemented
- **BusinessCardPolicy**: Ensures only card owners can view/edit/delete their cards
- **ThemePolicy**: Owner-only access for editing/deleting themes (public themes viewable by all)
- **PaymentPolicy**: Users can only view their own payment records

#### Policy Registration
All policies are registered in `app/Providers/AppServiceProvider.php` using Laravel's Gate facade.

#### Usage in Controllers
All sensitive operations use `$this->authorize()` to check permissions:
```php
$this->authorize('update', $card);
$this->authorize('view', $theme);
$this->authorize('view', $payment);
```

### 2. API Security ✅

#### Rate Limiting
- **60 requests per minute per user** for all authenticated API routes
- Configured in `bootstrap/app.php` using `throttleApi('60,1')`
- Prevents brute force attacks and API abuse

#### CORS Configuration
- Configured in `config/cors.php`
- Only allows requests from configured frontend URL
- Supports credentials for cookie-based authentication
- Paths protected: `api/*`, `sanctum/csrf-cookie`

#### Sanctum Authentication
- Token-based authentication for API routes
- Stateful domains configured in `.env.example`
- All API routes under `auth:sanctum` middleware

### 3. XSS (Cross-Site Scripting) Prevention ✅

#### Custom CSS Sanitization (CRITICAL)
Created `app/Services/CssSanitizer.php` to sanitize user-provided CSS:
- Removes dangerous patterns (javascript:, expression(), behavior:, etc.)
- Validates CSS properties against whitelist
- Sanitizes URLs to allow only HTTPS and safe data URIs
- Integrated into `ThemeService` for all theme operations

#### HTML Content Sanitization (CRITICAL)
Created `app/Services/HtmlSanitizer.php` for user-generated HTML:
- Whitelist-based approach for allowed tags
- Removes dangerous attributes (onclick, onerror, etc.)
- Sanitizes inline styles
- Removes javascript: and vbscript: URLs
- Secures external links with `rel="noopener noreferrer"`
- Custom Blade directive: `@sanitize($html)`

#### Blade Template Security
- All user input escaped with `{{ }}` by default
- Custom HTML content sanitized with `@sanitize()` directive
- File: `resources/views/cards/sections/custom.blade.php` secured

### 4. File Upload Security ✅

#### Validation Rules
```php
'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120'
```

#### Security Measures in `ThemeService::processImage()`:
- File type validation (only jpeg, jpg, png, webp)
- File size limit (5MB maximum)
- Unique filename generation with `uniqid()`
- Stored in user-specific directories
- MIME type validation
- Image dimension extraction for validation

#### Controller Authorization
Theme image uploads require theme ownership verification using policies.

### 5. CSRF Protection ✅

#### Built-in Laravel Protection
- CSRF middleware enabled by default on web routes
- All forms include `@csrf` token
- Public card view includes CSRF token for analytics tracking
- API routes use Sanctum token authentication

### 6. SQL Injection Prevention ✅

#### Audit Results
- **No raw SQL queries found** in models or controllers
- All database operations use **Eloquent ORM**
- Parameterized queries used throughout
- No string concatenation in queries

#### Examples of Safe Practices:
```php
// Eloquent ORM (safe)
User::where('email', $email)->first();
BusinessCard::forUser($userId)->get();

// Query builder with bindings (safe)
DB::select('select * from users where email = ?', [$email]);
```

### 7. Security Headers ✅

Created `app/Http/Middleware/SecurityHeaders.php` with:

#### Headers Implemented:
- **X-Frame-Options**: `SAMEORIGIN` - Prevents clickjacking
- **X-Content-Type-Options**: `nosniff` - Prevents MIME sniffing
- **X-XSS-Protection**: `1; mode=block` - Legacy XSS protection
- **Referrer-Policy**: `strict-origin-when-cross-origin`
- **Permissions-Policy**: Restricts geolocation, microphone, camera
- **Strict-Transport-Security**: HSTS for production (31536000 seconds)
- **Content-Security-Policy**: Comprehensive CSP policy

#### Content Security Policy:
```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net;
font-src 'self' https://fonts.gstatic.com data:;
img-src 'self' data: https: blob:;
connect-src 'self' https:;
frame-ancestors 'self';
base-uri 'self';
form-action 'self';
```

### 8. Environment Security ✅

#### `.env.example` Configuration
Enhanced with security notes:
- APP_DEBUG must be false in production
- APP_KEY must be generated
- Session security settings documented
- CORS configuration included
- Sanctum domains configuration
- No sensitive data in example file

#### Production Recommendations:
```env
APP_DEBUG=false
APP_ENV=production
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### 9. Input Validation ✅

#### Request Validation Classes
- `CreateCardRequest`
- `UpdateCardRequest`
- `CreateSectionRequest`
- `UpdateSectionRequest`

#### Inline Validation Examples:
```php
// Payment creation
'subscription_plan_id' => 'required|exists:subscription_plans,id',
'payment_method' => 'required|in:cash',
'notes' => 'nullable|string|max:1000',

// Theme creation
'name' => 'required|string|max:255',
'config' => 'required|array',
'is_public' => 'sometimes|boolean',

// Section reordering
'section_ids' => 'required|array',
'section_ids.*' => 'exists:card_sections,id',
```

## Security Testing Checklist

### Authorization Tests
- [ ] Test unauthorized card access → Should return 403
- [ ] Test unauthorized theme edit → Should return 403
- [ ] Test viewing another user's payment → Should return 403
- [ ] Test card deletion by non-owner → Should return 403

### Rate Limiting Tests
- [ ] Send 61 API requests in one minute → Should throttle
- [ ] Verify 429 status code returned
- [ ] Check Retry-After header present

### XSS Prevention Tests
- [ ] Submit custom CSS with `<script>` tags → Should be removed
- [ ] Submit custom CSS with `javascript:` URL → Should be removed
- [ ] Submit HTML content with `onclick` → Should be sanitized
- [ ] Submit HTML with `<script>` tag → Should be removed
- [ ] Verify custom CSS with `expression()` → Should be removed

### File Upload Tests
- [ ] Upload .exe file → Should be rejected
- [ ] Upload PHP file → Should be rejected
- [ ] Upload 10MB image → Should be rejected (max 5MB)
- [ ] Upload valid PNG → Should succeed
- [ ] Verify unique filename generated

### CSRF Tests
- [ ] Submit form without CSRF token → Should return 419
- [ ] Submit form with invalid token → Should return 419
- [ ] Submit valid form with token → Should succeed

### SQL Injection Tests
- [ ] Search with `'; DROP TABLE users; --` → Should be escaped
- [ ] Filter with SQL in parameters → Should be parameterized
- [ ] Verify no SQL errors exposed

## Known Security Considerations

### Areas Requiring Admin Panel Security (Filament)
1. Payment confirmation (admin-only operation)
2. User management and roles
3. Subscription plan management
4. System theme management

### Future Security Enhancements
1. **Two-Factor Authentication (2FA)** - Add for user accounts
2. **API Key Rotation** - Implement token expiration
3. **Security Audit Logging** - Log sensitive operations
4. **Intrusion Detection** - Monitor for suspicious patterns
5. **Database Encryption** - Encrypt sensitive user data
6. **Backup Encryption** - Encrypt database backups

## Security Incident Response

### If Security Vulnerability Discovered:
1. **Assess Impact** - Determine severity and affected users
2. **Patch Immediately** - Deploy fix to production
3. **Notify Users** - If data exposure, notify affected users
4. **Review Logs** - Check for exploitation attempts
5. **Update Documentation** - Document the issue and fix
6. **Post-Mortem** - Analyze how it occurred and prevent future issues

## Compliance Considerations

### GDPR Compliance
- User data minimization
- Right to data deletion
- Data export capability
- Clear privacy policy

### PCI DSS (If Processing Cards)
- Currently using cash payment method
- If credit cards added, PCI compliance required
- Use payment gateway (Stripe/PayPal) for PCI compliance
- Never store card numbers

## Security Contacts

For security vulnerabilities, contact:
- Email: security@tapit.example (update with actual email)
- Response time: 24-48 hours for critical issues

## Regular Security Maintenance

### Monthly Tasks:
- [ ] Update dependencies (`composer update`, `npm update`)
- [ ] Review security advisories
- [ ] Check failed login attempts
- [ ] Review API rate limit logs

### Quarterly Tasks:
- [ ] Security audit of new features
- [ ] Penetration testing
- [ ] Review user permissions
- [ ] Update security documentation

### Annually:
- [ ] Third-party security audit
- [ ] Review and update security policies
- [ ] Security training for team
- [ ] Disaster recovery testing

## Security Tools & Commands

### Check for Vulnerabilities
```bash
# Check PHP dependencies
composer audit

# Check npm dependencies
npm audit

# Static analysis (if configured)
./vendor/bin/phpstan analyse
```

### Generate Security Keys
```bash
# Generate application key
php artisan key:generate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Monitor Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check failed jobs
php artisan queue:failed
```

---

**Last Updated**: 2026-01-05
**Version**: 1.0
**Status**: Initial Security Implementation Complete
