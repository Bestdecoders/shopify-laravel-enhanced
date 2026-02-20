<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands;

use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\GrantGrandfatheredAccess;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ClientGrandfatheredMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminGrandfatheredAccessMail;
use Bestdecoders\ShopifyLaravelEnhanced\Traits\ResolvesShop;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Telegram Grandfathered Access Command
 *
 * Usage: /gf [action] [shopname] [duration?]
 * Actions:
 * - /gf [shopname] [duration] - Grant grandfathered access (1d, 12h, 30m, permanent)
 * - /gf list - List all grandfathered shops
 * - /gf revoke [shopname] - Manually revoke before expiration
 * - /gf help - Show help
 *
 * Examples:
 * - /gf myshop.myshopify.com 7d
 * - /gf myshop.myshopify.com 12h
 * - /gf myshop.myshopify.com 30m
 * - /gf myshop.myshopify.com permanent
 * - /gf list
 * - /gf revoke myshop.myshopify.com
 * - /gf help
 */
class GrandfatherCommand
{
    use ResolvesShop;

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
            Log::warning('GrandfatherCommand: No update found');
            return;
        }

        $message = $update['message'] ?? [];
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;

        if (empty($chatId)) {
            Log::warning('GrandfatherCommand: No chatId found', ['update' => $update]);
            return;
        }

        // Remove /gf command prefix to get just the arguments
        $commandText = str_replace('/gf', '', $text);
        $commandText = trim($commandText);

        Log::info('GrandfatherCommand: Processing', ['chatId' => $chatId, 'commandText' => $commandText]);

        if (empty($commandText)) {
            $telegram->sendToChat($chatId, $this->getHelpMessage());
            return;
        }

        try {
            $parts = explode(' ', $commandText);
            $action = $parts[0] ?? '';

            switch ($action) {
                case 'list':
                    Log::info('GrandfatherCommand: list action');
                    $this->list($telegram, $chatId);
                    break;
                case 'revoke':
                    Log::info('GrandfatherCommand: revoke action');
                    $this->revoke($telegram, $chatId, $parts);
                    break;
                case 'help':
                    Log::info('GrandfatherCommand: help action');
                    $helpMsg = $this->getHelpMessage();
                    $result = $telegram->sendToChat($chatId, $helpMsg);
                    Log::info('GrandfatherCommand: help sent', ['result' => $result, 'chatId' => $chatId]);
                    break;
                default:
                    Log::info('GrandfatherCommand: grant action', ['shop' => $action]);
                    // Treat as grant: /gf [shopname] [duration]
                    $this->grant($telegram, $chatId, $parts);
            }

        } catch (\Exception $e) {
            Log::error('Telegram Grandfather Command error', [
                'error' => $e->getMessage(),
                'user' => $userId,
                'command' => $commandText,
            ]);

            $telegram->sendToChat($chatId, 'Sorry, I encountered an error processing your grandfathered access command. Please try again.');
        }
    }

    /**
     * Grant grandfathered access to a shop
     */
    protected function grant(TelegramService $telegram, string $chatId, array $parts): void
    {
        if (count($parts) < 2) {
            $telegram->sendToChat($chatId, "Usage: /gf [shopname] [duration]\nExample: /gf myshop.myshopify.com 7d\n\nUse /gf help for more information.");
            return;
        }

        $shop = $parts[0] ?? '';
        $duration = $parts[1] ?? '';

        // Get user by shop domain
        $user = $this->getShop($shop);

        if (!$user) {
            $telegram->sendToChat($chatId, "Shop '{$shop}' not found.");
            return;
        }

        // Parse duration - returns array with 'valid' bool and 'date' (Carbon|null)
        $result = $this->parseDuration($duration);

        if (!$result['valid']) {
            $telegram->sendToChat($chatId, "Invalid duration format. Use: 1d, 12h, 30m, or permanent\nExample: /gf {$shop} 7d");
            return;
        }

        $validUntil = $result['date'];

        // Use GrantGrandfatheredAccess job
        GrantGrandfatheredAccess::dispatch($shop, $validUntil ?? now()->addYears(100));

        // Send Telegram notification to admin
        $validText = $validUntil ? $validUntil->format('F j, Y H:i') : 'Permanent';
        $message = "✅ Grandfathered access granted!\n\n";
        $message .= "<b>Shop:</b> {$user->name}\n";
        $message .= "<b>Email:</b> {$user->owner_email}\n";
        $message .= "<b>Valid Until:</b> {$validText}\n";
        $message .= "<b>Duration:</b> {$duration}";

        $telegram->sendToChat($chatId, $message);
    }

    /**
     * List all grandfathered shops
     */
    protected function list(TelegramService $telegram, string $chatId): void
    {
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);

        $grandfatheredUsers = $userModel::where('shopify_grandfathered', true)
            ->orderBy('grandfather_access_valid_until', 'desc')
            ->get();

        if ($grandfatheredUsers->isEmpty()) {
            $telegram->sendToChat($chatId, "No grandfathered shops found.");
            return;
        }

        $message = "🏆 <b>Grandfathered Shops</b> ({$grandfatheredUsers->count()} total)\n\n";

        foreach ($grandfatheredUsers as $user) {
            $validUntil = $user->grandfather_access_valid_until;
            $validText = $validUntil ? $validUntil->format('Y-m-d H:i') : 'Permanent';

            $isExpired = $validUntil && $validUntil->isPast();
            $statusIcon = $isExpired ? '⚠️' : '✅';

            $message .= "{$statusIcon} <b>{$user->name}</b>\n";
            $message .= "   Email: {$user->owner_email}\n";
            $message .= "   Valid Until: {$validText}\n";
            $message .= "   Owner: {$user->name}\n\n";
        }

        $telegram->sendToChat($chatId, $message);
    }

    /**
     * Manually revoke grandfathered access
     */
    protected function revoke(TelegramService $telegram, string $chatId, array $parts): void
    {
        if (count($parts) < 2) {
            $telegram->sendToChat($chatId, "Usage: /gf revoke [shopname]\nExample: /gf revoke myshop.myshopify.com");
            return;
        }

        $shop = $parts[1] ?? '';

        // Get user by shop domain
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        $user = $userModel::where('name', $shop)->first();

        if (!$user) {
            $telegram->sendToChat($chatId, "Shop '{$shop}' not found.");
            return;
        }

        if (!$user->shopify_grandfathered) {
            $telegram->sendToChat($chatId, "Shop '{$shop}' does not have grandfathered access.");
            return;
        }

        // Revoke access
        $user->shopify_grandfathered = false;
        $user->grandfather_access_valid_until = null;
        $user->save();

        // Send Telegram notification to admin
        $message = "🔁 Grandfathered access revoked!\n\n";
        $message .= "<b>Shop:</b> {$user->name}\n";
        $message .= "<b>Email:</b> {$user->owner_email}\n";
        $message .= "<b>Action:</b> Manually revoked via Telegram";

        $telegram->sendToChat($chatId, $message);

        // Send email to client
        try {
            $email = $user->owner_email;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(app(ClientGrandfatheredMail::class, [
                    'user' => $user,
                    'message' => 'Your grandfathered access has been manually revoked on ' . now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify client about grandfathered access revocation: " . $e->getMessage());
        }

        // Send email to admin
        try {
            $adminEmail = config('shopify-enhanced.admin_email');
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(app(AdminGrandfatheredAccessMail::class, [
                    'shop' => $user->name,
                    'email' => $user->owner_email,
                    'message' => 'Grandfathered access was manually revoked via Telegram on ' . now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify admin about grandfathered access revocation: " . $e->getMessage());
        }
    }

    /**
     * Parse duration string to Carbon date
     *
     * @param string $duration Duration string (e.g., "1d", "12h", "30m", "permanent")
     * @return array ['valid' => bool, 'date' => Carbon|null]
     */
    protected function parseDuration(string $duration): array
    {
        if ($duration === 'permanent') {
            return ['valid' => true, 'date' => null];
        }

        if (!preg_match('/^(\d+)([dhm])$/', $duration, $matches)) {
            return ['valid' => false, 'date' => null];
        }

        $value = (int) $matches[1];
        $unit = $matches[2];

        $now = now();

        switch ($unit) {
            case 'd':
                return ['valid' => true, 'date' => $now->addDays($value)];
            case 'h':
                return ['valid' => true, 'date' => $now->addHours($value)];
            case 'm':
                return ['valid' => true, 'date' => $now->addMinutes($value)];
            default:
                return ['valid' => false, 'date' => null];
        }
    }

    /**
     * Get help message
     */
    protected function getHelpMessage(): string
    {
        $message = "🏆 <b>Grandfathered Access Command Help</b>\n\n";
        $message .= "<b>/gf</b> [shopname] [duration]\n";
        $message .= "   Grant grandfathered access to a shop\n";
        $message .= "   Duration formats: 1d (days), 12h (hours), 30m (minutes), permanent\n";
        $message .= "   Example: /gf myshop.myshopify.com 7d\n\n";
        $message .= "<b>/gf list</b>\n";
        $message .= "   List all grandfathered shops\n\n";
        $message .= "<b>/gf revoke</b> [shopname]\n";
        $message .= "   Manually revoke grandfathered access\n";
        $message .= "   Example: /gf revoke myshop.myshopify.com\n\n";
        $message .= "<b>/gf help</b>\n";
        $message .= "   Show this help message";

        return $message;
    }
}
