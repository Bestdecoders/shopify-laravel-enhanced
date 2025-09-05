<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Bestdecoders\ShopifyLaravelEnhanced\Helpers\Queries;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ShopifyGraphqlService;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ThanksMail;

class ShopifyController extends Controller
{
    protected $size = 10;
    
    public function test()
    {
        return response()->json(['message' => 'Shopify Enhanced Package is working!']);
    }

    public function getShopInfo($shop)
    {
        $query = Queries::getQuery(config('shopify-enhanced.shop_query'));
        $service = app(ShopifyGraphqlService::class);

        $shopInfo = $service->execute($shop, $query);

        return response()->json([
            'name' => $shopInfo['shop']['name'] ?? null,
            'email' => $shopInfo['shop']['email'] ?? null,
            'primaryDomain' => $shopInfo['shop']['primaryDomain']['url'] ?? null,
        ]);
    }

    public function sendThanksEmail($shop)
    {
        $query = config('shopify-enhanced.queries.shop');
        $service = app(ShopifyGraphqlService::class);

        $shopInfo = $service->execute($shop, $query);

        if (isset($shopInfo['shop']['email'])) {
            Mail::to($shopInfo['shop']['email'])->send(new ThanksMail($shopInfo['shop']));
            return response()->json(['message' => 'Thanks email sent!']);
        }

        return response()->json(['message' => 'Shop email not found.'], 404);
    }

    public function getPlanDetails()
    {
        $shop = auth()->user();
        $current_plan = $shop->plan;
        
        // Get plans using the configured model from Shopify package
        $planModel = config('shopify-app.models.plan', \Osiset\ShopifyApp\Storage\Models\Plan::class);
        $plans = $planModel::where('active', true)->orderBy('price')->get();
        
        return response()->json([
            'current_plan' => $current_plan,
            'plans' => $plans
        ]);
    }

    public function getPlanSubscriptionUrl(Request $request)
    {
        $plan_id = $request->input('planId');
        $shop = $request->input('shop');

        $redirectUrl = route(
            \Osiset\ShopifyApp\Util::getShopifyConfig('route_names.billing'),
            [
                'shop' => $shop,
                'host' => $request->get('host'),
                'plan' => $plan_id,
            ]
        );

        return response()->json(['forceRedirectUrl' => $redirectUrl], 200);
    }


    public function getProductList(Request $request)
    {
        $query = '';
        if ($request->has('query')) {
            $query = (string)$request->input('query');
        }
        $shop = $request->input('shop');

        $direction = 'next';
        if ($request->has('direction')) {
            $direction = $request->input('direction');
        }
        $cursor = null;
        if ($request->has('cursor')) {
            $cursor = $request->input('cursor');
        }

        if ($request->has('size')) {
            $this->size = $request->input('size');
        }

        $selectedItems = $request->input('selectedItems', []);
        
        // If we have selected items and this is the first load (no cursor), prioritize selected items
        if (!empty($selectedItems) && $cursor === null) {
            $selectedProducts = [];
            
            // Fetch metadata for each selected product
            foreach ($selectedItems as $productId) {
                $productGid = "gid://shopify/Product/{$productId}";
                
                try {
                    $product = getProductById($shop, $productGid);
                    if ($product) {
                        // Transform product data to match expected GraphQL edge format
                        $selectedProducts[] = [
                            'node' => $product,
                            'cursor' => 'selected_' . $productId
                        ];
                    }
                } catch (\Exception $e) {
                    // Log error but continue processing other products
                    Log::warning("Failed to fetch selected product {$productId}: " . $e->getMessage());
                }
            }
            
            // Create exclude query to prevent duplicate selected items in regular results
            $excludeIds = implode(' OR ', array_map(function($id) {
                return "-id:{$id}";
            }, $selectedItems));
            $excludeQuery = $query ? "{$query} AND ({$excludeIds})" : "({$excludeIds})";
            
            // Get regular products, excluding selected ones and adjusting page size
            $remainingPageSize = max(1, $this->size - count($selectedProducts));
            $regularProducts = getProducts($shop, $excludeQuery, $direction, $cursor, $remainingPageSize);
            
            // Convert ResponseAccess to array if needed
            $regularProductsArray = is_array($regularProducts) ? $regularProducts : $regularProducts->toArray();
            
            // Merge selected products at the beginning of the edges array
            $regularProductsArray['edges'] = array_merge($selectedProducts, $regularProductsArray['edges'] ?? []);
            
            return $regularProductsArray;
        }
        
        // For subsequent loads (with cursor) or when no selected items, use regular flow
        return getProducts($shop, $query, $direction, $cursor, $this->size);
    }

