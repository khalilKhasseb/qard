# Lahza Payment Gateway Integration

## Overview

This document describes the integration of the Lahza payment gateway into the Qard application with subscription-based access control.

## Features Implemented

### ✅ Core Components

1. **LahzaPaymentGateway Service** (`app/Services/LahzaPaymentGateway.php`)
   - Implements `PaymentGatewayInterface`
   - Initialize transactions with Lahza API
   - Verify payment status via Lahza verify endpoint
   - Handle refunds through Lahza
   - Generate checkout URLs
   - Convert amounts to cents (lowest currency unit)
   - Webhook signature verification

2. **Enhanced PaymentService** (`app/Services/PaymentService.php`)
   - Updated to support Lahza gateway
   - Added `initializeLahzaCheckout()` method
   - Added `processCallback()` for callback handling
   - Added `getCheckoutUrlForPayment()` method
   - Added `verifyWebhookSignature()` method

3. **Configuration File** (`config/payments.php`)
   - Comprehensive payment configuration
   - Lahza-specific settings
   - Security configurations
   - Retry policies
   - Notification settings

4. **Webhook Controller** (`app/Http/Controllers/Webhooks/LahzaWebhookController.php`)
   - Handles Lahza webhook events
   - Verifies webhook signatures
   - Processes `charge.success`, `charge.failed`, `charge.refunded` events
   - Auto-activates subscriptions on successful payment
   - Creates payments from webhooks if not found

5. **Updated PaymentController** (`app/Http/Controllers/PaymentController.php`)
   - `initialize()` - Initialize Lahza payment
   - `callback()` - Handle payment callback

6. **Frontend Components**
   - `Checkout.vue` - Enhanced with Lahza popup integration
   - `Callback.vue` - New component for payment callback results

7. **Filament Payment Resource** (`app/Filament/Resources/PaymentResource.php`)
   - Enhanced admin interface for payment management
   - Manual payment verification with Lahza
   - Refund functionality
   - Metadata viewing
   - Updated filters and colors for Lahza payments

8. **Database Migration**
   - Updated `payments` table to include `'lahza'` in `payment_method` enum

## Payment Flow

### 1. User Initiates Payment

```
User selects plan → POST /payments/{plan}/initialize
    ↓
Backend creates pending payment record
    ↓
Backend calls Lahza API with payment details
    ↓
Lahza returns checkout URL
    ↓
Frontend opens popup with checkout URL
    ↓
User completes payment in Lahza popup
    ↓
Lahza redirects to callback URL
```

### 2. Payment Verification

**Callback Flow:**
```
Lahza redirects to /payments/callback?reference=TXN-XXX
    ↓
Backend verifies payment via Lahza API
    ↓
If successful:
    - Mark payment as completed
    - Create/activate subscription
    - Update user subscription status
    - Send notifications
```

**Webhook Flow:**
```
Lahza sends webhook to /webhooks/lahza
    ↓
Backend verifies webhook signature
    ↓
Processes event (charge.success, etc.)
    ↓
Auto-activates subscription if needed
```

### 3. Subscription Access Control

**Existing Logic (Already Working):**
- `User::canCreateCard()` checks subscription status
- `BusinessCardPolicy::create()` enforces access
- `CardService::createCard()` validates limits

**Access Rules:**
- **Free Tier**: 1 card, 1 theme
- **Pro Tier**: 5 cards, 10 themes, custom CSS, analytics, NFC
- **Business Tier**: 20 cards, 50 themes, custom CSS, analytics, NFC, custom domain

## Configuration

### Environment Variables

Add to `.env` file:

```env
# Payment Gateway
PAYMENT_GATEWAY=lahza

# Lahza Credentials
LAHZA_PUBLIC_KEY=your_public_key_here
LAHZA_SECRET_KEY=your_secret_key_here
LAHZA_WEBHOOK_SECRET=your_webhook_secret_here

# Test/Live Mode
LAHZA_TEST_MODE=true

# API Configuration
LAHZA_BASE_URL=https://api.lahza.io
LAHZA_CURRENCY=USD
LAHZA_CHANNELS=card
LAHZA_VERIFY_WEBHOOKS=true
LAHZA_TIMEOUT=30

# Retry Configuration
LAHZA_RETRY_ATTEMPTS=3
LAHZA_RETRY_DELAY=5

# Security
MAX_PAYMENT_AMOUNT=10000.00
MIN_PAYMENT_AMOUNT=0.01
```

### Webhook Configuration

In your Lahza dashboard:
1. Set webhook URL to: `https://yourdomain.com/webhooks/lahza`
2. Add webhook secret (match `LAHZA_WEBHOOK_SECRET` in .env)
3. Enable events: `charge.success`, `charge.failed`, `charge.refunded`

## Testing

### Integration Test

Run the integration test command:

```bash
php artisan lahza:test
```

This will:
- Check configuration
- Test gateway instantiation
- Test payment initialization
- Test callback URL generation
- Test webhook signature verification

### Manual Testing

1. **Test Payment Flow:**
   - Log in as a user
   - Go to /payments
   - Select a plan
   - Click "Pay with Card"
   - Complete payment in popup
   - Verify callback redirect shows success

