<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterWebhookHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

class ProductsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ShopDomain $shopDomain;
    public stdClass $data;

    public function __construct(ShopDomain $shopDomain, stdClass $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    public function handle(): void
    {
        // Only process if product filter feature is enabled
        if (!config('shopify-enhanced.product_filter.enabled', false)) {
            return;
        }

        try {
            $handler = app(ProductFilterWebhookHandler::class);

            // Create a mock request with the webhook data
            $request = new \Illuminate\Http\Request();
            $request->replace((array) $this->data);
            $request->headers->set('X-Shopify-Shop-Domain', $this->shopDomain->toNative());

            $handler->handleProductUpdate($request);

        } catch (\Exception $e) {
            \Log::error('Product update webhook job failed', [
                'shop_domain' => $this->shopDomain->toNative(),
                'error' => $e->getMessage(),
                'data' => $this->data
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }
}