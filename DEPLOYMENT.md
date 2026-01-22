# QCard Production Deployment Guide

## Overview
This guide covers deploying QCard - Digital Business Cards application to a production server.

## Prerequisites
- PHP 8.1 or higher with required extensions
- MySQL/PostgreSQL database
- Node.js 18+ and npm
- Web server (Nginx/Apache)
- SSL certificate
- Domain name

## Deployment Steps

### 1. Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-zip php8.1-curl php8.1-gd php8.1-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Clone Repository
```bash
cd /var/www/
sudo git clone https://github.com/yourusername/qcard.git
sudo chown -R www-data:www-data qcard
cd qcard
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.production.example .env

# Edit environment variables
nano .env
```

**Important**: Update these values in your `.env` file:
- `APP_KEY` - Generate new key for production
- `APP_URL` - Your production domain
- Database credentials
- Email configuration
- Payment gateway keys (production)
- API keys for translation service

### 4. Run Deployment Script
```bash
# Make script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### 5. Web Server Configuration

#### Nginx Configuration
Create `/etc/nginx/sites-available/qcard`:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/qcard/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/qcard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. SSL Certificate
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 7. Queue Worker Setup
Create supervisor configuration `/etc/supervisor/conf.d/qcard-worker.conf`:
```ini
[program:qcard-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/qcard/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/qcard/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start qcard-worker:*
```

### 8. Scheduled Tasks
Add to crontab:
```bash
sudo crontab -e
```

Add this line:
```cron
* * * * * cd /var/www/qcard && php artisan schedule:run >> /dev/null 2>&1
```

## Security Checklist

### Application Security
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure proper CORS origins
- [ ] Use HTTPS only
- [ ] Set secure session settings

### Server Security
- [ ] Configure firewall (ufw/iptables)
- [ ] Disable unused services
- [ ] Regular security updates
- [ ] Configure proper file permissions
- [ ] Set up fail2ban
- [ ] Regular backups

### Database Security
- [ ] Use strong database passwords
- [ ] Restrict database access
- [ ] Regular database backups
- [ ] Enable query logging (if needed)

## Monitoring & Maintenance

### Log Files
- Application logs: `storage/logs/laravel.log`
- Web server logs: `/var/log/nginx/`
- PHP logs: `/var/log/php8.1-fpm.log`
- Queue worker logs: `storage/logs/worker.log`

### Health Checks
- Application status: `https://yourdomain.com/health`
- Database connectivity
- Queue worker status
- Disk space usage
- SSL certificate expiry

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p qcard_production > backup_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf qcard_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/qcard/storage/app/public
```

## Troubleshooting

### Common Issues
1. **Permission errors**: Check file ownership and permissions
2. **Database connection**: Verify database credentials and connectivity
3. **Asset loading issues**: Ensure assets are built for production
4. **Queue not processing**: Check supervisor and worker status
5. **Email not sending**: Verify SMTP configuration

### Debug Mode (Temporary)
If issues occur, temporarily enable debug mode:
```bash
php artisan down
# Set APP_DEBUG=true in .env
php artisan config:clear
# Fix the issue
# Set APP_DEBUG=false
php artisan config:cache
php artisan up
```

## Performance Optimization

### PHP Optimization
- Enable OPcache
- Tune PHP-FPM settings
- Configure proper memory limits

### Database Optimization
- Index optimization
- Query optimization
- Connection pooling

### Caching
- Redis for session and cache storage
- CDN for static assets
- Browser caching headers

## Support
For deployment support, create an issue in the repository or contact the development team.