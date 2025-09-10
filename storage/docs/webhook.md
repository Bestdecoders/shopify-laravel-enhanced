# 🔗 Mandatory Compliance Webhooks System

This package provides **mandatory compliance webhooks** that are automatically served from the package routes. Users can extend the webhook handler to customize behavior while maintaining Shopify compliance requirements.

## 🎯 How It Works

### 1. **Package-Served Routes (Mandatory)**
These routes are automatically available and cannot be overridden:

```
POST /webhooks/orders/create               # Order creation
POST /webhooks/products/update             # Product updates
POST /webhooks/customers/data_request      # GDPR data request
POST /webhooks/customers/redact            # GDPR customer data erasure
POST /webhooks/shop/redact                 # GDPR shop data erasure
POST /webhooks/custom/{webhookType}        # Generic webhook handler
```

> **Note:** App install/uninstall webhooks are handled by the kyon/laravel-shopify package with existing jobs.

### 2. **Default Response**
All webhooks return a default response:

```json
{
  "status": "success",
  "message": "worked",
  "data": {
    "action": "webhook_type",
    "processed_at": "2024-01-15T10:30:00Z",
    "handler": "WebhookHandlerService"
  }
}
```

### 3. **User Extension System**
Users can customize webhook behavior by extending the base handler class.

## 🚀 Installation & Setup

### Step 1: Publish Webhook Handler
```bash
# Publish the customizable webhook handler
php artisan vendor:publish --tag=shopify-enhanced-webhooks
```

This creates: `app/Services/CustomWebhookHandler.php`

### Step 2: Bind Custom Handler (Optional)
In your `AppServiceProvider.php`:

```php
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use App\Services\CustomWebhookHandler;

public function register()
{
    // Bind your custom handler to override default behavior
    $this->app->bind(WebhookHandlerService::class, CustomWebhookHandler::class);
}
```

### Step 3: Configure Webhooks
Add to your `.env` file:

```env
# Webhook Configuration
SHOPIFY_WEBHOOK_SECRET=your_webhook_secret
VALIDATE_SHOPIFY_WEBHOOKS=true
LOG_WEBHOOKS=true

# Optional webhook endpoints
WEBHOOK_ORDERS_CREATE=true
WEBHOOK_PRODUCTS_UPDATE=true
WEBHOOK_CUSTOM_EVENTS=true

# GDPR Configuration
GDPR_DATA_RETENTION_DAYS=30
GDPR_AUTO_CLEANUP=true
GDPR_NOTIFICATION_EMAIL=admin@yourstore.com
```

## 🎨 Customization Examples

### Basic Customization
```php
// app/Services/CustomWebhookHandler.php

public function handleAppInstalled(Request $request): array
{
    $shopDomain = $request->input('domain');
    
    // Your custom logic
    $this->sendWelcomeEmail($shopDomain);
    $this->setupDefaultSettings($shopDomain);
    
    // Call parent for base functionality
    $baseResult = parent::handleAppInstalled($request);
    
    return array_merge($baseResult, [
        'welcome_email_sent' => true,
        'default_settings_created' => true
    ]);
}
```

### Advanced Customization
```php
public function handleOrderCreated(Request $request): array
{
    $orderId = $request->input('id');
    $lineItems = $request->input('line_items', []);
    
    // Custom business logic
    $this->updateInventory($lineItems);
    $this->triggerFulfillment($orderId);
    $this->sendNotifications($orderId);
    
    $baseResult = parent::handleOrderCreated($request);
    
    return array_merge($baseResult, [
        'inventory_updated' => true,
        'fulfillment_triggered' => true,
        'notifications_sent' => count($lineItems)
    ]);
}
```

### GDPR Compliance Handling
```php
public function handleCustomerDataErasure(Request $request): array
{
    $customerId = $request->input('customer.id');
    
    // Custom GDPR erasure logic
    $this->eraseCustomerFromDatabase($customerId);
    $this->removeCustomerFiles($customerId);
    $this->anonymizeCustomerRecords($customerId);
    
    $baseResult = parent::handleCustomerDataErasure($request);
    
    return array_merge($baseResult, [
        'database_erased' => true,
        'files_removed' => true,
        'records_anonymized' => true
    ]);
}
```

## 🔧 Available Webhook Methods

