<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Bestdecoders\ShopifyLaravelEnhanced\Models\CouponCode;
use Bestdecoders\ShopifyLaravelEnhanced\Services\SubscriptionManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class UserSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionManagementService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get subscription details for a user
     */
    public function show($user): JsonResponse
    {
        $userModel = $this->getUserModel($user);
        
        if (!$userModel) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $charge = $this->subscriptionService->getCurrentCharge($userModel);
        
        if (!$charge) {
            return response()->json(['message' => 'No subscription found'], 404);
        }

        return response()->json([
            'subscription' => [
                'id' => $charge->id,
                'type' => $charge->type,
                'status' => $charge->status,
                'price' => $charge->price,
                'charge_id' => $charge->charge_id,
                'trial_days' => $charge->trial_days,
                'coupon_code' => $charge->coupon_code,
                'cancelled_on' => $charge->cancelled_on,
                'created_at' => $charge->created_at,
                'updated_at' => $charge->updated_at
            ],
            'is_active' => $charge->isActive(),
            'is_trial' => $charge->isTrial(),
            'is_cancelled' => $charge->isCancelled(),
            'is_ongoing' => $charge->isOngoing()
        ]);
    }

    /**
     * Cancel a user's subscription
     */
    public function cancel(Request $request, $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
            'immediate' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $userModel = $this->getUserModel($user);
            
            if (!$userModel) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $charge = $this->subscriptionService->getCurrentCharge($userModel);
            
            if (!$charge) {
                return response()->json(['message' => 'No active subscription found'], 404);
            }

            if ($charge->isCancelled()) {
                return response()->json(['message' => 'Subscription already cancelled'], 400);
            }

            // Cancel using GraphQL service (which handles both Shopify and local records)
            $cancelled = $this->subscriptionService->cancelSubscription(
                $userModel, 
                $request->input('reason')
            );

            if (!$cancelled) {
                // Fallback - cancel the local charge record
                $charge->update([
                    'status' => 'cancelled',
                    'cancelled_on' => now(),
                    'cancellation_reason' => $request->input('reason')
                ]);
            }

            debug_log('Subscription cancelled', [
                'user_id' => $userModel->id,
                'charge_id' => $charge->id,
                'reason' => $request->input('reason'),
                'cancelled_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Subscription cancelled successfully',
                'subscription' => $charge->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'user_id' => $user,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Failed to cancel subscription'], 500);
        }
    }

    /**
     * Extend free time for a user
     */
    public function extendFreeTime(Request $request, $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $userModel = $this->getUserModel($user);
            
            if (!$userModel) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Use the SubscriptionManagementService to grant free time
            $granted = $this->subscriptionService->grantFreeTime(
                $userModel,
                $request->input('days'),
                $request->input('reason')
            );

            if (!$granted) {
                return response()->json(['message' => 'Failed to extend free time'], 500);
            }

            $charge = $this->subscriptionService->getCurrentCharge($userModel);

            debug_log('Free time extended', [
                'user_id' => $userModel->id,
                'days_added' => $request->input('days'),
                'reason' => $request->input('reason'),
                'extended_by' => auth()->id()
            ]);

            return response()->json([
                'message' => "Free time extended by {$request->input('days')} days",
                'subscription' => $charge,
                'new_free_until' => $charge->free_until ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to extend free time', [
                'user_id' => $user,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Failed to extend free time'], 500);
        }
    }

    /**
     * Apply coupon code to a user
     */
    public function applyCoupon(Request $request, $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|exists:coupon_codes,code',
            'plan_type' => 'nullable|string|in:monthly,yearly,lifetime'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $userModel = $this->getUserModel($user);
            
            if (!$userModel) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Use the SubscriptionManagementService to apply coupon
            $applied = $this->subscriptionService->applyCoupon(
                $userModel,
                $request->input('coupon_code')
            );

            if (!$applied) {
                return response()->json(['message' => 'Coupon code is not valid for this user or plan'], 400);
            }

            $charge = $this->subscriptionService->getCurrentCharge($userModel);
            $coupon = CouponCode::where('code', $request->input('coupon_code'))->first();

            debug_log('Coupon applied', [
                'user_id' => $userModel->id,
                'coupon_code' => $request->input('coupon_code'),
                'applied_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Coupon applied successfully',
                'subscription' => $charge,
                'coupon' => $coupon
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to apply coupon', [
                'user_id' => $user,
                'coupon_code' => $request->input('coupon_code'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Failed to apply coupon'], 500);
        }
    }

    /**
     * Reactivate a cancelled subscription
     */
    public function reactivate($user): JsonResponse
    {
        try {
            $userModel = $this->getUserModel($user);
            
            if (!$userModel) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $charge = $this->subscriptionService->getCurrentCharge($userModel);
            
            if (!$charge) {
                return response()->json(['message' => 'No subscription found'], 404);
            }

            if (!$charge->isCancelled()) {
                return response()->json(['message' => 'Subscription is not cancelled'], 400);
            }

            $charge->update([
                'status' => 'active',
                'cancelled_on' => null,
                'cancellation_reason' => null
            ]);

            debug_log('Subscription reactivated', [
                'user_id' => $userModel->id,
                'charge_id' => $charge->id,
                'reactivated_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Subscription reactivated successfully',
                'subscription' => $charge->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reactivate subscription', [
                'user_id' => $user,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Failed to reactivate subscription'], 500);
        }
    }

    private function getUserModel($user)
    {
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        
        if (is_numeric($user)) {
            return $userModel::find($user);
        }
        
        if (is_string($user)) {
            return $userModel::where('name', $user)->first();
        }
        
        return $user;
    }
}