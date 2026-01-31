# Phase 2 - Notification Agent Tasks (Email Notifications)

## Context
TapIt digital business card application at C:\Users\user\Herd\qard
- Laravel 12 with built-in mail system
- Laravel Breeze authentication installed
- Email verification already configured (EmailVerificationNotification exists)
- Need to configure and create custom notifications

## Objective
Set up email notification system for:
1. Welcome email on registration
2. Email verification (already exists, just configure)
3. Password reset (already exists, just configure)
4. Payment confirmation
5. Subscription expiry reminder
6. Subscription activated

## Task 1: Email Configuration

### Update `.env` file
Configure mail settings (use Mailtrap for development, SMTP for production):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tapit.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Configure `config/mail.php`
Ensure proper configuration (should already be set by Laravel):
- Default mailer
- From address and name
- Reply-to address

## Task 2: Welcome Email

### Create Notification
**File**: `app/Notifications/WelcomeNotification.php`

```bash
php artisan make:notification WelcomeNotification
```

**Requirements**:
- Send when user registers
- Include:
  - Personalized greeting with user's name
  - Brief intro to TapIt
  - Quick start guide (create your first card)
  - Link to dashboard
  - Link to help/support

**Properties**:
- Subject: "Welcome to TapIt! 🎉"
- Via: mail
- Queue: yes (ShouldQueue)

### Create Mailable (Alternative approach)
**File**: `app/Mail/WelcomeMail.php`

```bash
php artisan make:mail WelcomeMail
```

### Email Template
**File**: `resources/views/emails/welcome.blade.php`

**Content**:
```html
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Welcome to TapIt, {{ $user->name }}! 🎉</h1>
    
    <p>Thanks for joining TapIt - your digital business card platform.</p>
    
    <h2>Get Started in 3 Steps:</h2>
    <ol>
        <li><strong>Create Your First Card</strong> - Click below to create your digital business card</li>
        <li><strong>Customize Your Theme</strong> - Make it uniquely yours with our theme editor</li>
        <li><strong>Share Your Card</strong> - Share via QR code, NFC, or direct link</li>
    </ol>
    
    <a href="{{ url('/cards/create') }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0;">
        Create Your First Card
    </a>
    
    <p>If you have any questions, feel free to <a href="{{ url('/support') }}">contact our support team</a>.</p>
    
    <p>Happy card creating!<br>The TapIt Team</p>
</div>
```

### Trigger Welcome Email
**File**: `app/Http/Controllers/Auth/RegisteredUserController.php`

Add in `store()` method after user creation:
```php
$user->notify(new \App\Notifications\WelcomeNotification());
// or
Mail::to($user)->send(new \App\Mail\WelcomeMail($user));
```

## Task 3: Email Verification

**Status**: ✅ Already configured by Laravel Breeze

**Verify**:
- `Illuminate\Auth\Notifications\VerifyEmail` is used
- Email verification routes exist in `routes/auth.php`
- `MustVerifyEmail` interface on User model

**Optional**: Customize verification email template

**File**: `resources/views/vendor/notifications/email.blade.php`

```bash
php artisan vendor:publish --tag=laravel-notifications
```

Customize the template with TapIt branding.

## Task 4: Password Reset

**Status**: ✅ Already configured by Laravel Breeze

**Verify**:
- `Illuminate\Auth\Notifications\ResetPassword` is used
- Password reset routes exist in `routes/auth.php`

**Optional**: Customize reset email template (same as verification above)

## Task 5: Payment Confirmation Email

### Create Notification
**File**: `app/Notifications/PaymentConfirmedNotification.php`

```bash
php artisan make:notification PaymentConfirmedNotification
```

**Requirements**:
- Send when payment is confirmed by admin
- Include:
  - Payment amount
  - Subscription plan name
  - Payment reference number
  - Subscription start date
  - Subscription end date
  - Receipt/invoice link
  - Thank you message

