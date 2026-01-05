# Email Notifications Testing Guide

## Quick Start

### 1. Start the Queue Worker
Open a terminal and run:
```bash
php artisan queue:work
```
Keep this running in the background to process notifications.

### 2. Test All Notifications
In another terminal, run:
```bash
php artisan notifications:test admin@tapit.com
```
Replace `admin@tapit.com` with any existing user email.

### 3. Check the Emails
View the emails in:
```bash
storage/logs/laravel.log
```

## Individual Notification Tests

### Test Welcome Email
```php
// In tinker or a test script
$user = User::find(1);
$user->notify(new \App\Notifications\WelcomeEmail());
```

### Test Email Verification
```php
$user = User::find(1);
$user->notify(new \App\Notifications\VerifyEmail());
```

### Test Password Reset
```php
$user = User::find(1);
$user->notify(new \App\Notifications\ResetPassword('test-token-12345'));
```

### Test Payment Confirmed
```php
$payment = Payment::find(1); // Get a real payment
$user = $payment->user;
$user->notify(new \App\Notifications\PaymentConfirmed($payment));
```

### Test Subscription Expiring
```php
$subscription = UserSubscription::find(1); // Get a real subscription
$user = $subscription->user;
$user->notify(new \App\Notifications\SubscriptionExpiring($subscription, 7));
```

### Test Subscription Activated
```php
$subscription = UserSubscription::find(1); // Get a real subscription
$user = $subscription->user;
$user->notify(new \App\Notifications\SubscriptionActivated($subscription));
```

## Automatic Triggers

### 1. Welcome Email
Automatically sent when a user registers:
```
Visit: http://qard.test/register
```

### 2. Email Verification
Automatically sent when Laravel triggers email verification.

### 3. Password Reset
Automatically sent when user requests password reset:
```
Visit: http://qard.test/forgot-password
```

### 4. Payment Confirmed
Automatically sent when admin confirms a payment in Filament:
1. Go to Admin Panel
2. Navigate to Payments
3. Click "Confirm" on a pending payment

### 5. Subscription Expiring
Automatically sent daily by scheduler:
```bash
# Manual trigger
php artisan subscriptions:check-expiring

# Runs daily automatically via scheduler
php artisan schedule:run
```

### 6. Subscription Activated
Automatically sent when a payment is confirmed (same trigger as Payment Confirmed).

## Troubleshooting

### Emails not sending?
1. Check queue worker is running:
   ```bash
   php artisan queue:work
   ```

2. Check for failed jobs:
   ```bash
   php artisan queue:failed
   ```

3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Queue not processing?
1. Clear failed jobs:
   ```bash
   php artisan queue:flush
   ```

2. Restart queue worker:
   ```bash
   # Stop current worker (Ctrl+C)
   php artisan queue:restart
   php artisan queue:work
   ```

### Need to test without queue?
Change in `.env`:
```env
QUEUE_CONNECTION=sync
```
This will send notifications immediately without queuing.

## Production Setup

### 1. Configure Real Mail Service
Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Setup Queue Workers with Supervisor
Create `/etc/supervisor/conf.d/qard-worker.conf`:
```ini
[program:qard-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/qard/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/qard/storage/logs/worker.log
stopwaitsecs=3600
```

### 3. Setup Cron for Scheduler
Add to crontab:
```bash
* * * * * cd /path/to/qard && php artisan schedule:run >> /dev/null 2>&1
```

## Email Preview

All emails use Laravel's professional MailMessage styling:
- Responsive design
- Clean, modern layout
- Action buttons
- Proper branding
- Mobile-friendly

Example email structure:
```
[Header: App Name]

Hello John Doe!

[Email content lines]

[Action Button]

[Footer text]

Regards,
Your App Name

[Subcopy with alternative button URL]
[Footer: Copyright]
```
