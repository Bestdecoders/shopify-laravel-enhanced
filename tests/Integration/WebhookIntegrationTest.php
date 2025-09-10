<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class WebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // No configuration needed - webhooks work by default
        Config::set('app.debug', true);
    }



    /**
     * Test complete GDPR webhook flow works by default
     */
    public function test_complete_gdpr_webhook_flow_works_by_default()
    {
        // Arrange - Customer data request
        $customerData = [
            'shop_domain' => 'integration-test.myshopify.com',
            'customer' => [
                'id' => 555666777,
                'email' => 'integration@test.com',
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '+1234567890'
            ],
            'orders_requested' => [123456789, 987654321]
        ];

        // Act
        $response = $this->postJson('/webhooks/customers/data_request', $customerData, [
            'Content-Type' => 'application/json',
            'X-Shopify-Shop-Domain' => 'integration-test.myshopify.com',
            'X-Shopify-Topic' => 'customers/data_request',
            'X-Shopify-API-Version' => '2023-10',
            'X-Shopify-Webhook-Id' => 'webhook-test-id-123',
            'User-Agent' => 'Shopify-Webhook'
        ]);

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'action',
                        'customer_id',
                        'shop',
                        'processed_at',
                        'handler',
                        'gdpr_compliant'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('worked', $responseData['message']);
        $this->assertEquals('customer_data_request', $responseData['data']['action']);
        $this->assertEquals(555666777, $responseData['data']['customer_id']);
        $this->assertEquals('integration-test.myshopify.com', $responseData['data']['shop']);
        $this->assertTrue($responseData['data']['gdpr_compliant']);
    }

    /**
     * Test all GDPR webhooks work end-to-end
     */
    public function test_all_gdpr_webhooks_work_end_to_end()
    {
        $testCases = [
            [
                'route' => '/webhooks/customers/data_request',
                'data' => [
                    'shop_domain' => 'gdpr-test.myshopify.com',
                    'customer' => [
                        'id' => 555666777,
                        'email' => 'gdpr-test@example.com',
                        'phone' => '+1234567890'
                    ],
                    'orders_requested' => [123, 456, 789]
                ],
                'expectedAction' => 'customer_data_request'
            ],
            [
                'route' => '/webhooks/customers/redact',
                'data' => [
                    'shop_domain' => 'gdpr-test.myshopify.com',
                    'customer' => [
                        'id' => 555666777,
                        'email' => 'gdpr-test@example.com'
                    ],
                    'orders_to_redact' => [123, 456]
                ],
                'expectedAction' => 'customer_data_erasure'
            ],
            [
                'route' => '/webhooks/shop/redact',
                'data' => [
                    'shop_domain' => 'gdpr-test.myshopify.com',
                    'shop_id' => 12345
                ],
                'expectedAction' => 'shop_data_erasure'
            ]
        ];

        foreach ($testCases as $testCase) {
            $payload = json_encode($testCase['data']);
            $signature = $this->generateSignature($payload);

            $response = $this->postJson($testCase['route'], $testCase['data'], [
                'Content-Type' => 'application/json',
                'X-Shopify-Hmac-Sha256' => $signature,
                'X-Shopify-Shop-Domain' => 'gdpr-test.myshopify.com',
                'X-Shopify-Topic' => str_replace('/webhooks/', '', $testCase['route'])
            ]);

            $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'worked'
                    ]);

            $responseData = $response->json();
            $this->assertEquals($testCase['expectedAction'], $responseData['data']['action']);
            $this->assertTrue($responseData['data']['gdpr_compliant']);
        }
    }

    /**
     * Test custom webhook handler integration
     */
    public function test_custom_webhook_handler_integration()
    {
        // Create and bind a custom handler
        $customHandler = new class extends WebhookHandlerService {
            public function handleOrderCreated(Request $request): array
            {
                $baseResult = parent::handleOrderCreated($request);
                
                return array_merge($baseResult, [
                    'integration_test' => true,
                    'custom_logic_applied' => true,
                    'order_value' => $request->input('total_price'),
                    'line_items_count' => count($request->input('line_items', []))
                ]);
            }
        };

        $this->app->bind(WebhookHandlerService::class, function () use ($customHandler) {
            return $customHandler;
        });

        // Test the custom handler
        $orderData = [
            'id' => 999888777,
            'shop_domain' => 'custom-handler-test.myshopify.com',
            'total_price' => '149.99',
            'line_items' => [
                ['id' => 1, 'title' => 'Item 1'],
                ['id' => 2, 'title' => 'Item 2'],
                ['id' => 3, 'title' => 'Item 3']
            ]
        ];

        $payload = json_encode($orderData);
        $signature = $this->generateSignature($payload);

        $response = $this->postJson('/webhooks/orders/create', $orderData, [
            'Content-Type' => 'application/json',
            'X-Shopify-Hmac-Sha256' => $signature,
            'X-Shopify-Shop-Domain' => 'custom-handler-test.myshopify.com'
        ]);

        $response->assertStatus(200);
        $responseData = $response->json();

        // Assert base functionality
        $this->assertEquals('order_created', $responseData['data']['action']);
        $this->assertEquals(999888777, $responseData['data']['order_id']);

        // Assert custom functionality
        $this->assertTrue($responseData['data']['integration_test']);
        $this->assertTrue($responseData['data']['custom_logic_applied']);
        $this->assertEquals('149.99', $responseData['data']['order_value']);
        $this->assertEquals(3, $responseData['data']['line_items_count']);
    }

    /**
     * Test webhook error handling and recovery
     */
    public function test_webhook_error_handling_and_recovery()
    {
        // Create a handler that throws an exception
        $errorHandler = new class extends WebhookHandlerService {
            public function handleOrderCreated(Request $request): array
            {
                throw new \RuntimeException('Integration test error simulation');
            }
        };

        $this->app->bind(WebhookHandlerService::class, function () use ($errorHandler) {
            return $errorHandler;
        });

        $orderData = ['id' => 123, 'shop_domain' => 'error-test.myshopify.com'];
        $payload = json_encode($orderData);
        $signature = $this->generateSignature($payload);

        $response = $this->postJson('/webhooks/orders/create', $orderData, [
            'Content-Type' => 'application/json',
            'X-Shopify-Hmac-Sha256' => $signature
        ]);

        $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'webhook processing failed'
                ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('error', $responseData);
    }

    /**
     * Test webhook logging integration
     */
    public function test_webhook_logging_integration()
    {
        // Mock the Log facade to capture log calls
        Log::shouldReceive('info')
           ->once()
           ->with('Order Creation webhook received', \Mockery::subset([
               'order_id' => 123456789,
               'shop' => 'logging-test.myshopify.com'
           ]));

        $orderData = [
            'id' => 123456789,
            'shop_domain' => 'logging-test.myshopify.com'
        ];

        $payload = json_encode($orderData);
        $signature = $this->generateSignature($payload);

        $this->postJson('/webhooks/orders/create', $orderData, [
            'Content-Type' => 'application/json',
            'X-Shopify-Hmac-Sha256' => $signature
        ]);

        // Log expectations are verified by Mockery
    }

    /**
     * Test webhook performance under load (simplified)
     */
    public function test_webhook_performance_under_load()
    {
        $startTime = microtime(true);
        $requests = [];

        // Simulate multiple webhook requests
        for ($i = 1; $i <= 10; $i++) {
            $orderData = [
                'id' => 1000 + $i,
                'shop_domain' => "performance-test-{$i}.myshopify.com"
            ];

            $payload = json_encode($orderData);
            $signature = $this->generateSignature($payload);

            $response = $this->postJson('/webhooks/orders/create', $orderData, [
                'Content-Type' => 'application/json',
                'X-Shopify-Hmac-Sha256' => $signature
            ]);

            $requests[] = [
                'status' => $response->getStatusCode(),
                'response_time' => microtime(true) - $startTime
            ];
        }

        $totalTime = microtime(true) - $startTime;
        $avgResponseTime = $totalTime / count($requests);

        // Assert all requests succeeded
        foreach ($requests as $request) {
            $this->assertEquals(200, $request['status']);
        }

        // Assert reasonable performance (adjust threshold as needed)
        $this->assertLessThan(5.0, $totalTime, 'Total time for 10 requests should be under 5 seconds');
        $this->assertLessThan(0.5, $avgResponseTime, 'Average response time should be under 500ms');
    }

    /**
     * Test webhook configuration integration
     */
    public function test_webhook_configuration_integration()
    {
        // Test with different configurations
        $configurations = [
            ['validate_webhooks' => true, 'log_webhooks' => true],
            ['validate_webhooks' => false, 'log_webhooks' => true],
            ['validate_webhooks' => true, 'log_webhooks' => false],
        ];

        foreach ($configurations as $config) {
            Config::set('shopify-enhanced.webhooks.validate_webhooks', $config['validate_webhooks']);
            Config::set('shopify-enhanced.webhooks.log_webhooks', $config['log_webhooks']);

            $orderData = ['id' => 123, 'shop_domain' => 'config-test.myshopify.com'];
            $payload = json_encode($orderData);
            $signature = $this->generateSignature($payload);

            $headers = ['Content-Type' => 'application/json'];
            if ($config['validate_webhooks']) {
                $headers['X-Shopify-Hmac-Sha256'] = $signature;
            }

            $response = $this->postJson('/webhooks/orders/create', $orderData, $headers);

            // Should always succeed regardless of configuration
            $response->assertStatus(200);
        }
    }

    /**
     * Test webhook route registration integration
     */
    public function test_webhook_route_registration_integration()
    {
        $expectedRoutes = [
            ['POST', '/webhooks/orders/create'],
            ['POST', '/webhooks/products/update'],
            ['POST', '/webhooks/customers/data_request'],
            ['POST', '/webhooks/customers/redact'],
            ['POST', '/webhooks/shop/redact'],
            ['POST', '/webhooks/custom/test-type']
        ];

        foreach ($expectedRoutes as [$method, $route]) {
            $response = $this->json($method, $route, ['test' => 'data']);
            
            // Routes should exist (not return 404)
            $this->assertNotEquals(404, $response->getStatusCode(), 
                "Route {$method} {$route} should be registered");
        }
    }

    /**
     * Test webhook middleware integration
     */
    public function test_webhook_middleware_integration()
    {
        // Test that webhooks work with web middleware
        $orderData = ['id' => 123, 'shop_domain' => 'middleware-test.myshopify.com'];
        $payload = json_encode($orderData);
        $signature = $this->generateSignature($payload);

        $response = $this->postJson('/webhooks/orders/create', $orderData, [
            'Content-Type' => 'application/json',
            'X-Shopify-Hmac-Sha256' => $signature,
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);

        // Check that the response has proper headers set by middleware
        $this->assertNotEmpty($response->headers->get('Content-Type'));
    }
}