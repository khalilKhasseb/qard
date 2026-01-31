# Phase 2 - Testing Agent Tasks

## Context
TapIt digital business card application at C:\Users\user\Herd\qard
- Laravel 12 with Pest PHP testing framework
- 45 existing tests passing (Phase 1)
- New features to test: API endpoints, frontend components, payment flows, theme editor

## Objective
Comprehensive testing strategy:
1. API Endpoint Tests (Feature tests)
2. Service Layer Tests (Unit tests)
3. Authentication & Authorization Tests
4. File Upload Tests
5. Payment Flow Tests
6. Frontend Component Tests (Vue)
7. E2E Testing (optional but recommended)
8. Performance Tests
9. Security Tests

## Task 1: API Endpoint Tests

### 1.1 Business Cards API Tests
**File**: `tests/Feature/Api/CardApiTest.php`

```php
<?php

use App\Models\User;
use App\Models\BusinessCard;
use App\Models\Theme;

test('authenticated user can list their cards', function () {
    $user = User::factory()->create();
    $cards = BusinessCard::factory()->count(3)->create(['user_id' => $user->id]);
    $otherCard = BusinessCard::factory()->create(); // Different user

    $response = $this->actingAs($user)->getJson('/api/cards');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonMissing(['id' => $otherCard->id]);
});

test('user can create card within limit', function () {
    $user = User::factory()->create(['subscription_tier' => 'free']); // Limit: 1

    $response = $this->actingAs($user)->postJson('/api/cards', [
        'title' => 'My Card',
        'subtitle' => 'Software Engineer',
        'theme_id' => Theme::factory()->create()->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'My Card');
    
    $this->assertDatabaseHas('business_cards', [
        'user_id' => $user->id,
        'title' => 'My Card',
    ]);
});

test('free user cannot exceed card limit', function () {
    $user = User::factory()->create(['subscription_tier' => 'free']);
    BusinessCard::factory()->create(['user_id' => $user->id]); // Already at limit

    $response = $this->actingAs($user)->postJson('/api/cards', [
        'title' => 'Second Card',
        'subtitle' => 'Test',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Card limit reached for your plan');
});

test('user can update their own card', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/cards/{$card->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');
});

test('user cannot update other user card', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->putJson("/api/cards/{$card->id}", [
        'title' => 'Hacked',
    ]);

    $response->assertForbidden();
});

test('user can delete their own card', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/api/cards/{$card->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('business_cards', ['id' => $card->id]);
});

test('user can publish card', function () {
    $user = User::factory()->create(['subscription_status' => 'active']);
    $card = BusinessCard::factory()->create(['user_id' => $user->id, 'is_published' => false]);

    $response = $this->actingAs($user)->postJson("/api/cards/{$card->id}/publish");

    $response->assertOk();
    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'is_published' => true,
    ]);
});

test('user can duplicate card', function () {
    $user = User::factory()->create(['subscription_tier' => 'pro']); // Can create multiple
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/api/cards/{$card->id}/duplicate");

    $response->assertCreated()
        ->assertJsonPath('data.title', $card->title . ' (Copy)');
});

test('card requires title', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/cards', [
        'subtitle' => 'No title',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});
```

### 1.2 Theme API Tests
**File**: `tests/Feature/Api/ThemeApiTest.php`

