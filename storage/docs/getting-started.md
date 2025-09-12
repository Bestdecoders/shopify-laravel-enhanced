# 🚀 Getting Started with Shopify Laravel Enhanced

> **Welcome to the most comprehensive Shopify app development package!** This guide will walk you through setting up your first Shopify Laravel Enhanced application. Whether you're building FAQ systems, documentation platforms, or pricing modules, this package provides all the essential tools you need to create professional Shopify apps.

Our package streamlines the development process by providing pre-built components, automated job handling, and seamless Shopify integration. You'll be able to focus on your business logic while we handle the infrastructure complexities. From installation hooks to uninstall notifications, everything is designed to work seamlessly with Shopify's ecosystem.

![Shopify Laravel Enhanced Dashboard](https://via.placeholder.com/800x400/4F46E5/FFFFFF?text=Dashboard+Overview)

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Package Features](#-package-features)
- [Installation Process](#-installation-process)
- [First Steps](#-first-steps)
- [Configuration](#-configuration)
- [Resources](#-resources)

## ⚡ Quick Start

Get up and running in under 5 minutes with our streamlined setup process:

### 1. 📦 Package Installation

Install the package via Composer in your Laravel Shopify project. The package automatically registers its service provider and loads all necessary routes for FAQ, documentation, and pricing features. Once installed, you'll have immediate access to all the pre-built controllers and views.

```bash
composer require bestdecoders/shopify-laravel-enhanced
```

### 2. 🔧 Publish Core Assets

Publish the essential components needed for basic functionality. This includes install/uninstall jobs, mail notification classes, services, configuration files, and email templates. These are the minimum requirements for the package to work properly.

```bash
# Publish core essentials (jobs, mail, services, config)
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"
```

### 2.5. 🛡️ Setup Exception Handling (CRITICAL)

**⚠️ MANDATORY STEP** - This prevents "No authenticated user or shop domain" errors:

Add the trait to your `app/Exceptions/Handler.php`:

```php
use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;

class Handler extends ExceptionHandler
{
    use HandlesShopifyExceptions;

    public function register(): void
    {
        $this->mergeShopifyDontReport();
        // ... your existing code
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

📖 **Detailed guide**: [Exception Handling Setup](exception-handling.md)

### 3. 🎨 Add Frontend Features (Optional)

Choose which frontend features you want to include in your application. Each feature can be published separately, giving you complete control over what gets added to your project. This modular approach keeps your application clean and only includes what you actually need.

```bash
# Add core React components and styling
php artisan vendor:publish --tag=shopify-enhanced-core

# Add specific features as needed
php artisan vendor:publish --tag=shopify-enhanced-faq
php artisan vendor:publish --tag=shopify-enhanced-docs
php artisan vendor:publish --tag=shopify-enhanced-pricing
```

### 4. ⚙️ Configuration Setup

Configure your application settings in the published config file. Set up your admin email for notifications, configure grandfather access settings, and customize GraphQL queries according to your needs. All settings are well-documented with sensible defaults.

```bash
# Edit the configuration file
config/shopify-enhanced.php
```

## 🌟 Package Features

Our package comes loaded with enterprise-grade features designed for production Shopify apps:

| Feature | Description | Status |
|---------|-------------|--------|
| 📧 **Email Notifications** | Automated install/uninstall emails | ✅ Included |
| 🔄 **Job Management** | Background job processing | ✅ Included |
| 📝 **FAQ System** | Complete FAQ management | 🔧 Optional |
| 📚 **Documentation** | Built-in docs platform | 🔧 Optional |
| 💰 **Pricing Module** | Subscription management UI | 🔧 Optional |
| 🎨 **React Components** | Pre-built UI components | 🔧 Optional |

### Core Backend Features

The backend infrastructure handles all the heavy lifting for your Shopify app. Our job system automatically processes app installations and sends welcome emails to new users. When users uninstall your app, cleanup jobs run automatically and notification emails are sent to both the user and admin.

We've also included a sophisticated grandfather access system that allows you to grant special permissions to long-time users. The GraphQL service handles all Shopify API interactions with proper error handling and retry logic. All email templates are professionally designed and customizable.

```php
// Example: After Install Job automatically runs
class AfterInstallJob {
    public function handle() {
        // Fetch shop info
        // Send welcome email
        // Notify admin
    }
}
```

### Frontend Components

Our React components are built with [Shopify Polaris](https://polaris.shopify.com/) for consistent design. The sidebar navigation automatically handles authentication and routing. All components are fully responsive and optimized for both desktop and mobile experiences.

> **💡 Pro Tip:** Start with just the core backend features, then add frontend components as your app grows!

## 🏗️ Installation Process

### System Requirements

Ensure your development environment meets these requirements before starting:

- ✅ **Laravel 8.0+** - Modern PHP framework
- ✅ **PHP 8.0+** - Latest PHP version for optimal performance
- ✅ **Shopify CLI** - For theme development and testing
- ✅ **Node.js 16+** - For React component compilation
- ✅ **MySQL/PostgreSQL** - Database for storing app data

### Detailed Setup Steps

#### Step 1: Environment Preparation

Prepare your Laravel application for Shopify development. Install the necessary Shopify packages and configure your environment variables. Make sure your database is properly set up and your web server is configured to handle Shopify webhook requests.

```bash
# Install Shopify Laravel package (if not already installed)
composer require osiset/laravel-shopify

# Set up environment variables
cp .env.example .env
php artisan key:generate
```

#### Step 2: Package Integration

Add our package to your project and configure the service providers. The package will automatically register its routes and load all necessary dependencies. You can verify the installation by checking that the routes are properly loaded.

```bash
# Install our package
composer require bestdecoders/shopify-laravel-enhanced

# Verify routes are loaded
php artisan route:list | grep faq
php artisan route:list | grep docs
```

#### Step 3: Asset Publishing

Publish the assets you need for your specific use case. The default publishing includes only essential backend components. You can always add more features later as your application grows and your needs change.

![Asset Publishing Flow](https://via.placeholder.com/600x300/10B981/FFFFFF?text=Asset+Publishing+Flow)

## 🎯 First Steps

### Understanding the Architecture

Our package follows Laravel best practices with a clear separation of concerns. Controllers handle HTTP requests and return Inertia.js responses for seamless SPA functionality. Jobs process background tasks like sending emails and cleaning up data. Services encapsulate business logic and external API interactions.

The frontend uses React with Shopify Polaris components for a native Shopify admin feel. All components are designed to be reusable and customizable. The routing system integrates seamlessly with Shopify's embedded app requirements, handling authentication tokens and host parameters automatically.

```
package/
├── src/
│   ├── Controllers/     # HTTP request handlers
│   ├── Jobs/           # Background job processing
│   ├── Mail/           # Email notification classes
│   ├── Services/       # Business logic services
│   └── Middleware/     # Custom middleware
├── resources/
│   ├── js/
│   │   ├── Components/ # React UI components
│   │   └── Pages/      # Inertia.js pages
│   └── views/          # Blade templates
└── routes/             # Package routes
```

### Testing Your Installation

Verify that everything is working correctly by testing the core functionality. Check that routes are accessible, jobs can be dispatched, and emails are properly configured. Use Laravel's built-in testing tools to ensure your installation is solid.

```bash
# Test that routes are working
curl http://your-app.test/faq
curl http://your-app.test/docs

# Test job processing
php artisan queue:work

# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function($m) { $m->to('test@example.com')->subject('Test'); });
```

## ⚙️ Configuration

### Core Configuration Options

Customize the package behavior through the configuration file. Set your admin email for receiving installation notifications, configure grandfather access rules, and customize GraphQL queries for your specific needs. All options are documented with examples.

```php
// config/shopify-enhanced.php
return [
    'admin_email' => env('ADMIN_EMAIL', 'admin@yourstore.com'),
    'grandfather_access' => [
        'enabled' => true,
        'days_threshold' => 90,
        'benefits' => ['priority_support', 'beta_features']
    ],
    'queries' => [
        'shop' => 'query { shop { name email } }'
    ]
];
```

### Environment Variables

Set up your environment variables for proper integration with Shopify and your email service. These variables control how your app communicates with Shopify's API and how notifications are sent to users and administrators.

```env
# Shopify Configuration
SHOPIFY_API_KEY=your_api_key
SHOPIFY_API_SECRET=your_api_secret
SHOPIFY_WEBHOOKS_SECRET=your_webhook_secret

# Admin Configuration
ADMIN_EMAIL=admin@yourstore.com

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
```

## 📚 Resources

### 📖 Documentation Links

- [Size Chart Creation Guide](size-chart-creation) - Learn how to create effective size charts
- [Display Customization](display-customization) - Customize appearance and behavior
- [Troubleshooting Guide](troubleshooting) - Common issues and solutions

### 🌐 External Resources

- [Shopify App Development](https://shopify.dev/apps) - Official Shopify documentation
- [Laravel Documentation](https://laravel.com/docs) - Complete Laravel guide
- [Shopify Polaris](https://polaris.shopify.com/) - Design system for Shopify apps
- [Inertia.js Documentation](https://inertiajs.com/) - Modern SPA framework

### 💡 Best Practices

> **Security First:** Always validate webhook signatures and sanitize user input. Never store sensitive data in plain text and regularly update your dependencies for security patches.

> **Performance Matters:** Use queue workers for background jobs, implement caching where appropriate, and optimize database queries. Monitor your app's performance and set up proper logging.

> **User Experience:** Follow Shopify's UX guidelines, provide clear error messages, and ensure your app works well on mobile devices. Test thoroughly before releasing updates.

### 🆘 Getting Help

Need assistance? We're here to help!

- 📧 **Email Support:** [support@bestdecoders.com](mailto:support@bestdecoders.com)
- 💬 **Community Forum:** [Join our Discord](https://discord.gg/bestdecoders)
- 📚 **Knowledge Base:** [FAQ Section](faq)
- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/bestdecoders/shopify-laravel-enhanced/issues)

---

> **🎉 Congratulations!** You've successfully set up Shopify Laravel Enhanced. Ready to build something amazing? Check out our [Size Chart Creation Guide](size-chart-creation) to start building your first feature!