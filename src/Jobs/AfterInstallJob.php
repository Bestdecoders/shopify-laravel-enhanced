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
use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;

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
     * Notify admin about installation via Telegram.
     *
     * @param array $shopInfo
     * @return void
     */
    protected function notifyAdmin(array $shopInfo): void
    {
        $telegram = app(TelegramService::class);

        if (!$telegram->isConfigured()) {
            \debug_log("Telegram not configured. Skipping admin notification for shop: {$this->shop->name}");
            return;
        }

        try {
            $shopDomain = $this->shop->name;
            $userModel = config('shopify-enhanced.user_model');
            $user = $userModel::where('name', $shopDomain)->first();

            $ownerMail = $user->owner_email ?? 'N/A';
            $planName = $shopInfo['shop']['plan']['displayName'] ?? 'N/A';

            $message = $telegram->formatMessage([
                'title' => '🎉 New Installation',
                'lines' => [
                    ['key' => 'Shop', 'value' => $shopDomain],
                    ['key' => 'Owner Mail', 'value' => $ownerMail],
                    ['key' => 'Plan', 'value' => $planName],
                ],
                'footer' => 'A new customer has installed your app.',
            ]);

            $telegram->send($message);
            \debug_log("Admin notified via Telegram about installation for shop: {$this->shop->name}");
        } catch (\Exception $e) {
            Log::error("Failed to notify admin for {$this->shop->name}: {$e->getMessage()}");
        }
    }
}
