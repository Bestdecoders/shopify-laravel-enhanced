# Product Filter Feature - Setup Guide

## Overview
The Product Filter feature provides on-demand product caching and filtering capabilities for Shopify apps built with Laravel. It integrates seamlessly with the kyon/laravel-shopify package.

## Features
- ✅ On-demand product caching (only cache queried products)
- ✅ GraphQL-powered product fetching
- ✅ Collection membership tracking
- ✅ Webhook-based cache updates
- ✅ Scope validation
- ✅ 30-day TTL with automatic cleanup
- ✅ Vendor/title/collection filtering

## Installation & Setup

### 1. Enable the Feature
Add to your `.env` file:
```env
PRODUCT_FILTER_ENABLED=true
PRODUCT_FILTER_INCLUDE_INVENTORY=false
PRODUCT_FILTER_CACHE_TTL=30
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Configure Shopify Scopes
Ensure your app has the required scopes in `config/shopify-app.php`:
```php
'api_scopes' => 'read_products,read_collections', // Add read_inventory if needed
```

### 4. Configure Webhooks in kyon/laravel-shopify

Add these webhook configurations to your `config/shopify-app.php`:

```php
'webhooks' => [
    [
        'topic' => 'PRODUCTS_CREATE',
        'address' => env('APP_URL').'/webhook/products-create'
    ],
    [
        'topic' => 'PRODUCTS_UPDATE',
        'address' => env('APP_URL').'/webhook/products-update'
    ],
    [
        'topic' => 'PRODUCTS_DELETE',
        'address' => env('APP_URL').'/webhook/products-delete'
    ],
    [
        'topic' => 'COLLECTIONS_CREATE',
        'address' => env('APP_URL').'/webhook/collections-create'
    ],
    [
        'topic' => 'COLLECTIONS_UPDATE',
        'address' => env('APP_URL').'/webhook/collections-update'
    ],
    [
        'topic' => 'COLLECTIONS_DELETE',
        'address' => env('APP_URL').'/webhook/collections-delete'
    ],
    [
        'topic' => 'APP_SCOPES_UPDATE',
        'address' => env('APP_URL').'/webhook/app-scopes-update'
    ],
],
```

The webhook jobs are automatically available:
- `ProductsUpdateJob` (updates basic product fields only)
- `ProductsDeleteJob` (removes products from cache)
- `CollectionsCreateJob` (triggers UpdateCollectionProductsJob for background processing)
- `CollectionsUpdateJob` (triggers UpdateCollectionProductsJob for background processing)
- `CollectionsDeleteJob` (removes collection from product collection_ids)
- `AppScopesUpdateJob` (tracks scope changes)

Background jobs for collection processing:
- `UpdateCollectionProductsJob` (fetches collection products and updates all existing products)

### 5. Copy Webhook Jobs to Your App (Optional)
If you want to customize webhook handling, copy the job files to your main app:

```bash
# Copy jobs to your app's Jobs directory
cp packages/Bestdecoders/shopify-laravel-enhanced/src/Jobs/* app/Jobs/
```

## Usage Examples

### 1. Check Individual Product
```php
use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;

$productFilter = app(ProductFilterService::class);

// Check if product exists, fetch if not cached
$product = $productFilter->checkProduct($shopDomain, $productId);

// Check product for specific collection
$product = $productFilter->checkProduct($shopDomain, $productId, [
    'collection_id' => '123456789'
]);
```

### 2. Search Products
```php
// Search by vendor (DB only)
$products = $productFilter->searchProducts($shopDomain, [
    'vendor' => 'Nike'
]);

// Search by title (DB only)
$products = $productFilter->searchProducts($shopDomain, [
    'title' => 'Air Max'
]);

// Search by collection (GraphQL fetch + cache)
$products = $productFilter->searchProducts($shopDomain, [
    'collection_id' => '123456789'
]);
```

### 3. Apply Filters
```php
$products = $productFilter->searchProducts($shopDomain, [
    'vendor' => 'Nike'
], [
    'status' => 'active',
    'collection_id' => '123456789',
    'tag' => 'featured'
]);
```

### 4. Check Collection Membership
```php
$result = $productFilter->checkProductInCollections($shopDomain, $productId, [
    '123456789', '987654321'
]);

// Returns:
// [
//     'product_id' => '...',
//     'collections' => ['123456789', '987654321'],
//     'is_in_collections' => [
//         '123456789' => true,
//         '987654321' => false
//     ]
// ]
```

## Controller Usage

### Protected Routes
Use the scope validation middleware:
```php
Route::middleware(['auth', 'validate.product.filter.scopes'])->group(function () {
    Route::get('/products/{id}', [ProductController::class, 'show']);
});
```

### Controller Example
```php
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ProductFilterController;

class ProductController extends Controller
{
    public function show(Request $request, $productId)
    {
        $productFilter = app(ProductFilterService::class);
        $shopDomain = Auth::user()->name;

        $product = $productFilter->checkProduct($shopDomain, $productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product]);
    }
}
```

## How It Works

### 1. Product Lookup Flow
```
User requests product → Check DB cache → If not found → GraphQL fetch → Store in DB → Return
```

### 2. Collection Query Flow
```
User searches collection → Check DB for queried products → If none → GraphQL fetch collection products → Store + mark as queried → Return
```

### 3. Webhook Updates
```
Product webhooks → Update basic product fields only
Collection webhooks → Check for related products → Dispatch UpdateCollectionProductsJob → Update collection memberships
```

### 4. Cache Management
- **30-day TTL**: Products not accessed for 30 days are cleaned up
- **Access tracking**: Each query resets the 30-day timer
- **Webhook updates**: Keep cached data fresh without affecting TTL

## Database Schema

```sql
shopify_products:
- product_id: Shopify product ID
- user_id: Foreign key to users table
- shop_domain: Shop domain for multi-tenant
- title, handle, vendor, product_type, status
- collection_ids: JSON array of all collections
- queried_collection_ids: JSON array of collections specifically queried for
- tags: JSON array of product tags
- last_accessed_at: For 30-day TTL
- shopify_updated_at: Last update from Shopify
- price, compare_at_price, inventory_quantity, weight (optional)
```

## Commands

### Cleanup Old Products
```bash
# Manual cleanup
php artisan shopify:cleanup-products

# Cleanup with custom TTL
php artisan shopify:cleanup-products --days=60

# Dry run (see what would be deleted)
php artisan shopify:cleanup-products --dry-run

# Cleanup specific shop
php artisan shopify:cleanup-products --shop=example.myshopify.com
```

## Configuration Options

```php
// config/shopify-enhanced.php
'product_filter' => [
    'enabled' => env('PRODUCT_FILTER_ENABLED', false),
    'required_scopes' => ['read_products'],
    'optional_scopes' => ['read_inventory'],
    'cache_ttl_days' => env('PRODUCT_FILTER_CACHE_TTL', 30),
    'include_inventory_data' => env('PRODUCT_FILTER_INCLUDE_INVENTORY', false),
    'webhook_filtering_enabled' => env('PRODUCT_FILTER_WEBHOOK_FILTERING', true),
    'auto_cleanup_enabled' => env('PRODUCT_FILTER_AUTO_CLEANUP', true),
    'cleanup_schedule' => env('PRODUCT_FILTER_CLEANUP_SCHEDULE', 'daily'),
]
```

## Troubleshooting

### Check Scope Status
```php
use Bestdecoders\ShopifyLaravelEnhanced\Services\ScopeValidationService;

$scopeValidator = app(ScopeValidationService::class);
$validation = $scopeValidator->validateProductFilterScopes($user);

if (!$validation['has_required_scopes']) {
    // Handle insufficient scopes
    return redirect()->to('/install-scopes');
}
```

### Debug Webhooks
Check logs for webhook processing:
```bash
tail -f storage/logs/laravel.log | grep "webhook"
```

### Monitor Cache
```php
// Check cached products for a shop
$cachedCount = ShopifyProduct::where('shop_domain', $shopDomain)->count();

// Check old products
$oldCount = ShopifyProduct::olderThan(30)->count();
```

## Performance Notes

- **Cache-first**: Always check DB before GraphQL
- **On-demand only**: Never bulk cache entire product catalogs
- **Efficient queries**: Uses indexes on shop_domain, product_id, vendor, etc.
- **Background jobs**: Webhook processing doesn't block requests
- **TTL management**: Automatic cleanup prevents database bloat

## Important Notes

⚠️ **Webhook URLs**: Must be publicly accessible (not localhost)
⚠️ **Scopes**: Ensure your app requests appropriate Shopify scopes
⚠️ **Queue**: Webhook jobs require a working queue system
⚠️ **Storage**: Only caches products that are actually queried