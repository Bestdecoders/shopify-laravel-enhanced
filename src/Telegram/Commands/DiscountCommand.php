<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands;

use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;
use Bestdecoders\ShopifyLaravelEnhanced\Models\CouponCode;
use Bestdecoders\ShopifyLaravelEnhanced\Services\SubscriptionManagementService;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Discount Command
 *
 * Usage: /discount [action] [parameters]
 * Actions:
 * - /discount create [code] [type] [value] [description] - Create a new discount coupon
 * - /discount apply [user_shop_domain] [coupon_code] - Apply a discount to a user
 * - /discount list - List all active discount coupons
 * - /discount info [code] - Show information about a specific coupon
 *
 * Examples:
 * - /discount create SAVE10 percentage 10 "10% off coupon"
 * - /discount apply myshop.myshopify.com SAVE10
 * - /discount list
 * - /discount info SAVE10
 */
class DiscountCommand
{
    /**
     * Handle command
     *
     * @param TelegramService $telegram The Telegram service (injected by WebhookHandler)
     * @return void
     */
    public function handle(TelegramService $telegram): void
    {
        // Get update from Telegram service context
        $update = $telegram->getLastUpdate();

        if (empty($update)) {
            return;
        }

        $message = $update['message'] ?? [];
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;

        if (empty($chatId)) {
            return;
        }

        // Remove /discount command prefix to get just the arguments
        $commandText = str_replace('/discount', '', $text);
        $commandText = trim($commandText);

        if (empty($commandText)) {
            $helpMessage = $this->getHelpMessage();
            $telegram->sendToChat($chatId, $helpMessage);
            return;
        }

        try {
            $parts = explode(' ', $commandText);
            $action = $parts[0] ?? '';

            switch ($action) {
                case 'create':
                    $this->createDiscount($telegram, $chatId, $parts);
                    break;
                case 'apply':
                    $this->applyDiscount($telegram, $chatId, $parts);
                    break;
                case 'list':
                    $this->listDiscounts($telegram, $chatId);
                    break;
                case 'info':
                    $this->discountInfo($telegram, $chatId, $parts);
                    break;
                case 'help':
                    $telegram->sendToChat($chatId, $this->getHelpMessage());
                    break;
                default:
                    $telegram->sendToChat($chatId, "Unknown action: {$action}. Use /discount help for available commands.");
            }

        } catch (\Exception $e) {
            Log::error('Telegram Discount Command error', [
                'error' => $e->getMessage(),
                'user' => $userId,
                'command' => $commandText,
            ]);

            $telegram->sendToChat($chatId, 'Sorry, I encountered an error processing your discount command. Please try again.');
        }
    }

    /**
     * Create a new discount coupon
     */
    protected function createDiscount(TelegramService $telegram, string $chatId, array $parts): void
    {
        if (count($parts) < 4) {
            $telegram->sendToChat($chatId, "Usage: /discount create [code] [type] [value] [description]\nExample: /discount create SAVE10 percentage 10 \"10% off coupon\"");
            return;
        }

        $code = $parts[1] ?? '';
        $type = $parts[2] ?? '';
        $value = $parts[3] ?? '';
        $description = implode(' ', array_slice($parts, 4));

        // Validate discount type
        $validTypes = [CouponCode::TYPE_PERCENTAGE, CouponCode::TYPE_FIXED, CouponCode::TYPE_FREE_DAYS];
        if (!in_array($type, $validTypes)) {
            $telegram->sendToChat($chatId, "Invalid discount type. Valid types: " . implode(', ', $validTypes));
            return;
        }

        // Validate value
        if (!is_numeric($value) || $value <= 0) {
            $telegram->sendToChat($chatId, "Invalid discount value. Must be a positive number.");
            return;
        }

        // Check if coupon already exists
        $existingCoupon = CouponCode::where('code', $code)->first();
        if ($existingCoupon) {
            $telegram->sendToChat($chatId, "Coupon code '{$code}' already exists.");
            return;
        }

        try {
            // Create the coupon
            $coupon = CouponCode::create([
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'description' => $description,
                'max_uses' => null, // Unlimited uses by default
                'used_count' => 0,
                'expires_at' => null, // Never expires by default
                'is_active' => true,
                'minimum_amount' => 0,
                'applicable_plans' => null, // Applicable to all plans by default
                'created_by' => 'telegram',
                'metadata' => [
                    'created_via' => 'telegram',
                    'created_by_user_id' => $chatId
                ]
            ]);

            $message = "✅ Discount coupon created successfully!\n\n";
            $message .= "<b>Code:</b> {$coupon->code}\n";
            $message .= "<b>Type:</b> {$coupon->type}\n";
            $message .= "<b>Value:</b> {$coupon->value}" . ($type === CouponCode::TYPE_PERCENTAGE ? '%' : ($type === CouponCode::TYPE_FIXED ? '$' : ' days')) . "\n";
            $message .= "<b>Description:</b> {$coupon->description}\n";
            $message .= "<b>Status:</b> Active";

            $telegram->sendToChat($chatId, $message);

        } catch (\Exception $e) {
            Log::error('Error creating discount coupon', ['error' => $e->getMessage()]);
            $telegram->sendToChat($chatId, 'Error creating discount coupon: ' . $e->getMessage());
        }
    }

