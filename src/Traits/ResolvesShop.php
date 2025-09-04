<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Traits;

use Illuminate\Support\Facades\Auth;

trait ResolvesShop
{
    protected function getShop($shop)
    {
        if (Auth::check()) {
            return Auth::user();
        }
        $userModel = config('shopify-enhanced.user_model');
        if (!class_exists($userModel)) {
            throw new \Exception("The user model class '{$userModel}' does not exist.");
        }
        return $userModel::where('name', $shop)->first();
    }
}
