<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bestdecoders\ShopifyLaravelEnhanced\Jobs\RevokeGrandfatheredAccess;

class RevokeExpiredGrandfatheredAccessCommand extends Command
{
    protected $signature = 'grandfather:revoke-expired';
    protected $description = 'Run job to revoke expired grandfathered access';

    public function handle(): int
    {
        RevokeGrandfatheredAccess::dispatch();

        $this->info('📤 RevokeExpiredGrandfatheredAccess job dispatched.');
        return self::SUCCESS;
    }
}
