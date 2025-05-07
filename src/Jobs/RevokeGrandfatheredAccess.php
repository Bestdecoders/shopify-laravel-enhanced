<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Bestdecoders\ShopifyLaravelEnhanced\Traits\ResolvesShop;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ClientGrandfatheredMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminAllNotification;

class RevokeGrandfatheredAccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolvesShop;

    public function handle(): void
    {
        $userModel = config('shopify-enhanced.user_model');

        if (!class_exists($userModel)) {
            Log::error("User model class defined in config does not exist.");
            return;
        }

        $expiredShops = $userModel::where('shopify_grandfathered', true)
            ->whereNotNull('grandfather_access_valid_until')
            ->where('grandfather_access_valid_until', '<=', now())
            ->pluck('name');

        foreach ($expiredShops as $shop) {
            $user = $this->getShop($shop);

            if (!$user) {
                Log::warning("No user found for expired grandfathered shop: {$shop}");
                continue;
            }

            $user->forceFill([
                'shopify_grandfathered' => false,
                'grandfather_access_valid_until' => null,
            ])->save();

            $this->notifyAdmin($user);
            $this->notifyClient($user);
        }
    }

    protected function notifyAdmin($user): void
    {
        try {
            $adminEmail = config('shopify-enhanced.admin_email');

            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(app(AdminAllNotification::class, [
                    'shopDomain' => $user->name,
                    'data' => [],
                    'subject' => 'Free Access Revoked - ' . $user->name,
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify admin about {$user->name}: {$e->getMessage()}");
        }
    }

    protected function notifyClient($user): void
    {
        try {
            $clientEmail = $user->owner_email;

            if ($clientEmail && filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($clientEmail)->send(app(ClientGrandfatheredMail::class, [
                    'user' => $user,
                    'message' => 'Your free access period has ended. Please subscribe to continue using the app.',
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify client ({$user->name}): {$e->getMessage()}");
        }
    }
}
