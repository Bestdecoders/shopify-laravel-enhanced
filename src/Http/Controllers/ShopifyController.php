<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Bestdecoders\ShopifyLaravelEnhanced\Helpers\Queries;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ShopifyGraphqlService;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ThanksMail;
class ShopifyController extends Controller
{
    public function test()
    {
        return response()->json(['message' => 'Shopify Enhanced Package is working!']);
    }

    public function getShopInfo($shop)
{
    $query = Queries::getQuery(config('shopify-enhanced.shop_query'));
    $service = app(ShopifyGraphqlService::class);

    $shopInfo = $service->execute($shop, $query);

    return response()->json([
        'name' => $shopInfo['shop']['name'] ?? null,
        'email' => $shopInfo['shop']['email'] ?? null,
        'primaryDomain' => $shopInfo['shop']['primaryDomain']['url'] ?? null,
    ]);
}

public function sendThanksEmail($shop)
    {
        $query = config('shopify-enhanced.queries.shop');
        $service = app(ShopifyGraphqlService::class);

        $shopInfo = $service->execute($shop, $query);

        if (isset($shopInfo['shop']['email'])) {
            Mail::to($shopInfo['shop']['email'])->send(new ThanksMail($shopInfo['shop']));
            return response()->json(['message' => 'Thanks email sent!']);
        }

        return response()->json(['message' => 'Shop email not found.'], 404);
    }


}
