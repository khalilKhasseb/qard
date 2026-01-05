# Phase 2 - Security Agent Tasks

## Context
TapIt digital business card application at C:\Users\user\Herd\qard
- Laravel 12 with built-in security features
- Laravel Sanctum for API authentication
- Laravel Breeze for web authentication
- File uploads for theme images
- User-generated content (custom CSS, card content)

## Objective
Comprehensive security review and hardening:
1. Authentication & Authorization
2. API Security
3. File Upload Security
4. Input Validation & Sanitization
5. CSRF & XSS Protection
6. SQL Injection Prevention
7. Rate Limiting
8. Secure Configuration

## Task 1: Authentication & Authorization Review

### 1.1 Verify Laravel Breeze Security
**Files to check**:
- `routes/auth.php`
- `app/Http/Controllers/Auth/*`

**Verify**:
✅ Password hashing (bcrypt/argon2)
✅ Password reset tokens expire
✅ Email verification required for sensitive actions
✅ Secure session configuration
✅ Remember token security

### 1.2 API Authentication (Sanctum)
**Files to check**:
- `config/sanctum.php`
- `app/Http/Kernel.php` (sanctum middleware)

**Verify**:
✅ Token expiration configured
✅ Token prefix configured
✅ CORS properly configured
✅ Stateful domains set correctly

**Update `config/sanctum.php`**:
```php
'expiration' => 60, // 60 minutes
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### 1.3 Authorization Policies
**Create/Review Policies**:

**File**: `app/Policies/BusinessCardPolicy.php`
```php
public function view(User $user, BusinessCard $card): bool
{
    return $user->id === $card->user_id;
}

public function update(User $user, BusinessCard $card): bool
{
    return $user->id === $card->user_id;
}

public function delete(User $user, BusinessCard $card): bool
{
    return $user->id === $card->user_id;
}

public function publish(User $user, BusinessCard $card): bool
{
    return $user->id === $card->user_id 
        && $user->isSubscriptionActive();
}
```

**File**: `app/Policies/ThemePolicy.php`
```php
public function view(User $user, Theme $theme): bool
{
    return $theme->user_id === $user->id 
        || $theme->is_public 
        || $theme->is_system_default;
}

public function update(User $user, Theme $theme): bool
{
    return $theme->user_id === $user->id 
        && !$theme->is_system_default;
}

public function delete(User $user, Theme $theme): bool
{
    return $theme->user_id === $user->id 
        && !$theme->is_system_default;
}

public function useCustomCss(User $user): bool
{
    return in_array($user->subscription_tier, ['pro', 'business']);
}
```

**File**: `app/Policies/PaymentPolicy.php`
```php
public function view(User $user, Payment $payment): bool
{
    return $user->id === $payment->user_id;
}

public function confirm(User $user, Payment $payment): bool
{
    return $user->id === $payment->user_id 
        && $payment->status === 'pending';
}
```

**Register Policies** in `app/Providers/AuthServiceProvider.php`:
```php
protected $policies = [
    BusinessCard::class => BusinessCardPolicy::class,
    Theme::class => ThemePolicy::class,
    CardSection::class => CardSectionPolicy.php,
    Payment::class => PaymentPolicy::class,
];
```

**Apply in Controllers**:
```php
$this->authorize('update', $card);
$this->authorize('view', $theme);
```

## Task 2: API Security

### 2.1 Rate Limiting
**File**: `app/Providers/RouteServiceProvider.php`

Configure rate limits for API routes:
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Stricter limit for uploads
RateLimiter::for('uploads', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id);
});

// Stricter for payments
RateLimiter::for('payments', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id);
});
```

**Apply in routes**:
```php
Route::middleware(['auth:sanctum', 'throttle:uploads'])->group(function () {
    Route::post('/themes/upload', [ThemeController::class, 'upload']);
});

Route::middleware(['auth:sanctum', 'throttle:payments'])->group(function () {
    Route::post('/payments', [PaymentController::class, 'create']);
});
```

### 2.2 API Response Security
**Create API Response Trait**:
**File**: `app/Traits/ApiResponse.php`

```php
trait ApiResponse
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
```

