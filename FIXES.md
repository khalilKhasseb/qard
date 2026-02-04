# Fixes & Issues To Address

## 1. Canceled subscription immediately blocks user (should allow grace period)

**Current behavior:** When a user cancels their subscription, they are instantly locked out of the app (dashboard, cards, themes, analytics) even if they have days/weeks remaining on their billing period (`ends_at` is in the future).

**Expected behavior:** Canceling should disable auto-renewal but let the user continue using the app until `ends_at` is reached. Only after that date should access be revoked.

**Root causes (4 places need fixing):**

### A. `User::activeSubscription()` only matches `status = 'active'`
**File:** `app/Models/User.php` ~line 75
```php
// CURRENT: excludes canceled subscriptions entirely
return $this->hasOne(UserSubscription::class)
    ->where('status', 'active')
    ->latest();

// FIX: also include canceled subscriptions that haven't expired yet
return $this->hasOne(UserSubscription::class)
    ->where(function ($query) {
        $query->where('status', 'active')
              ->orWhere(function ($q) {
                  $q->where('status', 'canceled')
                    ->where('ends_at', '>', now());
              });
    })
    ->latest();
```

### B. `EnsureUserHasActiveSubscription` middleware only checks `status = 'active'`
**File:** `app/Http/Middleware/EnsureUserHasActiveSubscription.php` ~line 25
```php
// CURRENT: hard-coded status check
$hasActiveSubscription = $user->activeSubscription()
    ->where('status', 'active')
    ->exists();

// FIX: remove the redundant status filter since activeSubscription() already handles it
$hasActiveSubscription = $user->activeSubscription()->exists();
```

### C. `UserSubscription::isActive()` requires `status = 'active'`
**File:** `app/Models/UserSubscription.php` ~line 81
```php
// CURRENT: canceled + future ends_at = false
return $this->status === 'active' &&
       ($this->ends_at === null || $this->ends_at->isFuture());

// FIX: also treat canceled-but-not-expired as active
return ($this->status === 'active' || ($this->status === 'canceled' && $this->ends_at?->isFuture())) &&
       ($this->ends_at === null || $this->ends_at->isFuture());
```

### D. Three inconsistent cancel code paths
All three should use the model's `cancel()` method for consistency and should NOT change behavior (user keeps access until `ends_at`):

1. **Model method** `UserSubscription::cancel()` (line 117) - sets `canceled_at`, `auto_renew = false`, updates user `subscription_status`. This is the most complete. Keep as-is.
2. **API controller** `SubscriptionController::cancel()` - directly updates status, skips `auto_renew` and `canceled_at`. Should call `$subscription->cancel()` instead.
3. **Web route** `routes/web.php` subscription.cancel - only sets status. Should call `$subscription->cancel()` instead.

### E. `UserSubscription::scopeActive()` only matches `status = 'active'`
**File:** `app/Models/UserSubscription.php` ~line 65
```php
// CURRENT
return $query->where('status', 'active');

// FIX: include grace period subscriptions
return $query->where(function ($q) {
    $q->where('status', 'active')
      ->orWhere(function ($q2) {
          $q2->where('status', 'canceled')
             ->where('ends_at', '>', now());
      });
});
```

**Tests to update:** Any test that cancels a subscription and expects immediate loss of access when `ends_at` is in the future.

---

## 2. Session/CSRF lost on page refresh (redirected to login)

**Symptom:** After `npm run build`, every page refresh redirects to the login page. The session/auth cookie appears to not persist across requests.

**Environment:** Laravel Herd (Windows) serving `qard.test`, sessions stored in MySQL (`SESSION_DRIVER=database`), app accessed via `http://qard.test`.

**Likely causes to investigate (in order of probability):**

### A. `SESSION_DOMAIN=null` in `.env` may be parsed incorrectly
**File:** `.env` line 34
The value `SESSION_DOMAIN=null` relies on dotenv parsing `null` as PHP `null`. If for any reason this is treated as the literal string `"null"`, the session cookie domain becomes `"null"` which doesn't match `qard.test` and the browser won't send the cookie back.

**Fix:** Remove the line entirely or set it explicitly:
```
SESSION_DOMAIN=.qard.test
```

