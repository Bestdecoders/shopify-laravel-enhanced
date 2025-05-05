<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Bestdecoders\ShopifyLaravelEnhanced\Traits\ResolvesShop;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ClientGrandfatheredMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminGrandfatheredAccessMail;

class RevokeExpiredGrandfatheredAccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolvesShop;

    public function handle()
    {

        $userModel = config('shopify-enhanced.user_model');

        $expiredShops = $userModel::where('shopify_grandfathered', true)
            ->whereNotNull('grandfather_access_valid_until')
            ->where('grandfather_access_valid_until', '<=', now())
            ->pluck('name');

        foreach ($expiredShops as $shop) {
            $user = $this->getShop($shop);

            if (!$user) {
                continue;
            }

            $user->update([
                'shopify_grandfathered' => false,
                'grandfather_access_valid_until' => null,
            ]);

            // Admin notification
            Mail::to(config('shopify-enhanced.admin_email'))
                ->send(app(AdminGrandfatheredAccessMail::class, [
                    'shop' => $user->name,
                    'email' => $user->owner_email,
                    'message' => 'Grandfathered access was revoked after expiry.',
                ]));

            // Client notification
            Mail::to($user->owner_email)
                ->send(app(ClientGrandfatheredMail::class, [
                    'user' => $user,
                    'message' => 'Your free access period has ended. Please subscribe to continue using the app.',
                ]));
        }
    }
}
