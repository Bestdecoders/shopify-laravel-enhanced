# ⚡ Quick Reference

> **Instant access to the most important commands, configurations, and code snippets.**

## 🚀 Installation Commands

```bash
# Install package
composer require bestdecoders/shopify-laravel-enhanced

# Publish essentials
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"

# Publish specific features
php artisan vendor:publish --tag=shopify-enhanced-core      # Frontend components
php artisan vendor:publish --tag=shopify-enhanced-faq       # FAQ system  
php artisan vendor:publish --tag=shopify-enhanced-docs      # Documentation
php artisan vendor:publish --tag=shopify-enhanced-pricing   # Pricing module
```

## 🛡️ Exception Handling (Critical Setup)

**Add to `app/Exceptions/Handler.php`:**

```php
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

## 🌐 Environment Variables

```env
# Core Settings
ADMIN_EMAIL=admin@yourapp.com

# Billing
SHOPIFY_BILLING_TEST=true
SUBSCRIPTION_MONTHLY_PRICE=39.99

# Webhooks  
SHOPIFY_WEBHOOK_SECRET=your-webhook-secret
VALIDATE_SHOPIFY_WEBHOOKS=true

# GDPR
GDPR_DATA_RETENTION_DAYS=30
GDPR_AUTO_CLEANUP=true
GDPR_NOTIFICATION_EMAIL=gdpr@yourapp.com
```

## 📡 Available Routes

```php
# Package routes (automatically registered)
GET  /faq                    # FAQ system
GET  /docs                   # Documentation 
GET  /docs/{slug}           # Specific document
GET  /pricing               # Pricing plans
POST /pricing/subscription  # Create subscription

# GDPR Webhooks (mandatory)
POST /webhooks/customers/data_request
POST /webhooks/customers/redact  
POST /webhooks/shop/redact
```

## 🔧 Configuration Snippets

### Basic Configuration (`config/shopify-enhanced.php`)

```php
return [
    'admin_email' => env('ADMIN_EMAIL', 'admin@yourapp.com'),
    'user_model' => \App\Models\User::class,
    'auto_register_install_job' => true,
    
    'billing_overrides' => [
        'test_mode' => env('SHOPIFY_BILLING_TEST', true),
        'pricing' => [
            'monthly' => [
                'price' => env('SUBSCRIPTION_MONTHLY_PRICE', 39.99),
                'trial_days' => 21,
            ],
        ],
    ],
];
```

### Custom Webhook Handler

```php
// app/Services/CustomWebhookHandler.php
class CustomWebhookHandler extends WebhookHandlerService
{
    public function handleCustomerDataRequest($shop, $customerId, $payload)
    {
        // Custom logic
        parent::handleCustomerDataRequest($shop, $customerId, $payload);
    }
}
```

## 📧 Email Templates

### Thanks Email Data Structure

```php
// Available in email templates
$shop = [
    'name' => 'Shop Name',
    'email' => 'shop@example.com', 
    'contactEmail' => 'contact@example.com',
    'currencyCode' => 'USD',
    'plan' => ['displayName' => 'Basic Shopify'],
];
```

### Custom Email Template

```blade
{{-- resources/views/emails/custom.blade.php --}}
@component('mail::message')
# Hello {{ $shop['name'] ?? 'there' }}!

Your shop details:
- Email: {{ $shop['email'] }}
- Plan: {{ $shop['plan']['displayName'] }}
- Currency: {{ $shop['currencyCode'] }}

@component('mail::button', ['url' => config('app.url')])
Visit Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

## 🎛️ Feature Toggles

```php
// Enable/disable features
'feature_overrides' => [
    'enabled' => [
        'usage_billing' => true,
        'plan_upgrades' => true, 
        'analytics_dashboard' => false,
        'faq_system' => true,
        'documentation' => true,
        'pricing_page' => true,
    ],
],
```

## 🔍 Debugging Commands

```bash
# Check routes
php artisan route:list | grep shopify

# Check published assets
ls -la app/Jobs/
ls -la app/Mail/
ls -la app/Services/

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check migrations
php artisan migrate:status

# Test webhooks  
curl -X POST localhost:8000/webhooks/customers/data_request \
  -H "Content-Type: application/json" \
  -d '{"shop_domain":"test.myshopify.com","customer_id":123}'
```

## 🚨 Common Issues & Quick Fixes

### "No authenticated user or shop domain"
```php
// ✅ Fix: Add exception handling trait (see above)
```

### Class not found errors
```bash
composer dump-autoload
php artisan config:clear
```

### Webhook validation failing
```env
# ✅ Fix: Set correct webhook secret
SHOPIFY_WEBHOOK_SECRET=your-actual-webhook-secret
```

### Routes not working
```bash
# ✅ Fix: Clear route cache
php artisan route:clear
php artisan route:cache
```

### Email templates not found
```bash
# ✅ Fix: Publish email templates
php artisan vendor:publish --tag=shopify-enhanced-emails --force
```

## 📚 Documentation Links

- **[Installation Guide](installation.md)** - Complete setup instructions
- **[Exception Handling](exception-handling.md)** - Mandatory error handling setup  
- **[Configuration](configuration.md)** - Full configuration options
- **[Webhook Implementation](webhook-implementation.md)** - GDPR webhook setup
- **[Troubleshooting](troubleshooting.md)** - Common issues and solutions

## 🆘 Emergency Fixes

### Restore Default Handler
If exception handling breaks your app:

```php
// Temporarily disable trait in Handler.php
class Handler extends ExceptionHandler
{
    // Comment out: use HandlesShopifyExceptions;
    
    public function register(): void
    {
        // Comment out: $this->mergeShopifyDontReport();
        // ... rest of your code
    }
    
    public function render($request, Throwable $exception) 
    {
        // Comment out Shopify handling
        return parent::render($request, $exception);
    }
}
```

### Reset Package
```bash
# Remove published files
rm -rf app/Jobs/AppUninstalledJob.php
rm -rf app/Mail/*
rm -rf app/Services/CustomWebhookHandler.php
rm -rf config/shopify-enhanced.php

# Reinstall
composer remove bestdecoders/shopify-laravel-enhanced
composer require bestdecoders/shopify-laravel-enhanced
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"
```

## 💡 Pro Tips

- **Always backup** your Handler.php before adding the trait
- **Test exception handling** by visiting `/install` directly
- **Use feature toggles** to enable features gradually  
- **Monitor logs** for webhook processing errors
- **Set up proper queues** for background job processing
- **Use caching** for GraphQL queries in production

This quick reference covers 90% of common tasks. For detailed information, check the full documentation guides.