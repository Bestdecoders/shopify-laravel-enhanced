# Testing Guide for GDPR Webhook Implementation

This guide explains how to test the GDPR webhook implementation both automatically and manually.

## Table of Contents

1. [Automated Tests](#automated-tests)
2. [Manual Testing](#manual-testing)
3. [Testing in Development](#testing-in-development)
4. [Testing in Production](#testing-in-production)
5. [Custom Handler Testing](#custom-handler-testing)
6. [Troubleshooting Tests](#troubleshooting-tests)

## Automated Tests

### Running All Tests

**Important**: These tests need to be run from your main Laravel project (not the package directory) because they require Laravel's testing framework.

```bash
# From your main project root (/Users/apurba/Documents/Publicfolder/enhancer)
cd /Users/apurba/Documents/Publicfolder/enhancer

# Run all webhook tests (correct syntax for Laravel)
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/

# Run with more detailed output
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/ --testdox

# Run with stop on first failure
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/ --stop-on-failure
```

**Note**: The `-v` or `--verbose` options don't exist in Laravel's test command. Use `--testdox` for detailed output.

### Running Specific Test Suites

```bash
# Feature tests (HTTP endpoints)
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Feature/WebhookControllerTest.php

# Unit tests (Service logic)
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Unit/WebhookHandlerServiceTest.php

# Integration tests (Full workflow)
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Integration/WebhookIntegrationTest.php

# Signature validation tests
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Unit/WebhookSignatureTest.php

# Custom handler tests
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Unit/CustomWebhookHandlerTest.php
```

### Running Specific Test Methods

```bash
# Test only GDPR webhooks
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Feature/WebhookControllerTest.php --filter=gdpr

# Test webhook validation
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Unit/WebhookSignatureTest.php --filter=validation

# Test custom handlers
php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/Unit/CustomWebhookHandlerTest.php --filter=custom
```

### Test Coverage

The test suite covers:

- ✅ **All 3 GDPR webhook endpoints** (`customers/data_request`, `customers/redact`, `shop/redact`)
- ✅ **Zero-configuration setup** (works without any env variables)
- ✅ **Custom handler extension patterns**
- ✅ **Error handling and graceful failures**
- ✅ **Response structure validation**
- ✅ **GDPR compliance flags**
- ✅ **Route registration verification**
- ✅ **Non-GDPR routes are NOT present**

## Manual Testing

### 1. Using cURL Commands

#### Customer Data Request Webhook

```bash
curl -X POST http://your-app.test/webhooks/customers/data_request \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Shop-Domain: test-shop.myshopify.com" \
  -H "X-Shopify-Topic: customers/data_request" \
  -H "X-Shopify-API-Version: 2023-10" \
  -H "X-Shopify-Webhook-Id: test-webhook-123" \
  -H "User-Agent: Shopify-Webhook" \
  -d '{
    "shop_domain": "test-shop.myshopify.com",
    "customer": {
      "id": 555666777,
      "email": "customer@example.com",
      "phone": "+1234567890"
    },
    "orders_requested": [123, 456, 789]
  }'
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "worked",
  "data": {
    "action": "customer_data_request",
    "customer_id": 555666777,
    "shop": "test-shop.myshopify.com",
    "processed_at": "2024-01-15T10:30:00.000000Z",
    "handler": "Bestdecoders\\ShopifyLaravelEnhanced\\Services\\WebhookHandlerService",
    "gdpr_compliant": true
  }
}
```

#### Customer Data Erasure Webhook

```bash
curl -X POST http://your-app.test/webhooks/customers/redact \
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
```

#### Shop Data Erasure Webhook

```bash
curl -X POST http://your-app.test/webhooks/shop/redact \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Shop-Domain: test-shop.myshopify.com" \
  -H "X-Shopify-Topic: shop/redact" \
  -d '{
    "shop_domain": "test-shop.myshopify.com",
    "shop_id": 12345
  }'
```

### 2. Testing Non-GDPR Routes (Should Return 404)

```bash
# These routes should NOT exist in this package
curl -X POST http://your-app.test/webhooks/orders/create \
  -H "Content-Type: application/json" \
  -d '{"id": 123}'

# Expected: 404 Not Found

curl -X POST http://your-app.test/webhooks/products/update \
  -H "Content-Type: application/json" \
  -d '{"id": 456}'

# Expected: 404 Not Found
```

### 3. Using Postman Collection

Create a Postman collection with these requests:

**Collection Variables:**
- `base_url`: `http://your-app.test`
- `shop_domain`: `test-shop.myshopify.com`

**Requests:**

1. **Customer Data Request**
   - Method: POST
   - URL: `{{base_url}}/webhooks/customers/data_request`
   - Headers: `Content-Type: application/json`, `X-Shopify-Shop-Domain: {{shop_domain}}`
   - Body: Customer data request JSON

2. **Customer Data Erasure**
   - Method: POST
   - URL: `{{base_url}}/webhooks/customers/redact`
   - Headers: `Content-Type: application/json`, `X-Shopify-Shop-Domain: {{shop_domain}}`
   - Body: Customer erasure JSON

3. **Shop Data Erasure**
   - Method: POST
   - URL: `{{base_url}}/webhooks/shop/redact`
   - Headers: `Content-Type: application/json`, `X-Shopify-Shop-Domain: {{shop_domain}}`
   - Body: Shop erasure JSON

## Testing in Development

### 1. Local Development Setup

```bash
# Start your Laravel development server
php artisan serve

# The webhooks will be available at:
# http://localhost:8000/webhooks/customers/data_request
# http://localhost:8000/webhooks/customers/redact
# http://localhost:8000/webhooks/shop/redact
```

### 2. Using ngrok for Shopify Testing

```bash
# Install ngrok if you haven't already
npm install -g ngrok

# Expose your local server
ngrok http 8000

# Use the https URL in Shopify Partner Dashboard
# Example: https://abc123.ngrok.io/webhooks/customers/data_request
```

### 3. Shopify CLI Testing

```bash
# If using Shopify CLI
shopify app dev

# Test webhooks using Shopify CLI webhook simulator
shopify app generate webhook
```

## Testing in Production

### 1. Shopify Partner Dashboard Configuration

In your Shopify Partner Dashboard, configure these webhook URLs:

```
https://yourapp.com/webhooks/customers/data_request
https://yourapp.com/webhooks/customers/redact
https://yourapp.com/webhooks/shop/redact
```

### 2. Webhook Testing Tools

**Webhook.site:**
```bash
# Temporarily point your webhooks to webhook.site to see the payload structure
# https://webhook.site/unique-id
```

**RequestBin:**
```bash
# Create a RequestBin to capture webhook payloads
# https://requestbin.com/
```

### 3. Production Verification

```bash
# Check logs to verify webhooks are being received
tail -f storage/logs/laravel.log | grep webhook

# Check specific GDPR webhook logs
tail -f storage/logs/laravel.log | grep "Customer Data Request\|Customer Data Erasure\|Shop Data Erasure"
```

## Custom Handler Testing

### 1. Create Test Custom Handler

```php
<?php
// tests/TestCustomWebhookHandler.php

namespace Tests;

use Illuminate\Http\Request;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

class TestCustomWebhookHandler extends WebhookHandlerService
{
    public function handleCustomerDataRequest(Request $request): array
    {
        $baseResult = parent::handleCustomerDataRequest($request);
        
        return array_merge($baseResult, [
            'test_custom_handler' => true,
            'custom_processing' => 'executed'
        ]);
    }
}
```

### 2. Test Custom Handler Binding

```php
<?php
// tests/Feature/CustomHandlerTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestCustomWebhookHandler;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

class CustomHandlerTest extends TestCase
{
    public function test_custom_handler_is_used()
    {
        // Bind custom handler
        $this->app->bind(WebhookHandlerService::class, TestCustomWebhookHandler::class);
        
        // Test webhook
        $response = $this->postJson('/webhooks/customers/data_request', [
            'customer' => ['id' => 123],
            'shop_domain' => 'test.myshopify.com'
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        
        // Verify custom handler was used
        $this->assertTrue($data['data']['test_custom_handler']);
        $this->assertEquals('executed', $data['data']['custom_processing']);
    }
}
```

### 3. Run Custom Handler Tests

```bash
php artisan test tests/Feature/CustomHandlerTest.php
```

## Troubleshooting Tests

### Common Test Issues

#### 1. Tests Failing Due to Missing Routes

**Problem:** Tests return 404 errors

**Solution:**
```bash
# Clear route cache
php artisan route:clear

# Check routes are registered
php artisan route:list | grep webhook

# Should show only:
# webhooks/customers/data_request
# webhooks/customers/redact  
# webhooks/shop/redact
```

#### 2. Service Provider Not Loading

**Problem:** Webhook handler service not found

**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check service provider is registered
php artisan config:show app.providers | grep Shopify
```

#### 3. Custom Handler Not Working

**Problem:** Custom handler not being used in tests

**Solution:**
```php
// In your test setUp() method
protected function setUp(): void
{
    parent::setUp();
    
    // Ensure custom handler is bound
    $this->app->bind(
        WebhookHandlerService::class,
        YourCustomWebhookHandler::class
    );
}
```

### Debug Mode for Tests

Enable debug logging in tests:

```php
// In your test
protected function setUp(): void
{
    parent::setUp();
    
    // Enable debug logging
    Config::set('app.debug', true);
    Config::set('logging.default', 'single');
    
    // Log webhook calls
    Log::info('Starting webhook test');
}
```

### Memory Issues in Tests

```bash
# Run tests with more memory
php -d memory_limit=512M artisan test

# Or add to phpunit.xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Webhook Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        extensions: mbstring, pdo, sqlite
        
    - name: Install Dependencies
      run: composer install --no-ansi --no-interaction --no-scripts --prefer-dist
      
    - name: Run Webhook Tests
      run: php artisan test packages/Bestdecoders/shopify-laravel-enhanced/tests/
```

---

## Test Results Verification

After running tests, verify:

1. ✅ **All GDPR webhook endpoints work**
2. ✅ **Non-GDPR webhooks return 404**
3. ✅ **No configuration required**
4. ✅ **Custom handlers can be extended**
5. ✅ **GDPR compliance flags are always true**
6. ✅ **Response structure is consistent**
7. ✅ **Error handling works gracefully**

## Need Help?

If tests are failing or you need help:

1. **Check Laravel logs:** `tail -f storage/logs/laravel.log`
2. **Run with detailed output:** `php artisan test --testdox`
3. **Enable debug mode:** Set `APP_DEBUG=true`
4. **Check route registration:** `php artisan route:list | grep webhook`

---

*This testing guide is part of the [Shopify Laravel Enhanced](https://github.com/bestdecoders/shopify-laravel-enhanced) package.*