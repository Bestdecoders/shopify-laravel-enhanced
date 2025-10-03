<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as HttpResponse;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;
use Osiset\ShopifyApp\Objects\Values\Hmac;
use Osiset\ShopifyApp\Objects\Values\NullableShopDomain;
use Osiset\ShopifyApp\Util;

class WebhookController extends Controller
{
    protected WebhookHandlerService $webhookHandler;

    public function __construct(WebhookHandlerService $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
    }

    /**
     * Verify the webhook HMAC signature
     */
    protected function verifyWebhookSignature(Request $request): bool
    {
        $hmac = Hmac::fromNative($request->header('x-shopify-hmac-sha256', ''));
        $shop = NullableShopDomain::fromNative($request->header('x-shopify-shop-domain'));
        $data = $request->getContent();

        $hmacLocal = Util::createHmac(
            [
                'data' => $data,
                'raw' => true,
                'encode' => true,
            ],
            Util::getShopifyConfig('api_secret', $shop)
        );

        return $hmac->isSame($hmacLocal) && !$shop->isNull();
    }

    /**
     * Return HTTP 401 for invalid webhook signature
     */
    protected function unauthorizedResponse(): JsonResponse
    {
        return Response::json([
            'error' => 'Invalid webhook signature'
        ], HttpResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Handle customer data request webhook (GDPR)
     */
    public function customerDataRequest(Request $request): JsonResponse
    {
        // Verify webhook signature first
        if (!$this->verifyWebhookSignature($request)) {
            return $this->unauthorizedResponse();
        }

        try {
            debug_log('Customer Data Request webhook received', [
                'shop' => $request->input('shop_domain'),
                'customer_id' => $request->input('customer.id'),
                'timestamp' => now()
            ]);

            // Call the handler service (extensible by user)
            $result = $this->webhookHandler->handleCustomerDataRequest($request);

            return response()->json([
                'status' => 'success',
                'message' => 'worked',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Customer Data Request webhook failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'webhook processing failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error'
            ], 500);
        }
    }

    /**
     * Handle customer data erasure webhook (GDPR)
     */
    public function customerDataErasure(Request $request): JsonResponse
    {
        // Verify webhook signature first
        if (!$this->verifyWebhookSignature($request)) {
            return $this->unauthorizedResponse();
        }

        try {
            debug_log('Customer Data Erasure webhook received', [
                'shop' => $request->input('shop_domain'),
                'customer_id' => $request->input('customer.id'),
                'timestamp' => now()
            ]);

            // Call the handler service (extensible by user)
            $result = $this->webhookHandler->handleCustomerDataErasure($request);

            return response()->json([
                'status' => 'success',
                'message' => 'worked',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Customer Data Erasure webhook failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'webhook processing failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error'
            ], 500);
        }
    }

    /**
     * Handle shop data erasure webhook (GDPR)
     */
    public function shopDataErasure(Request $request): JsonResponse
    {
        // Verify webhook signature first
        if (!$this->verifyWebhookSignature($request)) {
            return $this->unauthorizedResponse();
        }

        try {
            debug_log('Shop Data Erasure webhook received', [
                'shop' => $request->input('shop_domain'),
                'timestamp' => now()
            ]);

            // Call the handler service (extensible by user)
            $result = $this->webhookHandler->handleShopDataErasure($request);

            return response()->json([
                'status' => 'success',
                'message' => 'worked',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Shop Data Erasure webhook failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'webhook processing failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error'
            ], 500);
        }
    }

}