```php
<?php

use App\Models\User;
use App\Models\Theme;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can create theme', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/themes', [
        'name' => 'My Theme',
        'config' => Theme::getDefaultConfig(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'My Theme');
});

test('user can view own themes and public themes', function () {
    $user = User::factory()->create();
    $ownTheme = Theme::factory()->create(['user_id' => $user->id]);
    $publicTheme = Theme::factory()->create(['is_public' => true]);
    $privateTheme = Theme::factory()->create(['is_public' => false]);

    $response = $this->actingAs($user)->getJson('/api/themes');

    $response->assertOk()
        ->assertJsonFragment(['id' => $ownTheme->id])
        ->assertJsonFragment(['id' => $publicTheme->id])
        ->assertJsonMissing(['id' => $privateTheme->id]);
});

test('user can update their theme', function () {
    $user = User::factory()->create();
    $theme = Theme::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/themes/{$theme->id}", [
        'name' => 'Updated Theme',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Theme');
});

test('user cannot update system default theme', function () {
    $user = User::factory()->create();
    $theme = Theme::factory()->create(['is_system_default' => true]);

    $response = $this->actingAs($user)->putJson("/api/themes/{$theme->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertForbidden();
});

test('pro user can use custom css', function () {
    $user = User::factory()->create(['subscription_tier' => 'pro']);
    $theme = Theme::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/themes/{$theme->id}", [
        'config' => array_merge($theme->config, [
            'custom_css' => 'body { color: red; }'
        ]),
    ]);

    $response->assertOk()
        ->assertJsonPath('data.config.custom_css', 'body { color: red; }');
});

test('free user cannot use custom css', function () {
    $user = User::factory()->create(['subscription_tier' => 'free']);
    $theme = Theme::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/themes/{$theme->id}", [
        'config' => array_merge($theme->config, [
            'custom_css' => 'body { color: red; }'
        ]),
    ]);

    $response->assertForbidden();
});

test('user can upload theme image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $theme = Theme::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('background.jpg', 1920, 1080);

    $response = $this->actingAs($user)->postJson('/api/themes/upload', [
        'image' => $file,
        'type' => 'background',
        'theme_id' => $theme->id,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['success', 'url', 'image_id']);
    
    Storage::disk('public')->assertExists('themes/' . $user->id . '/' . basename($response->json('url')));
});

test('user cannot upload non-image file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($user)->postJson('/api/themes/upload', [
        'image' => $file,
        'type' => 'background',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

test('user cannot upload image larger than 5mb', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('huge.jpg', 6000); // 6MB

    $response = $this->actingAs($user)->postJson('/api/themes/upload', [
        'image' => $file,
        'type' => 'background',
    ]);

    $response->assertStatus(422);
});
```

### 1.3 Card Sections API Tests
**File**: `tests/Feature/Api/SectionApiTest.php`

```php
<?php

use App\Models\User;
use App\Models\BusinessCard;
use App\Models\CardSection;

test('user can add section to their card', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/api/cards/{$card->id}/sections", [
        'section_type' => 'contact',
        'title' => 'Contact Me',
        'content' => [
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Contact Me');
});

test('user cannot add section to other user card', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->postJson("/api/cards/{$card->id}/sections", [
        'section_type' => 'contact',
        'title' => 'Hacked',
        'content' => [],
    ]);

    $response->assertForbidden();
});

test('user can reorder sections', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);
    $section1 = CardSection::factory()->create(['business_card_id' => $card->id, 'sort_order' => 0]);
    $section2 = CardSection::factory()->create(['business_card_id' => $card->id, 'sort_order' => 1]);
    $section3 = CardSection::factory()->create(['business_card_id' => $card->id, 'sort_order' => 2]);

    $response = $this->actingAs($user)->postJson("/api/cards/{$card->id}/sections/reorder", [
        'sections' => [$section3->id, $section1->id, $section2->id],
    ]);

    $response->assertOk();
    
    $this->assertDatabaseHas('card_sections', ['id' => $section3->id, 'sort_order' => 0]);
    $this->assertDatabaseHas('card_sections', ['id' => $section1->id, 'sort_order' => 1]);
    $this->assertDatabaseHas('card_sections', ['id' => $section2->id, 'sort_order' => 2]);
});
```

### 1.4 Payment API Tests
**File**: `tests/Feature/Api/PaymentApiTest.php`

```php
<?php

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Payment;

test('user can view subscription plans', function () {
    SubscriptionPlan::factory()->count(3)->create();

    $response = $this->getJson('/api/subscription-plans');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can create payment', function () {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create(['price' => 9.99]);

    $response = $this->actingAs($user)->postJson('/api/payments', [
        'subscription_plan_id' => $plan->id,
        'currency' => 'USD',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.amount', 9.99);
});

test('user can confirm payment', function () {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->postJson("/api/payments/{$payment->id}/confirm", [
        'confirmation_code' => 'CASH123',
    ]);

    $response->assertOk();
    
    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => 'completed',
    ]);
    
    $this->assertDatabaseHas('user_subscriptions', [
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
    ]);
});

test('user cannot confirm other user payment', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $payment = Payment::factory()->create(['user_id' => $user1->id, 'status' => 'pending']);

    $response = $this->actingAs($user2)->postJson("/api/payments/{$payment->id}/confirm");

    $response->assertForbidden();
});

test('user can view payment history', function () {
    $user = User::factory()->create();
    Payment::factory()->count(5)->create(['user_id' => $user->id]);
    Payment::factory()->count(3)->create(); // Other user

    $response = $this->actingAs($user)->getJson('/api/payments/history');

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});
```

