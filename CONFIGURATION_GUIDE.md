# Configuration System Guide

This guide explains the new modular configuration system for the Shopify Laravel Enhanced package, designed for use across multiple projects.

## Overview

The package now uses a modular configuration approach with separate files for different concerns:

- **`shopify-enhanced.php`** - Core package settings and project overrides
- **`graphql-queries.php`** - All GraphQL queries organized by category  
- **`billing.php`** - Billing, subscription, and pricing configuration
- **`features.php`** - Feature flags and capability toggles

## Configuration Files

### 1. Main Configuration (`config/shopify-enhanced.php`)

The main config file now focuses on core settings and project-specific overrides:

```php
return [
    // Core settings
    'admin_email' => env('ADMIN_EMAIL', 'admin@yourproject.com'),
    'user_model' => \App\Models\User::class,
    
    // Project-specific overrides
    'billing_overrides' => [
        'pricing' => [
            'monthly' => [
                'price' => 39.99, // Override for this project
                'trial_days' => 21,
            ],
        ],
    ],
    
    'feature_overrides' => [
        'enabled' => [
            'usage_billing' => true, // Enable for this project
        ],
    ],
];
```

### 2. GraphQL Queries (`config/graphql-queries.php`)

All GraphQL queries organized by category:

```php
return [
    'billing' => [
        'create_recurring_charge' => '...',
        'cancel_subscription' => '...',
        'get_app_subscriptions' => '...',
    ],
    'product' => [
        'search' => '...',
        'by_id' => '...',
        'create' => '...',
    ],
    'customer' => [
        'search' => '...',
        'by_id' => '...',
    ],
    // ... more categories
];
```

### 3. Billing Configuration (`config/billing.php`)

Complete billing and subscription settings:

```php
return [
    'test_mode' => env('SHOPIFY_BILLING_TEST', true),
    'callback_url' => env('SHOPIFY_BILLING_CALLBACK_URL', '...'),
    
    'pricing' => [
        'monthly' => [
            'price' => 29.99,
            'interval' => 'EVERY_30_DAYS',
            'name' => 'Monthly Plan',
            'trial_days' => 14,
        ],
        // ... other plans
    ],
    
    'features' => [
        'usage_charges' => false,
        'one_time_charges' => true,
        'recurring_charges' => true,
    ],
];
```

### 4. Features Configuration (`config/features.php`)

Feature flags for all package capabilities:

```php
return [
    'enabled' => [
        'subscription_management' => true,
        'coupon_system' => true,
        'webhook_handlers' => true,
    ],
    
    'subscriptions' => [
        'recurring_billing' => true,
        'usage_billing' => false,
        'trial_periods' => true,
    ],
    
    'coupons' => [
        'percentage_discounts' => true,
        'fixed_amount_discounts' => true,
        'free_time_coupons' => true,
    ],
];
```

## Using the Configuration

### Method 1: ConfigHelper Class (Recommended)

The `ConfigHelper` class provides a clean interface for accessing configurations:

```php
use Bestdecoders\ShopifyLaravelEnhanced\Support\ConfigHelper;

// Get GraphQL queries
$query = ConfigHelper::getGraphQLQuery('billing', 'create_recurring_charge');
$allBillingQueries = ConfigHelper::getGraphQLCategory('billing');

// Get billing configuration
$isTestMode = ConfigHelper::isBillingTestMode();
$monthlyPlan = ConfigHelper::getPlanDetails('monthly');
$trialDays = ConfigHelper::getTrialDays('monthly');

// Check feature flags
$subscriptionsEnabled = ConfigHelper::isFeatureEnabled('subscription_management');
$usageBillingEnabled = ConfigHelper::isSubscriptionFeatureEnabled('usage_billing');

// Get pricing
$monthlyPrice = ConfigHelper::getPlanDetails('monthly')['price'];
$currency = ConfigHelper::getCurrency('default'); // USD
```

### Method 2: Direct Config Access

You can still access configurations directly:

```php
// GraphQL queries
$query = config('shopify-enhanced-graphql-queries.billing.create_recurring_charge');

// Billing settings
$testMode = config('shopify-enhanced-billing.test_mode');
$pricing = config('shopify-enhanced-billing.pricing.monthly');

// Feature flags
$enabled = config('shopify-enhanced-features.enabled.subscription_management');
```

## Project Customization

### 1. Publishing Configurations

Publish the configuration files to your project for customization:

```bash
# Publish all configs
php artisan vendor:publish --tag=shopify-enhanced-all-configs

# Or publish specific configs
php artisan vendor:publish --tag=shopify-enhanced-graphql-config
php artisan vendor:publish --tag=shopify-enhanced-billing-config
php artisan vendor:publish --tag=shopify-enhanced-features-config
```

### 2. Project-Specific Overrides

In your main `config/shopify-enhanced.php`, override specific settings:

```php
return [
    'admin_email' => 'admin@myproject.com',
    
    // Override billing settings
    'billing_overrides' => [
        'pricing' => [
            'monthly' => [
                'price' => 49.99, // Different price for this project
                'trial_days' => 30, // Longer trial
            ],
        ],
    ],
    
    // Override features
    'feature_overrides' => [
        'enabled' => [
            'usage_billing' => true, // Enable for this project
            'analytics_dashboard' => true,
        ],
    ],
    
    // Override GraphQL queries
    'graphql_overrides' => [
        'billing' => [
            'create_recurring_charge' => '...custom query...',
        ],
    ],
];
```

