<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class CustomWebhookHandlerTest extends TestCase
{

    /**
     * Create a mock request with given data
     */
    private function createMockRequest(array $data): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Shopify-Shop-Domain', $data['shop_domain'] ?? 'test-shop.myshopify.com');
        return $request;
    }

    /**
     * Test custom webhook handler extension
     */
    public function test_custom_webhook_handler_can_extend_base_functionality()
    {
        // Create a custom handler that extends the base handler
        $customHandler = new class extends WebhookHandlerService {
            public function handleOrderCreated(Request $request): array
            {
                // Custom logic
                $orderId = $request->input('id');
                $customProcessing = "Custom processing for order: {$orderId}";
                
                // Call parent to maintain base functionality
                $baseResult = parent::handleOrderCreated($request);
                
                return array_merge($baseResult, [
                    'custom_processing' => $customProcessing,
                    'enhanced' => true,
                    'custom_handler_used' => true
                ]);
            }
        };

        // Test the custom handler
        $request = $this->createMockRequest([
            'id' => 123456,
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $result = $customHandler->handleOrderCreated($request);

        // Assert base functionality is maintained
        $this->assertEquals('order_created', $result['action']);
        $this->assertEquals(123456, $result['order_id']);
        $this->assertEquals('test-shop.myshopify.com', $result['shop']);

        // Assert custom functionality is added
        $this->assertTrue($result['enhanced']);
        $this->assertTrue($result['custom_handler_used']);
        $this->assertEquals('Custom processing for order: 123456', $result['custom_processing']);
    }

    /**
     * Test custom handler can completely override methods
     */
    public function test_custom_handler_can_completely_override_methods()
    {
        // Create a custom handler that completely overrides a method
        $customHandler = new class extends WebhookHandlerService {
            public function handleProductUpdated(Request $request): array
            {
                // Completely custom implementation
                return [
                    'action' => 'custom_product_updated',
                    'message' => 'Completely custom product update handling',
                    'product_id' => $request->input('id'),
                    'custom_timestamp' => now()->format('Y-m-d H:i:s'),
                    'handler' => static::class,
                    'override' => true
                ];
            }
        };

        $request = $this->createMockRequest([
            'id' => 789012,
            'title' => 'Test Product',
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $result = $customHandler->handleProductUpdated($request);

        // Assert completely custom behavior
        $this->assertEquals('custom_product_updated', $result['action']);
        $this->assertEquals('Completely custom product update handling', $result['message']);
        $this->assertTrue($result['override']);
        $this->assertArrayNotHasKey('processed_at', $result); // Base field not present
        $this->assertArrayHasKey('custom_timestamp', $result); // Custom field present
    }

    /**
     * Test custom handler can add validation logic
     */
    public function test_custom_handler_can_add_validation_logic()
    {
        // Create a custom handler with additional validation
        $customHandler = new class extends WebhookHandlerService {
            public function validateWebhook(Request $request): bool
            {
                // First run the base validation
                if (!parent::validateWebhook($request)) {
                    return false;
                }
                
                // Add custom validation
                $shopDomain = $request->header('X-Shopify-Shop-Domain');
                if (!$shopDomain || !str_ends_with($shopDomain, '.myshopify.com')) {
                    return false;
                }
                
                // Additional IP whitelist check (mock)
                $clientIp = $request->ip();
                $allowedIps = ['127.0.0.1', '::1'];
                if (!in_array($clientIp, $allowedIps)) {
                    return false;
                }
                
                return true;
            }
        };

        // Test with valid shop domain
        $request = $this->createMockRequest(['test' => 'data']);
        $request->headers->set('X-Shopify-Shop-Domain', 'valid-shop.myshopify.com');
        
        $result = $customHandler->validateWebhook($request);
        $this->assertTrue($result);

        // Test with invalid shop domain
        $request->headers->set('X-Shopify-Shop-Domain', 'invalid-shop.com');
        $result = $customHandler->validateWebhook($request);
        $this->assertFalse($result);
    }

    /**
     * Test custom handler can implement business-specific logic
     */
    public function test_custom_handler_can_implement_business_logic()
    {
        // Create a custom handler with business-specific logic
        $customHandler = new class extends WebhookHandlerService {
            private array $processedOrders = [];
            
            public function handleOrderCreated(Request $request): array
            {
                $orderId = $request->input('id');
                $orderTotal = (float) $request->input('total_price', 0);
                
                // Business logic: High-value order processing
                $isHighValue = $orderTotal >= 100.00;
                $priority = $isHighValue ? 'high' : 'normal';
                
                // Track processed orders
                $this->processedOrders[] = $orderId;
                
                // Mock business operations
                $businessOperations = [];
                if ($isHighValue) {
                    $businessOperations[] = 'notify_vip_team';
                    $businessOperations[] = 'expedite_shipping';
                }
                $businessOperations[] = 'update_inventory';
                $businessOperations[] = 'send_confirmation';
                
                $baseResult = parent::handleOrderCreated($request);
                
                return array_merge($baseResult, [
                    'business_priority' => $priority,
                    'high_value_order' => $isHighValue,
                    'operations_triggered' => $businessOperations,
                    'total_processed_today' => count($this->processedOrders),
                    'business_logic_applied' => true
                ]);
            }
        };

        // Test normal value order
        $normalRequest = $this->createMockRequest([
            'id' => 123,
            'total_price' => '45.99',
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $normalResult = $customHandler->handleOrderCreated($normalRequest);
        
        $this->assertEquals('normal', $normalResult['business_priority']);
        $this->assertFalse($normalResult['high_value_order']);
        $this->assertContains('update_inventory', $normalResult['operations_triggered']);
        $this->assertNotContains('notify_vip_team', $normalResult['operations_triggered']);

        // Test high value order
        $highValueRequest = $this->createMockRequest([
            'id' => 456,
            'total_price' => '299.99',
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $highValueResult = $customHandler->handleOrderCreated($highValueRequest);
        
        $this->assertEquals('high', $highValueResult['business_priority']);
        $this->assertTrue($highValueResult['high_value_order']);
        $this->assertContains('notify_vip_team', $highValueResult['operations_triggered']);
        $this->assertContains('expedite_shipping', $highValueResult['operations_triggered']);
        $this->assertEquals(2, $highValueResult['total_processed_today']); // Second order
    }

    /**
     * Test custom handler can implement GDPR-specific logic
     */
    public function test_custom_handler_can_implement_gdpr_logic()
    {
        // Create a custom handler with GDPR-specific logic
        $customHandler = new class extends WebhookHandlerService {
            public function handleCustomerDataErasure(Request $request): array
            {
                $customerId = $request->input('customer.id');
                $customerEmail = $request->input('customer.email');
                
                // Mock GDPR erasure operations
                $erasureOperations = [
                    'database_records_anonymized',
                    'customer_files_deleted',
                    'backup_data_purged',
                    'analytics_data_anonymized',
                    'marketing_lists_updated'
                ];
                
                // Mock audit log
                $auditLog = [
                    'customer_id' => $customerId,
                    'email' => $customerEmail,
                    'erasure_timestamp' => now()->toISOString(),
                    'operations_completed' => count($erasureOperations),
                    'compliance_verified' => true
                ];
                
                $baseResult = parent::handleCustomerDataErasure($request);
                
                return array_merge($baseResult, [
                    'erasure_operations' => $erasureOperations,
                    'audit_log' => $auditLog,
                    'retention_period_respected' => true,
                    'gdpr_article_17_compliant' => true, // Right to be forgotten
                    'custom_gdpr_processing' => true
                ]);
            }
        };

        $request = $this->createMockRequest([
            'shop_domain' => 'test-shop.myshopify.com',
            'customer' => [
                'id' => 555666,
                'email' => 'customer@example.com'
            ]
        ]);

        $result = $customHandler->handleCustomerDataErasure($request);

        // Assert base GDPR compliance is maintained
        $this->assertEquals('customer_data_erasure', $result['action']);
        $this->assertTrue($result['gdpr_compliant']);

        // Assert custom GDPR logic is applied
        $this->assertTrue($result['custom_gdpr_processing']);
        $this->assertTrue($result['gdpr_article_17_compliant']);
        $this->assertTrue($result['retention_period_respected']);
        $this->assertCount(5, $result['erasure_operations']);
        $this->assertArrayHasKey('audit_log', $result);
        $this->assertTrue($result['audit_log']['compliance_verified']);
    }

    /**
     * Test custom handler can implement error handling
     */
    public function test_custom_handler_can_implement_error_handling()
    {
        // Create a custom handler with error handling
        $customHandler = new class extends WebhookHandlerService {
            public function handleOrderCreated(Request $request): array
            {
                try {
                    $orderId = $request->input('id');
                    
                    // Simulate a business operation that might fail
                    if ($orderId === 999) {
                        throw new \Exception('Simulated business logic error');
                    }
                    
                    // Normal processing
                    $baseResult = parent::handleOrderCreated($request);
                    
                    return array_merge($baseResult, [
                        'error_handling' => 'success',
                        'business_operation_completed' => true
                    ]);
                    
                } catch (\Exception $e) {
                    // Custom error handling
                    Log::error('Custom webhook handler error', [
                        'order_id' => $request->input('id'),
                        'error' => $e->getMessage()
                    ]);
                    
                    // Return error state but don't throw exception
                    return [
                        'action' => 'order_created',
                        'order_id' => $request->input('id'),
                        'shop' => $request->input('shop_domain'),
                        'error_handling' => 'failed',
                        'error_message' => $e->getMessage(),
                        'business_operation_completed' => false,
                        'fallback_applied' => true,
                        'processed_at' => now()->toISOString(),
                        'handler' => static::class
                    ];
                }
            }
        };

        // Test successful processing
        $successRequest = $this->createMockRequest([
            'id' => 123,
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $successResult = $customHandler->handleOrderCreated($successRequest);
        
        $this->assertEquals('success', $successResult['error_handling']);
        $this->assertTrue($successResult['business_operation_completed']);
        $this->assertArrayNotHasKey('fallback_applied', $successResult);

        // Test error handling
        $errorRequest = $this->createMockRequest([
            'id' => 999, // Triggers error
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $errorResult = $customHandler->handleOrderCreated($errorRequest);
        
        $this->assertEquals('failed', $errorResult['error_handling']);
        $this->assertFalse($errorResult['business_operation_completed']);
        $this->assertTrue($errorResult['fallback_applied']);
        $this->assertEquals('Simulated business logic error', $errorResult['error_message']);
    }

    /**
     * Test service container binding for custom handlers
     */
    public function test_service_container_binding_for_custom_handlers()
    {
        // Create a custom handler class
        $customHandlerClass = new class extends WebhookHandlerService {
            public function handleOrderCreated(Request $request): array
            {
                $baseResult = parent::handleOrderCreated($request);
                return array_merge($baseResult, [
                    'container_binding_test' => true,
                    'handler_type' => 'container_bound'
                ]);
            }
        };

        // Bind the custom handler in the service container
        $this->app->bind(WebhookHandlerService::class, function () use ($customHandlerClass) {
            return $customHandlerClass;
        });

        // Resolve from container
        $handler = $this->app->make(WebhookHandlerService::class);
        
        $request = $this->createMockRequest([
            'id' => 123,
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        $result = $handler->handleOrderCreated($request);

        // Assert the custom handler was used
        $this->assertTrue($result['container_binding_test']);
        $this->assertEquals('container_bound', $result['handler_type']);
    }
}