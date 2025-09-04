<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\ClientGrandfatheredMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\AdminGrandfatheredAccessMail;

class RevokeExpiredGrandfatheredAccessCommand extends Command
{
    protected $signature = 'grandfather:revoke-expired';
    protected $description = 'Revoke expired grandfathered access for all shops';

    public function handle(): int
    {
        $userModel = config('shopify-enhanced.user_model');

        if (!class_exists($userModel)) {
            $this->error("❌ User model not found in config.");
            return Command::FAILURE;
        }

        $expiredUsers = $userModel::where('shopify_grandfathered', true)
            ->whereNotNull('grandfather_access_valid_until')
            ->where('grandfather_access_valid_until', '<', Carbon::now())
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info("✅ No expired grandfathered users found.");
            return Command::SUCCESS;
        }

        foreach ($expiredUsers as $user) {
            $user->shopify_grandfathered = false;
            $user->save();

            $this->notifyAdmin($user);
            $this->notifyClient($user);

            $this->info("🔁 Revoked grandfathered access for {$user->name}");
        }

        return Command::SUCCESS;
    }

    protected function notifyAdmin($user): void
    {
        try {
            $adminEmail = config('shopify-enhanced.admin_email');

            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(app(AdminGrandfatheredAccessMail::class, [
                    'shop' => $user->name,
                    'email' => $user->owner_email,
                    'message' => 'Grandfathered access was revoked on ' . now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("❌ Failed to notify admin: " . $e->getMessage());
        }
    }

    protected function notifyClient($user): void
    {
        try {
            $email = $user->owner_email;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(app(ClientGrandfatheredMail::class, [
                    'user' => $user,
                    'message' => 'Your grandfathered access has ended on ' . now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            Log::error("❌ Failed to notify client: " . $e->getMessage());
        }
    }
}
