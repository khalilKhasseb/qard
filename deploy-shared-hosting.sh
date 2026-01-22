#!/bin/bash

# QCard Deployment Script for Shared Hosting
# Run this script on your shared hosting account

echo "🚀 Starting QCard Deployment for Shared Hosting..."

# Update app path for your hosting
APP_PATH="/home/yourusername/public_html/qcard"  # Update this path
cd "$APP_PATH" || exit 1

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Please copy .env.production.example to .env and configure it."
    exit 1
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm ci

# Generate application key if not set
echo "🔑 Generating application key..."
php artisan key:generate --force

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Seed the database (if needed)
echo "🌱 Seeding database..."
php artisan db:seed --force

# Build production assets
echo "🏗️  Building production assets..."
npm run build

# Clear and optimize
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set up queue management scripts
echo "🔧 Setting up queue management..."
chmod +x queue-worker-shared.sh
chmod +x check-hosting-capabilities.sh

# Create log directories
mkdir -p storage/logs
touch storage/logs/queue-worker.log

# Check hosting capabilities
echo "🔍 Checking hosting capabilities..."
./check-hosting-capabilities.sh

# Set up cron jobs
echo "📅 Setting up cron jobs..."
echo "Add these lines to your crontab (crontab -e):"
echo ""
cat shared-hosting-crontab.txt
echo ""

# Start queue worker
echo "🔄 Starting queue worker..."
./queue-worker-shared.sh start

echo "✅ Deployment complete!"
echo ""
echo "🔧 Management commands:"
echo "  ./queue-worker-shared.sh status  - Check queue worker status"
echo "  ./queue-worker-shared.sh restart - Restart queue worker"
echo "  php artisan queue:failed        - View failed jobs"
echo "  php artisan queue:process-batch - Process jobs manually"
echo ""
echo "📋 Next steps:"
echo "1. Set up the cron jobs shown above"
echo "2. Test queue processing: php artisan queue:process-batch"
echo "3. Monitor logs: tail -f storage/logs/queue-worker.log"
echo "4. Test translation functionality"