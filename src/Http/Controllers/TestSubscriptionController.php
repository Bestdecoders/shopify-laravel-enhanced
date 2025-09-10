<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Bestdecoders\ShopifyLaravelEnhanced\Services\SubscriptionManagementService;
use Bestdecoders\ShopifyLaravelEnhanced\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Osiset\ShopifyApp\Storage\Models\Charge;

/**
 * Test controller for manual testing of subscription functionality
 * This controller provides debug endpoints to test the subscription flow
 */
class TestSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionManagementService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Test page to display testing interface
     */
    public function index()
    {
        return view('shopify-enhanced::test.subscription');
    }

    /**
     * Create a test subscription
     */
    public function createTestSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_type' => 'required|in:monthly,yearly,lifetime',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'coupon_code' => 'nullable|string'
        ]);

        try {
            $userModel = config('shopify-enhanced.user_model')::find($request->user_id);
            
            if (!$userModel) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $confirmationUrl = $this->subscriptionService->createSubscription(
                $userModel,
                $request->plan_type,
                $request->trial_days ?? 0,
                $request->coupon_code
            );

            if (!$confirmationUrl) {
                return response()->json(['error' => 'Failed to create subscription'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test subscription created successfully',
                'confirmation_url' => $confirmationUrl,
                'plan_type' => $request->plan_type,
                'trial_days' => $request->trial_days ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error('Test subscription creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Subscription creation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all charges for a user
     */
    public function getUserCharges($userId): JsonResponse
    {
        try {
            $userModel = config('shopify-enhanced.user_model')::find($userId);
            
            if (!$userModel) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $charges = $userModel->charges()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'user_id' => $userId,
                'charges_count' => $charges->count(),
                'charges' => $charges->map(function ($charge) {
                    return [
                        'id' => $charge->id,
                        'charge_id' => $charge->charge_id,
                        'type' => $charge->type,
                        'status' => $charge->status,
                        'price' => $charge->price,
                        'name' => $charge->name,
                        'trial_days' => $charge->trial_days,
                        'test' => $charge->test,
                        'cancelled_on' => $charge->cancelled_on,
                        'created_at' => $charge->created_at,
                        'is_active' => $charge->isActive(),
                        'is_trial' => $charge->isTrial(),
                        'is_cancelled' => $charge->isCancelled(),
                        'is_ongoing' => $charge->isOngoing(),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch charges: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Test GraphQL connection
     */
    public function testGraphQL(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $userModel = config('shopify-enhanced.user_model')::find($request->user_id);
            
            if (!$userModel) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Test basic shop query
            $shopQuery = config('shopify-enhanced.queries.shop');
            $result = app('shopify-graphql')->execute($userModel, $shopQuery);

            return response()->json([
                'success' => true,
                'message' => 'GraphQL connection working',
                'shop_data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('GraphQL test failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id
            ]);

            return response()->json(['error' => 'GraphQL test failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Test active subscriptions retrieval
     */
    public function testActiveSubscriptions($userId): JsonResponse
    {
        try {
            $userModel = config('shopify-enhanced.user_model')::find($userId);
            
            if (!$userModel) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $activeSubscriptions = $this->subscriptionService->getActiveSubscriptions($userModel);
            $hasActiveBilling = $this->subscriptionService->hasActiveBilling($userModel);

            return response()->json([
                'user_id' => $userId,
                'has_active_billing' => $hasActiveBilling,
                'active_subscriptions_count' => count($activeSubscriptions),
                'active_subscriptions' => $activeSubscriptions
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch active subscriptions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create test coupon codes
     */
    public function createTestCoupons(): JsonResponse
    {
        try {
            $coupons = [
                [
                    'code' => 'TEST10',
                    'type' => 'percentage',
                    'value' => 10,
                    'description' => '10% discount test coupon',
                    'usage_limit' => 10,
                    'expires_at' => now()->addDays(30),
                ],
                [
                    'code' => 'FREE5DAYS',
                    'type' => 'free_days',
                    'value' => 5,
                    'description' => '5 free days test coupon',
                    'usage_limit' => 5,
                    'expires_at' => now()->addDays(30),
                ],
                [
                    'code' => 'SAVE50',
                    'type' => 'fixed',
                    'value' => 50,
                    'description' => '$50 off test coupon',
                    'usage_limit' => 3,
                    'expires_at' => now()->addDays(30),
                ]
            ];

            $created = [];
            foreach ($coupons as $couponData) {
                $existing = CouponCode::where('code', $couponData['code'])->first();
                if (!$existing) {
                    $coupon = CouponCode::create($couponData);
                    $created[] = $coupon;
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($created) . ' test coupons created',
                'created_coupons' => $created
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create test coupons: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sync a charge status with Shopify
     */
    public function syncChargeStatus($chargeId): JsonResponse
    {
        try {
            $charge = Charge::find($chargeId);
            
            if (!$charge) {
                return response()->json(['error' => 'Charge not found'], 404);
            }

            $oldStatus = $charge->status;
            $this->subscriptionService->syncChargeStatus($charge);
            $charge->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Charge status synced',
                'charge_id' => $chargeId,
                'old_status' => $oldStatus,
                'new_status' => $charge->status,
                'charge_details' => [
                    'id' => $charge->id,
                    'shopify_charge_id' => $charge->charge_id,
                    'type' => $charge->type,
                    'status' => $charge->status,
                    'is_active' => $charge->isActive(),
                    'is_cancelled' => $charge->isCancelled()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to sync charge status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get debugging information
     */
    public function debug(): JsonResponse
    {
        return response()->json([
            'config' => [
                'user_model' => config('shopify-enhanced.user_model'),
                'test_mode' => config('shopify-enhanced.billing.test_mode'),
                'callback_url' => config('shopify-enhanced.billing.callback_url'),
                'pricing' => config('shopify-enhanced.subscription_pricing'),
                'trial_days' => config('shopify-enhanced.billing.trial_days'),
            ],
            'database' => [
                'users_count' => app(config('shopify-enhanced.user_model'))->count(),
                'charges_count' => Charge::count(),
                'coupons_count' => CouponCode::count(),
            ],
            'routes' => [
                'subscription_show' => route('api.subscriptions.show', ['user' => 1]),
                'subscription_cancel' => route('api.subscriptions.cancel', ['user' => 1]),
                'subscription_extend' => route('api.subscriptions.extend-free-time', ['user' => 1]),
                'subscription_coupon' => route('api.subscriptions.apply-coupon', ['user' => 1]),
                'subscription_reactivate' => route('api.subscriptions.reactivate', ['user' => 1]),
            ]
        ]);
    }
}