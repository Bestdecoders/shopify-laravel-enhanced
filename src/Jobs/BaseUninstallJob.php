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
use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;

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

            $this->safelyNotifyAdmin($shop);
            $this->safelyNotifyShopOwner($shop);

            parent::handle($shopCommand, $shopQuery, $cancelCurrentPlanAction);

            return true;

        } catch (\Exception $e) {
            Log::error("Error in BaseUninstallJob for shop {$this->domain->toNative()}: {$e->getMessage()}");
            return false;
        }
    }

    protected function safelyNotifyAdmin($shop): void
    {
        $telegram = app(TelegramService::class);

        if (!$telegram->isConfigured()) {
            \debug_log("Telegram not configured. Skipping admin notification for shop: {$shop->name}");
            return;
        }

        try {
            $shopDomain = $shop->name ?? 'Unknown';

            /** @var \App\Models\User $user */
            $user = config('shopify-enhanced.user_model')::where('name', $shopDomain)->first();

            if (!$user) {
                \debug_log("User not found in database for shop: {$shopDomain}");
                return;
            }

            $hasActiveTrial = (bool) $user->charges()->where('trial_ends_on', '>', now())->first();
            $trialStatus = $hasActiveTrial ? 'Running' : 'No charge found';

            $message = $telegram->formatMessage([
                'title' => '⚠️ App Uninstalled',
                'lines' => [
                    ['key' => 'Shop', 'value' => $shopDomain],
                    ['key' => 'Owner Mail', 'value' => $user->owner_email ?? 'N/A'],
                    ['key' => 'Plan', 'value' => $user->plan_id ?? 'N/A'],
                    ['key' => 'Trial', 'value' => $trialStatus],
                    ['key' => 'Installed', 'value' => $user->updated_at?->diffForHumans() ?? 'N/A'],
                ],
                'footer' => 'A customer has uninstalled your app.',
            ]);

            $telegram->send($message);
            \debug_log("Admin notified via Telegram about uninstall for shop: {$shopDomain}");
        } catch (\Exception $e) {
            Log::error("Failed to notify admin for {$shop->name}: {$e->getMessage()}");
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
