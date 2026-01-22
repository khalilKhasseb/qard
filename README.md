# QCard - Digital Business Cards Platform

<p align="center">
  <img src="https://laravel.com/img/logomark.min.svg" width="50" alt="Laravel Logo">
  <span style="font-size: 2em;">+</span>
  <img src="https://vuejs.org/logo.svg" width="50" alt="Vue.js Logo">
</p>

<p align="center">A modern, multilingual digital business cards platform built with Laravel and Vue.js</p>

## ✨ Features

- 🎨 **Modern UI/UX** - Clean, responsive design with multiple themes
- 🌍 **Multilingual Support** - Full support for Arabic, English, and more
- 🤖 **AI Translation** - Automatic translation using advanced AI models
- ⚡ **Real-time Updates** - Live translation progress with SSE
- 💳 **Payment Integration** - Lahza payment gateway support
- 📱 **Mobile Responsive** - Optimized for all device sizes
- 🔒 **Secure** - Built-in CSRF protection and security features
- 🚀 **Performance Optimized** - Cached routes, views, and configuration

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL 8.0+
- **Styling**: Tailwind CSS
- **Translation**: OpenRouter API with Xiaomi AI models
- **Payments**: Lahza Payment Gateway
- **Build Tools**: Vite
- **Queue**: Database-based job queues

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Node.js 18+ and npm
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/qcard.git
   cd qcard
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your .env file**
   - Database credentials
   - Email configuration  
   - Payment gateway keys
   - AI translation API keys

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run dev
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 📋 Production Deployment

For production deployment, follow our comprehensive guides:

- **[Deployment Guide](DEPLOYMENT.md)** - Complete production deployment instructions
- **[Production Checklist](PRODUCTION_CHECKLIST.md)** - Pre/post-deployment checklist
- **[Environment Configuration](.env.production.example)** - Production environment template

### Quick Deploy
```bash
# On your production server
./deploy.sh  # Linux/Mac
# or
deploy.bat   # Windows
```

## 🔧 Configuration

### Key Environment Variables

```env
# Application
APP_NAME="QCard - Digital Business Cards"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_DATABASE=qcard_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# AI Translation
OPENROUTER_API_KEY=your_api_key
PRISM_TRANSLATION_MODEL=xiaomi/mimo-v2-flash:free

# Payment Gateway
LAHZA_PUBLIC_KEY=your_public_key
LAHZA_SECRET_KEY=your_secret_key
```

## 🎯 Features Overview

### Card Management
- Create unlimited digital business cards
- Multiple sections: About, Contact, Services, Gallery, etc.
- Customizable themes and layouts
- Public sharing with unique URLs

### Translation System
- Real-time AI translation using advanced models
- Support for 10+ languages
- Section-level translation control
- Translation history and management

### Payment Integration
- Subscription-based pricing
- Secure payment processing with Lahza
- Automatic subscription management
- Webhook support for real-time updates

### Admin Panel
- Filament-powered admin interface
- User management
- Translation monitoring
- System analytics

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📊 Monitoring

### Health Check Endpoints
- `GET /health` - Application health status
- `GET /version` - Version and environment info

### Key Metrics
- Response times
- Translation success rates
- Payment processing status
- Queue job processing

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Documentation

- [API Documentation](DOCS/API_ENDPOINTS.md)
- [Frontend Build Summary](DOCS/FRONTEND_BUILD_SUMMARY.md)
- [Security Implementation](DOCS/SECURITY_IMPLEMENTATION_SUMMARY.md)
- [Testing Guide](DOCS/TESTING_GUIDE.md)

## 🔒 Security

- CSRF protection enabled
- SQL injection prevention
- XSS protection
- Rate limiting on sensitive endpoints
- Secure file uploads
- Environment-based configuration

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Laravel Team for the amazing framework
- Vue.js Team for the reactive frontend framework
- Filament for the admin panel
- OpenRouter for AI translation services
- Tailwind CSS for the utility-first styling

## 📞 Support

For support, email support@yourdomain.com or create an issue in this repository.

---

**QCard** - Making digital business cards simple, beautiful, and accessible worldwide. 🌍✨

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
