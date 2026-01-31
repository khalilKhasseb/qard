@echo off
REM QCard Production Deployment Script for Windows
REM Run this script on your production server after cloning the repository

echo 🚀 Starting QCard Production Deployment...

REM Check if .env exists
if not exist .env (
    echo ❌ .env file not found. Please copy .env.production.example to .env and configure it.
    exit /b 1
)

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
composer install --optimize-autoloader --no-dev

REM Install Node.js dependencies
echo 📦 Installing Node.js dependencies...
npm ci

REM Generate application key if not set
echo 🔑 Generating application key...
php artisan key:generate --force

REM Clear and cache configuration
echo 🔧 Optimizing configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM Run database migrations
echo 🗄️  Running database migrations...
php artisan migrate --force

REM Seed the database
echo 🌱 Seeding database...
php artisan db:seed --force

REM Build production assets
echo 🏗️  Building production assets...
npm run build

REM Clear all caches
echo 🧹 Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Cache everything for production
echo ⚡ Caching for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ✅ Deployment complete!
echo.
echo 🔧 Next steps:
echo 1. Configure your web server to point to the 'public' directory
echo 2. Set up SSL certificate
echo 3. Configure queue worker service
echo 4. Set up automated backups
echo 5. Configure monitoring and logging
echo.
echo 🌐 Your application should now be ready at your domain!