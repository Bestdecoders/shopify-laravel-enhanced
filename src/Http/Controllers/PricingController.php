<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Inertia\Inertia;
    use Osiset\ShopifyApp\Util;

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
                // Get user model from config
                $userModelClass = config('shopify-enhanced.user_model');

                // Get shop from authenticated user or request parameters
                $shop = auth()->user();
                $shopDomain = $shop ? $shop->getDomain()->toNative() : ($request->get('shop') ?? session('shopify_domain'));

                // If no authenticated user, try to find by domain
                if (!$shop && $shopDomain) {
                    $shop = $userModelClass::where('name', $shopDomain)->first();
                }

                // Sample plans data - replace with actual database logic
                $plans = [
                    [
                        'id' => 1,
                        'name' => 'Free Plan',
                        'price' => 0,
                        'interval' => 'EVERY_30_DAYS',
                        'terms' => 'Perfect for getting started with basic features',
                        'features' => [
                            'Basic table of contents',
                            'Up to 5 articles',
                            'Community support'
                        ]
                    ],
                    [
                        'id' => 2,
                        'name' => 'Pro Plan',
                        'price' => 9.99,
                        'interval' => 'EVERY_30_DAYS',
                        'terms' => 'Advanced features for growing businesses',
                        'features' => [
                            'Advanced table of contents',
                            'Unlimited articles',
                            'Custom styling',
                            'Priority support'
                        ]
                    ],
                    [
                        'id' => 3,
                        'name' => 'Enterprise Plan',
                        'price' => 29.99,
                        'interval' => 'EVERY_30_DAYS',
                        'terms' => 'Complete solution for large organizations',
                        'features' => [
                            'All Pro features',
                            'Custom integrations',
                            'White-label options',
                            '24/7 dedicated support'
                        ]
                    ]
                ];

                // Get current plan from shop data or default to free plan
                $currentPlan = [
                    'id' => 1,
                    'name' => 'Free Plan',
                    'price' => 0
                ];

                // If shop exists and has plan data, use it
                if ($shop && $shop->plan) {
                    $currentPlan = [
                        'id' => $shop->plan->id ?? 1,
                        'name' => $shop->plan->name ?? 'Free Plan',
                        'price' => $shop->plan->price ?? 0
                    ];
                }

                return response()->json([
                    'current_plan' => $currentPlan,
                    'plans' => $plans,
                    'shop' => $shop ? $shop->getDomain()->toNative() : $shopDomain,
                    'billing_enabled' => Util::getShopifyConfig('billing_enabled', false)
                ]);
            } catch (\Exception $e) {
                // Fallback to sample data if there's any error
                $plans = [
                    [
                        'id' => 1,
                        'name' => 'Free Plan',
                        'price' => 0,
                        'interval' => 'EVERY_30_DAYS',
                        'terms' => 'Perfect for getting started with basic features',
                        'features' => [
                            'Basic table of contents',
                            'Up to 5 articles',
                            'Community support'
                        ]
                    ],
                    [
                        'id' => 2,
                        'name' => 'Pro Plan',
                        'price' => 9.99,
                        'interval' => 'EVERY_30_DAYS',
                        'terms' => 'Advanced features for growing businesses',
                        'features' => [
                            'Advanced table of contents',
                            'Unlimited articles',
                            'Custom styling',
                            'Priority support'
                        ]
                    ]
                ];

                $currentPlan = [
                    'id' => 1,
                    'name' => 'Free Plan',
                    'price' => 0
                ];

                return response()->json([
                    'current_plan' => $currentPlan,
                    'plans' => $plans,
                    'error' => 'Could not fetch user data, using defaults'
                ]);
            }
        }

        /**
         * Get plan subscription URL
         */
        public function getPlanSubscriptionUrl(Request $request)
        {
            $planId = $request->input('planId');
            $shop = auth()->user();

            // Check if billing is enabled
            if (!Util::getShopifyConfig('billing_enabled')) {
                return response()->json([
                    'error' => 'Billing is not enabled'
                ], 400);
            }

            // Generate confirmation URL using Shopify route configuration
            $billingRoute = Util::getShopifyConfig('route_names.billing', 'billing');
            $confirmationUrl = route($billingRoute, [
                'shop' => $shop ? $shop->getDomain()->toNative() : $request->get('shop'),
                'planId' => $planId,
                'host' => $request->get('host')
            ]);

            return response()->json([
                'confirmationUrl' => $confirmationUrl,
                'forceRedirectUrl' => null
            ]);
        }
    }
