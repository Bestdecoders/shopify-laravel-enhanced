<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class WebhookHandlerServiceTest extends TestCase
{
    private WebhookHandlerService $webhookHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookHandler = new WebhookHandlerService();
        
        // No configuration needed - webhooks work by default
    }


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
     * Test customer data request handler (GDPR)
     */
    public function test_handle_customer_data_request_returns_gdpr_compliant_response()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'customer' => [
                'id' => 555666,
                'email' => 'customer@example.com'
            ]
        ];
        $request = $this->createMockRequest($gdprData);

        // Act
        $result = $this->webhookHandler->handleCustomerDataRequest($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('customer_data_request', $result['action']);
        $this->assertEquals(555666, $result['customer_id']);
        $this->assertEquals('test-shop.myshopify.com', $result['shop']);
        $this->assertTrue($result['gdpr_compliant']);
        $this->assertArrayHasKey('processed_at', $result);
        $this->assertArrayHasKey('handler', $result);
    }

    /**
     * Test customer data erasure handler (GDPR)
     */
    public function test_handle_customer_data_erasure_returns_gdpr_compliant_response()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'customer' => [
                'id' => 555666,
                'email' => 'customer@example.com'
            ]
        ];
        $request = $this->createMockRequest($gdprData);

        // Act
        $result = $this->webhookHandler->handleCustomerDataErasure($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('customer_data_erasure', $result['action']);
        $this->assertEquals(555666, $result['customer_id']);
        $this->assertEquals('test-shop.myshopify.com', $result['shop']);
        $this->assertTrue($result['gdpr_compliant']);
        $this->assertArrayHasKey('processed_at', $result);
    }

    /**
     * Test shop data erasure handler (GDPR)
     */
    public function test_handle_shop_data_erasure_returns_gdpr_compliant_response()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'shop_id' => 12345
        ];
        $request = $this->createMockRequest($gdprData);

        // Act
        $result = $this->webhookHandler->handleShopDataErasure($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('shop_data_erasure', $result['action']);
        $this->assertEquals('test-shop.myshopify.com', $result['shop']);
        $this->assertTrue($result['gdpr_compliant']);
        $this->assertArrayHasKey('processed_at', $result);
    }

    /**
     * Test generic webhook handler
     */
    public function test_handle_generic_webhook_returns_expected_data()
    {
        // Arrange
        $customData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'event_type' => 'custom_sync'
        ];
        $request = $this->createMockRequest($customData);
        $webhookType = 'integration_sync';

        // Act
        $result = $this->webhookHandler->handleGeneric($request, $webhookType);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('generic_webhook', $result['action']);
        $this->assertEquals('integration_sync', $result['webhook_type']);
        $this->assertEquals('test-shop.myshopify.com', $result['shop']);
        $this->assertArrayHasKey('processed_at', $result);
    }

    /**
     * Test webhook validation works by default
     */
    public function test_validate_webhook_works_by_default()
    {
        // Arrange
        $payload = json_encode(['test' => 'data']);
        $request = Request::create('/test', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Shopify-Shop-Domain', 'test.myshopify.com');

        // Act
        $result = $this->webhookHandler->validateWebhook($request);

        // Assert - Should work by default without any configuration
        $this->assertTrue($result);
    }

    /**
     * Test GDPR handlers with null/missing data gracefully
     */
    public function test_gdpr_handlers_with_missing_data_are_graceful()
    {
        // Arrange
        $emptyRequest = $this->createMockRequest([]);

        // Act & Assert - Should not throw exceptions
        $dataRequestResult = $this->webhookHandler->handleCustomerDataRequest($emptyRequest);
        $dataErasureResult = $this->webhookHandler->handleCustomerDataErasure($emptyRequest);
        $shopErasureResult = $this->webhookHandler->handleShopDataErasure($emptyRequest);

        // Basic structure should still be present
        $this->assertArrayHasKey('action', $dataRequestResult);
        $this->assertArrayHasKey('action', $dataErasureResult);
        $this->assertArrayHasKey('action', $shopErasureResult);
        $this->assertTrue($dataRequestResult['gdpr_compliant']);
        $this->assertTrue($dataErasureResult['gdpr_compliant']);
        $this->assertTrue($shopErasureResult['gdpr_compliant']);
    }

    /**
     * Test timestamp format in GDPR responses
     */
    public function test_processed_at_timestamp_format_gdpr()
    {
        // Arrange
        $request = $this->createMockRequest(['shop_domain' => 'test.myshopify.com']);

        // Act
        $result = $this->webhookHandler->handleCustomerDataRequest($request);

        // Assert
        $this->assertArrayHasKey('processed_at', $result);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            $result['processed_at'],
            'Timestamp should be in ISO 8601 format'
        );
    }

    /**
     * Test handler class reference in GDPR responses
     */
    public function test_handler_class_reference_in_gdpr_responses()
    {
        // Arrange
        $request = $this->createMockRequest(['shop_domain' => 'test.myshopify.com']);

        // Act
        $result = $this->webhookHandler->handleCustomerDataRequest($request);

        // Assert
        $this->assertEquals(WebhookHandlerService::class, $result['handler']);
    }

    /**
     * Test logging is called during GDPR webhook processing
     */
    public function test_gdpr_webhook_processing_triggers_logging()
    {
        // Arrange
        Log::shouldReceive('info')
           ->once()
           ->with(\Mockery::pattern('/Default .* handler executed/'), \Mockery::any());

        $request = $this->createMockRequest([
            'customer' => ['id' => 123],
            'shop_domain' => 'test.myshopify.com'
        ]);

        // Act
        $this->webhookHandler->handleCustomerDataRequest($request);

        // Logging assertion is handled by the shouldReceive mock
    }

    /**
     * Test all mandatory GDPR methods return gdpr_compliant true
     */
    public function test_all_gdpr_methods_return_compliant_flag()
    {
        // Arrange
        $request = $this->createMockRequest([
            'shop_domain' => 'test.myshopify.com',
            'customer' => ['id' => 123, 'email' => 'test@example.com']
        ]);

        // Act
        $dataRequest = $this->webhookHandler->handleCustomerDataRequest($request);
        $dataErasure = $this->webhookHandler->handleCustomerDataErasure($request);
        $shopErasure = $this->webhookHandler->handleShopDataErasure($request);

        // Assert
        $this->assertTrue($dataRequest['gdpr_compliant']);
        $this->assertTrue($dataErasure['gdpr_compliant']);
        $this->assertTrue($shopErasure['gdpr_compliant']);
    }
}