## Task 2: Service Layer Tests

### 2.1 CardService Tests (Enhanced)
**File**: `tests/Unit/CardServiceTest.php`

```php
<?php

use App\Services\CardService;
use App\Models\User;
use App\Models\BusinessCard;
use App\Models\Template;

test('card service generates unique share url', function () {
    $user = User::factory()->create();
    $service = new CardService();

    $card1 = $service->createCard($user, ['title' => 'Card 1']);
    $card2 = $service->createCard($user, ['title' => 'Card 2']);

    expect($card1->share_url)->not->toBe($card2->share_url);
});

test('card service validates custom slug uniqueness', function () {
    $user = User::factory()->create();
    $service = new CardService();

    $service->setCustomSlug(
        BusinessCard::factory()->create(),
        'test-slug'
    );

    expect(fn() => $service->setCustomSlug(
        BusinessCard::factory()->create(),
        'test-slug'
    ))->toThrow('This URL is already taken');
});

test('card service tracks analytics', function () {
    $card = BusinessCard::factory()->create();
    $service = new CardService();

    $service->trackView($card, ['ip' => '127.0.0.1']);

    expect($card->fresh()->views_count)->toBe(1);
    $this->assertDatabaseHas('analytics_events', [
        'business_card_id' => $card->id,
        'event_type' => 'view',
    ]);
});
```

### 2.2 ThemeService Tests (Enhanced)
**File**: `tests/Unit/ThemeServiceTest.php`

```php
<?php

use App\Services\ThemeService;
use App\Models\User;
use App\Models\Theme;

test('theme service generates valid css', function () {
    $theme = Theme::factory()->create([
        'config' => [
            'colors' => ['primary' => '#ff0000'],
            'fonts' => ['body' => 'Arial'],
            'images' => [],
            'layout' => ['border_radius' => '10px'],
            'custom_css' => '',
        ],
    ]);

    $service = new ThemeService();
    $css = $service->generateCSS($theme);

    expect($css)->toContain('--primary: #ff0000')
        ->toContain('font-family: Arial')
        ->toContain('border-radius: 10px');
});

test('theme service sanitizes custom css', function () {
    $service = new ThemeService();
    
    $maliciousCss = 'body { color: red; } <script>alert("XSS")</script>';
    $sanitized = $service->sanitizeCustomCss($maliciousCss);

    expect($sanitized)->not->toContain('<script>')
        ->not->toContain('javascript:');
});

test('theme service applies theme to card', function () {
    $theme = Theme::factory()->create(['used_by_cards_count' => 0]);
    $card = BusinessCard::factory()->create();
    $service = new ThemeService();

    $service->applyToCard($theme, $card);

    expect($card->fresh()->theme_id)->toBe($theme->id);
    expect($theme->fresh()->used_by_cards_count)->toBe(1);
});
```

### 2.3 PaymentService Tests (Enhanced)
**File**: `tests/Unit/PaymentServiceTest.php`

```php
<?php

use App\Services\PaymentService;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Payment;

test('payment service calculates correct end date for monthly plan', function () {
    $plan = SubscriptionPlan::factory()->create(['billing_cycle' => 'monthly']);
    $service = new PaymentService();

    $endDate = $service->calculateEndDate($plan);

    expect($endDate->diffInDays(now()))->toBe(30);
});

test('payment service calculates correct end date for yearly plan', function () {
    $plan = SubscriptionPlan::factory()->create(['billing_cycle' => 'yearly']);
    $service = new PaymentService();

    $endDate = $service->calculateEndDate($plan);

    expect($endDate->diffInDays(now()))->toBe(365);
});

test('payment service returns null for lifetime plan', function () {
    $plan = SubscriptionPlan::factory()->create(['billing_cycle' => 'lifetime']);
    $service = new PaymentService();

    $endDate = $service->calculateEndDate($plan);

    expect($endDate)->toBeNull();
});
```

## Task 3: Authentication & Authorization Tests

**File**: `tests/Feature/AuthorizationTest.php`