| Method | Purpose | Required |
|--------|---------|----------|
| `handleOrderCreated()` | Order processing | ⚪ Optional |
| `handleProductUpdated()` | Product change handling | ⚪ Optional |
| `handleCustomerDataRequest()` | GDPR data export | ✅ Mandatory |
| `handleCustomerDataErasure()` | GDPR customer erasure | ✅ Mandatory |
| `handleShopDataErasure()` | GDPR shop erasure | ✅ Mandatory |
| `handleGeneric()` | Custom webhook types | ⚪ Optional |
| `validateWebhook()` | Signature validation | ⚪ Optional |

> **Note:** App install/uninstall handled by kyon/laravel-shopify package jobs

## 🛡️ Security Features

### Automatic Signature Validation
```php
// Automatically validates Shopify webhook signatures
public function validateWebhook(Request $request): bool
{
    // Override for custom validation logic
    return parent::validateWebhook($request);
}
```

### Rate Limiting
Configure webhook rate limiting in config:
```php
'rate_limit_enabled' => true,
'rate_limit_max_attempts' => 60,
'rate_limit_decay_minutes' => 1,
```

### Request Logging
```php
'log_webhooks' => true,              // Log webhook calls
'log_webhook_payloads' => false,     // Log full payloads (debugging)
```

## 📊 Testing Webhooks

### Test with cURL
```bash
# Test order creation webhook
curl -X POST https://your-app.com/webhooks/orders/create \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Hmac-Sha256: your_signature" \
  -d '{"id": 123456, "order_number": "1001", "shop_domain": "test-shop.myshopify.com"}'
```

### Expected Response
```json
{
  "status": "success",
  "message": "worked",
  "data": {
    "action": "order_created",
    "order_id": 123456,
    "shop": "test-shop.myshopify.com",
    "processed_at": "2024-01-15T10:30:00Z",
    "handler": "CustomWebhookHandler"
  }
}
```

## 🔍 Monitoring & Debugging

### Enable Detailed Logging
```env
LOG_WEBHOOKS=true
LOG_WEBHOOK_PAYLOADS=true  # Only for debugging
```

### Check Logs
```bash
# View webhook logs
tail -f storage/logs/laravel.log | grep "webhook"
```

### Webhook Status Endpoint
```php
// Optional: Add status endpoint in your routes
Route::get('/webhooks/status', function() {
    return [
        'webhook_handler' => app(WebhookHandlerService::class)::class,
        'mandatory_webhooks' => config('shopify-enhanced.webhooks.mandatory_webhooks'),
        'optional_webhooks' => config('shopify-enhanced.webhooks.optional_webhooks'),
    ];
});
```

## ⚡ Performance Considerations

### Queue Processing
For heavy processing, dispatch jobs from webhook handlers:

```php
public function handleOrderCreated(Request $request): array
{
    // Dispatch heavy processing to queue
    ProcessOrderJob::dispatch($request->all());
    
    return parent::handleOrderCreated($request);
}
```

### Caching
Cache frequently accessed data:

```php
public function handleProductUpdated(Request $request): array
{
    $productId = $request->input('id');
    
    // Clear product cache
    Cache::forget("product.{$productId}");
    
    return parent::handleProductUpdated($request);
}
```

## 🎯 Key Benefits

✅ **Mandatory Compliance** - Routes served from package, always available  
✅ **Extensible Logic** - Users can customize behavior without breaking compliance  
✅ **Default "worked" Response** - Immediate webhook acknowledgment  
✅ **GDPR Ready** - Built-in GDPR compliance webhook handlers  
✅ **Security Built-in** - Signature validation, rate limiting, logging  
✅ **Easy Testing** - Clear endpoint structure and response format  
✅ **Professional Logging** - Comprehensive webhook activity logging  

## 🚨 Important Notes

- **Mandatory webhooks cannot be disabled** - They ensure Shopify compliance
- **Routes are served from the package** - Users cannot override the routes themselves
- **Always call `parent::method()`** - Maintains base functionality and compliance
- **GDPR webhooks are critical** - Handle customer data carefully
- **Test thoroughly** - Webhook failures can affect app store approval

---

> **🔥 Pro Tip:** The webhook system is designed to be "compliance-first" while remaining fully extensible. You get guaranteed Shopify compliance with unlimited customization possibilities!