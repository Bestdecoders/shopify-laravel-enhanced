<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ClientGrandfatheredMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminAllNotification;
use Bestdecoders\ShopifyLaravelEnhanced\Traits\ResolvesShop;
use Illuminate\Support\Facades\Log;

class GrantGrandfatheredAccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolvesShop;

    public function __construct(public string $shop, public \DateTimeInterface $validUntil) {}

    public function handle()
    {
        $user = $this->getShop($this->shop);

        $user->forceFill([
            'shopify_grandfathered' => true,
            'grandfather_access_valid_until' => $this->validUntil,
        ])->save();

        $adminEmail = config('shopify-enhanced.admin_email');

        Mail::to($adminEmail)->send(app(AdminAllNotification::class, [
            'shopDomain' => $user->name,
            'data' => [],
            'subject' => 'Free Access Granted - ' . $user->name.'until '. $this->validUntil->format('F j, Y H:i') ,
        ]));

        Mail::to($user->owner_email)
            ->send(app(ClientGrandfatheredMail::class, [
                'user' => $user,
                'message' => 'Your free access is active until ' . $this->validUntil->format('F j, Y H:i'),
            ]));
    }
}
