# Task: User Authentication System

**Agent:** auth-agent  
**Priority:** P0 (Critical)  
**Estimated Time:** 3-4 hours  
**Depends On:** Task 01 (Inertia setup)

## Objective
Implement complete user authentication system with registration, login, password reset, email verification, and profile management.

## Current State
- Laravel Breeze installed with Inertia Vue
- No authentication routes or pages
- User model exists with subscription fields
- Filament admin authentication separate

## Requirements

### 1. Authentication Routes
**Location:** `routes/auth.php`

```php
// Registration
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// Login
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

// Email Verification
Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verification.notice');
Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');

// Profile
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
```

### 2. Authentication Pages (Inertia Vue)

**Location:** `resources/js/Pages/Auth/`

#### Login.vue
- Email and password fields
- "Remember me" checkbox
- "Forgot password?" link
- Social login placeholders (future)
- Validation errors display
- Redirect to dashboard on success

#### Register.vue
- Name, email, password, password confirmation
- Terms of service checkbox
- Language selection (EN/AR)
- Validation errors display
- Auto-login after registration
- Redirect to dashboard

#### ForgotPassword.vue
- Email input
- Send reset link button
- Success message display

#### ResetPassword.vue
- Email (from token)
- New password
- Confirm password
- Submit button

#### VerifyEmail.vue
- Email verification notice
- Resend verification email button
- Check status message

### 3. Profile Management

**Location:** `resources/js/Pages/Profile/`

#### Edit.vue
- Update profile information (name, email, language)
- Change password section
- Delete account section
- Save changes button

### 4. Controllers

**Location:** `app/Http/Controllers/Auth/`

Create controllers:
- `RegisterController.php` - User registration
- `LoginController.php` - Login/logout
- `PasswordResetController.php` - Password reset flow
- `EmailVerificationController.php` - Email verification
- `ProfileController.php` - Profile management

### 5. Requests (Form Validation)

**Location:** `app/Http/Requests/Auth/`

- `LoginRequest.php` - Login validation
- `RegisterRequest.php` - Registration validation
- `UpdateProfileRequest.php` - Profile update validation

### 6. Middleware Configuration

**Location:** `app/Http/Kernel.php` or `bootstrap/app.php`

- Configure `auth` middleware for protected routes
- Setup `verified` middleware for email verification
- Add `guest` middleware for auth pages

### 7. Email Verification Setup

- Update User model with `MustVerifyEmail` interface
- Configure email templates
- Test email sending (use Mailtrap or log driver)

### 8. Password Reset Configuration

- Configure password reset token expiration
- Setup password reset email template
- Test reset flow

## Validation Rules

### Registration
- Name: required, string, max:255
- Email: required, email, unique:users
- Password: required, min:8, confirmed
- Language: required, in:en,ar

### Login
- Email: required, email
- Password: required
- Rate limiting: 5 attempts per minute

### Password
- Min 8 characters
- Must contain: uppercase, lowercase, number
- Cannot be common passwords

## Deliverables

1. ✅ 5 authentication pages created
2. ✅ All auth routes configured
3. ✅ Controllers implemented
4. ✅ Form validation requests
5. ✅ Email verification working
6. ✅ Password reset working
7. ✅ Profile management working
8. ✅ Middleware configured
9. ✅ Tests for auth flows (minimum 15 tests)

## Testing Requirements

Create tests in `tests/Feature/Auth/`:
- `RegistrationTest.php` - Test registration flow
- `LoginTest.php` - Test login/logout
- `PasswordResetTest.php` - Test password reset
- `EmailVerificationTest.php` - Test email verification
- `ProfileTest.php` - Test profile updates

## Validation Steps

1. ✅ Can register new user
2. ✅ Can login with correct credentials
3. ✅ Cannot login with wrong credentials
4. ✅ Rate limiting works (5 attempts)
5. ✅ Email verification sent on registration
6. ✅ Can verify email via link
7. ✅ Can reset password via email
8. ✅ Can update profile information
9. ✅ Can delete account
10. ✅ Redirects work correctly
11. ✅ All tests pass

## Notes
- Use Laravel Sanctum for API authentication
- Follow Laravel's authentication best practices
- Ensure password hashing uses bcrypt
- Add CSRF protection to all forms
- Implement proper error handling
- Add success/error toast notifications

## Dependencies
- Task 01 (Inertia setup) must be complete

## Next Tasks
After completion, integration-agent will build user dashboard.
