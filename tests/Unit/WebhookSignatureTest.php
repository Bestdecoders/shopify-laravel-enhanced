<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class WebhookSignatureTest extends TestCase
{
    private WebhookHandlerService $webhookHandler;
    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookHandler = new WebhookHandlerService();
        // No configuration needed - webhooks work by default with kyon package
    }


    /**
     * Test webhook validation works without signature (using kyon package)
     */
    public function test_webhook_validation_works_without_signature()
    {
        // Arrange
        $data = ['shop_domain' => 'test.myshopify.com', 'customer' => ['id' => 123]];
        $request = $this->createSignedRequest($data, null); // No signature

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert - Should work by default (kyon package handles validation)
        $this->assertTrue($isValid, 'Webhook validation should work by default');
    }

    /**
     * Create a request with webhook signature
     */
    private function createSignedRequest(array $data, ?string $signature = null): Request
    {
        $payload = json_encode($data);
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Shopify-Shop-Domain', $data['shop_domain'] ?? 'test.myshopify.com');
        
        if ($signature !== null) {
            $request->headers->set('X-Shopify-Hmac-Sha256', $signature);
        }
        
        return $request;
    }


    /**
     * Test webhook validation with complex GDPR payload
     */
    public function test_webhook_validation_with_complex_gdpr_payload()
    {
        // Arrange - Complex GDPR data request payload
        $complexData = [
            'shop_domain' => 'test.myshopify.com',
            'customer' => [
                'id' => 789,
                'email' => 'customer@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567890',
                'addresses' => [
                    [
                        'address1' => '123 Main St',
                        'city' => 'Anytown',
                        'country' => 'US'
                    ]
                ]
            ],
            'orders_requested' => [123, 456, 789],
            'special_chars' => 'áéíóú çñ 中文 🚀'
        ];
        
        $request = $this->createSignedRequest($complexData, null);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert - Should work by default
        $this->assertTrue($isValid, 'Complex GDPR payload should validate successfully');
    }

    /**
     * Test signature validation with modified payload fails
     */
    public function test_signature_validation_fails_with_modified_payload()
    {
        // Arrange
        $originalData = ['shop_domain' => 'test.myshopify.com', 'id' => 123];
        $originalPayload = json_encode($originalData);
        $signature = $this->generateValidSignature($originalPayload, $this->testSecret);
        
        // Create request with modified data but original signature
        $modifiedData = ['shop_domain' => 'test.myshopify.com', 'id' => 456]; // Changed id
        $request = $this->createSignedRequest($modifiedData, $signature);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert
        $this->assertFalse($isValid, 'Modified payload should fail signature validation');
    }

    /**
     * Test signature validation with different JSON formatting
     */
    public function test_signature_validation_with_different_json_formatting()
    {
        // Arrange
        $data = ['shop_domain' => 'test.myshopify.com', 'id' => 123];
        
        // Create payload with specific formatting (no spaces)
        $compactPayload = json_encode($data, JSON_UNESCAPED_SLASHES);
        $signature = $this->generateValidSignature($compactPayload, $this->testSecret);
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], $compactPayload);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Shopify-Hmac-Sha256', $signature);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert
        $this->assertTrue($isValid, 'Compact JSON formatting should validate correctly');
    }

    /**
     * Test signature validation is case sensitive
     */
    public function test_signature_validation_is_case_sensitive()
    {
        // Arrange
        $data = ['shop_domain' => 'test.myshopify.com', 'id' => 123];
        $payload = json_encode($data);
        $signature = $this->generateValidSignature($payload, $this->testSecret);
        $upperCaseSignature = strtoupper($signature);
        
        $request = $this->createSignedRequest($data, $upperCaseSignature);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert
        $this->assertFalse($isValid, 'Uppercase signature should fail validation (case sensitive)');
    }

    /**
     * Test signature validation with binary data
     */
    public function test_signature_validation_with_binary_data()
    {
        // Arrange
        $binaryData = "Binary content: \x00\x01\x02\x03\xFF";
        $signature = $this->generateValidSignature($binaryData, $this->testSecret);
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], $binaryData);
        $request->headers->set('Content-Type', 'application/octet-stream');
        $request->headers->set('X-Shopify-Hmac-Sha256', $signature);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert
        $this->assertTrue($isValid, 'Binary data with valid signature should pass validation');
    }

    /**
     * Test hash_equals timing attack protection
     */
    public function test_signature_comparison_uses_timing_safe_comparison()
    {
        // This test ensures that hash_equals is used for comparison
        // We can't directly test timing attack resistance, but we can ensure
        // the comparison behaves correctly with similar strings
        
        // Arrange
        $data = ['shop_domain' => 'test.myshopify.com', 'id' => 123];
        $payload = json_encode($data);
        $validSignature = $this->generateValidSignature($payload, $this->testSecret);
        
        // Create a signature that's similar but different by one character
        $similarSignature = substr($validSignature, 0, -1) . 'X';
        
        $request = $this->createSignedRequest($data, $similarSignature);

        // Act
        $isValid = $this->webhookHandler->validateWebhook($request);

        // Assert
        $this->assertFalse($isValid, 'Similar but incorrect signature should fail validation');
    }
}