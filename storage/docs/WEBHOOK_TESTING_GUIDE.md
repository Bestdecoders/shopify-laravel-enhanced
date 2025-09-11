# Mandatory Webhooks Testing Guide

This guide explains how to use the Postman collection to test Shopify's mandatory GDPR webhooks.

## 📁 Files Created

- `MandatoryWebhooks_Postman_Collection.json` - Complete Postman collection
- `WEBHOOK_TESTING_GUIDE.md` - This usage guide

## 🚀 Quick Setup

### 1. Import the Collection

1. Open Postman
2. Click **Import** button
3. Select `MandatoryWebhooks_Postman_Collection.json`
4. Collection will be imported with 4 test requests

### 2. Configure Variables

Before testing, update these collection variables:

| Variable | Example Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost:8000` | Your Laravel app URL |
| `webhook_secret` | `your_actual_webhook_secret` | From Shopify app settings |
| `test_shop_domain` | `test-shop.myshopify.com` | Test shop domain |
| `customer_id` | `12345678901234567` | Test customer ID |

**To update variables:**
1. Right-click collection → **Edit**
2. Go to **Variables** tab
3. Update the **Current Value** column
4. Click **Save**

## 🧪 Test Requests Included

### 1. GDPR - Customer Data Request
- **Endpoint:** `POST /webhooks/customers/data_request`
- **Purpose:** Test customer data request webhook
- **Expected:** 200 OK with success status

### 2. GDPR - Customer Data Erasure  
- **Endpoint:** `POST /webhooks/customers/redact`
- **Purpose:** Test customer data deletion webhook
- **Expected:** 200 OK with success status

### 3. GDPR - Shop Data Erasure
- **Endpoint:** `POST /webhooks/shop/redact` 
- **Purpose:** Test shop data deletion webhook
- **Expected:** 200 OK with success status

### 4. Test Invalid HMAC (Should Fail)
- **Purpose:** Verify HMAC signature validation is working
- **Expected:** 401/403 error for invalid signature

## 🔒 HMAC Signature Generation

The collection automatically generates HMAC signatures using a **Pre-request Script**:

```javascript
// Automatic HMAC generation
const crypto = require('crypto');
const secret = pm.collectionVariables.get('webhook_secret');
const body = pm.request.body.raw;

const hmac = crypto.createHmac('sha256', secret);
hmac.update(body, 'utf8');
const hash = hmac.digest('base64');

pm.request.headers.add({
    key: 'X-Shopify-Hmac-Sha256',
    value: hash
});
```

## ⚙️ Required Headers (Auto-Added)

Each request includes these Shopify headers:

```
Content-Type: application/json
X-Shopify-Topic: [webhook_topic]
X-Shopify-Shop-Domain: {{test_shop_domain}}
X-Shopify-API-Version: 2023-10
X-Shopify-Hmac-Sha256: [auto_generated]
```

## 🎯 How to Run Tests

### Option 1: Individual Tests
1. Select any request
2. Click **Send**
3. Check response in **Test Results** tab

### Option 2: Run All Tests
1. Right-click collection
2. Select **Run collection**
3. Click **Run Mandatory Webhooks**
4. View results in Collection Runner

## ✅ Expected Responses

### Success Response (200 OK):
```json
{
  "status": "success",
  "message": "worked",
  "data": {
    // Response data from your handler
  }
}
```

### Error Response (500):
```json
{
  "status": "error", 
  "message": "webhook processing failed",
  "error": "Error details"
}
```

## 🛠️ Troubleshooting

### HMAC Validation Fails
- ✅ Check `webhook_secret` variable matches your Shopify app
- ✅ Ensure webhook secret is correctly set in `.env`
- ✅ Verify HMAC generation script is running

### 404 Not Found
- ✅ Check `base_url` variable points to correct server
- ✅ Ensure Laravel routes are properly loaded
- ✅ Verify package routes are published/registered

### 500 Internal Server Error  
- ✅ Check Laravel logs: `tail -f storage/logs/laravel.log`
- ✅ Verify webhook handler service is properly configured
- ✅ Check database connections if handlers use DB

### Invalid JSON
- ✅ Ensure request body is valid JSON
- ✅ Check Content-Type header is `application/json`

## 🔍 Monitoring & Debugging

### Laravel Logs
```bash
# Watch real-time logs
tail -f storage/logs/laravel.log

# Filter webhook logs
grep "webhook" storage/logs/laravel.log
```

### Enable Debug Mode
In your `.env`:
```env
APP_DEBUG=true
LOG_WEBHOOK_PAYLOADS=true
```

## 📋 Test Checklist

Before considering webhooks production-ready:

- [ ] All 3 mandatory webhooks return 200 OK
- [ ] Invalid HMAC signatures are rejected (401/403)
- [ ] Webhook handlers process data correctly
- [ ] Error handling works for malformed requests
- [ ] Rate limiting is configured (if enabled)
- [ ] Logs are being written correctly
- [ ] GDPR data handling complies with requirements

## 🚨 Important Notes

1. **Webhook Secret:** Never commit real webhook secrets to version control
2. **Testing Environment:** Use development/staging for testing
3. **GDPR Compliance:** Ensure handlers actually process GDPR requests appropriately
4. **Error Handling:** Test edge cases and malformed payloads
5. **Rate Limiting:** Consider webhook rate limits in production

## 📚 Related Files

- `src/Http/Controllers/WebhookController.php` - Webhook handlers
- `src/Services/WebhookHandlerService.php` - Business logic
- `config/shopify-enhanced.php` - Webhook configuration
- `routes/web.php` - Webhook routes

---

**Happy Testing! 🎉**