**Never expose**:
- Stack traces in production
- Database queries
- Internal paths
- Environment variables
- User passwords (even hashed)

### 2.3 CORS Configuration
**File**: `config/cors.php`

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

## Task 3: File Upload Security

### 3.1 Image Upload Validation
**File**: `app/Http/Controllers/Api/ThemeController.php`

**Current validation**:
```php
$validated = $request->validate([
    'image' => 'required|image|max:5120', // 5MB
    'type' => 'required|in:background,header,logo,favicon',
]);
```

**Enhanced validation**:
```php
$validated = $request->validate([
    'image' => [
        'required',
        'image',
        'mimes:jpeg,png,jpg,webp', // Specific types
        'max:5120', // 5MB
        'dimensions:max_width=4000,max_height=4000', // Prevent huge images
    ],
    'type' => 'required|in:background,header,logo,favicon',
]);
```

### 3.2 File Upload Security in Service
**File**: `app/Services/ThemeService.php`

**Add security checks**:
```php
public function processImage(
    UploadedFile $file,
    string $type,
    User $user,
    ?Theme $theme = null
): ThemeImage {
    // Validate file type
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($file->getMimeType(), $allowedMimes)) {
        throw new \Exception('Invalid file type');
    }

    // Validate size
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file->getSize() > $maxSize) {
        throw new \Exception('File too large. Max 5MB');
    }

    // Validate image dimensions
    $imageInfo = @getimagesize($file->getRealPath());
    if (!$imageInfo) {
        throw new \Exception('Invalid image file');
    }

    // Prevent PHP files disguised as images
    if (str_contains($file->getClientOriginalName(), '.php')) {
        throw new \Exception('Invalid file name');
    }

    // Generate secure filename
    $extension = $file->getClientOriginalExtension();
    $filename = Str::random(40) . '.' . $extension;
    
    // Store with private visibility first, make public after validation
    $path = $file->storeAs("themes/{$user->id}", $filename, 'public');
    
    // Additional validation: try to load with image library
    try {
        $img = Image::make(Storage::disk('public')->path($path));
        // Re-encode to strip any malicious content
        $img->save();
    } catch (\Exception $e) {
        Storage::disk('public')->delete($path);
        throw new \Exception('Invalid image file');
    }

    // Store metadata
    $themeImage = ThemeImage::create([
        'user_id' => $user->id,
        'theme_id' => $theme?->id,
        'file_path' => $path,
        'file_type' => $type,
        'width' => $imageInfo[0] ?? null,
        'height' => $imageInfo[1] ?? null,
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
    ]);

    return $themeImage;
}
```

### 3.3 Storage Configuration
**File**: `config/filesystems.php`

Ensure proper disk configuration:
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
],
```

**Never store in `public/` directly** - use `storage/app/public` with symlink.

### 3.4 File Cleanup
**Create job** to cleanup orphaned images:
**File**: `app/Jobs/CleanupOrphanedImages.php`

```php
public function handle()
{
    // Delete images older than 24 hours with no theme_id
    $orphanedImages = ThemeImage::whereNull('theme_id')
        ->where('created_at', '<', now()->subDay())
        ->get();

    foreach ($orphanedImages as $image) {
        Storage::disk('public')->delete($image->file_path);
        $image->delete();
    }
}
```

Schedule daily:
```php
$schedule->job(new CleanupOrphanedImages)->daily();
```

## Task 4: Input Validation & Sanitization

### 4.1 Custom CSS Sanitization
**File**: `app/Services/ThemeService.php`

**Add CSS sanitization**:
```php
public function sanitizeCustomCss(string $css): string
{
    // Remove potentially dangerous CSS
    $dangerous = [
        'javascript:',
        'vbscript:',
        'data:text/html',
        '<script',
        '</script>',
        'expression(',
        'import',
        '@import',
        'behavior:',
    ];

    $css = str_ireplace($dangerous, '', $css);

    // Only allow safe CSS properties
    $allowedProperties = [
        'color', 'background', 'background-color', 'border',
        'padding', 'margin', 'font', 'text-align', 'display',
        'width', 'height', 'max-width', 'max-height',
        'border-radius', 'box-shadow', 'opacity',
    ];

    // Parse and validate CSS (basic approach)
    // For production, use a proper CSS parser library

    return $css;
}
```

**Apply in Theme validation**:
```php
public function updateTheme(Theme $theme, array $data): Theme
{
    if (isset($data['config']['custom_css'])) {
        if (!$theme->user->canUseCustomCss()) {
            throw new \Exception('Custom CSS not available on your plan');
        }
        $data['config']['custom_css'] = $this->sanitizeCustomCss(
            $data['config']['custom_css']
        );
    }
    
    // ...rest of method
}
```

### 4.2 Card Content Sanitization
**File**: `app/Http/Requests/CreateCardRequest.php`

```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'subtitle' => 'nullable|string|max:500',
        'custom_slug' => [
            'nullable',
            'string',
            'max:100',
            'alpha_dash', // Only letters, numbers, dashes, underscores
            'unique:business_cards,custom_slug',
        ],
        'theme_id' => 'nullable|exists:themes,id',
        'is_published' => 'boolean',
    ];
}
```

**File**: `app/Http/Requests/CreateSectionRequest.php`

```php
public function rules()
{
    return [
        'section_type' => 'required|in:contact,social,services,products,testimonials,hours,appointments,gallery',
        'title' => 'required|string|max:255',
        'content' => 'required|array',
        'content.*' => 'string|max:5000', // Limit individual content items
        'is_active' => 'boolean',
    ];
}

