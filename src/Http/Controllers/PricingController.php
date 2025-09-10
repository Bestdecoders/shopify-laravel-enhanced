<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Inertia\Inertia;
    use Osiset\ShopifyApp\Util;
    use Osiset\ShopifyApp\Storage\Models\Plan;
    use App\Models\User;

    class PricingController extends Controller
    {
        /**
         * Display the pricing page
         */
        public function index()
        {
            return Inertia::render('Pricing');
        }

        /**
         * Get plan details for API endpoint
         */
        public function getPlanDetails(Request $request)
        {
            try {
                $shop = $request->input('shop') ?? auth()->user()?->getDomain()->toNative() ?? session('shopify_domain');
                $user_details = User::where('name', $shop)->first();
                $current_plan = $user_details?->plan;
                $plans = Plan::all();
                
                return response()->json([
                    'current_plan' => $current_plan,
                    'plans' => $plans,
                    'shop' => $shop,
                    'billing_enabled' => Util::getShopifyConfig('billing_enabled', false)
                ]);
            } catch (\Exception $e) {
                // Fallback to empty data if there's any error
                return response()->json([
                    'current_plan' => null,
                    'plans' => [],
                    'error' => 'Could not fetch plan data: ' . $e->getMessage()
                ]);
            }
        }

        /**
         * Get plan subscription URL
         */
        public function getPlanSubscriptionUrl(Request $request)
        {
            try {
                $plan_id = $request->input('planId');
                $shop = $request->input('shop');
                $host = $request->get('host');

                // Check if billing is enabled
                if (!Util::getShopifyConfig('billing_enabled')) {
                    return response()->json([
                        'error' => 'Billing is not enabled'
                    ], 400);
                }

                // Validate required parameters
                if (!$plan_id || !$shop) {
                    return response()->json([
                        'error' => 'Missing required parameters: planId or shop'
                    ], 400);
                }

                $redirectUrl = route(
                    Util::getShopifyConfig('route_names.billing'),
                    [
                        'shop' => $shop,
                        'host' => $host,
                        'plan' => $plan_id,
                    ]
                );

                return response()->json(['forceRedirectUrl' => $redirectUrl], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to generate subscription URL: ' . $e->getMessage()
                ], 500);
            }
        }
    }
