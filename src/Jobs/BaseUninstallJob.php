<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob as BaseAppUninstalledJob;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\UserUninstallNotification;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminUninstallNotification;

class BaseUninstallJob extends BaseAppUninstalledJob
{
    public function __construct(string $domain, \stdClass $data)
    {
        parent::__construct($domain, $data);
        \debug_log("BaseUninstallJob initialized for shop: {$domain}");
    }

    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        try {
            $shopDomain = ShopDomain::fromNative($this->domain);
            \debug_log("BaseUninstallJob triggered for shop: {$shopDomain->toNative()}");

            $shop = $shopQuery->getByDomain($shopDomain);

            $this->safelyNotifyAdmin($shop->name);
            $this->safelyNotifyShopOwner($shop);

            parent::handle($shopCommand, $shopQuery, $cancelCurrentPlanAction);

            return true;

        } catch (\Exception $e) {
            Log::error("Error in BaseUninstallJob for shop {$this->domain->toNative()}: {$e->getMessage()}");
            return false;
        }
    }

    protected function safelyNotifyAdmin(string $shopDomain): void
    {
        try {
            $adminEmail = config('shopify-enhanced.admin_email');

            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($adminEmail)->send(new AdminUninstallNotification($shopDomain));
                Mail::to($adminEmail)->send(app(AdminUninstallNotification::class, [
                    'shopDomain' => $shopDomain,
                ]));
                \debug_log("Admin notified about uninstall for shop: {$shopDomain}");
            } else {
                Log::warning("Invalid or missing admin email. Notification not sent.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify admin for {$shopDomain}: {$e->getMessage()}");
        }
    }

    protected function safelyNotifyShopOwner($shop): void
    
    {
        try {
            $shopEmail = $shop->owner_email ?? null;

            if ($shopEmail && filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($shopEmail)->send(new UserUninstallNotification($this->domain));
                Mail::to($shopEmail)->send(app(UserUninstallNotification::class, [
                    'shopDomain' => $this->domain,
                ]));
                \debug_log("Shop owner notified about uninstall for shop: {$this->domain}");
            } else {
                Log::warning("Invalid or missing shop email for {$this->domain}. Notification not sent.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify shop owner for {$this->domain}: {$e->getMessage()}");
        }
    }
}
