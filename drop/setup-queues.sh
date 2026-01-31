#!/bin/bash

# QCard Production Queue & Scheduler Setup Script
# Run this script on your production server as root or with sudo

echo "🔄 Setting up QCard Queue Workers and Scheduler..."

# Check if we're running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   echo "❌ This script must be run as root or with sudo"
   exit 1
fi

# Variables - Update these paths for your server
APP_PATH="/var/www/qcard"
SUPERVISOR_CONF="/etc/supervisor/conf.d/qcard-worker.conf"
CRON_USER="www-data"  # or your web server user

# 1. Install Supervisor if not already installed
echo "📦 Installing Supervisor..."
apt-get update
apt-get install -y supervisor

# 2. Copy supervisor configuration
echo "⚙️  Setting up Supervisor configuration..."
cp ${APP_PATH}/supervisor-qcard.conf ${SUPERVISOR_CONF}

# Update the app path in supervisor config if different
sed -i "s|/var/www/qcard|${APP_PATH}|g" ${SUPERVISOR_CONF}

# 3. Reload supervisor configuration
echo "🔄 Reloading Supervisor..."
supervisorctl reread
supervisorctl update
supervisorctl start qcard-worker:*

# 4. Set up cron job for Laravel scheduler
echo "⏰ Setting up Laravel Scheduler cron job..."

# Create cron job for Laravel scheduler
CRON_JOB="* * * * * cd ${APP_PATH} && php artisan schedule:run >> /dev/null 2>&1"

# Add cron job if it doesn't exist
(crontab -u ${CRON_USER} -l 2>/dev/null; echo "${CRON_JOB}") | sort -u | crontab -u ${CRON_USER} -

# 5. Create log rotation for worker logs
echo "📝 Setting up log rotation..."
cat > /etc/logrotate.d/qcard-worker << EOF
${APP_PATH}/storage/logs/worker.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        supervisorctl restart qcard-worker:*
    endscript
}

${APP_PATH}/storage/logs/scheduler.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 0644 www-data www-data
}
EOF

# 6. Create monitoring script
echo "📊 Creating monitoring script..."
cat > ${APP_PATH}/monitor-queues.sh << 'EOF'
#!/bin/bash

APP_PATH="/var/www/qcard"
cd ${APP_PATH}

echo "=== Queue Status Check - $(date) ==="

# Check supervisor status
echo "📊 Supervisor Status:"
supervisorctl status qcard-worker:*

# Check for failed jobs
echo "❌ Failed Jobs:"
php artisan queue:failed --format=table

# Check queue size
echo "📋 Queue Size:"
php artisan queue:monitor default

# Check recent log entries
echo "📝 Recent Worker Logs (last 10 lines):"
tail -10 storage/logs/worker.log 2>/dev/null || echo "No worker logs found"

echo "=================================="
EOF

chmod +x ${APP_PATH}/monitor-queues.sh
chown www-data:www-data ${APP_PATH}/monitor-queues.sh

# 7. Test everything
echo "🧪 Testing setup..."
echo "Supervisor status:"
supervisorctl status qcard-worker:*

echo "Cron jobs for ${CRON_USER}:"
crontab -u ${CRON_USER} -l | grep qcard || crontab -u ${CRON_USER} -l | grep "schedule:run"

echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Monitor worker logs: tail -f ${APP_PATH}/storage/logs/worker.log"
echo "2. Check queue status: ${APP_PATH}/monitor-queues.sh"
echo "3. Test queue: cd ${APP_PATH} && php artisan queue:work --once"
echo "4. Check scheduler: cd ${APP_PATH} && php artisan schedule:list"
echo ""
echo "🔧 Management commands:"
echo "- Restart workers: supervisorctl restart qcard-worker:*"
echo "- Check workers: supervisorctl status qcard-worker:*"
echo "- View failed jobs: cd ${APP_PATH} && php artisan queue:failed"
echo "- Retry failed jobs: cd ${APP_PATH} && php artisan queue:retry all"