### B. Inertia version mismatch after build forces 409 reload
After `npm run build`, the Vite manifest hash changes. `HandleInertiaRequests::version()` returns a new hash. On the next Inertia request, the server returns a `409 Conflict` which tells the browser to do a full page reload. If the session cookie isn't being sent properly during this reload, auth is lost.

**Investigation:** Check browser DevTools > Network tab during the redirect. Look for:
- Is the session cookie (`qard-session`) present in request headers?
- Is the `XSRF-TOKEN` cookie present?
- Is there a `419` (CSRF mismatch) or `409` (Inertia version) response?

### C. MySQL sessions table may have stale/missing records
Sessions are stored in MySQL (`SESSION_DRIVER=database`). If the `sessions` table was cleared, migrated, or the DB connection dropped, all sessions are lost.

**Investigation:** Check if the session exists in DB:
```sql
SELECT * FROM sessions WHERE user_id IS NOT NULL ORDER BY last_activity DESC LIMIT 5;
```

### D. Cookie `SameSite=Lax` + HTTP may cause issues in some browsers
`SESSION_SAME_SITE=lax` (default) with `APP_URL=http://qard.test` (non-HTTPS) should work, but some browser security policies may interfere.

**Quick test:** Try setting in `.env`:
```
SESSION_SAME_SITE=none
SESSION_SECURE_COOKIE=false
```
If that fixes it, the issue is browser cookie policy related.

### E. `SESSION_SECURE_COOKIE` mismatch
Currently `false`, which is correct for HTTP. If Herd is serving via HTTPS but `APP_URL` says `http://`, or vice versa, the cookie won't be sent.

**Verify:** Does the browser URL show `http://qard.test` or `https://qard.test`? The `APP_URL` and `SESSION_SECURE_COOKIE` must match.

---

## 3. Session not persisting without "Remember Me" (redirected to login on refresh)

**Symptom:** Without checking "Remember Me" on login, every page refresh redirects to the login page. Checking "Remember Me" fixes the issue.

**Why "Remember Me" works as a workaround:** When `remember` is `true`, `Auth::attempt()` (in `LoginRequest.php` line 68) sets a long-lived `remember_token` cookie. On each request, even if the session is lost, Laravel's auth guard reads this cookie and re-authenticates the user transparently. This masks the underlying session persistence problem.

**Expected behavior:** Session should persist for `SESSION_LIFETIME=480` minutes (8 hours) without needing "Remember Me". The session cookie (`qard-session`) should be sent with every request and the database session record should remain valid.

**Current config:**
- `SESSION_DRIVER=database` (sessions stored in MySQL `sessions` table)
- `SESSION_LIFETIME=480` (8 hours)
- `SESSION_EXPIRE_ON_CLOSE` not set (defaults to `false`, meaning cookie has 8-hour expiry, not browser-session scoped)
- `SESSION_DOMAIN=null` (should resolve to PHP `null`, meaning cookie scoped to current host)
- `SESSION_SECURE_COOKIE=false` (correct for `http://qard.test`)
- `SESSION_SAME_SITE=lax` (default)

**Investigation steps (check in browser DevTools > Application > Cookies):**

1. **Is the session cookie present after login?** Look for `qard-session` cookie on `qard.test`. Check its domain, path, expiry, secure flag, and SameSite value.

2. **Is the cookie sent on refresh?** In DevTools > Network tab, click the page refresh request and check Request Headers for `Cookie:` — does it include `qard-session`?

3. **Does the session exist in the database?** Run in MySQL:
   ```sql
   SELECT id, user_id, ip_address, last_activity,
          FROM_UNIXTIME(last_activity) as last_active_at
   FROM sessions
   WHERE user_id IS NOT NULL
   ORDER BY last_activity DESC LIMIT 5;
   ```

4. **Is `SESSION_DOMAIN` causing issues?** The `.env` has `SESSION_DOMAIN=null`. Try removing this line entirely (let it default via config) or set it explicitly:
   ```
   SESSION_DOMAIN=.qard.test
   ```

5. **Is Herd serving via HTTPS while APP_URL says HTTP?** Check the actual browser URL bar. If Herd auto-upgrades to HTTPS, then `SESSION_SECURE_COOKIE` must be `true` and `APP_URL` must be `https://qard.test`.

