# 📦 Installation Guide

> **Complete setup guide for Shopify Laravel Enhanced package.** This guide covers everything from initial installation to final configuration.

## 🎯 Prerequisites

Before installing, ensure you have:

- ✅ **Laravel 8+** with Shopify app setup
- ✅ **kyon147/laravel-shopify** package installed and configured
- ✅ **PHP 8.0+** with required extensions
- ✅ **Node.js & NPM** for frontend assets (if using React components)
- ✅ **Database connection** configured

## 🚀 Step-by-Step Installation

### 1. Install via Composer

```bash
composer require bestdecoders/shopify-laravel-enhanced
```

**What this does:**
- Installs the package and its dependencies
- Automatically registers the service provider
- Makes all package features available

### 2. Publish Essential Components

Choose your publishing strategy based on your needs:

#### Option A: Publish All Defaults (Recommended)
```bash
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"
```

**Publishes:**
- Install/Uninstall jobs
- Mail notification classes
- Required services
- Configuration files
- Email templates
- Console commands

#### Option B: Selective Publishing
```bash
# Core frontend components
php artisan vendor:publish --tag=shopify-enhanced-core

# Essential backend functionality
php artisan vendor:publish --tag=default
```

### 3. Configure Exception Handling (CRITICAL)

**⚠️ This step is mandatory** to prevent "No authenticated user or shop domain" errors.

See our [Exception Handling Guide](exception-handling.md) for detailed setup instructions.

**Quick Setup:**
```php
// In app/Exceptions/Handler.php
use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;

class Handler extends ExceptionHandler
{
    use HandlesShopifyExceptions;

    public function register(): void
    {
        $this->mergeShopifyDontReport();
        // ... existing code
    }

    public function render($request, Throwable $exception)
    {
        $shopifyResponse = $this->renderShopifyException($request, $exception);
        if ($shopifyResponse !== null) {
            return $shopifyResponse;
        }
        return parent::render($request, $exception);
    }
}
```

### 4. Run Migrations

```bash
php artisan migrate
```

**Creates tables for:**
- Subscription management (if using billing features)
- Coupon codes
- User preferences

### 5. Configure Environment

Add these variables to your `.env` file:

```env
# Admin notifications
ADMIN_EMAIL=admin@yourapp.com

# Billing settings (if using subscription features)
SHOPIFY_BILLING_TEST=true
SUBSCRIPTION_MONTHLY_PRICE=39.99

# Webhook security
SHOPIFY_WEBHOOK_SECRET=your-webhook-secret
VALIDATE_SHOPIFY_WEBHOOKS=true

# GDPR compliance
GDPR_DATA_RETENTION_DAYS=30
GDPR_AUTO_CLEANUP=true
GDPR_NOTIFICATION_EMAIL=gdpr@yourapp.com
```

## 🎨 Optional Feature Installation

### FAQ System

```bash
php artisan vendor:publish --tag=shopify-enhanced-faq
```

**Adds:**
- FAQ React component
- FAQ data structure
- Search functionality

### Documentation System

```bash
php artisan vendor:publish --tag=shopify-enhanced-docs
```

**Adds:**
- Documentation viewer
- Markdown rendering
- Search capabilities

### Pricing Module

```bash
php artisan vendor:publish --tag=shopify-enhanced-pricing
```

**Adds:**
- Pricing page components
- Subscription management
- Billing integration

### Admin Dashboard

```bash
composer require bestdecoders/shopify-admin-dashboard
php artisan vendor:publish --tag=admin-dashboard
```

**Adds:**
- Admin-only routes and views
- User management
- Analytics dashboard

## ⚙️ Post-Installation Configuration

### 1. Update App Routes

The package automatically adds routes. Check your route list:

```bash
php artisan route:list | grep shopify
```

**Available routes:**
- `/faq` - FAQ system
- `/docs` - Documentation
- `/pricing` - Pricing plans
- `/webhooks/*` - GDPR compliance webhooks

### 2. Configure Shopify App Settings

Ensure your Shopify app configuration includes:

```php
// config/shopify-app.php (or similar)
'webhook_jobs' => [
    'app_uninstalled' => \App\Jobs\AppUninstalledJob::class,
    // ... other webhooks
],
```

### 3. Frontend Build (if using React components)

```bash
npm install
npm run build
```

### 4. Test Installation

```bash
# Test basic functionality
php artisan shopify-enhanced:test

# Check webhook handling
php artisan webhook:test
```

## 🔍 Verification Checklist

After installation, verify:

- ✅ **Exception handling** - Visit `/install` directly (should show friendly error)
- ✅ **Routes available** - FAQ, docs, pricing routes work
- ✅ **Webhooks registered** - GDPR webhooks respond properly
- ✅ **Mail templates** - Thanks/uninstall emails render correctly
- ✅ **Jobs queued** - Install/uninstall jobs run successfully

## 📁 File Structure

After installation, you should have:

```
app/
├── Console/Commands/
│   ├── GrantGrandfatherAccessCommand.php
│   └── RevokeExpiredGrandfatheredAccessCommand.php
├── Exceptions/
│   └── Handler.php (modified with trait)
├── Jobs/
│   └── AppUninstalledJob.php
├── Mail/
│   ├── ThanksMail.php
│   └── UserUninstallNotification.php
└── Services/
    └── CustomWebhookHandler.php

config/
└── shopify-enhanced.php

resources/
├── js/
│   ├── components/
│   ├── Pages/
│   └── app.jsx
├── css/
│   └── app.css
└── views/
    ├── emails/
    └── app.blade.php
```

## 🚨 Common Installation Issues

### 1. Service Provider Not Found
**Problem:** Package not auto-discovered
**Solution:** Add to `config/app.php` providers array manually:
```php
'providers' => [
    // ...
    Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider::class,
],
```

### 2. Class Not Found Errors
**Problem:** Autoloader not updated
**Solution:** 
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### 3. Migration Errors
**Problem:** Table already exists
**Solution:** Check existing migrations and run selectively:
```bash
php artisan migrate:status
php artisan migrate --path=/database/migrations/specific_file.php
```

### 4. Asset Publishing Conflicts
**Problem:** Files already exist
**Solution:** Use force flag or backup existing files:
```bash
php artisan vendor:publish --force --tag=shopify-enhanced-core
```

## 🔄 Updating the Package

```bash
# Update to latest version
composer update bestdecoders/shopify-laravel-enhanced

# Re-publish updated assets (with backup)
php artisan vendor:publish --force --tag=shopify-enhanced-core

# Run any new migrations
php artisan migrate
```

## 📚 Next Steps

1. **[Exception Handling Setup](exception-handling.md)** - Critical for proper error handling
2. **[Getting Started Guide](getting-started.md)** - Build your first features
3. **[Webhook Implementation](webhook-implementation.md)** - Set up GDPR compliance
4. **[Troubleshooting](troubleshooting.md)** - Common issues and solutions

## 💬 Need Help?

- 📖 **Documentation** - Check our comprehensive guides
- 🐛 **Issues** - Report bugs on GitHub
- 💌 **Support** - Contact our support team
- 💡 **Feature Requests** - Suggest improvements

The package is designed to be developer-friendly with comprehensive documentation and helpful error messages. Most installation issues can be resolved by following this guide carefully.