<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService;

class WebhookController extends Controller
{
    protected WebhookHandlerService $webhookHandler;

    public function __construct(WebhookHandlerService $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
    }

    /**
     * Handle customer data request webhook (GDPR)
     */
    public function customerDataRequest(Request $request): JsonResponse
    {
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