### 3. Environment-Specific Settings

Use environment variables for project customization:

```env
# Billing settings
SHOPIFY_BILLING_TEST=true
SUBSCRIPTION_MONTHLY_PRICE=39.99
SUBSCRIPTION_MONTHLY_TRIAL_DAYS=21

# Feature flags
FEATURE_USAGE_BILLING=true
FEATURE_ANALYTICS_TRACKING=true

# Admin settings
ADMIN_EMAIL=admin@myproject.com
```

## Service Integration

### Updated Services

The `SubscriptionManagementService` now uses the new configuration system:

```php
// Before (hardcoded config path)
$query = config('shopify-enhanced.queries.billing.cancel_subscription');

// After (uses modular config)
$query = config('shopify-enhanced-graphql-queries.billing.cancel_subscription');

// Or with ConfigHelper
$query = ConfigHelper::getGraphQLQuery('billing', 'cancel_subscription');
```

### Service Provider

The `ShopifyEnhancedServiceProvider` automatically registers all configuration files:

```php
// Configurations are automatically loaded
$this->mergeConfigFrom(__DIR__ . '/config/graphql-queries.php', 'shopify-enhanced-graphql-queries');
$this->mergeConfigFrom(__DIR__ . '/config/billing.php', 'shopify-enhanced-billing');
$this->mergeConfigFrom(__DIR__ . '/config/features.php', 'shopify-enhanced-features');
```

## Multi-Project Usage

### Project A (Basic E-commerce)
```php
// config/shopify-enhanced.php
return [
    'billing_overrides' => [
        'pricing' => [
            'monthly' => ['price' => 19.99], // Lower price
        ],
    ],
    'feature_overrides' => [
        'enabled' => [
            'usage_billing' => false, // Disable complex features
            'analytics_dashboard' => false,
        ],
    ],
];
```

### Project B (Enterprise Solution)
```php
// config/shopify-enhanced.php  
return [
    'billing_overrides' => [
        'pricing' => [
            'monthly' => ['price' => 99.99], // Higher price
            'enterprise' => [ // Custom plan
                'price' => 299.99,
                'interval' => 'ANNUAL',
                'name' => 'Enterprise Plan',
            ],
        ],
    ],
    'feature_overrides' => [
        'enabled' => [
            'usage_billing' => true, // Enable advanced features
            'analytics_dashboard' => true,
            'bulk_operations' => true,
        ],
    ],
];
```

## Configuration Validation

Use the ConfigHelper to validate your configuration:

```php
$issues = ConfigHelper::validateConfiguration();

if (!empty($issues)) {
    foreach ($issues as $issue) {
        Log::warning("Configuration issue: {$issue}");
    }
}
```

## Migration from Old Config

If you're migrating from the old single-file configuration:

### 1. Update Service Classes

Replace hardcoded config paths:

```php
// Old
$query = config('shopify-enhanced.queries.billing.create_recurring_charge');

// New
$query = ConfigHelper::getGraphQLQuery('billing', 'create_recurring_charge');
```

### 2. Move Settings to Appropriate Files

Move billing settings to `shopify-enhanced-billing.php`:
```php
// Old (in shopify-enhanced.php)
'subscription_pricing' => [...],

// New (in shopify-enhanced-billing.php)
'pricing' => [...],
```

### 3. Convert Feature Flags

Move feature toggles to `shopify-enhanced-features.php`:
```php
// Old
'enable_coupons' => true,

// New (in features.php)
'enabled' => ['coupon_system' => true],
```

## Best Practices

### 1. Use ConfigHelper
Always use `ConfigHelper` for consistent access patterns across projects.

### 2. Environment Variables
Use environment variables for project-specific values:
```php
'price' => env('SUBSCRIPTION_MONTHLY_PRICE', 29.99),
```

### 3. Override Strategy
Use overrides in the main config instead of editing package files directly.

### 4. Validation
Regularly validate your configuration in development:
```php
if (app()->environment('local')) {
    $issues = ConfigHelper::validateConfiguration();
    if (!empty($issues)) {
        dump('Config issues:', $issues);
    }
}
```

### 5. Documentation
Document your project-specific overrides:
```php
'billing_overrides' => [
    'pricing' => [
        'monthly' => [
            'price' => 39.99, // Higher price due to premium features
            'trial_days' => 21, // Longer trial for complex setup
        ],
    ],
],
```

## Available Config Keys

### GraphQL Categories
- `billing` - Subscription and billing queries
- `product` - Product management queries  
- `customer` - Customer management queries
- `order` - Order management queries
- `webhook` - Webhook management queries
- `metafield` - Metafield operations
- `files` - File upload operations

### Billing Configuration
- `test_mode` - Enable/disable test mode
- `pricing` - Plan pricing and details
- `trial_days` - Trial period configuration
- `features` - Billing feature flags
- `currency` - Currency settings
- `webhooks` - Billing webhook configuration

### Feature Categories
- `enabled` - Core feature toggles
- `subscriptions` - Subscription-related features
- `coupons` - Coupon system features
- `graphql` - GraphQL service features
- `webhooks` - Webhook processing features
- `admin` - Admin interface features
- `testing` - Development/testing features
- `security` - Security features
- `performance` - Performance optimization features

This modular approach makes the package much more maintainable and customizable across different projects while keeping configurations organized and easy to understand.