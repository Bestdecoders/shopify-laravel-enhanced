# Shopify Enhanced - UX/UI Design Rules & AI Prompts

> This file contains design rules and patterns that MUST be followed when building UI/UX for this Shopify app package.
> AI agents and developers should read this file before making any frontend or user experience changes.

---

## TABLE OF CONTENTS

1. [Billing & Subscription Flow](#billing--subscription-flow)
2. [Discount & Coupon System](#discount--coupon-system)
3. [Multi-Store Architecture](#multi-store-architecture)
4. [Shopify API Integration Rules](#shopify-api-integration-rules)
5. [GraphQL Mutation Patterns](#graphql-mutation-patterns)
6. [Error Handling & User Feedback](#error-handling--user-feedback)
7. [AI Coding Prompts](#ai-coding-prompts)

---

## BILLING & SUBSCRIPTION FLOW

### The Golden Rule of Shopify Billing

**NEVER calculate discounted prices locally for subscriptions. ALWAYS pass the original price to Shopify with a discount object.**

### Required Subscription Flow

```
User Installs App
    ↓
Check if user has active subscription
    ↓
If NOT subscribed → Redirect to billing approval page
    ↓
Show pricing with discounts (if applicable)
    ↓
User approves → Shopify activates subscription
    ↓
Redirect back to app dashboard
```

### MUST Follow These Rules

1. **Always redirect to confirmationUrl** - Never skip this step
2. **Show pricing BEFORE approval** - Merchants must see what they're paying
3. **Handle trial days in the mutation** - Not in your local logic
4. **Store original price in database** - Not the discounted price
5. **Let Shopify handle billing cycles** - Don't build your own billing scheduler

### Pricing Display Format

```
✅ CORRECT:
Monthly Plan: $5.00/month
Special Offer: $1 off for 2 months
You pay: $4.00/month for 2 months, then $5.00/month

❌ WRONG:
Monthly Plan: $4.00/month
(hides the discount - merchant doesn't see real value)
```

---

## DISCOUNT & COUPON SYSTEM

### Discount Types

| Type | GraphQL Structure | Example |
|------|-------------------|---------|
| Percentage | `value: { percentage: 0.5 }` | 50% off |
| Fixed | `value: { amount: 2.0 }` | $2 off |
| Free Days | Use `trialDays` instead | Extend trial |

### Duration Limits

```
durationLimitInIntervals: null      = Unlimited discount
durationLimitInIntervals: 2         = Discount for 2 billing cycles
durationLimitInIntervals: 12        = Discount for 12 billing cycles
```

### Coupon Creation Flow

```php
// ✅ CORRECT - Create coupon with duration
$coupon->setDurationLimit(2);  // 2 months
$coupon->save();

// ❌ WRONG - Create coupon without duration thinking it's forever
$coupon->save();  // This defaults to null (unlimited)
```

### What Merchants See

Shopify billing approval page shows:
- Base price (original)
- Discount amount
- Duration (if limited)
- Final price for each period

```
Example on Shopify Approval Screen:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Monthly Plan
$5.00 USD per month

Discount: -$1.00 USD
Duration: 2 billing cycles

You'll pay:
• Month 1-2: $4.00/month
• Month 3+: $5.00/month
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## MULTI-STORE ARCHITECTURE

### CRITICAL: One Query Per Store

**NEVER try to apply billing/coupons to multiple stores in one GraphQL query.**

Each store = Individual API call = Individual subscription

### Loop Pattern for Bulk Operations

```php
// ✅ CORRECT - Loop through each shop
$shops = Shop::where('is_beta_tester', true)->get();

foreach ($shops as $shop) {
    $subscription = $this->createSubscription($shop, 'monthly', 0, 'BETA50');
    // Each shop gets individual subscription
}

// ❌ WRONG - Trying to bulk apply
$this->applyCouponToMultipleStores($shops, 'BETA50');  // IMPOSSIBLE
```

### Why This Architecture?

1. **Security**: Each store has unique access token
2. **Isolation**: Billing is per-shop, never shared
3. **Compliance**: Shopify requires individual merchant approval
4. **Scalability**: Handle failures per store, not bulk failures

---

## SHOPIFY API INTEGRATION RULES

### Config File Structure

All GraphQL queries MUST be stored in:
```
package/bestdecoders/shopify-laravel-enhanced/config/shopify-enhanced.php
```

Under the `queries` array:

```php
'queries' => [
    'app_subscription' => [
        'create' => <<<GRAPHQL
            mutation appSubscriptionCreate(...) { ... }
        GRAPHQL,
        'cancel' => <<<GRAPHQL
            mutation appSubscriptionCancel(...) { ... }
        GRAPHQL,
    ],
],
```

### Access Pattern

```php
// ✅ CORRECT
$query = config('shopify-enhanced.queries.app_subscription.create');

// ❌ WRONG
$query = config('shopify-enhanced-graphql-queries.billing.create_recurring_charge');
```

---

## GRAPHQL MUTATION PATTERNS

### appSubscriptionCreate Pattern

```graphql
mutation appSubscriptionCreate(
    $name: String!
    $lineItems: [AppSubscriptionLineItemInput!]!
    $returnUrl: URL!
    $test: Boolean
    $trialDays: Int
) {
    appSubscriptionCreate(
        name: $name
        lineItems: $lineItems
        returnUrl: $returnUrl
        test: $test
        trialDays: $trialDays
    ) {
        confirmationUrl
        userErrors { field message }
        appSubscription {
            id
            name
            status
            lineItems {
                plan {
                    pricingDetails {
                        ... on AppRecurringPricing {
                            price { amount currencyCode }
                            interval
                            discount {
                                value { amount percentage }
                                durationLimitInIntervals
                            }
                        }
                    }
                }
            }
        }
    }
}
```

### Line Item Structure WITH Discount

```php
$lineItems = [[
    'plan' => [
        'appRecurringPricingDetails' => [
            'price' => [
                'amount' => 5.00,      // ORIGINAL PRICE - never discounted
                'currencyCode' => 'USD'
            ],
            'interval' => 'EVERY_30_DAYS',
            'discount' => [              // Discount object
                'value' => [
                    'amount' => 1.00     // or 'percentage' => 0.2
                ],
                'durationLimitInIntervals' => 2
            ]
        ]
    ]
]];
```

---

## ERROR HANDLING & USER FEEDBACK

### GraphQL Error Response

Always check for `userErrors` in GraphQL response:

```php
if (isset($result['appSubscriptionCreate']['userErrors'])) {
    foreach ($result['appSubscriptionCreate']['userErrors'] as $error) {
        Log::error('GraphQL error', [
            'field' => $error['field'],
            'message' => $error['message']
        ]);
    }
}
```

### User-Facing Messages

| Scenario | Message |
|----------|---------|
| Coupon expired | "This discount code has expired." |
| Coupon max reached | "This discount code has been fully used." |
| Invalid coupon | "This discount code is not valid." |
| Subscription active | "You already have an active subscription." |

---

## AI CODING PROMPTS

### Prompt for Creating New Billing Features

```
You are coding a Shopify app billing feature. FOLLOW THESE RULES:

1. Read: /Users/apurbapodder/Sites/shopify-enhancer/package/bestdecoders/shopify-laravel-enhanced/docs/DESIGN_RULES.md

2. GraphQL queries MUST be in: config/shopify-enhanced.php under 'queries.app_subscription'

3. When creating subscriptions:
   - Pass ORIGINAL price (never discounted price)
   - Add discount as separate object in appRecurringPricingDetails
   - Use config('shopify-enhanced.queries.app_subscription.create')

4. NEVER:
   - Calculate effective price locally for subscriptions
   - Skip confirmationUrl redirect
   - Apply billing to multiple stores in one query

5. ALWAYS:
   - Store coupon codes in charges table
   - Show discount details before approval
   - Handle userErrors from GraphQL response
```

### Prompt for Discount System

```
When implementing discount/coupon features:

1. Use CouponCode model with these types:
   - TYPE_PERCENTAGE = 'percentage'
   - TYPE_FIXED = 'fixed'
   - TYPE_FREE_DAYS = 'free_days'

2. Duration limit goes in metadata['duration_limit']:
   - null = unlimited
   - 2 = 2 billing cycles

3. Build discount structure:
   - Percentage: value.percentage = 0.5 (50% as decimal)
   - Fixed: value.amount = 2.00 (actual dollar amount)
   - Include durationLimitInIntervals

4. Free days coupons → use trial extension, not discount
```

### Prompt for Multi-Store Operations

```
When working with multiple Shopify stores:

1. EACH store needs INDIVIDUAL API call
2. NO bulk mutations exist
3. Loop pattern:
   foreach ($shops as $shop) {
       // individual call per shop
   }
4. Handle failures per store
5. Log each operation separately
```

---

## CHECKLIST FOR NEW FEATURES

### Before Committing Billing Code

- [ ] GraphQL query in `config/shopify-enhanced.php`
- [ ] Using `config('shopify-enhanced.queries.*')` pattern
- [ ] Original price passed to Shopify (not discounted)
- [ ] Discount object structured correctly
- [ ] confirmationUrl redirect implemented
- [ ] User feedback for errors
- [ ] Database stores original price + coupon code
- [ ] Duration limit in metadata for limited discounts

---

## VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-02-17 | Initial design rules for subscription discount system |

---

## NOTES

- This file should be updated whenever new UX patterns are established
- AI agents should always read this file before making UI/UX changes
- Breaking these rules will result in broken billing and angry merchants
