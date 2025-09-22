<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Models\ShopifyProduct;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\UpdateCollectionProductsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductFilterWebhookHandler extends WebhookHandlerService
{
    protected ProductFilterService $productFilterService;

    public function __construct(ProductFilterService $productFilterService)
    {
        $this->productFilterService = $productFilterService;
    }


    public function handleProductUpdate(Request $request): array
    {
        if (!$this->isProductFilterEnabled()) {
            return $this->createSkippedResponse('product_update', 'Product filter feature disabled');
        }

        try {
            $productData = $request->all();
            $shopDomain = $request->header('X-Shopify-Shop-Domain');

            // Only update if product exists in our cache
            $existingProduct = ShopifyProduct::where('shop_domain', $shopDomain)
                ->where('product_id', $productData['id'])
                ->first();

            if ($existingProduct) {
                $this->updateProductFromWebhook($existingProduct, $productData);
                Log::info("Updated cached product from webhook", [
                    'product_id' => $productData['id'],
                    'shop_domain' => $shopDomain
                ]);
            }

            return [
                'action' => 'product_update',
                'product_id' => $productData['id'],
                'shop_domain' => $shopDomain,
                'cached_product_updated' => $existingProduct ? true : false,
                'processed_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle product update webhook: " . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    public function handleProductDelete(Request $request): array
    {
        if (!$this->isProductFilterEnabled()) {
            return $this->createSkippedResponse('product_delete', 'Product filter feature disabled');
        }

        try {
            $productData = $request->all();
            $shopDomain = $request->header('X-Shopify-Shop-Domain');

            // Remove from cache if exists
            $deletedCount = ShopifyProduct::where('shop_domain', $shopDomain)
                ->where('product_id', $productData['id'])
                ->delete();

            Log::info("Removed product from cache", [
                'product_id' => $productData['id'],
                'shop_domain' => $shopDomain,
                'deleted_count' => $deletedCount
            ]);

            return [
                'action' => 'product_delete',
                'product_id' => $productData['id'],
                'shop_domain' => $shopDomain,
                'cached_product_removed' => $deletedCount > 0,
                'processed_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle product delete webhook: " . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    public function handleCollectionCreate(Request $request): array
    {
        if (!$this->isProductFilterEnabled()) {
            return $this->createSkippedResponse('collection_create', 'Product filter feature disabled');
        }

        return $this->handleCollectionChange($request, 'collection_create');
    }

    public function handleCollectionUpdate(Request $request): array
    {
        if (!$this->isProductFilterEnabled()) {
            return $this->createSkippedResponse('collection_update', 'Product filter feature disabled');
        }

        return $this->handleCollectionChange($request, 'collection_update');
    }

    public function handleCollectionDelete(Request $request): array
    {
        if (!$this->isProductFilterEnabled()) {
            return $this->createSkippedResponse('collection_delete', 'Product filter feature disabled');
        }

        try {
            $collectionData = $request->all();
            $shopDomain = $request->header('X-Shopify-Shop-Domain');
            $collectionId = (string) $collectionData['id'];

            // Update all cached products that belong to this collection
            $affectedProducts = ShopifyProduct::where('shop_domain', $shopDomain)
                ->inCollection($collectionId)
                ->get();

            foreach ($affectedProducts as $product) {
                $collectionIds = $product->collection_ids ?? [];
                $collectionIds = array_filter($collectionIds, fn($id) => $id !== $collectionId);
                $product->update(['collection_ids' => $collectionIds]);
            }

            Log::info("Removed collection from cached products", [
                'collection_id' => $collectionId,
                'shop_domain' => $shopDomain,
                'affected_products' => $affectedProducts->count()
            ]);

            return [
                'action' => 'collection_delete',
                'collection_id' => $collectionId,
                'shop_domain' => $shopDomain,
                'affected_products' => $affectedProducts->count(),
                'processed_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle collection delete webhook: " . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    public function handleAppScopesUpdate(Request $request): array
    {
        try {
            $scopeData = $request->all();
            $shopDomain = $request->header('X-Shopify-Shop-Domain');

            Log::info("App scopes updated", [
                'shop_domain' => $shopDomain,
                'new_scopes' => $scopeData
            ]);

            // If product filter feature is enabled, check if we lost required scopes
            if ($this->isProductFilterEnabled()) {
                $user = $this->getUserByShopDomain($shopDomain);
                if ($user) {
                    $hasRequiredScopes = $this->checkProductFilterScopes($user);

                    if (!$hasRequiredScopes) {
                        Log::warning("Lost required scopes for product filter", [
                            'shop_domain' => $shopDomain,
                            'user_scopes' => $user->shopify_scopes
                        ]);
                    }
                }
            }

            return [
                'action' => 'app_scopes_update',
                'shop_domain' => $shopDomain,
                'scopes_data' => $scopeData,
                'product_filter_enabled' => $this->isProductFilterEnabled(),
                'processed_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle app scopes update webhook: " . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    protected function handleCollectionChange(Request $request, string $action): array
    {
        try {
            $collectionData = $request->all();
            $shopDomain = $request->header('X-Shopify-Shop-Domain');
            $collectionId = (string) $collectionData['id'];

            // Always dispatch background job to fetch collection products and update database
            UpdateCollectionProductsJob::dispatch($shopDomain, $collectionId, $action);

            Log::info("Collection {$action} webhook: Background job dispatched", [
                'collection_id' => $collectionId,
                'shop_domain' => $shopDomain,
                'collection_title' => $collectionData['title'] ?? 'Unknown'
            ]);

            return [
                'action' => $action,
                'collection_id' => $collectionId,
                'shop_domain' => $shopDomain,
                'background_job_dispatched' => true,
                'processed_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle {$action} webhook: " . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    protected function updateProductFromWebhook(ShopifyProduct $product, array $productData): void
    {
        // Only update basic product fields, NOT collection_ids (handled by collection webhooks)
        $updateData = [
            'title' => $productData['title'] ?? $product->title,
            'handle' => $productData['handle'] ?? $product->handle,
            'vendor' => $productData['vendor'] ?? $product->vendor,
            'product_type' => $productData['product_type'] ?? $product->product_type,
            'status' => isset($productData['status']) ? strtolower($productData['status']) : $product->status,
            'tags' => $productData['tags'] ?? $product->tags,
            'shopify_updated_at' => isset($productData['updated_at']) ?
                Carbon::parse($productData['updated_at']) : $product->shopify_updated_at,
        ];

        // Update inventory data if enabled and available
        if ($this->shouldIncludeInventoryData() && isset($productData['variants'][0])) {
            $variant = $productData['variants'][0];
            $updateData = array_merge($updateData, [
                'price' => isset($variant['price']) ? (int) ($variant['price'] * 100) : $product->price,
                'compare_at_price' => isset($variant['compare_at_price']) ?
                    (int) ($variant['compare_at_price'] * 100) : $product->compare_at_price,
                'inventory_quantity' => $variant['inventory_quantity'] ?? $product->inventory_quantity,
                'weight' => $variant['weight'] ?? $product->weight,
            ]);
        }

        $product->update($updateData);
    }

    protected function isProductFilterEnabled(): bool
    {
        return config('shopify-enhanced.product_filter.enabled', false);
    }

    protected function shouldIncludeInventoryData(): bool
    {
        return config('shopify-enhanced.product_filter.include_inventory_data', false);
    }

    protected function checkProductFilterScopes($user): bool
    {
        $requiredScopes = config('shopify-enhanced.product_filter.required_scopes', ['read_products']);

        // Get user scopes from kyon package or user model
        $userScopesString = config('shopify-app.api_scopes');
        $userScopes = array_map('trim', explode(',', $userScopesString));

        foreach ($requiredScopes as $scope) {
            if (!in_array($scope, $userScopes) && !in_array('write_products', $userScopes)) {
                return false;
            }
        }

        return true;
    }

    protected function getUserByShopDomain($shopDomain)
    {
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        return $userModel::where('name', $shopDomain)->first();
    }

    protected function createSkippedResponse(string $action, string $reason): array
    {
        return [
            'action' => $action,
            'skipped' => true,
            'reason' => $reason,
            'processed_at' => now()->toISOString(),
        ];
    }
}