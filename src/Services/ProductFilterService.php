<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Models\ShopifyProduct;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ShopifyGraphqlService;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\UpdateExistingProductsForCollectionJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ProductFilterService
{
    protected ShopifyGraphqlService $graphqlService;

    public function __construct(ShopifyGraphqlService $graphqlService)
    {
        $this->graphqlService = $graphqlService;
    }

    public function checkProduct($shopDomain, $productId, $filters = [])
    {
        if (!$this->isFeatureEnabled()) {
            throw new \Exception('Product filter feature is not enabled.');
        }

        if (!$this->hasRequiredScopes($shopDomain)) {
            throw new \Exception('Missing required scopes for product filtering.');
        }

        // Try to find existing product
        $product = ShopifyProduct::where('shop_domain', $shopDomain)
            ->where('product_id', $this->normalizeProductId($productId))
            ->first();

        if ($product) {
            // Update last accessed (resets 30-day timer)
            $product->updateLastAccessed();
            return $this->applyFilters($product, $filters);
        }

        // Product not found - fetch from Shopify
        return $this->fetchAndStoreProduct($shopDomain, $productId, $filters);
    }

    public function searchProducts($shopDomain, $searchCriteria, $filters = [])
    {
        if (!$this->isFeatureEnabled()) {
            throw new \Exception('Product filter feature is not enabled.');
        }

        if (!$this->hasRequiredScopes($shopDomain)) {
            throw new \Exception('Missing required scopes for product filtering.');
        }

        $results = collect();

        // Search by vendor in DB
        if (isset($searchCriteria['vendor'])) {
            $vendorResults = $this->searchByVendorInDB($shopDomain, $searchCriteria['vendor']);
            $results = $results->merge($vendorResults);
        }

        // Search by title in DB
        if (isset($searchCriteria['title'])) {
            $titleResults = $this->searchByTitleInDB($shopDomain, $searchCriteria['title']);
            $results = $results->merge($titleResults);
        }

        // Search by collection - fetch from GraphQL and update queried collections
        if (isset($searchCriteria['collection_id'])) {
            $collectionResults = $this->fetchProductsFromCollection($shopDomain, $searchCriteria['collection_id']);
            $results = $results->merge($collectionResults);
        }

        // Apply filters to all results
        if (!empty($filters)) {
            $results = $results->filter(function ($product) use ($filters) {
                return $this->applyFilters($product, $filters);
            });
        }

        return $results->unique('product_id');
    }

    protected function fetchAndStoreProduct($shopDomain, $productId, $filters = [])
    {
        try {
            $user = $this->getUserByShopDomain($shopDomain);
            if (!$user) {
                throw new \Exception("User not found for shop domain: {$shopDomain}");
            }

            $productGid = $this->ensureGidFormat($productId);
            $query = config('shopify-enhanced.queries.product.details');

            $response = $this->graphqlService->execute($shopDomain, $query, [
                'id' => $productGid
            ]);

            if (!isset($response['product']) || !$response['product']) {
                throw new \Exception("Product not found: {$productId}");
            }

            $productData = $response['product'];

            // Check collection membership if needed
            $collectionIds = [];
            if (isset($productData['collections']['edges'])) {
                $edges = $productData['collections']['edges'];
                if (is_array($edges) || $edges instanceof \ArrayAccess) {
                    $collectionIds = array_map(function ($edge) {
                        return $this->normalizeProductId($edge['node']['id']);
                    }, is_array($edges) ? $edges : $edges->toArray());
                }
            }

            // Prepare data for storage
            $storeData = [
                'product_id' => $this->normalizeProductId($productData['id']),
                'user_id' => $user->id,
                'shop_domain' => $shopDomain,
                'title' => $productData['title'],
                'handle' => $productData['handle'],
                'vendor' => $productData['vendor'] ?? null,
                'product_type' => $productData['productType'] ?? null,
                'status' => strtolower($productData['status']),
                'collection_ids' => $collectionIds,
                'queried_collection_ids' => [], // Will be set by webhook when queried for specific collections
                'tags' => $productData['tags'] ?? [],
                'last_accessed_at' => now(),
                'shopify_updated_at' => $productData['updatedAt'] ? Carbon::parse($productData['updatedAt']) : null,
            ];

            // Add inventory data if enabled and available
            if ($this->shouldIncludeInventoryData() && isset($productData['variants']['edges'][0])) {
                $variant = $productData['variants']['edges'][0]['node'];
                $storeData = array_merge($storeData, [
                    'price' => $variant['price'] ? (int) ($variant['price'] * 100) : null,
                    'compare_at_price' => $variant['compareAtPrice'] ? (int) ($variant['compareAtPrice'] * 100) : null,
                    'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                    'weight' => $variant['weight'] ?? null,
                ]);
            }

            $product = ShopifyProduct::updateOrCreate(
                [
                    'shop_domain' => $shopDomain,
                    'product_id' => $storeData['product_id']
                ],
                $storeData
            );

            return $this->applyFilters($product, $filters);

        } catch (\Exception $e) {
            Log::error("Failed to fetch and store product: " . $e->getMessage(), [
                'shop_domain' => $shopDomain,
                'product_id' => $productId,
                'filters' => $filters
            ]);
            throw $e;
        }
    }

    protected function searchByVendorInDB($shopDomain, $vendor)
    {
        // Search only in local database - no GraphQL filtering
        $cachedProducts = ShopifyProduct::byShop($shopDomain)
            ->byVendor($vendor)
            ->get();

        // Update last accessed for all cached products
        $cachedProducts->each(fn($product) => $product->updateLastAccessed());

        return $cachedProducts;
    }

    protected function searchByTitleInDB($shopDomain, $title)
    {
        // Search only in local database - no GraphQL filtering
        $cachedProducts = ShopifyProduct::byShop($shopDomain)
            ->titleContains($title)
            ->get();

        // Update last accessed for all cached products
        $cachedProducts->each(fn($product) => $product->updateLastAccessed());

        return $cachedProducts;
    }

    protected function fetchProductsFromCollection($shopDomain, $collectionId)
    {
        try {
            $user = $this->getUserByShopDomain($shopDomain);
            if (!$user) {
                throw new \Exception("User not found for shop domain: {$shopDomain}");
            }

            $collectionGid = $this->ensureCollectionGidFormat($collectionId);
            $normalizedCollectionId = $this->normalizeProductId($collectionId);

            // First check cached products for this collection
            $cachedProducts = ShopifyProduct::byShop($shopDomain)
                ->queriedForCollection($normalizedCollectionId)
                ->get();

            if ($cachedProducts->isNotEmpty()) {
                // Update last accessed for cached products
                $cachedProducts->each(fn($product) => $product->updateLastAccessed());

                // Still dispatch background job to update other existing products
                $cachedProductIds = $cachedProducts->pluck('product_id')->toArray();
                UpdateExistingProductsForCollectionJob::dispatch(
                    $shopDomain,
                    $normalizedCollectionId,
                    $cachedProductIds
                );

                return $cachedProducts;
            }

            // Use query from config
            $query = config('shopify-enhanced.queries.product.collection_products');

            $response = $this->graphqlService->execute($shopDomain, $query, [
                'id' => $collectionGid,
                'cursor' => null,
                'pageSize' => 250
            ]);

            $products = collect();

            if (isset($response['collection']['products']['edges'])) {
                $fetchedProductIds = [];

                foreach ($response['collection']['products']['edges'] as $edge) {
                    $productData = $edge['node'];

                    $product = $this->storeOrUpdateProduct($shopDomain, $productData, $user->id);

                    // Mark this product as queried for this collection
                    $product->addQueriedCollection($normalizedCollectionId);

                    $products->push($product);
                    $fetchedProductIds[] = $product->product_id;
                }

                // Dispatch background job to update OTHER existing products in our DB
                UpdateExistingProductsForCollectionJob::dispatch(
                    $shopDomain,
                    $normalizedCollectionId,
                    $fetchedProductIds
                );
            }

            return $products;

        } catch (\Exception $e) {
            Log::error("Failed to fetch products from collection: " . $e->getMessage(), [
                'shop_domain' => $shopDomain,
                'collection_id' => $collectionId
            ]);
            throw $e;
        }
    }

    protected function storeOrUpdateProduct($shopDomain, $productData, $userId)
    {
        $collectionIds = [];
        if (isset($productData['collections']['edges'])) {
            $edges = $productData['collections']['edges'];
            if (is_array($edges) || $edges instanceof \ArrayAccess) {
                $collectionIds = array_map(function ($collectionEdge) {
                    return $this->normalizeProductId($collectionEdge['node']['id']);
                }, is_array($edges) ? $edges : $edges->toArray());
            }
        }

        $storeData = [
            'product_id' => $this->normalizeProductId($productData['id']),
            'user_id' => $userId,
            'shop_domain' => $shopDomain,
            'title' => $productData['title'],
            'handle' => $productData['handle'],
            'vendor' => $productData['vendor'] ?? null,
            'product_type' => $productData['productType'] ?? null,
            'status' => strtolower($productData['status']),
            'collection_ids' => $collectionIds,
            'queried_collection_ids' => [], // Will be managed separately
            'tags' => $productData['tags'] ?? [],
            'last_accessed_at' => now(),
            'shopify_updated_at' => $productData['updatedAt'] ? Carbon::parse($productData['updatedAt']) : null,
        ];

        // Add inventory data if enabled
        if ($this->shouldIncludeInventoryData() && isset($productData['variants']['edges'][0])) {
            $variant = $productData['variants']['edges'][0]['node'];
            $storeData = array_merge($storeData, [
                'price' => $variant['price'] ? (int) ($variant['price'] * 100) : null,
                'compare_at_price' => $variant['compareAtPrice'] ? (int) ($variant['compareAtPrice'] * 100) : null,
                'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                'weight' => $variant['weight'] ?? null,
            ]);
        }

        return ShopifyProduct::updateOrCreate(
            [
                'shop_domain' => $shopDomain,
                'product_id' => $storeData['product_id']
            ],
            $storeData
        );
    }

    protected function applyFilters($product, $filters)
    {
        if (empty($filters)) {
            return $product;
        }

        // Filter by collection
        if (isset($filters['collection_id']) && !$product->isInCollection($filters['collection_id'])) {
            return null;
        }

        // Filter by tag
        if (isset($filters['tag']) && !$product->hasTag($filters['tag'])) {
            return null;
        }

        // Filter by status
        if (isset($filters['status']) && $product->status !== $filters['status']) {
            return null;
        }

        // Filter by product type
        if (isset($filters['product_type']) && $product->product_type !== $filters['product_type']) {
            return null;
        }

        // Filter by vendor
        if (isset($filters['vendor']) && $product->vendor !== $filters['vendor']) {
            return null;
        }

        return $product;
    }

    public function checkProductInCollections($shopDomain, $productId, $collectionIds)
    {
        try {
            $productGid = $this->ensureGidFormat($productId);
            $collectionGids = array_map([$this, 'ensureGidFormat'], (array) $collectionIds);

            $query = config('shopify-enhanced.queries.product.check_collection_membership');

            $response = $this->graphqlService->execute($shopDomain, $query, [
                'productId' => $productGid,
                'collectionIds' => $collectionGids
            ]);

            $result = [
                'product_id' => $productId,
                'collections' => [],
                'is_in_collections' => []
            ];

            if (isset($response['product']['collections']['edges'])) {
                $edges = $response['product']['collections']['edges'];
                $productCollectionIds = [];
                if (is_array($edges) || $edges instanceof \ArrayAccess) {
                    $productCollectionIds = array_map(function ($edge) {
                        return $this->normalizeProductId($edge['node']['id']);
                    }, is_array($edges) ? $edges : $edges->toArray());
                }

                $result['collections'] = $productCollectionIds;

                foreach ($collectionIds as $collectionId) {
                    $normalizedCollectionId = $this->normalizeProductId($collectionId);
                    $result['is_in_collections'][$normalizedCollectionId] = in_array($normalizedCollectionId, $productCollectionIds);
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to check product collection membership: " . $e->getMessage(), [
                'shop_domain' => $shopDomain,
                'product_id' => $productId,
                'collection_ids' => $collectionIds
            ]);
            throw $e;
        }
    }

    public function cleanupOldProducts()
    {
        $ttlDays = config('shopify-enhanced.product_filter.cache_ttl_days', 30);

        $deletedCount = ShopifyProduct::olderThan($ttlDays)->delete();

        Log::info("Cleaned up {$deletedCount} old products", [
            'ttl_days' => $ttlDays,
            'deleted_count' => $deletedCount
        ]);

        return $deletedCount;
    }

    protected function isFeatureEnabled(): bool
    {
        return config('shopify-enhanced.product_filter.enabled', false);
    }

    protected function hasRequiredScopes($shopDomain): bool
    {
        $user = $this->getUserByShopDomain($shopDomain);
        if (!$user) {
            return false;
        }

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

    protected function shouldIncludeInventoryData(): bool
    {
        return config('shopify-enhanced.product_filter.include_inventory_data', false);
    }

    protected function getUserByShopDomain($shopDomain)
    {
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        return $userModel::where('name', $shopDomain)->first();
    }

    protected function normalizeProductId($id)
    {
        if (str_starts_with($id, 'gid://')) {
            return last(explode('/', $id));
        }
        return $id;
    }

    protected function ensureGidFormat($id)
    {
        if (str_starts_with($id, 'gid://')) {
            return $id;
        }

        return "gid://shopify/Product/{$id}";
    }

    protected function ensureCollectionGidFormat($id)
    {
        if (str_starts_with($id, 'gid://')) {
            return $id;
        }

        return "gid://shopify/Collection/{$id}";
    }
}