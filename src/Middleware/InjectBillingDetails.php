<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InjectBillingDetails
{
    /**
     * Handle an incoming request and inject billing details into the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // First try to get shop 
            $shop = auth()->user();


            // get shop  from request (set by ExtractShopName middleware)
            if ($request->has('shop')) {
                $shopName = $request->input('shop');
                // Get shop model from database using the shop name
                $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
                $shop = $userModel::where('name', $shopName)->first();
            }


            if (!$shop) {
                Log::warning('InjectBillingDetails: No shop found in request or authenticated user');
                return $next($request);
            }

            // Get the current active charge
            $activeCharge = $shop->charges()
                ->where('status', 'active')
                ->whereNull('cancelled_on')
                ->with('plan')
                ->latest()
                ->first();

            // Get current plan details
            $currentPlan = $shop->plan;

            // Build billing details object
            $billingDetails = [
                // Basic billing status
                'is_billable' => $shop->isFreemium() === false && $shop->isGrandfathered() === false,
                'is_freemium' => $shop->isFreemium(),
                'is_grandfathered' => $shop->isGrandfathered(),
                'has_active_subscription' => $activeCharge !== null,

                // Plan information
                'current_plan' => $currentPlan ? [
                    'id' => $currentPlan->id,
                    'name' => $currentPlan->name,
                    'type' => $currentPlan->type,
                    'price' => $currentPlan->price,
                    'interval' => $currentPlan->interval,
                    'trial_days' => $currentPlan->trial_days,
                    'features' => $currentPlan->features ?? [],
                ] : null,

                // Active charge information
                'active_charge' => $activeCharge ? [
                    'id' => $activeCharge->id,
                    'charge_id' => $activeCharge->charge_id,
                    'status' => $activeCharge->status,
                    'price' => $activeCharge->price,
                    'interval' => $activeCharge->interval,
                    'trial_days' => $activeCharge->trial_days,
                    'trial_ends_on' => $activeCharge->trial_ends_on,
                    'activated_on' => $activeCharge->activated_on,
                    'billing_on' => $activeCharge->billing_on,
                    'created_at' => $activeCharge->created_at,
                    'updated_at' => $activeCharge->updated_at,
                ] : null,

                // Subscription status calculations
                'subscription_status' => $this->getSubscriptionStatus($shop, $activeCharge),
                'days_until_billing' => $this->getDaysUntilBilling($activeCharge),
                'is_trial_active' => $this->isTrialActive($activeCharge),
                'trial_days_remaining' => $this->getTrialDaysRemaining($activeCharge),

                // Shop billing context
                'shop_domain' => $shop->getDomain()->toNative(),
                'shop_id' => $shop->id,
            ];

            // Inject billing details into the request
            $request->merge(['billing_details' => $billingDetails]);

            // Also make it available as a request attribute for easier access
            $request->attributes->set('billing_details', $billingDetails);

            Log::debug('InjectBillingDetails: Billing details injected', [
                'shop_domain' => $shop->getDomain()->toNative(),
                'subscription_status' => $billingDetails['subscription_status'],
                'has_active_subscription' => $billingDetails['has_active_subscription'],
            ]);
        } catch (\Exception $e) {
            Log::error('InjectBillingDetails: Error injecting billing details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Continue without billing details if there's an error
        }

        return $next($request);
    }

    /**
     * Determine the overall subscription status
     */
    protected function getSubscriptionStatus($shop, $activeCharge): string
    {
        if ($shop->isGrandfathered()) {
            return 'grandfathered';
        }

        if ($shop->isFreemium()) {
            return 'freemium';
        }

        if (!$activeCharge) {
            return 'no_subscription';
        }

        if ($activeCharge->status === 'active') {
            if ($this->isTrialActive($activeCharge)) {
                return 'trial';
            }
            return 'paid';
        }

        return $activeCharge->status; // pending, cancelled, etc.
    }

    /**
     * Calculate days until next billing
     */
    protected function getDaysUntilBilling($activeCharge): ?int
    {
        if (!$activeCharge || !$activeCharge->billing_on) {
            return null;
        }

        $billingDate = \Carbon\Carbon::parse($activeCharge->billing_on);
        $now = \Carbon\Carbon::now();

        if ($billingDate->isPast()) {
            return 0;
        }

        return $now->diffInDays($billingDate);
    }

    /**
     * Check if trial is currently active
     */
    protected function isTrialActive($activeCharge): bool
    {
        if (!$activeCharge || !$activeCharge->trial_ends_on) {
            return false;
        }

        $trialEndDate = \Carbon\Carbon::parse($activeCharge->trial_ends_on);
        return $trialEndDate->isFuture();
    }

    /**
     * Calculate remaining trial days
     */
    protected function getTrialDaysRemaining($activeCharge): ?int
    {
        if (!$this->isTrialActive($activeCharge)) {
            return null;
        }

        $trialEndDate = \Carbon\Carbon::parse($activeCharge->trial_ends_on);
        $now = \Carbon\Carbon::now();

        return $now->diffInDays($trialEndDate);
    }
}