    /**
     * Apply a discount to a user
     */
    protected function applyDiscount(TelegramService $telegram, string $chatId, array $parts): void
    {
        if (count($parts) < 3) {
            $telegram->sendToChat($chatId, "Usage: /discount apply [user_shop_domain] [coupon_code]\nExample: /discount apply myshop.myshopify.com SAVE10");
            return;
        }

        $shopDomain = $parts[1] ?? '';
        $couponCode = $parts[2] ?? '';

        // Get user by shop domain - in Shopify apps, shop name is typically stored in 'name' column
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        $user = $userModel::where('name', $shopDomain)->first();

        if (!$user) {
            $telegram->sendToChat($chatId, "User with shop domain '{$shopDomain}' not found.");
            return;
        }

        // Get subscription service
        $subscriptionService = app(SubscriptionManagementService::class);

        // Apply the coupon using the subscription service
        $applied = $subscriptionService->applyCoupon($user, $couponCode);

        if ($applied) {
            $message = "✅ Discount applied successfully!\n\n";
            $message .= "<b>Shop:</b> {$user->name}\n";
            $message .= "<b>Coupon:</b> {$couponCode}\n";
            $message .= "<b>User:</b> {$user->name}";

            $telegram->sendToChat($chatId, $message);
        } else {
            $telegram->sendToChat($chatId, "Failed to apply coupon '{$couponCode}' to user with shop domain '{$shopDomain}'. Check if the coupon is valid and hasn't expired.");
        }
    }

    /**
     * List all active discount coupons
     */
    protected function listDiscounts(TelegramService $telegram, string $chatId): void
    {
        $coupons = CouponCode::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($coupons->isEmpty()) {
            $telegram->sendToChat($chatId, "No active discount coupons found.");
            return;
        }

        $message = "🏷️ <b>Active Discount Coupons</b> (Showing most recent 10)\n\n";

        foreach ($coupons as $coupon) {
            $message .= "<b>Code:</b> {$coupon->code}\n";
            $message .= "<b>Type:</b> {$coupon->type}\n";
            $message .= "<b>Value:</b> {$coupon->value}" . ($coupon->type === CouponCode::TYPE_PERCENTAGE ? '%' : ($coupon->type === CouponCode::TYPE_FIXED ? '$' : ' days')) . "\n";
            $message .= "<b>Used:</b> {$coupon->used_count}" . ($coupon->max_uses ? "/{$coupon->max_uses}" : '') . "\n";
            $message .= "<b>Expires:</b> " . ($coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i') : 'Never') . "\n";
            $message .= "<b>Description:</b> {$coupon->description}\n\n";
        }

        $telegram->sendToChat($chatId, $message);
    }

    /**
     * Show information about a specific coupon
     */
    protected function discountInfo(TelegramService $telegram, string $chatId, array $parts): void
    {
        if (count($parts) < 2) {
            $telegram->sendToChat($chatId, "Usage: /discount info [code]\nExample: /discount info SAVE10");
            return;
        }

        $code = $parts[1] ?? '';
        
        $coupon = CouponCode::where('code', $code)->first();

        if (!$coupon) {
            $telegram->sendToChat($chatId, "Coupon code '{$code}' not found.");
            return;
        }

        $message = "🏷️ <b>Coupon Information</b>\n\n";
        $message .= "<b>Code:</b> {$coupon->code}\n";
        $message .= "<b>Type:</b> {$coupon->type}\n";
        $message .= "<b>Value:</b> {$coupon->value}" . ($coupon->type === CouponCode::TYPE_PERCENTAGE ? '%' : ($coupon->type === CouponCode::TYPE_FIXED ? '$' : ' days')) . "\n";
        $message .= "<b>Status:</b> " . ($coupon->is_active ? 'Active' : 'Inactive') . "\n";
        $message .= "<b>Used Count:</b> {$coupon->used_count}" . ($coupon->max_uses ? "/{$coupon->max_uses}" : '') . "\n";
        $message .= "<b>Minimum Amount:</b> $" . number_format($coupon->minimum_amount, 2) . "\n";
        $message .= "<b>Expires:</b> " . ($coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i') : 'Never') . "\n";
        $message .= "<b>Applicable Plans:</b> " . ($coupon->applicable_plans ? implode(', ', $coupon->applicable_plans) : 'All plans') . "\n";
        $message .= "<b>Description:</b> {$coupon->description}\n";
        $message .= "<b>Created:</b> {$coupon->created_at->format('Y-m-d H:i')}";

        $telegram->sendToChat($chatId, $message);
    }

    /**
     * Get help message
     */
    protected function getHelpMessage(): string
    {
        $message = "🏷️ <b>Discount Command Help</b>\n\n";
        $message .= "<b>/discount create</b> [code] [type] [value] [description]\n";
        $message .= "   Create a new discount coupon\n";
        $message .= "   Types: percentage, fixed, free_days\n";
        $message .= "   Example: /discount create SAVE10 percentage 10 \"10% off coupon\"\n\n";
        $message .= "<b>/discount apply</b> [user_shop_domain] [coupon_code]\n";
        $message .= "   Apply a discount to a user\n";
        $message .= "   Example: /discount apply myshop.myshopify.com SAVE10\n\n";
        $message .= "<b>/discount list</b>\n";
        $message .= "   List all active discount coupons\n\n";
        $message .= "<b>/discount info</b> [code]\n";
        $message .= "   Show information about a specific coupon\n";
        $message .= "   Example: /discount info SAVE10\n\n";
        $message .= "<b>/discount help</b>\n";
        $message .= "   Show this help message";

        return $message;
    }
}