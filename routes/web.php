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

    // Subscription Management API Routes
    Route::prefix('api/subscriptions')->name('api.subscriptions.')->middleware(['verify.shopify', 'App\Http\Middleware\ExtractShopName'])->group(function () {
        // Get subscription details
        Route::get('/{user}', [UserSubscriptionController::class, 'show'])->name('show');
        
        // Cancel subscription
        Route::post('/{user}/cancel', [UserSubscriptionController::class, 'cancel'])->name('cancel');
        
        // Extend free time
        Route::post('/{user}/extend-free-time', [UserSubscriptionController::class, 'extendFreeTime'])->name('extend-free-time');
        
        // Apply coupon code
        Route::post('/{user}/apply-coupon', [UserSubscriptionController::class, 'applyCoupon'])->name('apply-coupon');
        
        // Reactivate subscription
        Route::post('/{user}/reactivate', [UserSubscriptionController::class, 'reactivate'])->name('reactivate');
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
