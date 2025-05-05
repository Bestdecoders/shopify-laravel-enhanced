<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\GrantGrandfatheredAccess;
use Bestdecoders\ShopifyLaravelEnhanced\Traits\ResolvesShop;

class GrantGrandfatherAccessCommand extends Command
{
    use ResolvesShop;

    protected $signature = 'grandfather:grant {shop} {duration}';
    protected $description = 'Grant grandfathered access for a specific Shopify store for a given duration (e.g. 1d, 12h, 30m)';

    public function handle(): int
    {
        $shop = $this->argument('shop');
        $duration = $this->argument('duration');

        $user = $this->getShop($shop);

        if (!$user) {
            $this->error("❌ Shop '{$shop}' not found.");
            return Command::FAILURE;
        }

        $validUntil = $this->parseDuration($duration);

        if (!$validUntil) {
            $this->error("❌ Invalid duration format. Use formats like '1d', '12h', '30m'.");
            return Command::INVALID;
        }

        GrantGrandfatheredAccess::dispatch($shop, $validUntil);
        $this->info("✅ Grandfathered access granted to {$shop} until {$validUntil->toDateTimeString()}");

        return Command::SUCCESS;
    }

    protected function parseDuration(string $duration): ?Carbon
    {
        $now = now();

        if (preg_match('/^(\d+)([dhm])$/', $duration, $matches)) {
            [$full, $amount, $unit] = $matches;

            return match ($unit) {
                'd' => $now->copy()->addDays((int) $amount),
                'h' => $now->copy()->addHours((int) $amount),
                'm' => $now->copy()->addMinutes((int) $amount),
                default => null,
            };
        }

        return null;
    }
}
