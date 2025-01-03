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
        Log::info("BaseUninstallJob initialized for shop: {$domain}");
    }

    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        try {

            // Convert the string domain to a ShopDomain object
            $shopDomain = ShopDomain::fromNative($this->domain);
            Log::info("BaseUninstallJob triggered for shop: {$shopDomain->toNative()}");

            // Fetch the shop using ShopQuery
            $shop = $shopQuery->getByDomain($shopDomain);

            // Notify the admin
            $this->notifyAdmin($shopDomain->toNative());

            // Notify the shop owner
            $this->notifyShopOwner($shop);

            parent::handle($shopCommand, $shopQuery, $cancelCurrentPlanAction);

            return true;
        } catch (\Exception $e) {
            Log::error("Error in BaseUninstallJob for shop {$this->domain->toNative()}: {$e->getMessage()}");
            return false;
        }
    }

    private function notifyAdmin(string $shopDomain): void
    {
        $adminEmail = config('shopify-enhanced.admin_email');
        if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($adminEmail)->send(new AdminUninstallNotification($shopDomain));
            Log::info("Admin notified about uninstall for shop: {$shopDomain}");
        } else {
            Log::warning("Invalid or missing admin email. Notification not sent.");
        }
    }

    private function notifyShopOwner($shop): void
    {
        $shopEmail = $shop->email ?? null;
        if ($shopEmail && filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($shopEmail)->send(new UserUninstallNotification($this->domain));
            Log::info("Shop owner notified about uninstall for shop: {$this->domain}");
        } else {
            Log::warning("Invalid or missing shop email for {$this->domain}. Notification not sent.");
        }
    }
}
