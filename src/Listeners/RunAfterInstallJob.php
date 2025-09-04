<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Listeners;

use Osiset\ShopifyApp\Messaging\Events\ShopAuthenticatedEvent;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\AfterInstallJob;
use Exception;
use Illuminate\Support\Facades\Log;

class RunAfterInstallJob
{
    public function handle(ShopAuthenticatedEvent $event)
    {
        $shopId = $event->shopId->toNative(); // This is the shop's database ID

        $userModel = config('shopify-enhanced.user_model');

        if (!class_exists($userModel)) {
            throw new Exception("The user model class '{$userModel}' does not exist.");
        }

        // Get the shop by primary key ID
        $shop = $userModel::find($shopId);

        if ($shop) {
            AfterInstallJob::dispatch($shop)->delay(now()->addSeconds(5));
        } else {
            Log::warning("Shop not found for ID: {$shopId}");
        }
    }
}