**Properties**:
- Subject: "Payment Confirmed - Welcome to {{ $plan->name }}!"
- Via: mail, database (for in-app notification)
- Queue: yes

### Email Template
**File**: `resources/views/emails/payment-confirmed.blade.php`

**Content**:
```html
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Payment Confirmed! ✅</h1>
    
    <p>Hi {{ $user->name }},</p>
    
    <p>Great news! Your payment has been confirmed and your subscription is now active.</p>
    
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3>Payment Details</h3>
        <table style="width: 100%;">
            <tr>
                <td><strong>Plan:</strong></td>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <td><strong>Amount:</strong></td>
                <td>${{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Reference:</strong></td>
                <td>{{ $payment->transaction_id }}</td>
            </tr>
            <tr>
                <td><strong>Start Date:</strong></td>
                <td>{{ $subscription->starts_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td><strong>End Date:</strong></td>
                <td>{{ $subscription->ends_at?->format('M d, Y') ?? 'Lifetime' }}</td>
            </tr>
        </table>
    </div>
    
    <h3>What's Next?</h3>
    <ul>
        <li>Create up to {{ $plan->card_limit }} business cards</li>
        <li>Design {{ $plan->theme_limit }} custom themes</li>
        @if($plan->features['custom_css'] ?? false)
        <li>Use custom CSS in your themes</li>
        @endif
    </ul>
    
    <a href="{{ url('/dashboard') }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0;">
        Go to Dashboard
    </a>
    
    <p>Thank you for subscribing to TapIt!<br>The TapIt Team</p>
</div>
```

### Trigger Payment Confirmation Email
**File**: `app/Services/PaymentService.php`

In `confirmPaymentAndActivateSubscription()` method, after subscription activation:
```php
$user->notify(new \App\Notifications\PaymentConfirmedNotification($payment, $subscription));
```

## Task 6: Subscription Expiry Reminder

### Create Notification
**File**: `app/Notifications/SubscriptionExpiringNotification.php`

```bash
php artisan make:notification SubscriptionExpiringNotification
```

**Requirements**:
- Send 7 days before subscription expires
- Include:
  - Current plan name
  - Expiry date
  - What happens after expiry (downgrade to free)
  - Renew subscription button
  - Contact support link

**Properties**:
- Subject: "Your {{ $plan->name }} subscription expires soon"
- Via: mail, database
- Queue: yes

### Email Template
**File**: `resources/views/emails/subscription-expiring.blade.php`

**Content**:
```html
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #f59e0b;">Subscription Expiring Soon ⏰</h1>
    
    <p>Hi {{ $user->name }},</p>
    
    <p>Your <strong>{{ $plan->name }}</strong> subscription will expire on <strong>{{ $subscription->ends_at->format('M d, Y') }}</strong>.</p>
    
    <p>After expiry, you'll be downgraded to the Free plan with limited features:</p>
    <ul>
        <li>1 business card (instead of {{ $plan->card_limit }})</li>
        <li>1 custom theme (instead of {{ $plan->theme_limit }})</li>
        <li>No custom CSS</li>
    </ul>
    
    <p>Want to continue enjoying all features? Renew your subscription now!</p>
    
    <a href="{{ url('/subscription/plans') }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0;">
        Renew Subscription
    </a>
    
    <p>Questions? <a href="{{ url('/support') }}">Contact our support team</a>.</p>
    
    <p>Best regards,<br>The TapIt Team</p>
</div>
```

### Trigger Expiry Reminder
**File**: `app/Console/Commands/SendSubscriptionReminders.php`

Create command:
```bash
php artisan make:command SendSubscriptionReminders
```

**Logic**:
```php
// Find subscriptions expiring in 7 days
$subscriptions = UserSubscription::where('status', 'active')
    ->where('ends_at', '<=', now()->addDays(7))
    ->where('ends_at', '>', now())
    ->get();

foreach ($subscriptions as $subscription) {
    $subscription->user->notify(new SubscriptionExpiringNotification($subscription));
}
```

