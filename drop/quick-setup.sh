#!/bin/bash

# Quick Setup for weno@vmi2030188 hosting environment

echo "🚀 Setting up QCard for your hosting environment..."

# Make scripts executable
chmod +x queue-worker-shared.sh
chmod +x deploy-shared-hosting.sh

# Create necessary directories
mkdir -p storage/logs
touch storage/logs/queue-worker.log

# Set up cron jobs
echo "📅 Setting up cron jobs..."
echo "Run this command to add cron jobs:"
echo "crontab -e"
echo ""
echo "Then add these lines:"
echo ""
cat shared-hosting-crontab.txt
echo ""

# Test queue processing
echo "🧪 Testing queue functionality..."

# Check if artisan commands work
if php artisan --version >/dev/null 2>&1; then
    echo "✅ Laravel artisan working"
else
    echo "❌ Laravel artisan not working - check PHP path"
    exit 1
fi

# Test queue command
echo "Testing queue batch processing..."
php artisan queue:process-batch --jobs=1

# Start the queue worker
echo "🔄 Starting queue worker..."
./queue-worker-shared.sh start

# Show status
echo "📊 Current status:"
./queue-worker-shared.sh status

echo ""
echo "✅ Setup complete!"
echo ""
echo "🔧 Next steps:"
echo "1. Set up the cron jobs shown above"
echo "2. Test translation functionality"
echo "3. Monitor with: ./queue-worker-shared.sh status"