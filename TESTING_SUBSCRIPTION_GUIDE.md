# UserSubscriptionController Testing Guide

This guide provides comprehensive instructions for testing the UserSubscriptionController with Shopify integration using the Kyon Charge model and ShopifyGraphqlService.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Test Data Setup](#test-data-setup)
4. [Testing Process](#testing-process)
5. [API Endpoints Reference](#api-endpoints-reference)
6. [GraphQL Testing](#graphql-testing)
7. [Troubleshooting](#troubleshooting)

## Prerequisites

Before testing, ensure you have:

### 1. Shopify Partner Account & Test Store
- Create a [Shopify Partner account](https://partners.shopify.com/)
- Set up a development store
- Create a Shopify app with the following scopes:
  - `read_products`
  - `write_products` 
  - `read_orders`
  - `write_orders`

### 2. Laravel Environment
- Laravel 8+ with the enhanced package installed
- Kyon Laravel-Shopify package configured
- Database with users and charges tables

### 3. Required Environment Variables
```env
# Shopify App Credentials
SHOPIFY_APP_NAME="Your Test App"
SHOPIFY_API_KEY="your_api_key"
SHOPIFY_API_SECRET="your_api_secret"
SHOPIFY_API_SCOPES="read_products,write_products,read_orders,write_orders"

# Billing Configuration
SHOPIFY_BILLING_TEST=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Environment Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Test Data
```bash
php artisan db:seed --class="Bestdecoders\ShopifyLaravelEnhanced\Database\Seeders\SubscriptionTestDataSeeder"
```

This creates:
- 3 test users with Shopify store data
- 5 sample coupon codes (various types and statuses)
- 4 test charges (active, cancelled, free time, pending)

### 4. Configure Webhooks (Production Testing)
If testing with real Shopify webhooks, configure:
```
https://yourdomain.com/webhooks/app_subscriptions/update
```

## Test Data Setup

After running the seeder, you'll have:

### Test Users
| ID | Name | Email | Domain |
|----|------|-------|---------|
| 1 | Test Shop 1 | testshop1@example.com | testshop1.myshopify.com |
| 2 | Test Shop 2 | testshop2@example.com | testshop2.myshopify.com |
| 3 | Test Shop 3 | testshop3@example.com | testshop3.myshopify.com |

### Test Coupons
| Code | Type | Value | Status |
|------|------|-------|--------|
| WELCOME10 | percentage | 10% | Active |
| SAVE25 | fixed | $25 | Active |
| FREETRIAL | free_days | 7 days | Active |
| BLACKFRIDAY | percentage | 50% | Expired |
| FREEMONTH | free_days | 30 days | Active |

## Testing Process

### Phase 1: Local API Testing

#### 1. Test Debug Endpoint
```bash
curl -X GET "http://localhost:8000/test/subscriptions/debug"
```

Expected response includes configuration, database counts, and route information.

#### 2. Test GraphQL Connection
```bash
curl -X POST "http://localhost:8000/test/subscriptions/test-graphql" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1}'
```

#### 3. View User Charges
```bash
curl -X GET "http://localhost:8000/test/subscriptions/user/1/charges"
```

#### 4. Test Subscription Details
```bash
curl -X GET "http://localhost:8000/api/subscriptions/1"
```

### Phase 2: Subscription Management Testing

#### 1. Create Test Subscription
```bash
curl -X POST "http://localhost:8000/test/subscriptions/create" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "plan_type": "monthly",
    "trial_days": 14,
    "coupon_code": "WELCOME10"
  }'
```

#### 2. Cancel Subscription
```bash
curl -X POST "http://localhost:8000/api/subscriptions/1/cancel" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Testing cancellation flow",
    "immediate": false
  }'
```

#### 3. Extend Free Time
```bash
curl -X POST "http://localhost:8000/api/subscriptions/3/extend-free-time" \
  -H "Content-Type: application/json" \
  -d '{
    "days": 10,
    "reason": "Customer service extension"
  }'
```

#### 4. Apply Coupon
```bash
curl -X POST "http://localhost:8000/api/subscriptions/1/apply-coupon" \
  -H "Content-Type: application/json" \
  -d '{
    "coupon_code": "SAVE25",
    "plan_type": "monthly"
  }'
```

#### 5. Reactivate Subscription
```bash
curl -X POST "http://localhost:8000/api/subscriptions/2/reactivate"
```

### Phase 3: End-to-End Shopify Testing

#### 1. Install App in Test Store
- Navigate to your Shopify Partner dashboard
- Open your test store
- Install your app
- Complete OAuth flow

#### 2. Test Real Subscription Creation
Use the `/test/subscriptions/create` endpoint with a real authenticated Shopify user to create an actual subscription.

#### 3. Test Shopify Admin Panel
- Check if subscription appears in Shopify admin
- Verify billing information is correct
- Test subscription approval flow

#### 4. Test Webhooks (if configured)
- Create subscription → Check webhook delivery
- Cancel subscription → Verify webhook received
- Update subscription → Confirm webhook processed

## API Endpoints Reference

### Production API Endpoints

#### Get Subscription Details
```http
GET /api/subscriptions/{user}
```

Response:
```json
{
  "subscription": {
    "id": 1,
    "type": "recurring",
    "status": "active",
    "price": 29.99,
    "charge_id": "1001",
    "trial_days": 14,
    "coupon_code": "WELCOME10",
    "cancelled_on": null,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "is_active": true,
  "is_trial": true,
  "is_cancelled": false,
  "is_ongoing": true
}
```

#### Cancel Subscription
```http
POST /api/subscriptions/{user}/cancel
Content-Type: application/json

{
  "reason": "Customer request",
  "immediate": false
}
```

#### Extend Free Time
```http
POST /api/subscriptions/{user}/extend-free-time
Content-Type: application/json

{
  "days": 30,
  "reason": "Customer service gesture"
}
```

#### Apply Coupon
```http
POST /api/subscriptions/{user}/apply-coupon
Content-Type: application/json

{
  "coupon_code": "WELCOME10",
  "plan_type": "monthly"
}
```

#### Reactivate Subscription
```http
POST /api/subscriptions/{user}/reactivate
```

### Test Endpoints (Development Only)

#### Debug Information
```http
GET /test/subscriptions/debug
```

#### Create Test Subscription  
```http
POST /test/subscriptions/create
Content-Type: application/json

{
  "user_id": 1,
  "plan_type": "monthly|yearly|lifetime",
  "trial_days": 14,
  "coupon_code": "OPTIONAL_CODE"
}
```

#### Get User Charges
```http
GET /test/subscriptions/user/{userId}/charges
```

#### Test GraphQL Connection
```http
POST /test/subscriptions/test-graphql
Content-Type: application/json

{
  "user_id": 1
}
```

#### Get Active Subscriptions
```http
GET /test/subscriptions/user/{userId}/active
```

#### Create Test Coupons
```http
POST /test/subscriptions/create-test-coupons
```

#### Sync Charge Status
```http
POST /test/subscriptions/charge/{chargeId}/sync
```

## GraphQL Testing

### Manual GraphQL Queries

You can test GraphQL queries directly using your Shopify store's GraphQL endpoint:

#### Create Subscription
```graphql
mutation appSubscriptionCreate($name: String!, $lineItems: [AppSubscriptionLineItemInput!]!, $returnUrl: URL!, $test: Boolean, $trialDays: Int) {
  appSubscriptionCreate(
    name: $name
    lineItems: $lineItems
    returnUrl: $returnUrl
    test: $test
    trialDays: $trialDays
  ) {
    userErrors {
      field
      message
    }
    confirmationUrl
    appSubscription {
      id
      name
      status
      test
      trialDays
    }
  }
}
```

Variables:
```json
{
  "name": "Monthly Plan",
  "lineItems": [{
    "plan": {
      "appRecurringPricingDetails": {
        "price": {
          "amount": 29.99,
          "currencyCode": "USD"
        },
        "interval": "EVERY_30_DAYS"
      }
    }
  }],
  "returnUrl": "https://yourdomain.com/billing/callback",
  "test": true,
  "trialDays": 14
}
```

#### Cancel Subscription
```graphql
mutation appSubscriptionCancel($id: ID!) {
  appSubscriptionCancel(id: $id) {
    userErrors {
      field
      message
    }
    appSubscription {
      id
      status
    }
  }
}
```

#### Get Active Subscriptions
```graphql
query {
  currentAppInstallation {
    activeSubscriptions {
      id
      name
      status
      test
      trialDays
      currentPeriodEnd
    }
  }
}
```

## Troubleshooting

### Common Issues

#### 1. "User not found" Error
- Verify user ID exists in database
- Check user model configuration in `config/shopify-enhanced.php`
- Ensure test data was seeded correctly

#### 2. "No subscription found" Error
- Check if user has active charges in database
- Verify charge status and relationships
- Use debug endpoint to inspect data

#### 3. GraphQL Connection Errors
- Verify Shopify API credentials
- Check user has valid `shopify_token`
- Ensure scopes include required permissions
- Test with simpler GraphQL query first

#### 4. "Coupon code is not valid" Error
- Check coupon exists in database
- Verify coupon hasn't expired
- Check usage limits and applicable plans
- Run `create-test-coupons` endpoint

#### 5. Webhook Issues
- Verify webhook URL is accessible
- Check webhook signature validation
- Ensure HTTPS in production
- Check Shopify webhook delivery logs

### Debug Steps

1. **Check Configuration**
   ```bash
   curl -X GET "http://localhost:8000/test/subscriptions/debug"
   ```

2. **Verify Database State**
   ```sql
   SELECT * FROM users;
   SELECT * FROM charges;
   SELECT * FROM coupon_codes;
   ```

3. **Test GraphQL Connection**
   ```bash
   curl -X POST "http://localhost:8000/test/subscriptions/test-graphql" \
     -H "Content-Type: application/json" \
     -d '{"user_id": 1}'
   ```

4. **Check Laravel Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Verify Routes**
   ```bash
   php artisan route:list | grep subscription
   ```

### Performance Testing

For load testing, use tools like Apache Bench or Artillery:

```bash
# Test subscription details endpoint
ab -n 100 -c 10 http://localhost:8000/api/subscriptions/1

# Test with JSON payload
curl -X POST "http://localhost:8000/api/subscriptions/1/cancel" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Load test"}' \
  --repeat 10
```

### Security Testing

1. **Test Authentication**
   - Ensure endpoints require proper authentication
   - Test with invalid tokens
   - Verify user isolation (user can't access other user's data)

2. **Input Validation**
   - Test with malformed JSON
   - Try SQL injection attempts
   - Test XSS in reason/coupon fields

3. **Rate Limiting**
   - Test API rate limits
   - Verify Shopify API rate limiting handling

## Success Criteria

Your testing is successful when:

- ✅ All API endpoints return expected responses
- ✅ GraphQL queries execute without errors
- ✅ Subscriptions are created in test mode
- ✅ Cancellation updates both local and Shopify records
- ✅ Coupon codes apply correctly
- ✅ Free time extensions work as expected
- ✅ Webhooks are received and processed (if applicable)
- ✅ Error handling works for edge cases
- ✅ No sensitive data is logged or exposed

## Next Steps

After successful testing:

1. **Production Deployment**
   - Set `SHOPIFY_BILLING_TEST=false`
   - Configure production webhook URLs
   - Update callback URLs

2. **Monitoring**
   - Set up logging for subscription events
   - Configure alerts for failed payments
   - Monitor GraphQL API usage

3. **Documentation**
   - Update API documentation
   - Create user guides for subscription management
   - Document common issues and solutions

---

For additional help or issues, check the Laravel logs and Shopify webhook delivery status in your Partner dashboard.