### Schedule Command
**File**: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('subscription:send-reminders')
        ->dailyAt('09:00');
}
```

## Task 7: Subscription Activated Email

### Create Notification
**File**: `app/Notifications/SubscriptionActivatedNotification.php`

```bash
php artisan make:notification SubscriptionActivatedNotification
```

**Requirements**:
- Send when subscription is activated (either new or renewed)
- Include:
  - Welcome message
  - Plan features
  - Expiry date
  - Call to action (create cards)

**Similar to Payment Confirmation** but focused on subscription activation.

## Task 8: Queue Configuration

### Verify Queue Setup
**File**: `.env`

```env
QUEUE_CONNECTION=database
```

### Run Queue Worker
```bash
# For development
php artisan queue:listen

# For production (via Supervisor)
php artisan queue:work --tries=3
```

### Create jobs table (if not exists)
```bash
php artisan queue:table
php artisan migrate
```

## Task 9: Database Notifications (Optional)

### Create notifications table
```bash
php artisan notifications:table
php artisan migrate
```

### Display in-app notifications
**Component**: `resources/js/Components/NotificationBell.vue`

Show unread notifications count in navigation.

**API Endpoint**: 
```php
Route::get('/notifications', function () {
    return auth()->user()->unreadNotifications;
});
```

## Task 10: Testing Email Notifications

### Create Test
**File**: `tests/Feature/NotificationTest.php`

```php
test('welcome email is sent on registration', function () {
    Notification::fake();
    
    $user = User::factory()->create();
    
    Notification::assertSentTo($user, WelcomeNotification::class);
});

test('payment confirmation email sent', function () {
    Notification::fake();
    
    $user = User::factory()->create();
    $payment = Payment::factory()->create(['user_id' => $user->id]);
    
    $paymentService->confirmPaymentAndActivateSubscription($payment);
    
    Notification::assertSentTo($user, PaymentConfirmedNotification::class);
});
```

### Manual Testing with Mailtrap
1. Configure Mailtrap credentials in `.env`
2. Register new user
3. Check Mailtrap inbox for welcome email
4. Verify email formatting and links

## Task 11: Email Templates Best Practices

**Requirements**:
- Mobile responsive
- Plain text fallback for each email
- Consistent branding (colors, logo)
- Clear call-to-action buttons
- Unsubscribe link (if needed)
- Footer with company info

### Create Base Email Layout
**File**: `resources/views/layouts/email.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Responsive email styles */
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 20px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 8px;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 20px; text-align: center; background: #2563eb; border-radius: 8px 8px 0 0;">
                            <img src="{{ asset('logo.png') }}" alt="TapIt" style="height: 40px;">
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            @yield('content')
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; text-align: center; background: #f9fafb; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;">
                            <p>© {{ date('Y') }} TapIt. All rights reserved.</p>
                            <p>
                                <a href="{{ url('/') }}">Visit Website</a> |
                                <a href="{{ url('/support') }}">Support</a> |
                                <a href="{{ url('/privacy') }}">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
```

## Success Criteria

✅ Welcome email sent on registration
✅ Email verification working (already exists)
✅ Password reset working (already exists)
✅ Payment confirmation email sent when payment confirmed
✅ Subscription expiry reminders sent 7 days before
✅ All emails are mobile responsive
✅ All emails have proper branding
✅ Queue system configured and working
✅ Tests pass for all notifications
✅ Mailtrap/email testing verified

## Dependencies

- Mail configuration in `.env`
- Queue system configured
- User, Payment, Subscription models
- PaymentService

## Notes

- Use queues for all email sending (ShouldQueue interface)
- Test emails thoroughly in Mailtrap before production
- Consider adding email templates for:
  - Card published notification
  - Weekly analytics summary
  - New feature announcements