```php
<?php

use App\Models\User;
use App\Models\BusinessCard;
use App\Models\Theme;

test('guest cannot access api endpoints', function () {
    $response = $this->getJson('/api/cards');
    $response->assertUnauthorized();
});

test('user can only access own cards', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->getJson("/api/cards/{$card->id}");
    $response->assertForbidden();
});

test('user can access public themes', function () {
    $user = User::factory()->create();
    $publicTheme = Theme::factory()->create(['is_public' => true]);

    $response = $this->actingAs($user)->getJson("/api/themes/{$publicTheme->id}");
    $response->assertOk();
});

test('subscription status gates features', function () {
    $user = User::factory()->create([
        'subscription_status' => 'expired',
        'subscription_tier' => 'free'
    ]);
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/api/cards/{$card->id}/publish");
    $response->assertForbidden();
});
```

## Task 4: Rate Limiting Tests

**File**: `tests/Feature/RateLimitingTest.php`

```php
<?php

use App\Models\User;

test('api rate limit is enforced', function () {
    $user = User::factory()->create();

    // Make 70 requests (limit is 60 per minute)
    for ($i = 0; $i < 70; $i++) {
        $response = $this->actingAs($user)->getJson('/api/cards');
        
        if ($i < 60) {
            expect($response->status())->toBe(200);
        } else {
            expect($response->status())->toBe(429); // Too many requests
        }
    }
});

test('upload rate limit is stricter', function () {
    $user = User::factory()->create();

    // Make 15 upload requests (limit is 10 per minute)
    for ($i = 0; $i < 15; $i++) {
        $response = $this->actingAs($user)->postJson('/api/themes/upload', [
            'image' => UploadedFile::fake()->image('test.jpg'),
            'type' => 'background',
        ]);
        
        if ($i < 10) {
            expect($response->status())->not->toBe(429);
        } else {
            expect($response->status())->toBe(429);
        }
    }
});
```

## Task 5: Frontend Component Tests (Vue)

### 5.1 Setup Vitest
**File**: `vitest.config.js`

```javascript
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        globals: true,
    },
})
```

Install dependencies:
```bash
npm install --save-dev vitest @vue/test-utils jsdom
```

### 5.2 Component Tests
**File**: `resources/js/Components/__tests__/ColorPicker.test.js`

```javascript
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ColorPicker from '../ColorPicker.vue'

describe('ColorPicker', () => {
    it('renders color input', () => {
        const wrapper = mount(ColorPicker, {
            props: { modelValue: '#ff0000', label: 'Primary Color' }
        })
        
        expect(wrapper.find('input[type="color"]').exists()).toBe(true)
    })
    
    it('emits update on color change', async () => {
        const wrapper = mount(ColorPicker, {
            props: { modelValue: '#ff0000' }
        })
        
        await wrapper.find('input').setValue('#00ff00')
        
        expect(wrapper.emitted('update:modelValue')[0]).toEqual(['#00ff00'])
    })
})
```

**File**: `resources/js/Components/__tests__/ThemePreview.test.js`

```javascript
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ThemePreview from '../ThemePreview.vue'

describe('ThemePreview', () => {
    it('applies theme colors', () => {
        const config = {
            colors: { primary: '#ff0000', background: '#ffffff' },
            fonts: { body: 'Arial' },
            images: {},
            layout: { border_radius: '10px' }
        }
        
        const wrapper = mount(ThemePreview, {
            props: { config }
        })
        
        const styles = wrapper.find('.theme-wrapper').attributes('style')
        expect(styles).toContain('background')
    })
})
```

### 5.3 Run Component Tests
```bash
npm run test:unit
```

Add to `package.json`:
```json
{
    "scripts": {
        "test:unit": "vitest",
        "test:unit:ui": "vitest --ui"
    }
}
```

## Task 6: E2E Testing (Optional but Recommended)

### 6.1 Setup Laravel Dusk
```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

### 6.2 Create E2E Tests
**File**: `tests/Browser/CardCreationTest.php`

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CardCreationTest extends DuskTestCase
{
    public function test_user_can_create_card()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/cards/create')
                ->type('title', 'My Business Card')
                ->type('subtitle', 'Software Engineer')
                ->press('Create Card')
                ->assertPathIs('/cards')
                ->assertSee('My Business Card');
        });
    }

    public function test_theme_editor_live_preview()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/themes/create')
                ->type('name', 'My Theme')
                ->click('input[type="color"]')
                ->type('input[type="color"]', '#ff0000')
                ->pause(500) // Wait for preview update
                ->assertVisible('.theme-preview')
                ->assertPresent('.theme-preview [style*="ff0000"]');
        });
    }
}
```

