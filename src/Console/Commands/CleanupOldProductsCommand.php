<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Console\Commands;

use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldProductsCommand extends Command
{
    protected $signature = 'shopify:cleanup-products
                           {--days=30 : Number of days after which products are considered old}
                           {--dry-run : Show what would be deleted without actually deleting}
                           {--shop= : Cleanup products for specific shop domain only}';

    protected $description = 'Clean up old cached Shopify products that haven\'t been accessed recently';

    protected ProductFilterService $productFilterService;

    public function __construct(ProductFilterService $productFilterService)
    {
        parent::__construct();
        $this->productFilterService = $productFilterService;
    }

    public function handle()
    {
        if (!config('shopify-enhanced.product_filter.enabled', false)) {
            $this->error('Product filter feature is not enabled.');
            return 1;
        }

        if (!config('shopify-enhanced.product_filter.auto_cleanup_enabled', true)) {
            $this->warn('Auto cleanup is disabled in configuration.');
        }

        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $shopDomain = $this->option('shop');

        $this->info("Starting cleanup of products older than {$days} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No products will actually be deleted');
        }

        if ($shopDomain) {
            $this->info("Filtering by shop domain: {$shopDomain}");
        }

        try {
            $query = \Bestdecoders\ShopifyLaravelEnhanced\Models\ShopifyProduct::olderThan($days);

            if ($shopDomain) {
                $query->where('shop_domain', $shopDomain);
            }

            $oldProducts = $query->get();

            if ($oldProducts->isEmpty()) {
                $this->info('No old products found to cleanup.');
                return 0;
            }

            $this->info("Found {$oldProducts->count()} products to cleanup:");

            // Group by shop for better reporting
            $productsByShop = $oldProducts->groupBy('shop_domain');

            foreach ($productsByShop as $shop => $products) {
                $this->line("  {$shop}: {$products->count()} products");

                if ($this->output->isVerbose()) {
                    foreach ($products as $product) {
                        $lastAccessed = $product->last_accessed_at ?
                            $product->last_accessed_at->diffForHumans() : 'Never';
                        $this->line("    - {$product->title} (ID: {$product->product_id}, Last accessed: {$lastAccessed})");
                    }
                }
            }

            if (!$dryRun) {
                if ($this->confirm('Do you want to proceed with the cleanup?')) {
                    $deletedCount = $this->performCleanup($days, $shopDomain);
                    $this->info("Successfully deleted {$deletedCount} old products.");

                    debug_log("Manual product cleanup completed", [
                        'deleted_count' => $deletedCount,
                        'days_threshold' => $days,
                        'shop_domain' => $shopDomain,
                        'initiated_by' => 'console_command'
                    ]);
                } else {
                    $this->info('Cleanup cancelled.');
                }
            } else {
                $this->info('Dry run completed. Use without --dry-run to actually delete products.');
            }

        } catch (\Exception $e) {
            $this->error("Cleanup failed: " . $e->getMessage());
            Log::error("Product cleanup command failed", [
                'error' => $e->getMessage(),
                'days' => $days,
                'shop_domain' => $shopDomain,
                'dry_run' => $dryRun
            ]);
            return 1;
        }

        return 0;
    }

    protected function performCleanup(int $days, ?string $shopDomain = null): int
    {
        $query = \Bestdecoders\ShopifyLaravelEnhanced\Models\ShopifyProduct::olderThan($days);

        if ($shopDomain) {
            $query->where('shop_domain', $shopDomain);
        }

        return $query->delete();
    }
}