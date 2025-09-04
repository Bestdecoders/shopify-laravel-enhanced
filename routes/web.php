<?php

use Illuminate\Support\Facades\Route;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\ShopifyController;

Route::middleware('web')->group(function () {
    Route::get('shopify-enhanced/test', [ShopifyController::class, 'test'])->name('shopify-enhanced.test');
    Route::get('shopify-enhanced/shop-info/{shop}', [ShopifyController::class, 'getShopInfo']);
    Route::post('shopify-enhanced/send-thanks-email/{shop}', [ShopifyController::class, 'sendThanksEmail']);


});
