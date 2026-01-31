# TapIt Test Suite Documentation

## Overview

Comprehensive test suite for the TapIt business card application with **211+ tests** covering all critical functionality.

## Test Statistics

- **Total Tests:** 211
- **Passing:** 106
- **Failing:** 94 (implementation pending)
- **Skipped:** 11 (require additional implementation)
- **Assertions:** 252+

## Test Categories

### 1. API Endpoint Tests (48 tests)

Located in `tests/Feature/Api/`

#### CardApiTest.php (16 tests)
- List cards with authentication
- Create cards with validation
- View, update, delete cards with authorization
- Publish, duplicate cards
- View analytics
- Authentication requirements

#### SectionApiTest.php (8 tests)
- Create sections for cards
- Update and delete sections
- Reorder sections
- Authorization checks
- Validation

#### ThemeApiTest.php (20 tests)
- List, create, update, delete themes
- View public vs private themes
- System theme protection
- Theme duplication
- Apply themes to cards
- Upload theme images
- Preview theme CSS

#### PaymentApiTest.php (11 tests)
- View subscription plans
- Create payments
- Admin confirm payments
- Payment history
- Pending payments
- Authorization

#### SubscriptionApiTest.php (4 tests)
- View subscription
- Cancel subscription
- Authentication

### 2. Service Layer Tests (34 tests)

#### CardServiceTest.php (10 tests)
- Basic card operations
- Primary card assignment
- Card duplication
- Custom slugs
- Section management

#### CardServiceAdvancedTest.php (15 tests)
- QR code generation
- NFC identifier assignment
- Custom slug management
- Analytics tracking (views, taps, scans, shares)
- Get card by slug/URL/NFC
- Published card retrieval

#### PaymentServiceAdvancedTest.php (5 tests)
- Payment creation
- Transaction ID generation
- Payment confirmation
- Subscription activation
- Payment history

#### ThemeServiceTest.php (5 tests)
- Theme creation
- CSS generation
- Theme limits
- Theme duplication

### 3. Authorization Tests (17 tests)

#### CardAuthorizationTest.php (7 tests)
- View own cards
- View published cards
- Prevent unauthorized access
- Update/delete permissions

#### ThemeAuthorizationTest.php (10 tests)
- View own themes
- View public themes
- Prevent private theme access
- System theme protection
- Update/delete permissions

### 4. File Upload Tests (8 tests - skipped)

Located in `tests/Feature/FileUpload/ImageUploadTest.php`

- Valid image upload (JPG, PNG, GIF)
- Invalid file type rejection
- File size limits
- Image resizing
- Multiple uploads
- Authentication requirements

*Note: Skipped pending upload endpoint implementation*

### 5. Payment Flow Tests (12 tests)

Located in `tests/Feature/PaymentFlow/SubscriptionFlowTest.php`

- Complete subscription upgrade flow
- Payment notifications
- Subscription expiry dates
- Payment history maintenance
- Tier limit updates
- Failed payment handling
- Subscription cancellation
- Payment refunds
- Multiple payment methods
- Pending/completed payments
- Admin payment management

### 6. Feature Tests (20 tests)

Located in `tests/Feature/Features/CompleteUserJourneyTest.php`

- User registration and first card
- Card creation with sections and themes
- Publish cards and view analytics
- Subscription upgrades
- QR code sharing
- Card duplication
- Custom theme creation
- Card limits by tier
- Custom slugs and NFC access
- Analytics tracking
- Multiple card management
- Primary card assignment
- Card search
- Section reordering
- Section visibility
- Public theme sharing
- Profile updates
- Subscription cancellation

### 7. Security Tests (10 tests)

#### AuthorizationSecurityTest.php
- Prevent unauthorized card access
- Prevent unauthorized modifications
- Prevent unauthorized deletions
- Section access control
- Private theme protection
- System theme protection
- Authentication enforcement
- SQL injection prevention
- XSS escape verification

#### RateLimitingTest.php (skipped)
- API rate limiting
- Login rate limiting
- Analytics tracking rate limiting

### 8. Existing Tests

#### Authentication Tests (13 tests)
- Login/logout
- Registration
- Email verification
- Password reset
- Password confirmation
- Password updates

#### NotificationTest.php (4 tests)
- Payment notifications
- Subscription notifications

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test tests/Feature/Api/
php artisan test tests/Feature/Authorization/
php artisan test tests/Feature/Services/
```

### Run With Coverage
```bash
php artisan test --coverage
```

### Run Specific Test File
```bash
php artisan test tests/Feature/Api/CardApiTest.php
```

### Run Specific Test
```bash
php artisan test --filter="api: user can list their cards"
```

## Test Coverage Goals

- **Target:** 70%+ code coverage
- **Current Focus:**
  - API Controllers: Full coverage
  - Services: Full coverage
  - Models: Relationship and scope coverage
  - Policies: Authorization logic coverage

## Test Structure

All tests use **Pest PHP** framework with:

- `beforeEach()` for setup
- Descriptive test names
- Clear assertion messages
- Database refresh for isolation
- Factory usage for test data

## Common Test Patterns

### API Test Pattern
```php
test('api: user can create resource', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.resource.store'), $data);
    
    $response->assertCreated()
        ->assertJsonStructure([...]);
});
```

### Authorization Test Pattern
```php
test('auth: user cannot access other users resource', function () {
    $resource = Resource::factory()->create(['user_id' => $otherUser->id]);
    
    expect($this->user->can('view', $resource))->toBeFalse();
});
```

### Service Test Pattern
```php
test('service: operation succeeds', function () {
    $result = $this->service->performOperation($data);
    
    expect($result)->toBeInstanceOf(Model::class);
    $this->assertDatabaseHas('table', ['field' => 'value']);
});
```

## Known Issues

### Failing Tests
Most failing tests are due to:
1. Missing API controller implementations
2. Missing validation rules
3. Missing policies
4. Missing service methods

### Skipped Tests
- File upload tests (pending upload endpoint)
- Rate limiting tests (pending rate limit configuration)
- Some payment flow tests (pending full integration)

## Next Steps

1. Implement missing API endpoints
2. Add validation rules
3. Create authorization policies
4. Implement file upload functionality
5. Configure rate limiting
6. Achieve 70%+ code coverage

## Contributing

When adding new tests:

1. Follow existing naming conventions
2. Use factories for test data
3. Keep tests isolated (use RefreshDatabase)
4. Test both success and failure cases
5. Test authorization and validation
6. Add descriptive test names
7. Group related tests in test classes

## Test Data

### Factories Used
- `UserFactory` - Test users with various tiers
- `BusinessCardFactory` - Cards with various states
- `CardSectionFactory` - Card sections
- `ThemeFactory` - Themes (public, private, system)
- `PaymentFactory` - Payments with various statuses
- `SubscriptionPlanFactory` - Subscription plans
- `UserSubscriptionFactory` - User subscriptions

### Test Database
- Uses SQLite in-memory database (`:memory:`)
- Refreshed before each test
- Isolated transactions

## Performance

- **Average test duration:** 33-47 seconds for full suite
- **Fast tests:** Unit tests (<0.01s)
- **Slow tests:** Feature tests with middleware (2-3s)

## Continuous Integration

Tests should run on:
- Every commit
- Every pull request
- Before deployment

Recommended CI configuration:
```yaml
- php artisan test --parallel
- php artisan test --coverage --min=70
```
