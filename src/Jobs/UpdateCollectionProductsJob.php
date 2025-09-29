<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateCollectionProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $shopDomain;
    public string $collectionId;
    public string $action; // 'create' or 'update'

    public function __construct(string $shopDomain, string $collectionId, string $action = 'update')
    {
        $this->shopDomain = $shopDomain;
        $this->collectionId = $collectionId;
        $this->action = $action;
    }

    public function handle(): void
    {
        try {
            $productFilterService = app(ProductFilterService::class);

            // Fetch all products from this collection via GraphQL
            // This will handle everything: GraphQL fetch, caching, and updating existing products
            $collectionProducts = $productFilterService->searchProducts(
                $this->shopDomain,
                ['collection_id' => $this->collectionId]
            );

            debug_log("Background job completed: Update collection products", [
                'action' => $this->action,
                'shop_domain' => $this->shopDomain,
                'collection_id' => $this->collectionId,
                'fetched_products_count' => $collectionProducts->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Background job failed: Update collection products", [
                'action' => $this->action,
                'shop_domain' => $this->shopDomain,
                'collection_id' => $this->collectionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to mark job as failed for retry
            throw $e;
        }
    }
}