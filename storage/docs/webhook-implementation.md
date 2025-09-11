# GDPR Webhook Implementation Guide

![Shopify GDPR Compliance](https://help.shopify.com/assets/images/gdpr-webhooks.png)

This guide covers implementing, testing, and extending the **mandatory GDPR compliance webhooks** provided by the Shopify Laravel Enhanced package. These webhooks work automatically without any configuration required.

## Table of Contents

1. [Overview](#overview)
2. [Automatic Setup](#automatic-setup) 
3. [GDPR Webhooks](#gdpr-webhooks)
4. [Testing Webhooks](#testing-webhooks)
5. [Extending Webhook Handlers](#extending-webhook-handlers)
6. [Customization Examples](#customization-examples)
7. [Troubleshooting](#troubleshooting)
8. [API Reference](#api-reference)

## Overview

The Shopify Laravel Enhanced package provides **only the 3 mandatory GDPR compliance webhooks** required by Shopify. All other webhooks are handled by the [kyon/laravel-shopify](https://github.com/kyon147/laravel-shopify) package in your main project.

### Key Features

- ✅ **GDPR Compliant**: Handles all mandatory Shopify GDPR webhooks
- ✅ **Zero Configuration**: Works immediately after package installation
- ✅ **Extensible**: Override methods to add custom business logic
- ✅ **Automatic Validation**: Uses kyon/laravel-shopify for webhook verification
- ✅ **Package Routes**: Served directly from package - no route copying needed
- ✅ **Testable**: Comprehensive test suite included

### GDPR Webhooks Handled

| Topic | Event | Route | Required by Shopify |
|-------|--------|-------|-------------------|
| `customers/data_request` | Customer data export request | `/webhooks/customers/data_request` | ✅ **Mandatory** |
| `customers/redact` | Customer data deletion request | `/webhooks/customers/redact` | ✅ **Mandatory** |
| `shop/redact` | Shop data deletion request | `/webhooks/shop/redact` | ✅ **Mandatory** |

**Important**: All other webhooks (orders, products, etc.) should be configured in your main project using [kyon/laravel-shopify](https://github.com/kyon147/laravel-shopify).

## Automatic Setup

The GDPR webhooks work immediately after package installation with **zero configuration required**.

### 1. Package Installation

```bash
composer require bestdecoders/shopify-laravel-enhanced
```

### 2. Automatic Registration

The package automatically registers the service provider and routes. No additional setup needed.

### 3. Configure in Shopify Partner Dashboard

Add these webhook endpoints in your Shopify app settings:

```
https://yourapp.com/webhooks/customers/data_request
https://yourapp.com/webhooks/customers/redact
https://yourapp.com/webhooks/shop/redact
```

**That's it!** The webhooks are now working and GDPR compliant.

## GDPR Webhooks

### Customer Data Request (`customers/data_request`)

Handles requests for customer data export under GDPR Article 15 (Right of Access).

**Example Request:**
```json
{
  "shop_domain": "example-shop.myshopify.com",
  "customer": {
    "id": 555666777,
    "email": "customer@example.com",
    "phone": "+1234567890"
  },
  "orders_requested": [123, 456, 789]
}
```

**Default Response:**
```json
{
  "status": "success",
  "message": "worked",
  "data": {
    "action": "customer_data_request",
    "customer_id": 555666777,
    "shop": "example-shop.myshopify.com",
    "processed_at": "2024-01-15T10:30:00.000000Z",
    "handler": "Bestdecoders\\ShopifyLaravelEnhanced\\Services\\WebhookHandlerService",
    "gdpr_compliant": true
  }
}
```

### Customer Data Erasure (`customers/redact`)

Handles customer data deletion requests under GDPR Article 17 (Right to be Forgotten).

**Example Request:**
```json
{
  "shop_domain": "example-shop.myshopify.com",
  "customer": {
    "id": 555666777,
    "email": "customer@example.com"
  },
  "orders_to_redact": [123, 456]
}
```

### Shop Data Erasure (`shop/redact`)

Handles shop data deletion when a shop is uninstalled or deleted.

**Example Request:**
```json
{
  "shop_domain": "example-shop.myshopify.com",
  "shop_id": 12345
}
```

## Testing Webhooks

### Running Package Tests

```bash
# Run all webhook tests
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/

# Run specific GDPR tests
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Feature/WebhookControllerTest.php --filter=gdpr
```

### Manual Testing with cURL

Test the GDPR webhooks manually:

```bash
# Test customer data request
curl -X POST https://yourapp.com/webhooks/customers/data_request \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Shop-Domain: test-shop.myshopify.com" \
  -H "X-Shopify-Topic: customers/data_request" \
  -d '{
    "shop_domain": "test-shop.myshopify.com",
    "customer": {
      "id": 555666777,
      "email": "customer@example.com"
    }
  }'

# Test customer data erasure
curl -X POST https://yourapp.com/webhooks/customers/redact \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Shop-Domain: test-shop.myshopify.com" \
  -H "X-Shopify-Topic: customers/redact" \
  -d '{
    "shop_domain": "test-shop.myshopify.com",
    "customer": {
      "id": 555666777,
      "email": "customer@example.com"
    },
    "orders_to_redact": [123, 456]
  }'

# Test shop data erasure
curl -X POST https://yourapp.com/webhooks/shop/redact \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Shop-Domain: test-shop.myshopify.com" \
  -H "X-Shopify-Topic: shop/redact" \
  -d '{
    "shop_domain": "test-shop.myshopify.com",
    "shop_id": 12345
  }'
```

### Expected Response

All webhooks return a consistent success response:

```json
{
  "status": "success",
  "message": "worked",
  "data": {
    "action": "customer_data_request",
    "customer_id": 555666777,
    "shop": "test-shop.myshopify.com",
    "processed_at": "2024-01-15T10:30:00.000000Z",
    "handler": "YourApp\\Services\\CustomWebhookHandler",
    "gdpr_compliant": true
  }
}
```

## Extending Webhook Handlers

### Using Custom Webhook Handler

**Step 1:** Create your custom webhook handler:

```php
<?php
// app/Services/CustomWebhookHandler.php

namespace App\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CustomWebhookHandler extends WebhookHandlerService
{
    // Override methods as needed
}
```

**Step 2:** Update your `config/shopify-enhanced.php`:

```php
'webhooks' => [
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
    'handler_service' => \App\Services\CustomWebhookHandler::class,
    'base_url' => env('APP_URL'), // Automatically uses your APP_URL
    // ... other config options
],
```

### Basic Extension Example

```php
<?php
// app/Services/CustomWebhookHandler.php

namespace App\Services;

use Illuminate\Http\Request;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

class CustomWebhookHandler extends WebhookHandlerService
{
    /**
     * Handle customer data request with business logic
     */
    public function handleCustomerDataRequest(Request $request): array
    {
        $customerId = $request->input('customer.id');
        $customerEmail = $request->input('customer.email');
        
        // Custom business logic - export customer data
        $this->exportCustomerData($customerId, $customerEmail);
        $this->notifyComplianceTeam($customerId);
        
        // Always call parent to maintain GDPR compliance
        $baseResult = parent::handleCustomerDataRequest($request);
        
        return array_merge($baseResult, [
            'data_exported' => true,
            'export_format' => 'json',
            'compliance_team_notified' => true,
            'custom_processing' => true,
        ]);
    }
    
    /**
     * Export customer data to file system
     */
    private function exportCustomerData(int $customerId, string $email): void
    {
        // Implementation: Export customer data
        // - Query database for customer records
        // - Generate export file (JSON, CSV, etc.)
        // - Store in secure location for customer download
    }
    
    /**
     * Notify compliance team of data request
     */
    private function notifyComplianceTeam(int $customerId): void
    {
        // Implementation: Send notification
        // - Email compliance team
        // - Log in audit system
        // - Create support ticket if needed
    }
}
```

### Configuration Complete

That's it! Your custom webhook handler is now active. The package will automatically:
- Use your custom handler for all GDPR webhooks
- Generate webhook URLs using your `APP_URL`
- Validate webhooks using your `SHOPIFY_WEBHOOK_SECRET`

No additional service provider binding needed when using the config approach.

## Customization Examples

### 1. Enhanced Customer Data Export

```php
public function handleCustomerDataRequest(Request $request): array
{
    $customerId = $request->input('customer.id');
    $customerEmail = $request->input('customer.email');
    
    // Comprehensive data export
    $exportData = $this->gatherCustomerData($customerId);
    $exportFile = $this->createExportFile($exportData, $customerId);
    $downloadUrl = $this->generateSecureDownloadUrl($exportFile);
    
    // Send export email to customer
    $this->sendExportEmail($customerEmail, $downloadUrl);
    
    // Audit logging
    $this->auditLog('customer_data_export', [
        'customer_id' => $customerId,
        'export_file' => $exportFile,
        'exported_at' => now()
    ]);
    
    $baseResult = parent::handleCustomerDataRequest($request);
    
    return array_merge($baseResult, [
        'export_file' => $exportFile,
        'download_url' => $downloadUrl,
        'email_sent' => true,
        'audit_logged' => true,
        'records_exported' => count($exportData),
    ]);
}

private function gatherCustomerData(int $customerId): array
{
    return [
        'customer_profile' => $this->getCustomerProfile($customerId),
        'order_history' => $this->getOrderHistory($customerId),
        'support_tickets' => $this->getSupportTickets($customerId),
        'marketing_preferences' => $this->getMarketingPreferences($customerId),
        'loyalty_points' => $this->getLoyaltyPoints($customerId),
    ];
}
```

### 2. Advanced Customer Data Erasure

```php
public function handleCustomerDataErasure(Request $request): array
{
    $customerId = $request->input('customer.id');
    $ordersToRedact = $request->input('orders_to_redact', []);
    
    // Multi-system data erasure
    $erasureResults = [
        'database_anonymized' => $this->anonymizeDatabase($customerId),
        'files_deleted' => $this->deleteCustomerFiles($customerId),
        'backups_purged' => $this->purgeBackups($customerId),
        'analytics_anonymized' => $this->anonymizeAnalytics($customerId),
        'marketing_removed' => $this->removeFromMarketing($customerId),
        'orders_redacted' => $this->redactOrders($ordersToRedact),
    ];
    
    // Verification step
    $verificationResult = $this->verifyErasure($customerId);
    
    // Create erasure certificate
    $certificate = $this->createErasureCertificate($customerId, $erasureResults);
    
    $baseResult = parent::handleCustomerDataErasure($request);
    
    return array_merge($baseResult, $erasureResults, [
        'verification_passed' => $verificationResult,
        'erasure_certificate' => $certificate,
        'gdpr_article_17_compliant' => true,
        'erasure_timestamp' => now()->toISOString(),
    ]);
}
```

### 3. Shop Data Erasure with Multi-Tenant Cleanup

```php
public function handleShopDataErasure(Request $request): array
{
    $shopDomain = $request->input('shop_domain');
    $shopId = $request->input('shop_id');
    
    // Comprehensive shop data cleanup
    $cleanupResults = [
        'shop_settings_removed' => $this->removeShopSettings($shopId),
        'user_accounts_anonymized' => $this->anonymizeShopUsers($shopId),
        'app_data_deleted' => $this->deleteAppData($shopId),
        'webhooks_unregistered' => $this->unregisterWebhooks($shopDomain),
        'api_tokens_revoked' => $this->revokeApiTokens($shopId),
        'database_cleaned' => $this->cleanDatabase($shopId),
    ];
    
    // Final verification
    $verificationPassed = $this->verifyShopErasure($shopId);
    
    $baseResult = parent::handleShopDataErasure($request);
    
    return array_merge($baseResult, $cleanupResults, [
        'verification_passed' => $verificationPassed,
        'cleanup_completed' => array_sum($cleanupResults) === count($cleanupResults),
        'shop_fully_removed' => true,
    ]);
}
```

## Troubleshooting

### Common Issues

#### 1. Webhooks Not Receiving Requests

**Problem**: Shopify not sending webhook requests

**Solutions**:
- Verify webhook URLs are correctly configured in Shopify Partner Dashboard
- Check that your app is properly installed on the test shop
- Ensure your server is publicly accessible (use ngrok for local development)

```bash
# For local development
ngrok http 8000
# Use the https URL in Shopify webhook settings
```

#### 2. Custom Handler Not Working

**Problem**: Custom webhook handler not being used

**Solution**: Verify service binding in `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->bind(
        \Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService::class, 
        \App\Services\CustomWebhookHandler::class
    );
}

// Clear cache after changes
php artisan cache:clear
php artisan config:clear
```

#### 3. GDPR Compliance Concerns

**Problem**: Ensuring webhooks meet GDPR requirements

**Solution**: The package provides GDPR-compliant default responses. For full compliance:

```php
public function handleCustomerDataErasure(Request $request): array
{
    // Ensure actual data deletion occurs
    $customerId = $request->input('customer.id');
    
    // Delete from all systems
    $this->deleteFromPrimaryDatabase($customerId);
    $this->deleteFromBackupSystems($customerId);
    $this->anonymizeInAnalytics($customerId);
    $this->removeFromThirdPartyServices($customerId);
    
    // Document the erasure
    $this->createAuditTrail($customerId, 'full_erasure_completed');
    
    return parent::handleCustomerDataErasure($request);
}
```

### Debug Mode

Enable detailed logging for troubleshooting:

```php
// In your custom handler
public function handleCustomerDataRequest(Request $request): array
{
    Log::info('GDPR webhook debug', [
        'payload' => $request->all(),
        'headers' => $request->headers->all(),
        'timestamp' => now()
    ]);
    
    return parent::handleCustomerDataRequest($request);
}
```

### Testing Webhook Delivery

1. **Shopify Partner Dashboard**: Use the webhook testing tool
2. **Local Development**: Use ngrok to expose local server
3. **Webhook Inspector**: Use [webhook.site](https://webhook.site) for testing

## API Reference

### WebhookHandlerService Methods

#### GDPR Handler Methods

```php
// Handle customer data request (GDPR Article 15)
public function handleCustomerDataRequest(Request $request): array

// Handle customer data erasure (GDPR Article 17)  
public function handleCustomerDataErasure(Request $request): array

// Handle shop data erasure (App uninstall/deletion)
public function handleShopDataErasure(Request $request): array
```

#### Validation Methods

```php
// Validate webhook using kyon/laravel-shopify package
public function validateWebhook(Request $request): bool
```

### Standard Response Structure

All GDPR webhook handlers return:

```php
[
    'action' => 'customer_data_request',      // Webhook action type
    'customer_id' => 555666777,              // Customer ID (if applicable)
    'shop' => 'shop.myshopify.com',          // Shop domain
    'processed_at' => '2024-01-15T10:30:00.000000Z', // Processing timestamp
    'handler' => 'App\\Services\\CustomWebhookHandler', // Handler class used
    'gdpr_compliant' => true,                // GDPR compliance flag (always true)
    // ... additional custom fields from extensions
]
```

### Request Data Structure

#### Customer Data Request
```php
[
    'shop_domain' => 'shop.myshopify.com',
    'customer' => [
        'id' => 555666777,
        'email' => 'customer@example.com',
        'phone' => '+1234567890'
    ],
    'orders_requested' => [123, 456, 789]  // Optional
]
```

#### Customer Data Erasure
```php
[
    'shop_domain' => 'shop.myshopify.com', 
    'customer' => [
        'id' => 555666777,
        'email' => 'customer@example.com'
    ],
    'orders_to_redact' => [123, 456]  // Optional
]
```

#### Shop Data Erasure
```php
[
    'shop_domain' => 'shop.myshopify.com',
    'shop_id' => 12345
]
```

---

## External Resources

- [Shopify GDPR Webhook Documentation](https://shopify.dev/docs/apps/webhooks/mandatory-webhooks)
- [GDPR Compliance Guide for Shopify Apps](https://shopify.dev/docs/apps/store/data-protection/gdpr)
- [kyon/laravel-shopify Package](https://github.com/kyon147/laravel-shopify)
- [Laravel Service Container Documentation](https://laravel.com/docs/container)

## Need Help?

- 📚 [Package Documentation](../README.md)
- 🐛 [Report Issues](https://github.com/bestdecoders/shopify-laravel-enhanced/issues)
- 💬 [Discussions](https://github.com/bestdecoders/shopify-laravel-enhanced/discussions)
- 📧 [Support Email](mailto:support@bestdecoders.com)

---

*This documentation is part of the [Shopify Laravel Enhanced](https://github.com/bestdecoders/shopify-laravel-enhanced) package.*