**Most likely fix:** Set `SESSION_DOMAIN` explicitly in `.env`:
```
SESSION_DOMAIN=.qard.test
```
And if Herd serves HTTPS:
```
APP_URL=https://qard.test
SESSION_SECURE_COOKIE=true
```

---

## 4. Social media links prepend app base URL instead of linking externally

**Symptom:** Social media URLs entered without `https://` (e.g. `www.linkedin.com/in/user`) are rendered as relative links, so the browser navigates to `http://qard.test/u/www.linkedin.com/in/user` instead of `https://www.linkedin.com/in/user`.

**Root cause:** When a user enters a URL like `www.linkedin.com` or `linkedin.com/in/user` without a protocol prefix (`https://`), the `<a href="...">` tag treats it as a relative path. The browser resolves it relative to the current page URL (`http://qard.test/u/slug`), producing the broken link.

**Fix (two layers):**

### A. Frontend: Normalize URLs before rendering
Wherever social media links are rendered in the public card view, ensure the `href` always has a protocol. Add a helper:
```javascript
function ensureAbsoluteUrl(url) {
    if (!url) return '';
    if (url.match(/^https?:\/\//i)) return url;
    return 'https://' + url;
}
```
Then use it: `<a :href="ensureAbsoluteUrl(link.url)">`

**Files to check:**
- Public card view component(s) that render social media links
- Card section components that render URL-type fields
- Any shared link rendering component

### B. Backend: Validate/normalize on save
When saving social media URLs, normalize them to always include `https://` if no protocol is present. This prevents bad data from being stored in the first place.

**Files to check:**
- Card creation/update validation (Form Request or controller)
- Section data save logic
- Any model mutator/accessor for URL fields

---

## 5. Save sections button only at top — must scroll up to save

**Symptom:** On the card edit page, the "Save Sections" button is only at the top of the sections area. When editing sections at the bottom of a long card, the user has to scroll all the way back up to save.

**Expected behavior:** A save button should also be accessible at the bottom of the sections list, or always be visible via a sticky bar.

**Fix options (pick one):**

### A. Add a duplicate save button at the bottom of the sections list
After the last section, add another save button that triggers the same save action.

### B. Sticky save bar (recommended)
Make the save button area sticky (`position: sticky; bottom: 0; z-index: 10`) so it stays visible as the user scrolls through sections. This is the most common pattern in form-heavy UIs.

### C. Floating action button
A fixed-position save button in the bottom-right corner, only shown when there are unsaved changes.

**Files to check:**
- `resources/js/Pages/Cards/Edit.vue` — main card edit page
- Section panel component(s) that contain the save button

---

## 6. Text content in card sections lacks formatting (products, services, free text)

**Symptom:** Products, services, and other free-text content sections on the public card view display as plain unformatted text. No way to add line breaks, bold, spacing, or any basic formatting. Content looks flat and unprofessional.

**Expected behavior:** Users should be able to apply basic formatting (bold, italic, line breaks, lists, headings) when editing text content in card sections. The public card view should render this formatted content properly.

**Solution: Add a lightweight rich text editor**

Use a minimal WYSIWYG editor for text/description fields in card sections. It should support only basic formatting — not a full document editor.

### Recommended: Tiptap (Vue 3 compatible, lightweight)
- Headless, unstyled — fits any design system
- Vue 3 first-class support via `@tiptap/vue-3`
- Only import the extensions you need (bold, italic, lists, headings, line breaks)
- Outputs HTML that can be rendered safely with `v-html` + sanitization
- Very small bundle size when using only basic extensions

### Alternative: Quill (via `@vueup/vue-quill`)
- Pre-styled toolbar, quicker to set up
- Slightly heavier but still lightweight for basic use

### Required extensions (keep minimal):
- Bold, Italic, Underline
- Bullet list, Ordered list
- Headings (h3, h4 only)
- Line break / paragraph
- Link

### Implementation:

**A. Install editor (Tiptap example):**
```bash
npm install @tiptap/vue-3 @tiptap/starter-kit @tiptap/extension-underline @tiptap/extension-link
```

**B. Create a reusable `SimpleEditor.vue` component:**
- Minimal toolbar with icon buttons for each formatting option
- Emits HTML string via `v-model`
- Can be used in any section form field that needs rich text

**C. Update card section forms:**
Replace plain `<textarea>` fields for description/content with the new `SimpleEditor` component in the card edit page.