protected function prepareForValidation()
{
    // Sanitize HTML content
    if ($this->has('content')) {
        $this->merge([
            'content' => $this->sanitizeContent($this->content),
        ]);
    }
}

protected function sanitizeContent(array $content): array
{
    array_walk_recursive($content, function (&$value) {
        if (is_string($value)) {
            // Strip tags except safe ones
            $value = strip_tags($value, '<p><br><strong><em><a><ul><ol><li>');
            // Sanitize URLs in links
            $value = preg_replace_callback('/<a\s+href="([^"]+)"/', function($matches) {
                $url = filter_var($matches[1], FILTER_SANITIZE_URL);
                return '<a href="' . $url . '"';
            }, $value);
        }
    });
    return $content;
}
```

## Task 5: CSRF & XSS Protection

### 5.1 CSRF Protection
**Laravel handles this automatically** for forms.

**Verify**:
✅ `VerifyCsrfToken` middleware in `app/Http/Kernel.php`
✅ `@csrf` directive in Blade templates
✅ Inertia automatically includes CSRF token

**Exclude from CSRF** (if needed):
```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/*', // External webhooks
];
```

### 5.2 XSS Protection
**Laravel escapes output by default** with `{{ }}`.

**Verify**:
- Use `{{ $variable }}` NOT `{!! $variable !!}` unless absolutely necessary
- When using `{!! !!}`, sanitize first with `htmlspecialchars()` or `strip_tags()`

**For Vue.js**:
- Use `v-text` instead of `v-html` when possible
- Sanitize user input before rendering with `v-html`

**Install DOMPurify for Vue**:
```bash
npm install dompurify
```

```javascript
import DOMPurify from 'dompurify'

const sanitizeHTML = (html) => {
    return DOMPurify.sanitize(html, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'a', 'ul', 'ol', 'li'],
        ALLOWED_ATTR: ['href', 'target']
    })
}
```

## Task 6: SQL Injection Prevention

**Laravel Eloquent protects by default** with parameter binding.

**Verify**:
✅ Always use Eloquent query builder
✅ Never concatenate user input into raw queries
✅ Use parameter binding for raw queries

**Good**:
```php
DB::table('users')->where('email', $email)->first();
User::where('email', $email)->first();
```

**Bad**:
```php
DB::select("SELECT * FROM users WHERE email = '$email'"); // NEVER DO THIS
```

**If raw queries needed**:
```php
DB::select("SELECT * FROM users WHERE email = ?", [$email]); // Use parameter binding
```

## Task 7: Secure Configuration

### 7.1 Environment Configuration
**File**: `.env`

**Security checklist**:
```env
APP_ENV=production
APP_DEBUG=false # NEVER true in production
APP_KEY=base64:... # Strong key

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tapit
DB_USERNAME=tapit_user # Not root!
DB_PASSWORD=strong_random_password

# Session
SESSION_DRIVER=database # More secure than file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true # Only over HTTPS
SESSION_HTTP_ONLY=true # Prevent JavaScript access
SESSION_SAME_SITE=lax # CSRF protection

# CORS
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
```

### 7.2 Security Headers
**File**: `app/Http/Middleware/SecurityHeaders.php`

Create middleware:
```php
public function handle($request, Closure $next)
{
    $response = $next($request);

    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    
    if (config('app.env') === 'production') {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    return $response;
}
```

Register in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### 7.3 Database Security
**Migrations** - ensure proper indexes and constraints:
```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->string('email')->unique();
$table->index(['created_at']);
```

**Database user** - minimal privileges:
- SELECT, INSERT, UPDATE, DELETE on app tables
- NO DROP, CREATE DATABASE, GRANT

## Task 8: Dependency Security

### 8.1 Update Dependencies
```bash
composer update
npm update
```

### 8.2 Check for Vulnerabilities
```bash
composer audit
npm audit
```

Fix vulnerabilities:
```bash
npm audit fix
```

### 8.3 Keep Laravel Updated
```bash
composer update laravel/framework
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Task 9: Logging & Monitoring

### 9.1 Security Event Logging
**File**: `app/Providers/EventServiceProvider.php`

Log security events:
```php
Event::listen('Illuminate\Auth\Events\Failed', function ($event) {
    Log::warning('Failed login attempt', [
        'email' => $event->credentials['email'] ?? 'unknown',
        'ip' => request()->ip(),
    ]);
});

Event::listen('Illuminate\Auth\Events\Lockout', function ($event) {
    Log::error('Account lockout', [
        'email' => $event->request->email,
        'ip' => request()->ip(),
    ]);
});
```

### 9.2 Audit Trail
**Create model**: `app/Models/AuditLog.php`

Log important actions:
- Card published/unpublished
- Theme created/deleted
- Payment created/confirmed
- User data changed

## Task 10: Security Testing

### 10.1 Create Security Tests
**File**: `tests/Feature/SecurityTest.php`

```php
test('unauthorized user cannot access other user cards', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->get("/api/cards/{$card->id}");

    $response->assertForbidden();
});

test('custom css is rejected for free users', function () {
    $user = User::factory()->create(['subscription_tier' => 'free']);
    
    $response = $this->actingAs($user)->post('/api/themes', [
        'name' => 'Test',
        'config' => [
            'custom_css' => 'body { color: red; }'
        ]
    ]);

    $response->assertStatus(403);
});

test('malicious file upload is rejected', function () {
    $user = User::factory()->create();
    
    // Create fake PHP file disguised as image
    $file = UploadedFile::fake()->create('malicious.php.jpg', 100);
    
    $response = $this->actingAs($user)->post('/api/themes/upload', [
        'image' => $file,
        'type' => 'background'
    ]);

    $response->assertStatus(422);
});

test('rate limiting prevents brute force', function () {
    // Attempt 100 requests
    for ($i = 0; $i < 100; $i++) {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong'
        ]);
    }

    // Should be rate limited
    $response->assertStatus(429);
});
```

## Success Criteria

✅ All authorization policies implemented and tested
✅ Rate limiting configured for all API endpoints
✅ File upload security hardened
✅ Input validation and sanitization in place
✅ CSRF and XSS protection verified
✅ SQL injection prevention verified
✅ Security headers configured
✅ Secure configuration in production
✅ Dependency vulnerabilities fixed
✅ Security logging implemented
✅ Security tests passing
✅ No sensitive data exposed in responses
✅ No stack traces in production

## Critical Security Checklist

Before deployment:
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database user has minimal privileges
- [ ] HTTPS enforced
- [ ] CORS properly configured
- [ ] Rate limiting tested
- [ ] File upload restrictions tested
- [ ] All policies enforced
- [ ] Security headers present
- [ ] Dependencies updated
- [ ] Vulnerability scan passed
- [ ] Security tests passing

## Resources

- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
