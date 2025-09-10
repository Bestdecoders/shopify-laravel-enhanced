<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\WebhookController;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // No configuration needed - webhooks work by default
        Config::set('app.debug', true);
    }



    /**
     * Test GDPR customer data request webhook
     */
    public function test_customer_data_request_webhook_returns_success()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'customer' => [
                'id' => 555666,
                'email' => 'customer@example.com',
                'phone' => '+1234567890'
            ],
            'orders_requested' => [
                123, 456, 789
            ]
        ];

        // Act
        $response = $this->postJson('/webhooks/customers/data_request', $gdprData, [
            'X-Shopify-Shop-Domain' => 'test-shop.myshopify.com',
            'X-Shopify-Topic' => 'customers/data_request',
            'Content-Type' => 'application/json'
        ]);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'worked'
                ])
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
        $this->assertEquals('customer_data_request', $responseData['data']['action']);
        $this->assertTrue($responseData['data']['gdpr_compliant']);
    }

    /**
     * Test GDPR customer data erasure webhook
     */
    public function test_customer_data_erasure_webhook_returns_success()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'customer' => [
                'id' => 555666,
                'email' => 'customer@example.com'
            ],
            'orders_to_redact' => [123, 456]
        ];

        // Act
        $response = $this->postJson('/webhooks/customers/redact', $gdprData, [
            'X-Shopify-Shop-Domain' => 'test-shop.myshopify.com',
            'X-Shopify-Topic' => 'customers/redact',
            'Content-Type' => 'application/json'
        ]);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'worked'
                ])
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
        $this->assertEquals('customer_data_erasure', $responseData['data']['action']);
        $this->assertTrue($responseData['data']['gdpr_compliant']);
    }

    /**
     * Test GDPR shop data erasure webhook
     */
    public function test_shop_data_erasure_webhook_returns_success()
    {
        // Arrange
        $gdprData = [
            'shop_domain' => 'test-shop.myshopify.com',
            'shop_id' => 12345
        ];

        // Act
        $response = $this->postJson('/webhooks/shop/redact', $gdprData, [
            'X-Shopify-Shop-Domain' => 'test-shop.myshopify.com',
            'X-Shopify-Topic' => 'shop/redact',
            'Content-Type' => 'application/json'
        ]);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'worked'
                ])
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'action',
                        'shop',
                        'processed_at',
                        'handler',
                        'gdpr_compliant'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('shop_data_erasure', $responseData['data']['action']);
        $this->assertTrue($responseData['data']['gdpr_compliant']);
    }


    /**
     * Test GDPR webhook error handling
     */
    public function test_gdpr_webhook_error_handling()
    {
        // Mock the webhook handler to throw an exception
        $this->app->bind(WebhookHandlerService::class, function () {
            $mock = $this->createMock(WebhookHandlerService::class);
            $mock->method('handleCustomerDataRequest')
                 ->willThrowException(new \Exception('Test error'));
            return $mock;
        });

        // Act
        $response = $this->postJson('/webhooks/customers/data_request', [
            'customer' => ['id' => 123],
            'shop_domain' => 'test-shop.myshopify.com'
        ]);

        // Assert
        $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'webhook processing failed',
                    'error' => 'Test error'
                ]);
    }


    /**
     * Test GDPR webhook with missing data
     */
    public function test_gdpr_webhook_with_missing_data()
    {
        // Act - Send GDPR webhook with missing required data
        $response = $this->postJson('/webhooks/customers/data_request', [
            // Missing customer and shop_domain
        ]);

        // Assert - Should still return success (graceful handling)
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'worked'
                ]);

        $responseData = $response->json();
        $this->assertEquals('customer_data_request', $responseData['data']['action']);
        $this->assertTrue($responseData['data']['gdpr_compliant']);
    }

    /**
     * Test GDPR webhook content type validation
     */
    public function test_gdpr_webhook_content_type_handling()
    {
        // Act - Send GDPR webhook with non-JSON content type
        $response = $this->post('/webhooks/customers/redact', [
            'customer' => ['id' => 123],
            'shop_domain' => 'test-shop.myshopify.com'
        ], [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        // Assert - Should still work
        $response->assertStatus(200);
    }

    /**
     * Test only GDPR webhook routes are registered
     */
    public function test_only_gdpr_webhook_routes_exist()
    {
        $gdprRoutes = [
            '/webhooks/customers/data_request',
            '/webhooks/customers/redact',
            '/webhooks/shop/redact',
        ];

        // Test GDPR routes exist
        foreach ($gdprRoutes as $route) {
            $response = $this->postJson($route, ['test' => 'data']);
            
            // GDPR routes should exist (not return 404)
            $this->assertNotEquals(404, $response->getStatusCode(), 
                "GDPR route {$route} should exist");
        }
        
        // Test non-GDPR routes do NOT exist
        $nonGdprRoutes = [
            '/webhooks/orders/create',
            '/webhooks/products/update',
            '/webhooks/custom/test'
        ];
        
        foreach ($nonGdprRoutes as $route) {
            $response = $this->postJson($route, ['test' => 'data']);
            
            // Non-GDPR routes should NOT exist (return 404)
            $this->assertEquals(404, $response->getStatusCode(), 
                "Non-GDPR route {$route} should NOT exist in this package");
        }
    }
}