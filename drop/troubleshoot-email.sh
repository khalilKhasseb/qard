#!/bin/bash

# Email Troubleshooting Script for QCard
echo "🔧 Troubleshooting Email Issues..."

# Check failed jobs
echo "❌ Checking failed email jobs..."
php artisan queue:failed | grep -E "(WelcomeEmail|VerifyEmail)"

# Test email configuration
echo "📧 Testing email configuration..."
php artisan email:test --to=admin@example.com

# Check Laravel logs for email errors
echo "📝 Recent email errors in logs:"
if [ -f "storage/logs/laravel.log" ]; then
    grep -i "mail\|smtp\|email" storage/logs/laravel.log | tail -10
else
    echo "No laravel.log file found"
fi

# Show email queue jobs
echo "📊 Current email jobs in queue:"
php artisan queue:monitor default | grep -E "(Email|Notification)"

# Suggest solutions
echo ""
echo "🔧 Common Solutions:"
echo "1. Check SMTP credentials in .env file"
echo "2. Verify firewall allows SMTP port (587/465/25)"
echo "3. Check if hosting provider blocks SMTP"
echo "4. Consider using hosting provider's SMTP service"
echo "5. Test with mail driver instead of smtp for debugging"
echo ""
echo "🧪 Test commands:"
echo "  php artisan email:test --to=your-email@domain.com"
echo "  php artisan queue:retry all  # Retry failed jobs"
echo "  php artisan queue:failed     # View failed jobs details"