**D. Update public card view:**
Render the HTML content using `v-html` with DOMPurify sanitization to prevent XSS:
```bash
npm install dompurify
```
```vue
<div v-html="sanitize(section.content)"></div>
```

**E. Backend: Allow HTML in content fields**
- Store the HTML as-is in the database (the fields likely already accept text)
- Sanitize on output if needed (or trust frontend sanitization via DOMPurify)
- No migration changes needed if content columns are already `text` type

**Files to check:**
- `resources/js/Pages/Cards/Edit.vue` — section editing forms
- Section form components under `resources/js/Components/Card/`
- Public card view components that render section content
- Section model/migration to confirm column types

---

## 7. Card sections stacked too tightly on public card view

**Symptom:** On the public card view page, sections (social links, products, services, about, etc.) appear crammed together with little to no spacing between them. They look like one continuous block instead of distinct sections.

**Expected behavior:** Each section should have clear vertical spacing between it and the next, giving the card a clean, readable layout.

**Fix:** Add vertical gap/margin between sections in the public card view template. This is a CSS-only change.

**Options:**
- Add `space-y-6` or `space-y-8` (Tailwind) to the sections container
- Or add `mb-6` / `mb-8` to each individual section wrapper
- Optionally add a subtle divider (`border-b border-gray-200`) between sections for extra visual separation

**Files to check:**
- Public card view component(s) that loop over and render sections (likely under `resources/js/Pages/` or `resources/views/` for the public card page)
- Any shared section wrapper/layout component

---

## 8. Section cancel icon overlaps options & RTL misalignment + section options not translated

**Two sub-issues:**

### A. Cancel/close icon overlaps section options and misaligned in RTL

**Symptom:** When adding section data (gallery, products, services, etc.), the cancel/close (X) icon overlaps other options or buttons. In RTL mode (Arabic), the icon is not properly aligned — it stays in its LTR position instead of mirroring.

**Fix:**
- Use directional-aware Tailwind classes: replace `right-2` with `end-2` (or `me-2`) so it mirrors automatically in RTL
- Ensure the icon has proper `z-index` and doesn't overlap adjacent elements
- Add sufficient padding/margin to the section header to accommodate the icon

**Example:**
```html
<!-- BEFORE (LTR-only) -->
<button class="absolute top-2 right-2">X</button>

<!-- AFTER (RTL-aware) -->
<button class="absolute top-2 end-2">X</button>
```

### B. Section type options are not translated

**Symptom:** Section type names (Gallery, Products, Services, Social Links, etc.) shown in the section picker/dropdown are displayed in English regardless of the user's language setting. They are not using translation keys.

**Fix:**
- Add translation keys for all section types in `lang/en/cards.php` and `lang/ar/cards.php` (or wherever section labels are defined)
- Update the section picker component to use `t('cards.sections.gallery')` instead of hardcoded strings

**Example keys to add:**
```php
// lang/en/cards.php
'section_types' => [
    'gallery' => 'Gallery',
    'products' => 'Products',
    'services' => 'Services',
    'social_links' => 'Social Links',
    'about' => 'About',
    'contact' => 'Contact',
    // ... all other section types
],

// lang/ar/cards.php
'section_types' => [
    'gallery' => 'معرض الصور',
    'products' => 'المنتجات',
    'services' => 'الخدمات',
    'social_links' => 'روابط التواصل',
    'about' => 'حول',
    'contact' => 'التواصل',
    // ... all other section types
],
```

**Files to check:**
- Section picker/add section component in `resources/js/Components/Card/`
- Section header/wrapper component that renders the close icon
- `resources/js/Pages/Cards/Edit.vue`
- Translation files `lang/en/cards.php` and `lang/ar/cards.php`

---

## 9. Filament admin panel resources lack organization, hints, and proper layout

**Symptom:** All admin resources (Users, Subscriptions, Payments, Plans, Addons, etc.) have form fields that appear randomly placed without logical grouping. No hint texts, no sections, no side panels. The forms feel unfinished and hard to navigate.

**Expected behavior:** Each resource form should be well-organized using Filament's layout components with descriptive hint texts to guide the admin.

**Fix: Restructure all resource forms using Filament layout components**

