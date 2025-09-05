# Pricing Plans Feature

This package now includes a complete pricing plans implementation that works with the kyon147/laravel-shopify package.

## Features Included

### Backend Components
- **Controller Methods**: `getPlanDetails()` and `getPlanSubscriptionUrl()` in `ShopifyController`
- **Routes**: RESTful API endpoints for pricing plan operations
- **Middleware**: Proper authentication using Shopify's auth middleware
- **Billing Integration**: Works with existing Shopify billing system

### Frontend Components
- **Pricing Component**: Complete React component with modern Shopify Polaris UI
- **Page Integration**: Ready-to-use pricing page
- **Responsive Design**: Mobile-friendly pricing cards
- **Plan Features**: Support for displaying plan features, pricing, and intervals

## API Endpoints

### Get Plan Details
```
GET /plan/details
```
Returns current user's plan and all available plans.

### Get Subscription URL
```
POST /get/plan/subscription/url
Body: { "planId": "plan_id_here" }
```
Returns the Shopify billing URL for subscribing to a plan.

## Usage

### 1. Publish Assets
```bash
php artisan vendor:publish --tag=shopify-enhanced-assets
```

### 2. Setup Plans
Use the kyon147/laravel-shopify package to create plans in your database:

```php
// Create plans using Shopify package
$plan = new \Osiset\ShopifyApp\Storage\Models\Plan([
    'name' => 'Basic Plan',
    'price' => 9.99,
    'interval' => 'EVERY_30_DAYS',
    'trial_days' => 7,
    'active' => true,
    'terms' => 'Perfect for small stores'
]);
$plan->save();
```

### 3. Use in Frontend
Import and use the Pricing component in your React pages:

```jsx
import { Pricing } from '../components/Pricing';

function PricingPage() {
    return <Pricing />;
}
```

## Required Dependencies

This feature requires:
- kyon147/laravel-shopify package
- Proper Shopify app authentication
- Shopify Polaris (already included)

## Configuration

The pricing system uses the existing Shopify configuration from your `config/shopify-app.php` file. No additional configuration is needed.

## Middleware

The pricing endpoints are protected by:
- `web` middleware group
- `auth.shopify` middleware for authentication

## Frontend Features

- Modern Shopify Polaris design
- Responsive pricing cards
- Current plan highlighting
- Feature lists for each plan
- Upgrade/downgrade buttons
- Loading states and error handling
- Support for free plans
- Trial period display