    public function  getCollectionList(Request $request)
    {
        $query = '';

        if ($request->has('query')) {
            $query = (string)$request->input('query');
        }
        $shop = $request->input('shop');

        $direction = 'next';
        if ($request->has('direction')) {
            $direction = $request->input('direction');
        }
        $cursor = null;
        if ($request->has('cursor')) {
            $cursor = $request->input('cursor');
        }

        if ($request->has('size')) {
            $this->size = $request->input('size');
        }
        return  getCollections($shop, $query, $direction, $cursor, $this->size);
    }

    /**
     * Get app settings for the authenticated shop
     */
    public function getSettings(Request $request)
    {
        try {
            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            // Get settings from cache or database
            $settings = Cache::get("app_settings_{$shopDomain}", [
                'appName' => 'Size Chart App',
                'appDescription' => 'Professional size chart solution for your store',
                'primaryColor' => '#000000',
                'secondaryColor' => '#ffffff',
                'fontSize' => '14',
                'isEnabled' => true,
                'displayPosition' => 'bottom',
                'animationSpeed' => '300',
                'showOnMobile' => true,
                'autoHide' => false,
                'customCSS' => '',
                'selectedProducts' => [],
                'selectedCollections' => [],
                'targetingType' => 'all'
            ]);
            
            return response()->json($settings);
        } catch (\Exception $e) {
            Log::error('Error fetching settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch settings'], 500);
        }
    }

    /**
     * Get data items for the authenticated shop
     */
    public function getDataItems(Request $request, $entityType = 'items')
    {
        try {
            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            // Get items from cache (in production, you might want to use database)
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            // Apply filters if provided
            $filters = $request->only(['status', 'search', 'tags']);
            if (!empty($filters)) {
                $items = $this->filterItems($items, $filters);
            }
            
            return response()->json($items);
        } catch (\Exception $e) {
            Log::error("Error fetching {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to fetch {$entityType}"], 500);
        }
    }

    /**
     * Create a new data item
     */
    public function createDataItem(Request $request, $entityType = 'items')
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive,draft',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $itemData = $request->all();
            $itemData['id'] = uniqid();
            $itemData['created_at'] = now()->toISOString();
            $itemData['updated_at'] = now()->toISOString();
            
            // Get existing items
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            // Add new item
            array_unshift($items, $itemData);
            
            // Save back to cache
            Cache::put($cacheKey, $items, now()->addDays(30));
            
            return response()->json($itemData, 201);
        } catch (\Exception $e) {
            Log::error("Error creating {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to create {$entityType}"], 500);
        }
    }

    /**
     * Update a data item
     */
    public function updateDataItem(Request $request, $entityType, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive,draft',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            // Find and update item
            $itemIndex = array_search($id, array_column($items, 'id'));
            if ($itemIndex === false) {
                return response()->json(['error' => 'Item not found'], 404);
            }
            
            $items[$itemIndex] = array_merge($items[$itemIndex], $request->all());
            $items[$itemIndex]['updated_at'] = now()->toISOString();
            
            Cache::put($cacheKey, $items, now()->addDays(30));
            
            return response()->json($items[$itemIndex]);
        } catch (\Exception $e) {
            Log::error("Error updating {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to update {$entityType}"], 500);
        }
    }

    /**
     * Delete data items
     */
    public function deleteDataItems(Request $request, $entityType)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            $idsToDelete = $request->input('ids');
            $items = array_filter($items, function($item) use ($idsToDelete) {
                return !in_array($item['id'], $idsToDelete);
            });
            
            Cache::put($cacheKey, array_values($items), now()->addDays(30));
            
            return response()->json(['message' => 'Items deleted successfully']);
        } catch (\Exception $e) {
            Log::error("Error deleting {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to delete {$entityType}"], 500);
        }
    }

    /**
     * Activate data items
     */
    public function activateDataItems(Request $request, $entityType)
    {
        return $this->updateItemsStatus($request, $entityType, 'active');
    }

    /**
     * Deactivate data items
     */
    public function deactivateDataItems(Request $request, $entityType)
    {
        return $this->updateItemsStatus($request, $entityType, 'inactive');
    }

    /**
     * Duplicate a data item
     */
    public function duplicateDataItem(Request $request, $entityType, $id)
    {
        try {
            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            // Find item to duplicate
            $originalItem = null;
            foreach ($items as $item) {
                if ($item['id'] === $id) {
                    $originalItem = $item;
                    break;
                }
            }
            
            if (!$originalItem) {
                return response()->json(['error' => 'Item not found'], 404);
            }
            
            // Create duplicate
            $duplicate = $originalItem;
            $duplicate['id'] = uniqid();
            $duplicate['title'] = $originalItem['title'] . ' (Copy)';
            $duplicate['created_at'] = now()->toISOString();
            $duplicate['updated_at'] = now()->toISOString();
            $duplicate['status'] = 'draft'; // New duplicates start as draft
            
            // Add to items
            array_unshift($items, $duplicate);
            Cache::put($cacheKey, $items, now()->addDays(30));
            
            return response()->json($duplicate, 201);
        } catch (\Exception $e) {
            Log::error("Error duplicating {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to duplicate {$entityType}"], 500);
        }
    }

    /**
     * Export data items
     */
    public function exportDataItems(Request $request, $entityType)
    {
        try {
            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            $format = $request->input('format', 'csv');
            $selectedIds = $request->input('selected_ids');
            
            if ($selectedIds) {
                $selectedIds = explode(',', $selectedIds);
                $items = array_filter($items, function($item) use ($selectedIds) {
                    return in_array($item['id'], $selectedIds);
                });
            }
            
            $filename = "{$entityType}_export_" . date('Y-m-d_H-i-s') . ".{$format}";
            
            if ($format === 'json') {
                return response()->json($items)
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('Content-Type', 'application/json');
            } else {
                // CSV export
                $csv = $this->arrayToCsv($items);
                return response($csv)
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('Content-Type', 'text/csv');
            }
        } catch (\Exception $e) {
            Log::error("Error exporting {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to export {$entityType}"], 500);
        }
    }

    /**
     * Import data items
     */
    public function importDataItems(Request $request, $entityType)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,json,xlsx,xls|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            $importedItems = [];
            
            if (in_array($extension, ['csv'])) {
                $importedItems = $this->parseCsvFile($file);
            } elseif ($extension === 'json') {
                $content = file_get_contents($file->getRealPath());
                $importedItems = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON format');
                }
            }
            
            if (empty($importedItems)) {
                return response()->json(['error' => 'No valid data found in file'], 422);
            }
            
            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            // Process and validate imported items
            $validItems = [];
            foreach ($importedItems as $item) {
                if (isset($item['title']) && !empty($item['title'])) {
                    $item['id'] = uniqid();
                    $item['created_at'] = now()->toISOString();
                    $item['updated_at'] = now()->toISOString();
                    $item['status'] = $item['status'] ?? 'draft';
                    $validItems[] = $item;
                }
            }
            
            if (empty($validItems)) {
                return response()->json(['error' => 'No valid items found in import file'], 422);
            }
            
            // Add to existing items
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $existingItems = Cache::get($cacheKey, []);
            $items = array_merge($validItems, $existingItems);
            
            Cache::put($cacheKey, $items, now()->addDays(30));
            
            return response()->json([
                'message' => 'Import completed successfully',
                'count' => count($validItems),
                'total' => count($items)
            ]);
        } catch (\Exception $e) {
            Log::error("Error importing {$entityType}: " . $e->getMessage());
            return response()->json(['error' => "Failed to import {$entityType}: " . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to update items status
     */
    private function updateItemsStatus(Request $request, $entityType, $status)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            $cacheKey = "data_{$entityType}_{$shopDomain}";
            $items = Cache::get($cacheKey, []);
            
            $idsToUpdate = $request->input('ids');
            foreach ($items as &$item) {
                if (in_array($item['id'], $idsToUpdate)) {
                    $item['status'] = $status;
                    $item['updated_at'] = now()->toISOString();
                }
            }
            
            Cache::put($cacheKey, $items, now()->addDays(30));
            
            return response()->json(['message' => 'Items updated successfully']);
        } catch (\Exception $e) {
            Log::error("Error updating {$entityType} status: " . $e->getMessage());
            return response()->json(['error' => "Failed to update {$entityType} status"], 500);
        }
    }

    /**
     * Helper method to filter items
     */
    private function filterItems($items, $filters)
    {
        if (isset($filters['status']) && !empty($filters['status'])) {
            $items = array_filter($items, function($item) use ($filters) {
                return $item['status'] === $filters['status'];
            });
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $items = array_filter($items, function($item) use ($search) {
                return strpos(strtolower($item['title']), $search) !== false ||
                       strpos(strtolower($item['description'] ?? ''), $search) !== false;
            });
        }
        
        return array_values($items);
    }

    /**
     * Helper method to convert array to CSV
     */
    private function arrayToCsv($items)
    {
        if (empty($items)) {
            return '';
        }
        
        $csv = '';
        $headers = array_keys($items[0]);
        $csv .= implode(',', $headers) . "\n";
        
        foreach ($items as $item) {
            $row = [];
            foreach ($headers as $header) {
                $value = $item[$header] ?? '';
                // Escape commas and quotes in CSV
                if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
                $row[] = $value;
            }
            $csv .= implode(',', $row) . "\n";
        }
        
        return $csv;
    }

    /**
     * Helper method to parse CSV file
     */
    private function parseCsvFile($file)
    {
        $items = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $items[] = array_combine($headers, $row);
                }
            }
            
            fclose($handle);
        }
        
        return $items;
    }

    /**
     * Save app settings for the authenticated shop
     */
    public function saveSettings(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'appName' => 'required|string|min:2|max:100',
                'appDescription' => 'required|string|min:10|max:500',
                'primaryColor' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                'secondaryColor' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                'fontSize' => 'required|integer|min:10|max:30',
                'isEnabled' => 'required|boolean',
                'displayPosition' => 'required|in:top,bottom,before-cart,after-cart',
                'animationSpeed' => 'required|integer|min:100|max:2000',
                'showOnMobile' => 'required|boolean',
                'autoHide' => 'required|boolean',
                'customCSS' => 'nullable|string|max:10000',
                'selectedProducts' => 'array',
                'selectedProducts.*.id' => 'required|string',
                'selectedProducts.*.title' => 'required|string',
                'selectedCollections' => 'array',
                'selectedCollections.*.id' => 'required|string',
                'selectedCollections.*.title' => 'required|string',
                'targetingType' => 'required|in:all,products,collections,custom'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shop = auth()->user();
            $shopDomain = $shop->name;
            
            // Save settings to cache (in production, you might want to use database)
            $settings = $request->all();
            Cache::put("app_settings_{$shopDomain}", $settings, now()->addDays(30));
            
            // Log the settings update
            Log::info('Settings updated for shop: ' . $shopDomain, [
                'settings' => Arr::except($settings, ['customCSS']) // Don't log CSS for brevity
            ]);
            
            return response()->json([
                'message' => 'Settings saved successfully',
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save settings'], 500);
        }
    }


}