### Layout components to use:
- **`Section::make()`** — group related fields with a title and optional description
- **`Grid::make(columns: 2)`** — side-by-side fields where appropriate
- **`Fieldset::make()`** — lighter grouping without card styling
- **`Tabs::make()`** — for resources with many fields, split into logical tabs
- **`Split::make()`** or side panel layout — put main content left, metadata/status right

### Hint texts:
- Add `->helperText('...')` to fields that need clarification
- Add `->placeholder('...')` for expected input format
- Add `->hint('...')` for inline tips on the field label row

### Recommended structure per resource:

**UserResource form:**
- Section: "Personal Information" — name, email, phone
- Section: "Account Settings" — is_admin, language, subscription_status
- Section: "Verification" — email_verified_at, phone_verified_at
- Sidebar/aside: timestamps, last_login

**SubscriptionPlanResource form:**
- Section: "Plan Details" — name, slug, description, price, billing_cycle
- Section: "Limits" — cards_limit, themes_limit, translation_credits_monthly
- Section: "Feature Toggles" — nfc_enabled, analytics_enabled, custom_domain_allowed, custom_css_allowed, unlimited_translations
- Grid for toggles (2 or 3 columns)
- Sidebar: is_active, sort_order

**PaymentResource form:**
- Section: "Payment Info" — amount, currency, payment_method, status
- Section: "References" — transaction_id, gateway_reference, user, plan, addon
- Section: "Notes" — notes, metadata
- Sidebar: timestamps, paid_at

**AddonResource form:**
- Section: "Add-on Details" — name, slug, description, type
- Section: "Pricing" — price, currency
- Section: "Configuration" — feature_key (visible when type=feature_unlock), value, sort_order
- Sidebar: is_active, timestamps

**UserSubscriptionResource form:**
- Section: "Subscription" — user, plan, status
- Section: "Dates" — starts_at, ends_at, trial_ends_at, canceled_at
- Sidebar: auto_renew, timestamps

### Example pattern (Filament v4):
```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

public static function form(Schema $form): Schema
{
    return $form->schema([
        Section::make(__('filament.plans.sections.details'))
            ->description(__('filament.plans.sections.details_desc'))
            ->schema([
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->helperText(__('filament.plans.hints.name')),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->helperText(__('filament.plans.hints.slug')),
                ]),
                Forms\Components\Textarea::make('description')
                    ->helperText(__('filament.plans.hints.description')),
            ]),

        Section::make(__('filament.plans.sections.limits'))
            ->description(__('filament.plans.sections.limits_desc'))
            ->schema([
                Grid::make(3)->schema([
                    Forms\Components\TextInput::make('cards_limit')
                        ->numeric()
                        ->helperText(__('filament.plans.hints.cards_limit')),
                    // ...
                ]),
            ]),
    ]);
}
```

**Files to update:**
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/SubscriptionPlanResource.php`
- `app/Filament/Resources/PaymentResource.php`
- `app/Filament/Resources/UserSubscriptionResource.php`
- `app/Filament/Resources/AddonResource.php`
- `app/Filament/Resources/UserAddonResource.php`
- All corresponding translation keys in `lang/en/filament.php` and `lang/ar/filament.php`

---

## 10. Site settings: Add toggles for "Enable Card Payments" and "Enable AI Translation"

**Symptom:** There are no global admin toggles to enable/disable card payments or AI translation features. These features are always available (or hardcoded). The admin should be able to turn them on/off site-wide, and their related settings/UI should only appear when enabled.

**Expected behavior:**
- Admin can toggle "Enable Card Payments" on/off in site settings
- Admin can toggle "Enable AI Translation" on/off in site settings
- When a feature is disabled, all related UI, routes, and functionality should be hidden/blocked across the entire application
- When enabled, show the relevant configuration settings for that feature

### Implementation:

#### A. Add settings to `GeneralSettings` (or create a `FeatureSettings` Spatie settings class)

**File:** `app/Settings/GeneralSettings.php` (or new `app/Settings/FeatureSettings.php`)

```php
// New properties
public bool $enable_card_payments = true;
public bool $enable_ai_translation = true;

// If AI translation is enabled, these config fields become relevant:
public ?string $ai_translation_provider = 'openai';  // openai, google, deepl
public ?string $ai_translation_api_key = null;
public ?int $ai_translation_max_credits = 100;

