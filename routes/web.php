<?php

use Illuminate\Support\Facades\Route;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ShopifyController;

Route::middleware('web')->group(function () {
    Route::get('shopify-enhanced/test', [ShopifyController::class, 'test'])->name('shopify-enhanced.test');
    Route::get('shopify-enhanced/shop-info/{shop}', [ShopifyController::class, 'getShopInfo']);
    Route::post('shopify-enhanced/send-thanks-email/{shop}', [ShopifyController::class, 'sendThanksEmail']);
    
    // Pricing Plan Routes - These should be used within authenticated Shopify app context
    Route::middleware(['auth.shopify'])->group(function () {
        Route::get('plan/details', [ShopifyController::class, 'getPlanDetails'])->name('shopify-enhanced.plan.details');
        Route::post('get/plan/subscription/url', [ShopifyController::class, 'getPlanSubscriptionUrl'])->name('shopify-enhanced.plan.subscription');
        
        // Settings API Routes
        Route::get('api/settings', [ShopifyController::class, 'getSettings'])->name('shopify-enhanced.settings.get');
        Route::post('api/settings', [ShopifyController::class, 'saveSettings'])->name('shopify-enhanced.settings.save');
        
        // Product and Collection Iterator Routes
        Route::get('api/products', [ShopifyController::class, 'getProductList'])->name('shopify-enhanced.products.list');
        Route::get('api/collections', [ShopifyController::class, 'getCollectionList'])->name('shopify-enhanced.collections.list');
        
        // Generalized Data Management Routes
        Route::get('api/data/{entityType}', [ShopifyController::class, 'getDataItems'])->name('shopify-enhanced.data.index');
        Route::post('api/data/{entityType}', [ShopifyController::class, 'createDataItem'])->name('shopify-enhanced.data.store');
        Route::put('api/data/{entityType}/{id}', [ShopifyController::class, 'updateDataItem'])->name('shopify-enhanced.data.update');
        Route::delete('api/data/{entityType}/delete', [ShopifyController::class, 'deleteDataItems'])->name('shopify-enhanced.data.delete');
        Route::post('api/data/{entityType}/activate', [ShopifyController::class, 'activateDataItems'])->name('shopify-enhanced.data.activate');
        Route::post('api/data/{entityType}/deactivate', [ShopifyController::class, 'deactivateDataItems'])->name('shopify-enhanced.data.deactivate');
        Route::post('api/data/{entityType}/{id}/duplicate', [ShopifyController::class, 'duplicateDataItem'])->name('shopify-enhanced.data.duplicate');
        Route::get('api/data/{entityType}/export', [ShopifyController::class, 'exportDataItems'])->name('shopify-enhanced.data.export');
        Route::post('api/data/{entityType}/import', [ShopifyController::class, 'importDataItems'])->name('shopify-enhanced.data.import');
    });
});
