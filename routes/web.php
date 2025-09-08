<?php

use Illuminate\Support\Facades\Route;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ShopifyController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\FaqController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\DocumentationController;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\PricingController;

Route::middleware('web')->group(function () {
    Route::get('shopify-enhanced/test', [ShopifyController::class, 'test'])->name('shopify-enhanced.test');
    Route::get('shopify-enhanced/shop-info/{shop}', [ShopifyController::class, 'getShopInfo']);
    Route::post('shopify-enhanced/send-thanks-email/{shop}', [ShopifyController::class, 'sendThanksEmail']);

    // FAQ Routes
    Route::prefix('faq')->name('faq.')->middleware('verify.shopify')->group(function () {
        Route::get('/', [FaqController::class, 'index'])->name('index');
        Route::get('/search', [FaqController::class, 'search'])->name('search');
        Route::get('/api/faqs', [FaqController::class, 'getFaqs'])->name('api.faqs');
        Route::get('/{id}', [FaqController::class, 'show'])->name('show');
    });

    // Documentation Routes
    Route::prefix('docs')->name('docs.')->middleware('verify.shopify')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/search', [DocumentationController::class, 'search'])->name('search');
        Route::get('/api/{slug}', [DocumentationController::class, 'getDocument'])->name('api.doc');
        Route::get('/{slug}', [DocumentationController::class, 'show'])->name('show');
    });

    // Pricing Routes
    Route::prefix('pricing')->name('pricing.')->middleware('verify.shopify')->group(function () {
        Route::get('/', [PricingController::class, 'index'])->name('index');
        Route::get('/plan/details', [PricingController::class, 'getPlanDetails'])->name('plan.details');
        Route::post('/subscription/url', [PricingController::class, 'getPlanSubscriptionUrl'])->name('subscription.url');
    });
});