// If card payments is enabled, these config fields become relevant:
public ?string $payment_gateway = 'lahza';
public ?string $payment_gateway_public_key = null;
public ?string $payment_gateway_secret_key = null;
```

**Migration:** Create a Spatie settings migration to add these fields.

#### B. Filament admin settings page

Add these toggles to the site settings page in the admin panel. Use `->live()` on the toggle so the related config fields show/hide reactively:

```php
Forms\Components\Toggle::make('enable_card_payments')
    ->label('Enable Card Payments')
    ->helperText('Allow users to make payments via card. When disabled, only admin-granted subscriptions work.')
    ->live(),

Section::make('Payment Gateway Settings')
    ->visible(fn (Get $get) => $get('enable_card_payments'))
    ->schema([
        Forms\Components\Select::make('payment_gateway')
            ->options(['lahza' => 'Lahza', 'stripe' => 'Stripe']),
        Forms\Components\TextInput::make('payment_gateway_public_key')
            ->password(),
        Forms\Components\TextInput::make('payment_gateway_secret_key')
            ->password(),
    ]),

Forms\Components\Toggle::make('enable_ai_translation')
    ->label('Enable AI Translation')
    ->helperText('Allow users to use AI-powered translation for their cards.')
    ->live(),

Section::make('AI Translation Settings')
    ->visible(fn (Get $get) => $get('enable_ai_translation'))
    ->schema([
        Forms\Components\Select::make('ai_translation_provider')
            ->options(['openai' => 'OpenAI', 'google' => 'Google Translate', 'deepl' => 'DeepL']),
        Forms\Components\TextInput::make('ai_translation_api_key')
            ->password(),
        Forms\Components\TextInput::make('ai_translation_max_credits')
            ->numeric()
            ->helperText('Default monthly credits per user'),
    ]),
```

#### C. Share settings with frontend via HandleInertiaRequests

**File:** `app/Http/Middleware/HandleInertiaRequests.php`

Add feature flags to shared props so Vue pages can conditionally render UI:

```php
'features' => [
    'card_payments' => app(GeneralSettings::class)->enable_card_payments,
    'ai_translation' => app(GeneralSettings::class)->enable_ai_translation,
],
```

#### D. Hide UI when features are disabled

**Card Payments disabled — hide across the app:**
- Payments/Index.vue: Hide plan selection, payment forms, checkout buttons
- Payments/Checkout.vue: Redirect or show "payments disabled" message
- Subscription/Index.vue: Hide upgrade/renew buttons that lead to payment
- Addons/Index.vue: Hide "Buy Now" buttons
- Addons/Checkout.vue: Redirect or show disabled message
- Cards/Index.vue: Hide "Buy Extra Card Slots" link in limit reached banner
- Dashboard.vue: Adjust subscription section (no payment CTAs)
- Navigation: Hide "Payments" link in user dropdown

**AI Translation disabled — hide across the app:**
- Cards/Edit.vue: Hide the AI Translation panel/section entirely
- Remove translation credits display from subscription/dashboard if not relevant
- Hide translation-related settings from user-facing UI

#### E. Backend: Guard routes when features are disabled

Create a simple middleware or inline check:

```php
// Option 1: Middleware
class EnsureFeatureEnabled
{
    public function handle($request, Closure $next, string $feature)
    {
        $settings = app(GeneralSettings::class);

        if ($feature === 'payments' && !$settings->enable_card_payments) {
            abort(403, 'Card payments are currently disabled.');
        }

        if ($feature === 'ai_translation' && !$settings->enable_ai_translation) {
            abort(403, 'AI translation is currently disabled.');
        }

        return $next($request);
    }
}

// Option 2: Inline in routes
Route::middleware(['feature:payments'])->group(function () {
    // Payment routes...
});
```

**Files to check/update:**
- `app/Settings/GeneralSettings.php` (or new FeatureSettings)
- Filament settings page for site configuration
- `app/Http/Middleware/HandleInertiaRequests.php` — share feature flags
- All Vue pages that show payment or translation UI — wrap in `v-if="$page.props.features.card_payments"` etc.
- `routes/web.php` — guard payment and translation routes
- `resources/js/Layouts/AuthenticatedLayout.vue` — conditionally show nav links
- Translation files for new setting labels

---