2. **Test Webhook:**
   - Send test webhook from Lahza dashboard
   - Check logs: `tail -f storage/logs/laravel.log`
   - Verify payment and subscription created

3. **Test Access Control:**
   - With active subscription: should be able to create cards
   - Without subscription: should see "Upgrade Required" message

### Unit Tests

Run payment service tests:

```bash
php artisan test --filter=PaymentService
```

## Security Features

1. **Webhook Signature Verification**
   - All webhooks are signed with HMAC-SHA512
   - Signatures are verified before processing
   - Invalid signatures are rejected (401)

2. **Secret Key Protection**
   - Secret keys never exposed to frontend
   - All Lahza API calls go through backend
   - Keys stored in environment variables

3. **Duplicate Payment Prevention**
   - Unique transaction IDs generated for each payment
   - 30-minute window for reusing pending payments
   - Prevents double-charging on page refresh

4. **Amount Validation**
   - Minimum/Maximum payment limits
   - Amounts converted to cents for Lahza API

## Error Handling

### Common Errors

1. **Payment Initialization Failed**
   - Check Lahza API credentials
   - Verify account is active in Lahza dashboard
   - Check amount is within limits

2. **Callback Not Processing**
   - Verify transaction reference exists in database
   - Check Lahza API is responding
   - Review logs for errors

3. **Webhook Signature Invalid**
   - Verify `LAHZA_WEBHOOK_SECRET` matches dashboard
   - Check webhook wasn't tampered with

### Logging

All payment operations are logged:
- Payment initialization: `info`
- Payment completion: `info`
- Webhook received: `info`
- Errors: `error`
- Signature failures: `warning`

Logs available in: `storage/logs/laravel.log`

## Admin Features

### Filament Payment Resource

URL: `/admin/payments`

**Features:**
- View all payments with filters
- Manual payment verification (for Lahza payments)
- Refund payments (for completed payments)
- View payment metadata
- Search by transaction ID, user, or amount

**Actions:**
- **Verify** - Verify pending Lahza payment and activate subscription
- **Refund** - Refund payment and cancel subscription
- **View** - View payment details and metadata
- **Edit** - Edit payment notes and metadata

## Deployment Checklist

### Pre-Production

- [ ] Set `LAHZA_TEST_MODE=false` in production
- [ ] Use live credentials from Lahza dashboard
- [ ] Update webhook URL to production domain
- [ ] Set webhook secret in production .env
- [ ] Test in staging environment first
- [ ] Set `APP_DEBUG=false`
- [ ] Configure SSL/HTTPS for webhooks
- [ ] Set up payment monitoring (logs, alerts)
- [ ] Test full payment flow end-to-end
- [ ] Verify webhook delivery and processing

### Post-Production

- [ ] Monitor logs for payment errors
- [ ] Set up backup webhook handler
- [ ] Configure payment alerts (large amounts)
- [ ] Test refund functionality
- [ ] Verify subscription expiration job

## Troubleshooting

### Common Issues

**Issue: "Failed to initialize payment"**
- Check Lahza API credentials are correct
- Verify account has sufficient balance
- Check `LAHZA_TEST_MODE` setting

**Issue: Webhooks not received**
- Verify webhook URL is publicly accessible
- Check SSL certificate is valid
- Test webhook endpoint manually

**Issue: Subscription not activating**
- Check webhook is processing
- Verify callback URL is correct
- Check payment status in database

**Issue: Amount calculation wrong**
- Ensure amounts are in dollars (not cents)
- Lahza converts automatically
- Check currency configuration

## API Reference

### Initialize Payment

```php
POST /payments/{plan}/initialize
```

Response:
```json
{
    "message": "Payment initialized successfully",
    "payment": {
        "id": 123,
        "transaction_id": "TXN-XXX",
        "amount": 10.00,
        "status": "pending"
    },
    "checkout_url": "https://checkout.lahza.io/pay/...",
    "reference": "TXN-XXX"
}
```

### Callback Endpoint

```php
GET /payments/callback?reference=TXN-XXX
```

Returns Inertia page with:
- `success`: boolean
- `subscription`: subscription details (if success)
- `message`: status message

### Webhook Endpoint

```php
POST /webhooks/lahza
```

Headers:
- `X-Lahza-Signature`: HMAC-SHA512 signature

Request body:
```json
{
    "event": "charge.success",
    "data": {
        "reference": "TXN-XXX",
        "status": "success",
        "amount": 1000,
        "currency": "USD"
    }
}
```

## Maintenance

### Database Cleanup

Old pending payments can be cleaned up:

```php
// Run this as a scheduled job (daily)
Payment::where('status', 'pending')
    ->where('created_at', '<', now()->subDays(1))
    ->delete();
```

### Monitoring

Monitor these metrics:
- Payment success rate
- Webhook processing time
- Failed payment rate
- Refund rate

## Support

For issues:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Run integration test: `php artisan lahza:test`
3. Check Lahza dashboard for payment status
4. Contact support if issue persists

## References

- [Lahza Documentation](https://docs.lahza.io)
- [Lahza Dashboard](https://dashboard.lahza.io)
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
