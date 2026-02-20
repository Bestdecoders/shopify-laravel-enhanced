<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use Osiset\ShopifyApp\Util;

class Billable
{
    public function handle(Request $request, Closure $next): Response
    {
        debug_log('appeared');
        if (!Util::getShopifyConfig('billing_enabled')) {
            return $next($request);
        }

        // Skip billing check for non-AJAX requests (removed deprecated useNativeAppBridge check)
        if (!$request->ajax()) {
            return $next($request);
        }

        if (Util::getShopifyConfig('billing_enabled') === true) {
            $shop = auth()->user();
            if (!$shop->plan && !$shop->isFreemium() && !$shop->isGrandfathered() && $request->ajax()) {
                $redirectUrl = route(
                    Util::getShopifyConfig('route_names.billing'),
                    array_merge($request->input(), [
                        'shop' => $shop->getDomain()->toNative(),
                        'host' => $request->input('host'),
                    ])
                );
                debug_log($redirectUrl);
                return response()->json(['forceRedirectUrl' => $redirectUrl], 403);
            }
        }

        return $next($request);
    }
}
