<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Models\CouponCode;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Exception;

class SubscriptionManagementService
{
    protected $graphqlService;

    public function __construct(ShopifyGraphqlService $graphqlService)
    {
        $this->graphqlService = $graphqlService;
    }

    /**
     * Create a new subscription using GraphQL with discount support
     */
    public function createSubscription($user, string $planType = 'monthly', int $trialDays = 0, ?string $couponCode = null): ?string
    {
        try {
            $planDetails = $this->getPlanDetails($planType);

            if (!$planDetails) {
                Log::error('Invalid subscription type', ['plan_type' => $planType]);
                return null;
            }

            $basePrice = $planDetails['price'];
            $returnUrl = config('app.url') . '/billing/callback?charge_id=';

            // Build discount structure from coupon
            $discount = $this->buildDiscountFromCoupon($couponCode);

            // Prepare line items with ORIGINAL price (not discounted)
            $lineItems = [[
                'plan' => [
                    'appRecurringPricingDetails' => [
                        'price' => [
                            'amount' => $basePrice,
                            'currencyCode' => 'USD'
                        ],
                        'interval' => $planDetails['interval']
                    ]
                ]
            ]];

            // Add discount if coupon exists
            if ($discount) {
                $lineItems[0]['plan']['appRecurringPricingDetails']['discount'] = $discount;
            }

            // Get query from config: shopify-enhanced.queries.app_subscription.create
            $query = config('shopify-enhanced.queries.app_subscription.create');

            $result = $this->graphqlService->execute($user, $query, [
                'lineItems' => $lineItems,
                'name' => $planDetails['name'],
                'returnUrl' => $returnUrl,
                'test' => config('shopify-app.billing_test', true),
                'trialDays' => $trialDays
            ]);

            if (isset($result['appSubscriptionCreate']['appSubscription']['id'])) {
                $shopifySubscriptionId = $result['appSubscriptionCreate']['appSubscription']['id'];
                $confirmationUrl = $result['appSubscriptionCreate']['confirmationUrl'];

                // Extract numeric ID for our database
                $numericId = str_replace('gid://shopify/AppSubscription/', '', $shopifySubscriptionId);

                // Create charge record
                $charge = new Charge();
                $charge->user_id = $user->id;
                $charge->charge_id = $numericId;
                $charge->type = 'recurring';
                $charge->status = 'pending';
                $charge->price = $basePrice;
                $charge->interval = $planDetails['interval'];
                $charge->name = $planDetails['name'];
                $charge->test = config('shopify-app.billing_test', true);
                $charge->trial_days = $trialDays;
                $charge->coupon_code = $couponCode;
                $charge->save();

                debug_log('Subscription created', [
                    'user_id' => $user->id,
                    'charge_id' => $charge->id,
                    'shopify_subscription_id' => $shopifySubscriptionId,
                    'base_price' => $basePrice,
                    'discount_applied' => $discount !== null,
                    'coupon_code' => $couponCode
                ]);

                return $confirmationUrl;
            }

            return null;

        } catch (Exception $e) {
            Log::error('Failed to create subscription', [
                'user_id' => $user->id,
                'plan_type' => $planType,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cancel a user's Shopify subscription using GraphQL
     */
    public function cancelSubscription($user, ?string $reason = null): bool
    {
        try {
            $activeCharge = $user->charges()
                ->where('status', 'active')
                ->whereNull('cancelled_on')
                ->latest()
                ->first();

            if (!$activeCharge) {
                Log::warning('No active charge found for cancellation', ['user_id' => $user->id]);
                return false;
            }

            // Get query from config: shopify-enhanced.queries.app_subscription.cancel
            $query = config('shopify-enhanced.queries.app_subscription.cancel');
            $shopifySubscriptionId = "gid://shopify/AppSubscription/{$activeCharge->charge_id}";

            $result = $this->graphqlService->execute($user, $query, [
                'id' => $shopifySubscriptionId
            ]);

            if (isset($result['appSubscriptionCancel']['appSubscription']['status'])) {
                $activeCharge->update([
                    'status' => 'cancelled',
                    'cancelled_on' => now(),
                    'cancellation_reason' => $reason
                ]);

                debug_log('Subscription cancelled', [
                    'user_id' => $user->id,
                    'charge_id' => $activeCharge->id,
                    'reason' => $reason
                ]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error('Failed to cancel subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user's current active charge
     */
    public function getCurrentCharge($user): ?Charge
    {
        return $user->charges()
            ->where('status', 'active')
            ->whereNull('cancelled_on')
            ->latest()
            ->first();
    }

    /**
     * Check if user has active billing
     */
    public function hasActiveBilling($user): bool
    {
        try {
            $hasActiveCharge = $user->charges()
                ->where('status', 'active')
                ->whereNull('cancelled_on')
                ->exists();

            return $hasActiveCharge;

        } catch (Exception $e) {
            Log::error('Failed to check active billing', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Apply coupon to user's billing
     */
    public function applyCoupon($user, string $couponCode): bool
    {
        try {
            $coupon = CouponCode::where('code', $couponCode)->first();

            if (!$coupon || !$coupon->canBeUsedBy($user)) {
                return false;
            }

            $activeCharge = $this->getCurrentCharge($user);

            if ($activeCharge) {
                $activeCharge->update(['coupon_code' => $couponCode]);
            }

            // If it's a free days coupon, grant free time
            if ($coupon->type === CouponCode::TYPE_FREE_DAYS) {
                $this->grantFreeTime($user, $coupon->getFreeDays(), "Coupon: {$couponCode}");
            }

            $coupon->use();
            $coupon->recordUsage($user);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to apply coupon', [
                'user_id' => $user->id,
                'coupon_code' => $couponCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Grant free time by creating a free charge record
     */
    public function grantFreeTime($user, int $days, ?string $reason = null): bool
    {
        try {
            $existingFreeCharge = $user->charges()
                ->where('type', 'free_time')
                ->where('status', 'active')
                ->first();

            if ($existingFreeCharge) {
                $currentEndDate = $existingFreeCharge->free_until ?? now();
                $newEndDate = $currentEndDate->addDays($days);
                $existingFreeCharge->update(['free_until' => $newEndDate]);

                debug_log('Free time extended', [
                    'user_id' => $user->id,
                    'charge_id' => $existingFreeCharge->id,
                    'days_added' => $days
                ]);
            } else {
                $charge = new Charge();
                $charge->user_id = $user->id;
                $charge->type = 'free_time';
                $charge->status = 'active';
                $charge->price = 0;
                $charge->name = 'Free Time Grant';
                $charge->free_until = now()->addDays($days);
                $charge->grant_reason = $reason;
                $charge->save();

                debug_log('Free time granted', [
                    'user_id' => $user->id,
                    'charge_id' => $charge->id,
                    'days_granted' => $days
                ]);
            }

            return true;

        } catch (Exception $e) {
            Log::error('Failed to grant free time', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Build Shopify discount structure from coupon code
     */
    private function buildDiscountFromCoupon(?string $couponCode): ?array
    {
        if (!$couponCode) {
            return null;
        }

        $coupon = CouponCode::where('code', $couponCode)->first();

        if (!$coupon || !$coupon->isValid()) {
            return null;
        }

        $discount = ['value' => []];

        // Build discount based on coupon type
        switch ($coupon->type) {
            case CouponCode::TYPE_PERCENTAGE:
                $discount['value']['percentage'] = (float) $coupon->value / 100;
                break;

            case CouponCode::TYPE_FIXED:
                $discount['value']['amount'] = (float) $coupon->value;
                break;

            case CouponCode::TYPE_FREE_DAYS:
                // Free days handled via trial extension instead
                return null;

            default:
                return null;
        }

        // Add duration limit from metadata (null = unlimited)
        $durationLimit = $coupon->metadata['duration_limit'] ?? null;
        $discount['durationLimitInIntervals'] = $durationLimit;

        return $discount;
    }

    /**
     * Get plan details (price, interval, name)
     */
    private function getPlanDetails(string $planType): ?array
    {
        $pricing = config('shopify-enhanced.subscription_pricing', [
            'monthly' => 5.00,
            'yearly' => 50.00,
            'lifetime' => 299.99
        ]);

        $plans = [
            'monthly' => [
                'name' => 'Monthly Plan',
                'interval' => 'EVERY_30_DAYS',
                'price' => $pricing['monthly']
            ],
            'yearly' => [
                'name' => 'Yearly Plan',
                'interval' => 'ANNUAL',
                'price' => $pricing['yearly']
            ],
            'lifetime' => [
                'name' => 'Lifetime Plan',
                'interval' => 'ANNUAL',
                'price' => $pricing['lifetime']
            ]
        ];

        return $plans[$planType] ?? null;
    }
}
