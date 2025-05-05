<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ThanksMail;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ShopifyGraphqlService;

class AfterInstallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected object $shop;

    /**
     * Constructor.
     *
     * @param object $shop Shopify shop object.
     */
    public function __construct(object $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \debug_log("Processing app installation for shop: " . json_encode($this->shop));

            // Fetch shop info via GraphQL
            $shopInfo = $this->fetchShopInfo($this->shop->name);

            // Update shop email
            $shopEmail = $shopInfo['shop']['email'] ?? null;
            $this->updateShopEmail($shopEmail);

            // Send thanks mail to shop
            $this->sendThanksMail($shopEmail, $shopInfo['shop']);

            // Notify admin
            $this->notifyAdmin($shopInfo['shop']);
        } catch (\Exception $e) {
            Log::error("Error handling AfterInstallJob for shop {$this->shop->name}: {$e->getMessage()}");
        }
    }

    /**
     * Fetch shop information via GraphQL.
     *
     * @param string $shopDomain
     * @return array
     */
    protected function fetchShopInfo(string $shopDomain): array
    {
        $service = app(ShopifyGraphqlService::class);
        $query = config('shopify-enhanced.queries.shop');
        $response = $service->execute($shopDomain, $query);
        // Convert ResponseAccess to an array
        return $response->toArray();
    }


    /**
     * Update the shop email in the database.
     *
     * @param string|null $shopEmail
     * @return void
     */
    protected function updateShopEmail(?string $shopEmail): void
    {
        if ($shopEmail) {
            $this->shop->owner_email = $shopEmail;
            $this->shop->save();
            \debug_log("Shop email updated to {$shopEmail} for {$this->shop->name}");
        } else {
            Log::warning("Shop email not available for {$this->shop->name}");
        }
    }

    /**
     * Send thanks mail to the shop owner.
     *
     * @param string|null $shopEmail
     * @param array $shopInfo
     * @return void
     */
    protected function sendThanksMail(?string $shopEmail, array $shopInfo): void
    {
        if ($shopEmail && filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($shopEmail)->send(app(ThanksMail::class, [
                'shopInfo' => $shopInfo,
            ]));
            \debug_log("Thanks mail sent to {$shopEmail}");
        } else {
            Log::warning("Invalid or missing shop email for {$this->shop->name}");
        }
    }

    /**
     * Notify the admin about the installation.
     *
     * @param array $shopInfo
     * @return void
     */
    protected function notifyAdmin(array $shopInfo): void
    {
        $adminEmail = config('shopify-enhanced.admin_email');
        if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($adminEmail)->send(app(ThanksMail::class, [
                'shopInfo' => $shopInfo,
            ]));
            \debug_log("Admin notified about installation for shop: {$this->shop->name}");
        } else {
            Log::warning("Invalid or missing admin email. Notification not sent.");
        }
    }
}
