<?php

use Illuminate\Support\Facades\Route;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ShopifyController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\FaqController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\DocumentationController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\PricingController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\WebhookController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\HomeController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\UserSubscriptionController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\TestSubscriptionController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\SupportController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ProductFilterController;

Route::middleware('web')->group(function () {
   
    // Bestdecoders Privacy Policy
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('bestdecoders.privacy');
    
    Route::get('shopify-enhanced/test', [ShopifyController::class, 'test'])->name('shopify-enhanced.test');
    Route::get('shopify-enhanced/shop-info/{shop}', [ShopifyController::class, 'getShopInfo']);
    Route::post('shopify-enhanced/send-thanks-email/{shop}', [ShopifyController::class, 'sendThanksEmail']);

    // FAQ Routes
    Route::prefix('faq')->name('faq.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        Route::get('/', [FaqController::class, 'index'])->name('index');
        Route::get('/search', [FaqController::class, 'search'])->name('search');
        Route::get('/api/faqs', [FaqController::class, 'getFaqs'])->name('api.faqs');
        Route::get('/{id}', [FaqController::class, 'show'])->name('show');
    });

    // Documentation Routes
    Route::prefix('docs')->name('docs.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/search', [DocumentationController::class, 'search'])->name('search');
        Route::get('/api/{slug}', [DocumentationController::class, 'getDocument'])->name('api.doc');
        Route::get('/{slug}', [DocumentationController::class, 'show'])->name('show');
    });

    // Pricing Routes
    Route::prefix('pricing')->name('pricing.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        Route::get('/', [PricingController::class, 'index'])->name('index');
        Route::get('/plan/details', [PricingController::class, 'getPlanDetails'])->name('plan.details');
        Route::post('/subscription/url', [PricingController::class, 'getPlanSubscriptionUrl'])->name('subscription.url');
    });

    // Support Routes
    Route::prefix('support')->name('support.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::post('/submit/expectation', [SupportController::class, 'submitExpectation'])->name('submit.expectation');
        Route::get('/replies/{expectationId}', [SupportController::class, 'getReplies'])->name('get.replies');
    });

    // Admin Support API Routes
    Route::prefix('admin/api/support')->name('admin.api.support.')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/expectations', [SupportController::class, 'getAllExpectations'])->name('expectations.index');
        Route::get('/expectations/{id}', [SupportController::class, 'getExpectation'])->name('expectations.show');
        Route::post('/expectations/{id}/reply', [SupportController::class, 'replyToExpectation'])->name('expectations.reply');
    });

    // User-Accessible Subscription Routes (for shop owners)
    Route::prefix('api/my-subscription')->name('api.my-subscription.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        // User can view their own subscription details
        Route::get('/', [UserSubscriptionController::class, 'showMySubscription'])->name('show');
        
        // User can cancel their own subscription
        Route::post('/cancel', [UserSubscriptionController::class, 'cancelMySubscription'])->name('cancel');
        
        // User can apply coupon codes to their own subscription
        Route::post('/apply-coupon', [UserSubscriptionController::class, 'applyMyCoupon'])->name('apply-coupon');
        
        // User can reactivate their own subscription
        Route::post('/reactivate', [UserSubscriptionController::class, 'reactivateMySubscription'])->name('reactivate');
    });

    // Admin-Only Subscription Management API Routes (for shopify-admin-dashboard)
    Route::prefix('admin/api/subscriptions')->name('admin.api.subscriptions.')->middleware(['auth:sanctum'])->group(function () {
        // Admin can view any user's subscription details
        Route::get('/{user}', [UserSubscriptionController::class, 'show'])->name('show');
        
        // Admin can cancel any user's subscription
        Route::post('/{user}/cancel', [UserSubscriptionController::class, 'cancel'])->name('cancel');
        
        // Admin can extend free time for any user
        Route::post('/{user}/extend-free-time', [UserSubscriptionController::class, 'extendFreeTime'])->name('extend-free-time');
        
        // Admin can apply coupon codes for any user
        Route::post('/{user}/apply-coupon', [UserSubscriptionController::class, 'applyCoupon'])->name('apply-coupon');
        
        // Admin can reactivate any user's subscription
        Route::post('/{user}/reactivate', [UserSubscriptionController::class, 'reactivate'])->name('reactivate');
        
        // Admin-only bulk operations
        Route::post('/bulk/extend-trial', [UserSubscriptionController::class, 'bulkExtendTrial'])->name('bulk.extend-trial');
        Route::post('/bulk/apply-discount', [UserSubscriptionController::class, 'bulkApplyDiscount'])->name('bulk.apply-discount');
    });

    // Testing Routes (only in development)
    if (app()->environment(['local', 'testing', 'development'])) {
        Route::prefix('test/subscriptions')->name('test.subscriptions.')->group(function () {
            // Test interface
            Route::get('/', [TestSubscriptionController::class, 'index'])->name('index');
            
            // Test API endpoints
            Route::post('/create', [TestSubscriptionController::class, 'createTestSubscription'])->name('create');
            Route::get('/user/{userId}/charges', [TestSubscriptionController::class, 'getUserCharges'])->name('user-charges');
            Route::post('/test-graphql', [TestSubscriptionController::class, 'testGraphQL'])->name('test-graphql');
            Route::get('/user/{userId}/active', [TestSubscriptionController::class, 'testActiveSubscriptions'])->name('active-subscriptions');
            Route::post('/create-test-coupons', [TestSubscriptionController::class, 'createTestCoupons'])->name('create-coupons');
            Route::post('/charge/{chargeId}/sync', [TestSubscriptionController::class, 'syncChargeStatus'])->name('sync-charge');
            Route::get('/debug', [TestSubscriptionController::class, 'debug'])->name('debug');
        });
    }

    // ===========================================
    // PRODUCT FILTER API ROUTES
    // ===========================================

    Route::prefix('api/product-filter')->name('api.product-filter.')->middleware(['verify.shopify', 'Bestdecoders\ShopifyLaravelEnhanced\Middleware\ValidateProductFilterScopes', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        // Check individual product (with optional collection filtering)
        Route::post('/product/check', [ProductFilterController::class, 'checkProduct'])->name('product.check');

        // Check if product matches any of: vendor, title, or collections (OR logic)
        Route::post('/product/match', [ProductFilterController::class, 'matchProduct'])->name('product.match');

        // Search products by vendor, title, or collection
        Route::post('/products/search', [ProductFilterController::class, 'searchProducts'])->name('products.search');

        // Check product collection membership
        Route::post('/product/collections', [ProductFilterController::class, 'checkCollectionMembership'])->name('product.collections');

        // Get scope validation status
        Route::get('/scopes/status', [ProductFilterController::class, 'getScopeStatus'])->name('scopes.status');

        // Manual cleanup of old products (admin only)
        Route::post('/cleanup', [ProductFilterController::class, 'cleanupOldProducts'])->name('cleanup');
    });

    // ===========================================
    // MANDATORY GDPR COMPLIANCE WEBHOOKS ONLY
    // ===========================================
    // These 3 mandatory webhooks are served from the package and cannot be overridden
    // Users can extend the WebhookHandlerService class to customize behavior
    // All other webhooks should be handled by kyon/laravel-shopify package in the main project
    
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        // GDPR Compliance Webhooks (Mandatory by Shopify)
        Route::post('customers/data_request', [WebhookController::class, 'customerDataRequest'])->name('customers.data_request');
        Route::post('customers/redact', [WebhookController::class, 'customerDataErasure'])->name('customers.redact');
        Route::post('shop/redact', [WebhookController::class, 'shopDataErasure'])->name('shop.redact');
    });
});
