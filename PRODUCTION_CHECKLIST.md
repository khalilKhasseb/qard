# Production Deployment Checklist

## Pre-Deployment Checklist

### ✅ Environment Configuration
- [ ] Copy `.env.production.example` to `.env` on production server
- [ ] Update `APP_NAME` with your application name
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` with `php artisan key:generate`
- [ ] Update `APP_URL` with your production domain
- [ ] Configure database credentials
- [ ] Set up email configuration
- [ ] Configure payment gateway (Lahza) with production keys
- [ ] Set OpenRouter API key for translations
- [ ] Update CORS and Sanctum domains

### ✅ Security Configuration
- [ ] SSL certificate installed and configured
- [ ] HTTPS redirect enabled
- [ ] Security headers configured
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] Storage and cache directories writable by web server
- [ ] Sensitive files (.env, composer.json) not publicly accessible

### ✅ Database Setup
- [ ] Database created
- [ ] Database user with appropriate permissions
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan db:seed --force` (if needed)

### ✅ Dependencies & Assets
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm ci`
- [ ] Run `npm run build`
- [ ] Verify all assets are built correctly

### ✅ Application Optimization
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Optimize autoloader with `composer dump-autoload --optimize`

### ✅ Queue & Workers
- [ ] Configure queue worker (supervisor recommended)
- [ ] Test queue processing
- [ ] Set up cron job for `php artisan schedule:run`

### ✅ Monitoring & Logging
- [ ] Configure log rotation
- [ ] Set up application monitoring
- [ ] Test health check endpoint: `/health`
- [ ] Monitor disk space
- [ ] Set up backup strategy

## Post-Deployment Verification

### ✅ Functionality Tests
- [ ] Homepage loads correctly
- [ ] User registration/login works
- [ ] Card creation functionality
- [ ] Card editing and sections
- [ ] Translation feature works
- [ ] Payment processing (if applicable)
- [ ] Email notifications
- [ ] Public card viewing

### ✅ Performance Tests
- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Assets loading from CDN (if configured)
- [ ] Cache working correctly

### ✅ Security Tests
- [ ] HTTPS working correctly
- [ ] No debug information exposed
- [ ] File upload restrictions working
- [ ] Rate limiting functional
- [ ] CSRF protection active

## Health Check Endpoints

- **Application Health**: `GET /health`
- **Version Info**: `GET /version`

## Common Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Recache Everything
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Deployment Script
```bash
# Run the deployment script
./deploy.sh  # Linux/Mac
deploy.bat   # Windows
```

### Queue Worker Management
```bash
# Check supervisor status
sudo supervisorctl status

# Restart queue workers
sudo supervisorctl restart qcard-worker:*

# View worker logs
tail -f /var/www/qcard/storage/logs/worker.log
```

## Troubleshooting

### Common Issues & Solutions

1. **500 Server Error**
   - Check Laravel logs: `tail -f storage/logs/laravel.log`
   - Verify file permissions
   - Check .env configuration

2. **Assets Not Loading**
   - Verify build process completed
   - Check web server configuration
   - Verify asset URLs in HTML

3. **Database Connection Issues**
   - Verify database credentials in .env
   - Check database server status
   - Test connection manually

4. **Queue Not Processing**
   - Check supervisor configuration
   - Verify worker is running
   - Check for failed jobs: `php artisan queue:failed`

5. **Email Not Sending**
   - Verify SMTP configuration
   - Check mail logs
   - Test with `php artisan tinker` and Mail facade

## Backup & Recovery

### Database Backup
```bash
mysqldump -u username -p qcard_production > backup_$(date +%Y%m%d_%H%M%S).sql
```

### File Backup
```bash
tar -czf qcard_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/qcard/storage/app/public
```

### Automated Backup Script
Create a cron job for regular backups:
```bash
0 2 * * * /path/to/backup_script.sh
```

## Monitoring

### Log Files to Monitor
- `storage/logs/laravel.log` - Application logs
- `/var/log/nginx/error.log` - Web server errors
- `/var/log/php8.1-fpm.log` - PHP errors
- `storage/logs/worker.log` - Queue worker logs

### Metrics to Track
- Response times
- Error rates
- Database query performance
- Queue job success/failure rates
- SSL certificate expiry
- Disk space usage
- Memory usage

## Support

For deployment issues or questions, refer to:
- `DEPLOYMENT.md` for detailed deployment instructions
- GitHub repository issues
- Application logs for debugging information