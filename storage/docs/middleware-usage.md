# 🛡️ Middleware Usage Guide

## Overview

Starting with shopify-laravel-enhanced v2.x, middleware is automatically registered from the package and served as route aliases. You no longer need to publish middleware files unless you want to customize them.

## Available Middleware Aliases

| Alias | Class | Purpose |
|-------|-------|---------|
| `enhancer.extract-shop` | `ExtractShopName` | Extracts shop context from request |
| `enhancer.billable` | `Billable` | Checks subscription status |
| `enhancer.inertia` | `HandleInertiaRequests` | Handles Inertia.js requests |
| `enhancer.product-filter` | `ValidateProductFilterScopes` | Validates product filter scopes |
| `enhancer.inject-billing` | `InjectBillingDetails` | Injects comprehensive billing details into request |

## Usage in Routes

```php
// routes/web.php

// Single middleware
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('enhancer.extract-shop');

// Multiple middleware with billing details
Route::group(['middleware' => ['enhancer.extract-shop', 'enhancer.inject-billing']], function () {
    Route::get('/premium-feature', [PremiumController::class, 'index']);
});

// Complete billing setup
Route::middleware(['enhancer.extract-shop', 'enhancer.inject-billing', 'enhancer.billable'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'store']);
});
```

## Usage in Controllers

```php
class MyController extends Controller
{
    public function __construct()
    {
        $this->middleware('enhancer.extract-shop');
        $this->middleware('enhancer.billable')->only(['premium']);
    }
}
```

## Customizing Middleware (Optional)

If you need to customize the middleware behavior:

1. **Publish the middleware files:**
   ```bash
   php artisan vendor:publish --tag=shopify-enhanced-middleware-publish
   ```

2. **Modify the published files** in `app/Http/Middleware/`

3. **Register your custom middleware** in `app/Http/Kernel.php`:
   ```php
   protected $middlewareAliases = [
       // ... other middleware
       'custom.extract-shop' => \App\Http\Middleware\ExtractShopName::class,
   ];
   ```

4. **Use your custom middleware** in routes:
   ```php
   Route::get('/dashboard', [DashboardController::class, 'index'])
       ->middleware('custom.extract-shop');
   ```

## Migration from v1.x

If you were previously using published middleware, you can:

1. **Keep using your published files** - they will continue to work
2. **Switch to package middleware** - remove published files and use the new aliases
3. **Hybrid approach** - use package middleware for standard cases, custom middleware for special cases

## Accessing Billing Details

When using `enhancer.inject-billing` middleware, comprehensive billing information is injected into your request:

```php
public function index(Request $request)
{
    $billing = $request->billing_details;

    // Basic status
    $isBillable = $billing['is_billable'];
    $isFreemium = $billing['is_freemium'];
    $hasActiveSubscription = $billing['has_active_subscription'];

    // Plan information
    $currentPlan = $billing['current_plan'];
    if ($currentPlan) {
        $planName = $currentPlan['name'];
        $planPrice = $currentPlan['price'];
        $planFeatures = $currentPlan['features'];
    }

    // Subscription status
    $status = $billing['subscription_status']; // 'trial', 'paid', 'freemium', etc.
    $daysUntilBilling = $billing['days_until_billing'];
    $trialDaysRemaining = $billing['trial_days_remaining'];

    // Active charge details
    $activeCharge = $billing['active_charge'];
    if ($activeCharge) {
        $chargeStatus = $activeCharge['status'];
        $billingDate = $activeCharge['billing_on'];
    }
}
```

### Available Billing Data

The `billing_details` object contains:

**Status Fields:**
- `is_billable` - Whether the shop requires billing
- `is_freemium` - Whether the shop is on freemium plan
- `is_grandfathered` - Whether the shop has grandfathered access
- `has_active_subscription` - Whether there's an active subscription

**Plan Information:**
- `current_plan` - Complete plan details (id, name, price, features, etc.)

**Subscription Status:**
- `subscription_status` - Overall status ('trial', 'paid', 'freemium', 'grandfathered', 'no_subscription')
- `days_until_billing` - Days until next billing
- `is_trial_active` - Whether trial is currently active
- `trial_days_remaining` - Remaining trial days

**Charge Information:**
- `active_charge` - Complete active charge details

## Benefits of Package Middleware

- ✅ **No file publishing required** - works out of the box
- ✅ **Automatic updates** - get fixes and improvements automatically
- ✅ **Cleaner project structure** - fewer files in your app
- ✅ **Easy to customize** - publish only when you need changes
- ✅ **Consistent behavior** - same middleware across all projects
- ✅ **Rich billing context** - Access complete billing information easily