<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

/**
 * Custom Webhook Handler
 * 
 * This class extends the base WebhookHandlerService to provide custom webhook handling.
 * Users can override any method to implement custom business logic while maintaining
 * the mandatory compliance webhook routes served by the package.
 * 
 * Usage:
 * 1. Customize the methods below with your business logic
 * 2. Bind this class in your AppServiceProvider:
 *    $this->app->bind(WebhookHandlerService::class, CustomWebhookHandler::class);
 */
class CustomWebhookHandler extends WebhookHandlerService
{

    /**
     * Handle order creation webhook
     * 
     * Override this method to customize order processing
     * 
     * @param Request $request
     * @return array
     */
    public function handleOrderCreated(Request $request): array
    {
        $orderId = $request->input('id');
        $orderNumber = $request->input('order_number');
        $shopDomain = $request->input('shop_domain');
        
        // Custom order processing logic
        Log::info("Custom order creation handler for order: {$orderNumber}");
        
        // Example: Update inventory, send notifications, trigger fulfillment, etc.
        // $this->updateInventory($request->input('line_items'));
        // $this->sendOrderNotification($orderId);
        // $this->triggerFulfillment($orderId);
        
        $baseResult = parent::handleOrderCreated($request);
        
        return array_merge($baseResult, [
            'order_number' => $orderNumber,
            'custom_processing' => true,
            'inventory_updated' => true,
            'notifications_sent' => true
        ]);
    }

    /**
     * Handle product update webhook
     * 
     * Override this method to customize product update handling
     * 
     * @param Request $request
     * @return array
     */
    public function handleProductUpdated(Request $request): array
    {
        $productId = $request->input('id');
        $productTitle = $request->input('title');
        $shopDomain = $request->input('shop_domain');
        
        // Custom product update logic
        Log::info("Custom product update handler for product: {$productTitle}");
        
        // Example: Update search index, sync with external systems, etc.
        // $this->updateSearchIndex($productId, $request->all());
        // $this->syncWithExternalSystems($productId);
        // $this->invalidateProductCache($productId);
        
        $baseResult = parent::handleProductUpdated($request);
        
        return array_merge($baseResult, [
            'product_title' => $productTitle,
            'custom_processing' => true,
            'search_index_updated' => true,
            'cache_invalidated' => true
        ]);
    }

    /**
     * Handle customer data request webhook (GDPR)
     * 
     * IMPORTANT: This handles GDPR compliance - be careful with modifications
     * 
     * @param Request $request
     * @return array
     */
    public function handleCustomerDataRequest(Request $request): array
    {
        $customerId = $request->input('customer.id');
        $customerEmail = $request->input('customer.email');
        $shopDomain = $request->input('shop_domain');
        
        // Custom GDPR data collection logic
        Log::info("Custom GDPR data request for customer: {$customerEmail}");
        
        // Example: Collect all customer data from your custom tables
        // $customerData = $this->collectCustomerData($customerId);
        // $this->generateGDPRReport($customerData, $customerEmail);
        
        $baseResult = parent::handleCustomerDataRequest($request);
        
        return array_merge($baseResult, [
            'customer_email' => $customerEmail,
            'custom_data_collected' => true,
            'gdpr_report_generated' => true
        ]);
    }

    /**
     * Handle customer data erasure webhook (GDPR)
     * 
     * IMPORTANT: This handles GDPR compliance - be careful with modifications
     * 
     * @param Request $request
     * @return array
     */
    public function handleCustomerDataErasure(Request $request): array
    {
        $customerId = $request->input('customer.id');
        $customerEmail = $request->input('customer.email');
        $shopDomain = $request->input('shop_domain');
        
        // Custom GDPR data erasure logic
        Log::info("Custom GDPR data erasure for customer: {$customerEmail}");
        
        // Example: Delete customer data from your custom tables
        // $this->eraseCustomerData($customerId);
        // $this->anonymizeCustomerRecords($customerId);
        
        $baseResult = parent::handleCustomerDataErasure($request);
        
        return array_merge($baseResult, [
            'customer_email' => $customerEmail,
            'custom_data_erased' => true,
            'records_anonymized' => true
        ]);
    }

    /**
     * Handle shop data erasure webhook (GDPR)
     * 
     * IMPORTANT: This handles GDPR compliance - be careful with modifications
     * 
     * @param Request $request
     * @return array
     */
    public function handleShopDataErasure(Request $request): array
    {
        $shopDomain = $request->input('shop_domain');
        
        // Custom shop data erasure logic
        Log::info("Custom GDPR shop data erasure for: {$shopDomain}");
        
        // Example: Delete all shop-related data from your custom tables
        // $this->eraseShopData($shopDomain);
        // $this->cleanupShopFiles($shopDomain);
        
        $baseResult = parent::handleShopDataErasure($request);
        
        return array_merge($baseResult, [
            'custom_data_erased' => true,
            'files_cleaned' => true,
            'complete_erasure' => true
        ]);
    }

    /**
     * Handle generic/custom webhooks
     * 
     * @param Request $request
     * @param string $webhookType
     * @return array
     */
    public function handleGeneric(Request $request, string $webhookType): array
    {
        $shopDomain = $request->input('shop_domain');
        
        Log::info("Custom generic webhook handler: {$webhookType}");
        
        // Handle custom webhook types
        switch ($webhookType) {
            case 'custom_event':
                return $this->handleCustomEvent($request);
            case 'integration_sync':
                return $this->handleIntegrationSync($request);
            default:
                return parent::handleGeneric($request, $webhookType);
        }
    }

    /**
     * Custom webhook signature validation (optional override)
     * 
     * @param Request $request
     * @return bool
     */
    public function validateWebhook(Request $request): bool
    {
        // Add custom validation logic if needed
        // Example: Additional security checks, rate limiting, etc.
        
        // Always call parent validation first
        if (!parent::validateWebhook($request)) {
            return false;
        }
        
        // Add your custom validation here
        // Example: Check IP whitelist, additional headers, etc.
        
        return true;
    }

    // ===========================================
    // CUSTOM HELPER METHODS
    // ===========================================
    // Add your custom helper methods below

    /**
     * Example: Handle custom event webhook
     */
    private function handleCustomEvent(Request $request): array
    {
        // Your custom event logic here
        return [
            'action' => 'custom_event',
            'processed_at' => now()->toISOString(),
            'handler' => static::class
        ];
    }

    /**
     * Example: Handle integration sync webhook
     */
    private function handleIntegrationSync(Request $request): array
    {
        // Your integration sync logic here
        return [
            'action' => 'integration_sync',
            'processed_at' => now()->toISOString(),
            'handler' => static::class
        ];
    }

    /**
     * Example: Send welcome email to new shop
     */
    private function sendWelcomeEmail(string $shopDomain): void
    {
        // Implementation here
        Log::info("Sending welcome email to: {$shopDomain}");
    }

    /**
     * Example: Setup default settings for new shop
     */
    private function setupDefaultSettings(string $shopDomain): void
    {
        // Implementation here
        Log::info("Setting up default settings for: {$shopDomain}");
    }

    /**
     * Example: Cleanup shop data on uninstall
     */
    private function cleanupShopData(string $shopDomain): void
    {
        // Implementation here
        Log::info("Cleaning up data for: {$shopDomain}");
    }
}