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
     * Cancel a user's Shopify subscription using GraphQL
     */
    public function cancelSubscription($user, ?string $reason = null): bool
    {
        try {
            // Get the active charge from the charges table
            $activeCharge = $user->charges()
                ->where('status', 'active')
                ->whereNull('cancelled_on')
                ->latest()
                ->first();

            if (!$activeCharge) {
                Log::warning('No active charge found for user cancellation', [
                    'user_id' => $user->id
                ]);
                return false;
            }

            // Use GraphQL to cancel the subscription
            $query = config('shopify-enhanced-graphql-queries.billing.cancel_subscription');
            $shopifySubscriptionId = "gid://shopify/AppSubscription/{$activeCharge->charge_id}";
            
            $result = $this->graphqlService->execute($user, $query, [
                'id' => $shopifySubscriptionId
            ]);

            if (isset($result['appSubscriptionCancel']['appSubscription']['status'])) {
                // Update the charge record
                $activeCharge->update([
                    'status' => 'cancelled',
                    'cancelled_on' => now(),
                    'cancellation_reason' => $reason
                ]);

                Log::info('Subscription cancelled via GraphQL', [
                    'user_id' => $user->id,
                    'charge_id' => $activeCharge->id,
                    'shopify_subscription_id' => $shopifySubscriptionId,
                    'reason' => $reason
                ]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error('Failed to cancel subscription via GraphQL', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create a new subscription using GraphQL
     */
    public function createSubscription($user, string $planType = 'monthly', int $trialDays = 0, ?string $couponCode = null): ?string
    {
        try {
            $planDetails = $this->getPlanDetails($planType);
            
            if (!$planDetails) {
                Log::error('Invalid subscription type for GraphQL creation', [
                    'plan_type' => $planType
                ]);
                return null;
            }

            $basePrice = $planDetails['price'];
            $effectivePrice = $this->calculateEffectivePrice($basePrice, $couponCode);
            $returnUrl = config('app.url') . '/billing/callback?charge_id=';

            // Prepare line items for GraphQL
            $lineItems = [[
                'plan' => [
                    'appRecurringPricingDetails' => [
                        'price' => [
                            'amount' => $effectivePrice,
                            'currencyCode' => 'USD'
                        ],
                        'interval' => $planDetails['interval']
                    ]
                ]
            ]];

            $query = config('shopify-enhanced-graphql-queries.billing.create_recurring_charge');
            
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

                // Create charge record in the charges table (Kyon package)
                $charge = new Charge();
                $charge->user_id = $user->id;
                $charge->charge_id = $numericId;
                $charge->type = 'recurring';
                $charge->status = 'pending';
                $charge->price = $effectivePrice;
                $charge->interval = $planDetails['interval'];
                $charge->name = $planDetails['name'];
                $charge->test = config('shopify-app.billing_test', true);
                $charge->trial_days = $trialDays;
                $charge->coupon_code = $couponCode;
                $charge->save();

                Log::info('Subscription created via GraphQL', [
                    'user_id' => $user->id,
                    'charge_id' => $charge->id,
                    'shopify_subscription_id' => $shopifySubscriptionId,
                    'confirmation_url' => $confirmationUrl
                ]);

                return $confirmationUrl;
            }

            return null;

        } catch (Exception $e) {
            Log::error('Failed to create subscription via GraphQL', [
                'user_id' => $user->id,
                'plan_type' => $planType,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get active subscriptions for a user using GraphQL
     */
    public function getActiveSubscriptions($user): array
    {
        try {
            $query = config('shopify-enhanced-graphql-queries.billing.get_app_subscriptions');
            
            $result = $this->graphqlService->execute($user, $query);

            if (isset($result['currentAppInstallation']['activeSubscriptions'])) {
                return $result['currentAppInstallation']['activeSubscriptions'];
            }

            return [];

        } catch (Exception $e) {
            Log::error('Failed to get active subscriptions via GraphQL', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Check if user has active billing via GraphQL
     */
    public function hasActiveBilling($user): bool
    {
        try {
            // First check charges table
            $hasActiveCharge = $user->charges()
                ->where('status', 'active')
                ->whereNull('cancelled_on')
                ->exists();

            if ($hasActiveCharge) {
                return true;
            }

            // Double-check with GraphQL
            $activeSubscriptions = $this->getActiveSubscriptions($user);
            return count($activeSubscriptions) > 0;

        } catch (Exception $e) {
            Log::error('Failed to check active billing via GraphQL', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sync charge status with Shopify via GraphQL
     */
    public function syncChargeStatus(Charge $charge): void
    {
        if (!$charge->charge_id) {
            return;
        }

        try {
            $user = $charge->shop;
            $shopifySubscriptionId = "gid://shopify/AppSubscription/{$charge->charge_id}";
            
            $query = config('shopify-enhanced-graphql-queries.billing.get_subscription_by_id');
            
            $result = $this->graphqlService->execute($user, $query, [
                'id' => $shopifySubscriptionId
            ]);

            if (isset($result['node']['status'])) {
                $shopifyStatus = $result['node']['status'];
                $newStatus = strtolower($shopifyStatus);

                if ($charge->status !== $newStatus) {
                    $charge->update(['status' => $newStatus]);
                    
                    Log::info('Charge status synced via GraphQL', [
                        'charge_id' => $charge->id,
                        'old_status' => $charge->status,
                        'new_status' => $newStatus,
                        'shopify_status' => $shopifyStatus
                    ]);
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to sync charge status via GraphQL', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage()
            ]);
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
     * Grant free time by creating a free charge record
     */
    public function grantFreeTime($user, int $days, ?string $reason = null): bool
    {
        try {
            // Check if user has existing free time charge
            $existingFreeCharge = $user->charges()
                ->where('type', 'free_time')
                ->where('status', 'active')
                ->first();

            if ($existingFreeCharge) {
                // Extend existing free time
                $currentEndDate = $existingFreeCharge->free_until ?? now();
                $newEndDate = $currentEndDate->addDays($days);
                $existingFreeCharge->update(['free_until' => $newEndDate]);
                
                Log::info('Free time extended', [
                    'user_id' => $user->id,
                    'charge_id' => $existingFreeCharge->id,
                    'days_added' => $days,
                    'new_end_date' => $newEndDate
                ]);
            } else {
                // Create new free time charge
                $charge = new Charge();
                $charge->user_id = $user->id;
                $charge->type = 'free_time';
                $charge->status = 'active';
                $charge->price = 0;
                $charge->name = 'Free Time Grant';
                $charge->free_until = now()->addDays($days);
                $charge->grant_reason = $reason;
                $charge->save();

                Log::info('Free time granted', [
                    'user_id' => $user->id,
                    'charge_id' => $charge->id,
                    'days_granted' => $days,
                    'end_date' => $charge->free_until
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
     * Apply coupon to user's billing
     */
    public function applyCoupon($user, string $couponCode): bool
    {
        try {
            $coupon = CouponCode::where('code', $couponCode)->first();
            
            if (!$coupon || !$coupon->canBeUsedBy($user)) {
                return false;
            }

            // Get active charge
            $activeCharge = $this->getCurrentCharge($user);
            
            if ($activeCharge) {
                $activeCharge->update(['coupon_code' => $couponCode]);
            }

            // If it's a free days coupon, grant free time
            if ($coupon->type === CouponCode::TYPE_FREE_DAYS) {
                $this->grantFreeTime($user, $coupon->getFreeDays(), "Coupon: {$couponCode}");
            }

            $coupon->use();

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
     * Calculate effective price with coupon discount
     */
    private function calculateEffectivePrice(float $basePrice, ?string $couponCode = null): float
    {
        if (!$couponCode) {
            return $basePrice;
        }

        $coupon = CouponCode::where('code', $couponCode)->first();
        if (!$coupon || !$coupon->isValid()) {
            return $basePrice;
        }

        return $basePrice - $coupon->getDiscountAmount($basePrice);
    }

    private function getPlanDetails(string $planType): ?array
    {
        // Billing config removed for simplicity - implement pricing logic directly here
        $billingConfig = [];
        
        if (isset($billingConfig[$planType])) {
            return $billingConfig[$planType];
        }

        // Fallback to legacy config
        $pricing = config('shopify-enhanced.subscription_pricing', [
            'monthly' => 29.99,
            'yearly' => 299.99,
            'lifetime' => 999.99
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
                'interval' => 'ANNUAL', // Closest to lifetime
                'price' => $pricing['lifetime']
            ]
        ];

        return $plans[$planType] ?? null;
    }
}