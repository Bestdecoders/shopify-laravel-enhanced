<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ScopeValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductFilterController
{
    protected ProductFilterService $productFilterService;
    protected ScopeValidationService $scopeValidator;

    public function __construct(
        ProductFilterService $productFilterService,
        ScopeValidationService $scopeValidator
    ) {
        $this->productFilterService = $productFilterService;
        $this->scopeValidator = $scopeValidator;
    }

    public function checkProduct(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'collection_id' => 'nullable|string', // If checking for specific collection
        ]);

        try {
            $user = Auth::user();
            $shopDomain = $user->name;

            $product = $this->productFilterService->checkProduct(
                $shopDomain,
                $request->product_id,
                $request->only(['collection_id', 'status', 'tag', 'product_type'])
            );

            // If checking for specific collection, mark it as queried
            if ($request->collection_id && $product) {
                $product->addQueriedCollection($request->collection_id);
            }

            return response()->json([
                'success' => true,
                'product' => $product ? $product->toArray() : null,
                'found' => $product !== null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'vendor' => 'nullable|string',
            'title' => 'nullable|string',
            'collection_id' => 'nullable|string',
            'status' => 'nullable|in:active,draft,archived',
            'tag' => 'nullable|string',
            'product_type' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $shopDomain = $user->name;

            $searchCriteria = $request->only(['vendor', 'title', 'collection_id']);
            $filters = $request->only(['status', 'tag', 'product_type', 'collection_id']);

            $products = $this->productFilterService->searchProducts(
                $shopDomain,
                $searchCriteria,
                $filters
            );

            return response()->json([
                'success' => true,
                'products' => $products->toArray(),
                'count' => $products->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkCollectionMembership(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'collection_ids' => 'required|array',
            'collection_ids.*' => 'string',
        ]);

        try {
            $user = Auth::user();
            $shopDomain = $user->name;

            $result = $this->productFilterService->checkProductInCollections(
                $shopDomain,
                $request->product_id,
                $request->collection_ids
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getScopeStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            $validation = $this->scopeValidator->validateProductFilterScopes($user);

            return response()->json([
                'success' => true,
                'scope_validation' => $validation,
                'recommended_scopes' => $this->scopeValidator->getRecommendedScopes(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cleanupOldProducts(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $days = $request->input('days', 30);
            $deletedCount = $this->productFilterService->cleanupOldProducts();

            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "Cleaned up {$deletedCount} old products",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function matchProduct(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'vendor' => 'nullable|string',
            'title' => 'nullable|string',
            'collection_ids' => 'nullable|array',
            'collection_ids.*' => 'string',
        ]);

        try {
            $user = Auth::user();
            $shopDomain = $user->name;

            // Get the product first (fetch if not cached)
            $product = $this->productFilterService->checkProduct(
                $shopDomain,
                $request->product_id
            );

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'matches' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $matches = false;
            $matchReasons = [];

            // Check if matches vendor
            if ($request->vendor && $product->vendor === $request->vendor) {
                $matches = true;
                $matchReasons[] = "vendor: {$product->vendor}";
            }

            // Check if title contains search term
            if ($request->title && str_contains(strtolower($product->title), strtolower($request->title))) {
                $matches = true;
                $matchReasons[] = "title contains: {$request->title}";
            }

            // Check if belongs to any of the specified collections
            if ($request->collection_ids) {
                foreach ($request->collection_ids as $collectionId) {
                    // First check if already in collection (cached)
                    if ($product->isInCollection($collectionId)) {
                        $matches = true;
                        $matchReasons[] = "collection: {$collectionId} (cached)";
                        break;
                    }

                    // Check if we've already queried for this collection
                    if ($product->wasQueriedForCollection($collectionId)) {
                        continue; // We already checked this collection, product not in it
                    }

                    // Need to fetch collection from Shopify
                    try {
                        $collectionProducts = $this->productFilterService->fetchProductsFromCollection(
                            $shopDomain,
                            $collectionId
                        );

                        // Mark this product as queried for this collection (before checking)
                        $product->addQueriedCollection($collectionId);

                        // Check if our product is in the fetched results
                        $foundInCollection = $collectionProducts->contains(function ($collectionProduct) use ($product) {
                            return $collectionProduct->product_id === $product->product_id;
                        });

                        if ($foundInCollection) {
                            // Update the product's collection_ids to include this collection
                            $currentCollections = $product->collection_ids ?? [];
                            if (!in_array($collectionId, $currentCollections)) {
                                $currentCollections[] = $collectionId;
                                $product->update(['collection_ids' => $currentCollections]);
                            }

                            $matches = true;
                            $matchReasons[] = "collection: {$collectionId} (verified via GraphQL)";
                            break; // Found it, stop checking other collections
                        }

                    } catch (\Exception $e) {
                        // Log error but continue checking other collections
                        Log::warning("Failed to fetch collection {$collectionId}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'matches' => $matches,
                'product' => [
                    'id' => $product->product_id,
                    'title' => $product->title,
                    'vendor' => $product->vendor,
                    'collection_ids' => $product->collection_ids,
                ],
                'match_reasons' => $matchReasons,
                'checked_criteria' => [
                    'vendor' => $request->vendor,
                    'title' => $request->title,
                    'collection_ids' => $request->collection_ids,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}