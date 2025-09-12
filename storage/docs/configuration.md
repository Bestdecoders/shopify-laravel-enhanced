# ⚙️ Configuration Guide

> **Complete configuration reference for Shopify Laravel Enhanced.** Learn how to customize every aspect of the package to match your application's needs.

## 📋 Table of Contents

- [Package Configuration](#-package-configuration)
- [Billing Settings](#-billing-settings)
- [Webhook Configuration](#-webhook-configuration)
- [Email Templates](#-email-templates)
- [Feature Toggles](#-feature-toggles)
- [Environment Variables](#-environment-variables)
- [Advanced Customization](#-advanced-customization)

## 📄 Package Configuration

The main configuration file is published to `config/shopify-enhanced.php`:

```php
<?php
return [
    // Essential settings
    'admin_email' => env('ADMIN_EMAIL', 'admin@yourproject.com'),
    'thanks_email_template' => 'emails.thanks',
    'user_model' => \App\Models\User::class,
    'auto_register_install_job' => true,

    // Billing configuration overrides
    'billing_overrides' => [
        'test_mode' => env('SHOPIFY_BILLING_TEST', true),
        'callback_url' => env('APP_URL') . '/billing/callback',
        'pricing' => [
            'monthly' => [
                'price' => env('SUBSCRIPTION_MONTHLY_PRICE', 39.99),
                'trial_days' => 21,
            ],
        ],
    ],

    // Feature toggles
    'feature_overrides' => [
        'enabled' => [
            'usage_billing' => true,
            'plan_upgrades' => true,
            'analytics_dashboard' => true,
        ],
    ],

    // GraphQL queries for custom shop data
    'queries' => [
        'shop' => '
            query {
                shop {
                    name
                    currencyCode
                    contactEmail
                    email
                    plan { displayName }
                }
            }
        ',
    ],

    // Webhook configuration
    'webhooks' => [
        'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
        'validate_webhooks' => env('VALIDATE_SHOPIFY_WEBHOOKS', true),
        'handler_service' => \App\Services\CustomWebhookHandler::class,
        'log_webhooks' => env('LOG_WEBHOOKS', true),
        
        'gdpr' => [
            'data_retention_days' => env('GDPR_DATA_RETENTION_DAYS', 30),
            'auto_cleanup_enabled' => env('GDPR_AUTO_CLEANUP', true),
            'notification_email' => env('GDPR_NOTIFICATION_EMAIL'),
        ],
    ],
];
```

## 💰 Billing Settings

Configure subscription billing and pricing:

### Basic Billing Setup

```php
'billing_overrides' => [
    // Enable test mode for development
    'test_mode' => env('SHOPIFY_BILLING_TEST', true),
    
    // Where Shopify redirects after billing approval
    'callback_url' => env('APP_URL') . '/billing/callback',
    
    // Plan pricing configuration
    'pricing' => [
        'monthly' => [
            'price' => env('SUBSCRIPTION_MONTHLY_PRICE', 39.99),
            'trial_days' => 21,
            'features' => [
                'unlimited_size_charts',
                'custom_styling',
                'priority_support',
            ],
        ],
        'yearly' => [
            'price' => env('SUBSCRIPTION_YEARLY_PRICE', 399.99),
            'trial_days' => 30,
            'discount_percentage' => 15,
        ],
    ],
],
```

### Environment Variables for Billing

```env
# Billing configuration
SHOPIFY_BILLING_TEST=true
SUBSCRIPTION_MONTHLY_PRICE=39.99
SUBSCRIPTION_YEARLY_PRICE=399.99

# Billing callbacks
BILLING_CALLBACK_URL="${APP_URL}/billing/callback"
```

### Usage-Based Billing

For apps with usage charges:

```php
'billing_overrides' => [
    'usage_billing' => [
        'enabled' => true,
        'capped_amount' => 100.00,
        'terms' => '$1 per additional size chart after 10',
        'charge_per_unit' => 1.00,
        'free_allowance' => 10,
    ],
],
```

## 🔗 Webhook Configuration

### GDPR Compliance Webhooks

Required by Shopify for all public apps:

```php
'webhooks' => [
    // Webhook verification
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET', 'your-secret'),
    'validate_webhooks' => env('VALIDATE_SHOPIFY_WEBHOOKS', true),
    
    // Custom handler for webhook processing
    'handler_service' => \App\Services\CustomWebhookHandler::class,
    
    // Logging configuration
    'log_webhooks' => env('LOG_WEBHOOKS', true),
    'log_webhook_payloads' => env('LOG_WEBHOOK_PAYLOADS', false),
    
    // GDPR settings
    'gdpr' => [
        'data_retention_days' => env('GDPR_DATA_RETENTION_DAYS', 30),
        'auto_cleanup_enabled' => env('GDPR_AUTO_CLEANUP', true),
        'notification_email' => env('GDPR_NOTIFICATION_EMAIL', env('ADMIN_EMAIL')),
    ],
    
    // Rate limiting for webhook endpoints
    'rate_limit_enabled' => env('WEBHOOK_RATE_LIMIT', true),
    'rate_limit_max_attempts' => 60,
    'rate_limit_decay_minutes' => 1,
],
```

### Custom Webhook Handler

Create a custom webhook handler in `app/Services/CustomWebhookHandler.php`:

```php
<?php

namespace App\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

class CustomWebhookHandler extends WebhookHandlerService
{
    public function handleCustomerDataRequest($shop, $customerId, $payload)
    {
        // Your custom data request handling
        \Log::info("Processing data request for customer: {$customerId}");
        
        // Call parent method for default handling
        parent::handleCustomerDataRequest($shop, $customerId, $payload);
        
        // Add your custom logic here
        $this->notifyGDPRTeam($shop, $customerId);
    }

    public function handleCustomerDataErasure($shop, $customerId, $payload)
    {
        // Your custom data erasure handling
        \Log::info("Processing data erasure for customer: {$customerId}");
        
        // Delete custom data
        $this->deleteCustomerSizeCharts($customerId);
        
        parent::handleCustomerDataErasure($shop, $customerId, $payload);
    }

    private function notifyGDPRTeam($shop, $customerId)
    {
        // Send notification to GDPR compliance team
    }

    private function deleteCustomerSizeCharts($customerId)
    {
        // Delete customer-specific size charts
    }
}
```

## 📧 Email Templates

### Template Configuration

```php
'email_templates' => [
    'thanks_mail' => [
        'template' => 'emails.thanks',
        'subject' => 'Welcome to {{ config("app.name") }}!',
        'from_email' => env('MAIL_FROM_ADDRESS'),
        'from_name' => env('MAIL_FROM_NAME'),
    ],
    
    'uninstall_notification' => [
        'template' => 'emails.user-uninstall',
        'subject' => 'Thank you for trying {{ config("app.name") }}',
        'send_to_admin' => true,
        'admin_template' => 'emails.admin-uninstall',
    ],
],
```

### Custom Email Data

Pass custom data to email templates:

```php
'email_data' => [
    'company_info' => [
        'name' => 'Your Company',
        'website' => 'https://yourcompany.com',
        'support_email' => 'support@yourcompany.com',
    ],
    
    'social_links' => [
        'twitter' => 'https://twitter.com/yourcompany',
        'linkedin' => 'https://linkedin.com/company/yourcompany',
    ],
],
```

## 🎛️ Feature Toggles

Enable or disable package features:

```php
'feature_overrides' => [
    'enabled' => [
        // Billing features
        'usage_billing' => env('FEATURE_USAGE_BILLING', true),
        'plan_upgrades' => env('FEATURE_PLAN_UPGRADES', true),
        'subscription_management' => env('FEATURE_SUBSCRIPTIONS', true),
        
        // Admin features
        'analytics_dashboard' => env('FEATURE_ANALYTICS', false),
        'user_management' => env('FEATURE_USER_MGMT', false),
        
        // Frontend features
        'faq_system' => env('FEATURE_FAQ', true),
        'documentation' => env('FEATURE_DOCS', true),
        'pricing_page' => env('FEATURE_PRICING', true),
        
        // Advanced features
        'multi_language' => env('FEATURE_MULTI_LANG', false),
        'custom_themes' => env('FEATURE_THEMES', false),
    ],
    
    'disabled_in_production' => [
        'debug_toolbar',
        'test_webhooks',
        'development_tools',
    ],
],
```

## 🌐 Environment Variables

Complete `.env` configuration:

```env
# Package Core Settings
ADMIN_EMAIL=admin@yourapp.com

# Billing Configuration
SHOPIFY_BILLING_TEST=true
SUBSCRIPTION_MONTHLY_PRICE=39.99
SUBSCRIPTION_YEARLY_PRICE=399.99
BILLING_CALLBACK_URL="${APP_URL}/billing/callback"

# Feature Toggles
FEATURE_USAGE_BILLING=true
FEATURE_PLAN_UPGRADES=true
FEATURE_ANALYTICS=false
FEATURE_FAQ=true
FEATURE_DOCS=true
FEATURE_PRICING=true

# Webhook Security
SHOPIFY_WEBHOOK_SECRET=your-webhook-secret-here
VALIDATE_SHOPIFY_WEBHOOKS=true
LOG_WEBHOOKS=true
LOG_WEBHOOK_PAYLOADS=false

# GDPR Compliance
GDPR_DATA_RETENTION_DAYS=30
GDPR_AUTO_CLEANUP=true
GDPR_NOTIFICATION_EMAIL=gdpr@yourapp.com

# Rate Limiting
WEBHOOK_RATE_LIMIT=true
WEBHOOK_RATE_LIMIT_ATTEMPTS=60
WEBHOOK_RATE_LIMIT_DECAY=1

# Email Configuration
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Your App Name"

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Caching (recommended for production)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🔧 Advanced Customization

### Custom GraphQL Queries

Override default GraphQL queries for shop data:

```php
'queries' => [
    'shop' => <<<GRAPHQL
        query {
            shop {
                name
                currencyCode
                contactEmail
                email
                timezone
                weightUnit
                plan {
                    displayName
                    partnerDevelopment
                    shopifyPlus
                }
                paymentSettings {
                    supportedDigitalWallets
                }
            }
        }
    GRAPHQL,
    
    'custom_product_query' => <<<GRAPHQL
        query getProductsWithSizeCharts(\$query: String, \$first: Int!) {
            products(first: \$first, query: \$query) {
                edges {
                    node {
                        id
                        title
                        handle
                        metafields(namespace: "size_chart", first: 10) {
                            edges {
                                node {
                                    key
                                    value
                                }
                            }
                        }
                    }
                }
            }
        }
    GRAPHQL,
],
```

### Helper Functions

Add custom helper functions:

```php
'helpers' => [
    'get_plan_price' => function($planType) {
        $override = config("shopify-enhanced.billing_overrides.pricing.{$planType}.price");
        return $override ?: 0;
    },
    
    'is_feature_enabled' => function($feature) {
        return config("shopify-enhanced.feature_overrides.enabled.{$feature}", false);
    },
    
    'get_shop_timezone' => function($shop) {
        return $shop['timezone'] ?? 'UTC';
    },
],
```

### Custom Middleware

Register custom middleware for package routes:

```php
'middleware' => [
    'global' => [
        'throttle:api',
        'auth:shopify',
    ],
    
    'groups' => [
        'shopify-enhanced' => [
            'verify.shopify',
            'billable',
            'extract.shop.name',
        ],
        
        'admin-only' => [
            'verify.shopify',
            'admin.access',
        ],
    ],
],
```

## 🚀 Performance Configuration

### Caching Settings

```php
'cache' => [
    'shop_data_ttl' => env('CACHE_SHOP_DATA_TTL', 3600), // 1 hour
    'graphql_queries_ttl' => env('CACHE_GRAPHQL_TTL', 1800), // 30 minutes
    'webhook_validation_ttl' => env('CACHE_WEBHOOK_TTL', 300), // 5 minutes
    
    'tags' => [
        'shop_data' => 'shopify_shop_data',
        'billing_data' => 'shopify_billing',
        'webhooks' => 'shopify_webhooks',
    ],
],
```

### Queue Configuration

```php
'queue' => [
    'install_job' => env('QUEUE_INSTALL_JOB', 'default'),
    'uninstall_job' => env('QUEUE_UNINSTALL_JOB', 'default'),
    'webhook_processing' => env('QUEUE_WEBHOOKS', 'webhooks'),
    'email_notifications' => env('QUEUE_EMAILS', 'emails'),
],
```

## 🔍 Configuration Validation

Add validation for critical configuration:

```php
'validation' => [
    'required_env_vars' => [
        'ADMIN_EMAIL',
        'SHOPIFY_WEBHOOK_SECRET',
    ],
    
    'billing_validation' => [
        'test_mode_required_in_development' => true,
        'callback_url_must_be_https_in_production' => true,
    ],
],
```

## 📚 Next Steps

1. **[Exception Handling](exception-handling.md)** - Set up error handling
2. **[Webhook Implementation](webhook-implementation.md)** - Configure webhooks
3. **[Troubleshooting](troubleshooting.md)** - Common configuration issues

## 💡 Configuration Tips

- **Development**: Use `.env.example` to document all configuration options
- **Production**: Ensure all sensitive values are in environment variables
- **Testing**: Create separate configuration for testing environments
- **Staging**: Mirror production configuration for accurate testing

The package is designed to be highly configurable while maintaining sensible defaults. Most features work out of the box, but customization options are available for advanced use cases.