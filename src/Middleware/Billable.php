<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use Osiset\ShopifyApp\Util;

class Billable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Util::getShopifyConfig('billing_enabled')) {
            return $next($request);
        }

        if (!Util::useNativeAppBridge() && !$request->ajax()) {
            return $next($request);
        }

   

        if (Util::getShopifyConfig('billing_enabled') === true) {
            $shop = auth()->user();
            if (!$shop->plan && !$shop->isFreemium() && !$shop->isGrandfathered() && $request->ajax()) {
                $redirectUrl = route(
                    Util::getShopifyConfig('route_names.billing'),
                    array_merge($request->input(), [
                        'shop' => $shop->getDomain()->toNative(),
                        'host' => $request->get('host'),
                    ])
                );
                return response()->json(['forceRedirectUrl' => $redirectUrl], 403);
            }
        }

        return $next($request);
    }
}

