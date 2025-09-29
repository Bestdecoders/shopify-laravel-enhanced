<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Bestdecoders\ShopifyLaravelEnhanced\Models\ShopifyProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateExistingProductsForCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $shopDomain;
    public string $collectionId;
    public array $fetchedProductIds;

    public function __construct(string $shopDomain, string $collectionId, array $fetchedProductIds)
    {
        $this->shopDomain = $shopDomain;
        $this->collectionId = $collectionId;
        $this->fetchedProductIds = $fetchedProductIds;
    }

    public function handle(): void
    {
        try {
            // Get all existing products in our DB for this shop (excluding the ones we just fetched)
            $existingProducts = ShopifyProduct::byShop($this->shopDomain)
                ->whereNotIn('product_id', $this->fetchedProductIds)
                ->get();

            $updatedCount = 0;

            foreach ($existingProducts as $product) {
                // Check if this product is in the fetched collection products
                $isInCollection = in_array($product->product_id, $this->fetchedProductIds);

                // Update collection_ids if the product is in this collection
                if ($isInCollection) {
                    $currentCollections = $product->collection_ids ?? [];
                    if (!in_array($this->collectionId, $currentCollections)) {
                        $currentCollections[] = $this->collectionId;
                        $product->update(['collection_ids' => $currentCollections]);
                        $updatedCount++;
                    }
                }

                // Always mark as queried for this collection (whether in it or not)
                $product->addQueriedCollection($this->collectionId);
            }

            debug_log("Background job: Updated existing products for collection", [
                'shop_domain' => $this->shopDomain,
                'collection_id' => $this->collectionId,
                'existing_products_checked' => $existingProducts->count(),
                'products_updated' => $updatedCount,
                'fetched_products_count' => count($this->fetchedProductIds)
            ]);

        } catch (\Exception $e) {
            Log::error("Background job failed: Update existing products for collection", [
                'shop_domain' => $this->shopDomain,
                'collection_id' => $this->collectionId,
                'error' => $e->getMessage()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }
}