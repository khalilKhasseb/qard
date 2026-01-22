#!/bin/bash

# QCard Production Deployment Script
# Run this script on your production server after cloning the repository

echo "🚀 Starting QCard Production Deployment..."

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

# Clear and cache configuration
echo "🔧 Optimizing configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Seed the database (if needed)
echo "🌱 Seeding database..."
php artisan db:seed --force

# Build production assets
echo "🏗️  Building production assets..."
npm run build

# Set proper permissions
echo "🔒 Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache everything for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker setup (optional - you may want to use supervisor instead)
echo "⚙️  Setting up queue worker..."
# php artisan queue:work --daemon

echo "✅ Deployment complete!"
echo ""
echo "🔧 Next steps:"
echo "1. Configure your web server to point to the 'public' directory"
echo "2. Set up SSL certificate"
echo "3. Configure queue worker with supervisor or systemd"
echo "4. Set up automated backups"
echo "5. Configure monitoring and logging"
echo ""
echo "🌐 Your application should now be ready at your domain!"