### 6.3 Run E2E Tests
```bash
php artisan dusk
```

## Task 7: Performance Tests

**File**: `tests/Performance/ApiPerformanceTest.php`

```php
<?php

use App\Models\User;
use App\Models\BusinessCard;

test('card list endpoint performs well', function () {
    $user = User::factory()->create();
    BusinessCard::factory()->count(100)->create(['user_id' => $user->id]);

    $start = microtime(true);
    
    $response = $this->actingAs($user)->getJson('/api/cards');
    
    $duration = microtime(true) - $start;

    expect($duration)->toBeLessThan(1); // Should respond in less than 1 second
    expect($response)->assertOk();
});

test('theme css generation is fast', function () {
    $theme = Theme::factory()->create();
    $service = new ThemeService();

    $start = microtime(true);
    
    $css = $service->generateCSS($theme);
    
    $duration = microtime(true) - $start;

    expect($duration)->toBeLessThan(0.1); // Should generate in less than 100ms
});
```

## Task 8: Database Query Tests

**File**: `tests/Feature/DatabaseQueryTest.php`

```php
<?php

use App\Models\User;
use App\Models\BusinessCard;
use Illuminate\Support\Facades\DB;

test('card list uses eager loading', function () {
    $user = User::factory()->create();
    BusinessCard::factory()->count(10)->create(['user_id' => $user->id]);

    DB::enableQueryLog();

    $this->actingAs($user)->getJson('/api/cards');

    $queries = DB::getQueryLog();
    
    // Should be 1 query for cards + 1 for relationships (with eager loading)
    // Not N+1 problem
    expect(count($queries))->toBeLessThan(5);
});
```

## Task 9: Run All Tests

### 9.1 Create Test Suite
**File**: `phpunit.xml`

Ensure test suites are configured:
```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
</testsuites>
```

### 9.2 Run Tests
```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=CardApiTest

# Run in parallel (faster)
php artisan test --parallel
```

## Task 10: Continuous Integration

### 10.1 GitHub Actions Workflow
**File**: `.github/workflows/tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, pdo, pdo_mysql
        coverage: xdebug

    - name: Install Composer dependencies
      run: composer install --prefer-dist --no-interaction

    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"

    - name: Generate key
      run: php artisan key:generate

    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache

    - name: Run migrations
      run: php artisan migrate --force

    - name: Run tests
      run: php artisan test --coverage --min=70
```

## Task 11: Test Coverage Goals

### Coverage Targets:
- **Overall**: 70%+ coverage
- **Services**: 90%+ coverage
- **Controllers**: 80%+ coverage
- **Models**: 80%+ coverage

### Generate Coverage Report:
```bash
php artisan test --coverage --min=70
```

## Success Criteria

✅ All 45 existing tests still pass
✅ API endpoint tests cover all CRUD operations
✅ Authorization tests verify access control
✅ File upload tests validate security
✅ Payment flow tests cover complete cycle
✅ Frontend component tests pass
✅ E2E tests cover critical user journeys
✅ Performance tests validate response times
✅ No N+1 query problems
✅ Rate limiting tests pass
✅ Test coverage > 70%
✅ CI/CD pipeline configured
✅ All tests run in under 2 minutes

## Test Checklist

Before marking Phase 2 complete:
- [ ] All API endpoints tested
- [ ] All service methods tested
- [ ] Authorization policies tested
- [ ] File uploads tested (success & failure cases)
- [ ] Payment flow tested end-to-end
- [ ] Rate limiting verified
- [ ] XSS/CSRF protection tested
- [ ] Input validation tested
- [ ] Database queries optimized (no N+1)
- [ ] Frontend components tested
- [ ] E2E tests for critical flows
- [ ] Performance benchmarks met
- [ ] CI/CD passing
- [ ] Test coverage > 70%

## Running Tests Efficiently

```bash
# Quick test run (no coverage)
php artisan test

# Full test suite with coverage
php artisan test --coverage

# Parallel execution (faster)
php artisan test --parallel

# Watch mode during development
php artisan test --watch

# Specific tests
php artisan test --filter=CardApi

# Pest specific
php artisan test --group=api
```

## Notes

- Write tests BEFORE or DURING feature development (TDD)
- Keep tests fast (use factories, avoid external calls)
- Mock external services
- Use database transactions to keep tests isolated
- Test both success and failure cases
- Test edge cases and boundary conditions
