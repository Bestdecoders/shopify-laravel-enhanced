<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;

class SupportListCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'support:list
                            {--status= : Filter by status}
                            {--recent= : Show recent requests (days, default: 7)}
                            {--limit=10 : Limit results}';

    /**
     * The console command description.
     */
    protected $description = 'List support expectations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $status = $this->option('status');
        $recent = (int) $this->option('recent') ?: 7;
        $limit = (int) $this->option('limit') ?: 10;

        $query = SupportExpectation::with('user')
            ->recent($recent)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($status) {
            $query->withStatus($status);
        }

        $expectations = $query->get();

        if ($expectations->isEmpty()) {
            $this->warn("No support requests found.");
            return Command::SUCCESS;
        }

        $this->info("📋 Support Requests (Last {$recent} days):");
        $this->newLine();

        $headers = ['ID', 'Shop', 'Status', 'Created', 'Last Reply', 'Preview'];
        $rows = [];

        foreach ($expectations as $expectation) {
            $preview = $expectation->expectation['message'] ?? 'No message';
            $preview = Str::limit($preview, 50);

            $rows[] = [
                "#{$expectation->id}",
                $expectation->user->name ?? 'Unknown',
                $expectation->status,
                $expectation->created_at->format('M d, H:i'),
                $expectation->last_reply_at ? $expectation->last_reply_at->format('M d, H:i') : 'None',
                $preview
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info("💡 Use 'php artisan support:reply {id} \"Your message\"' to reply to a request.");

        return Command::SUCCESS;
    }
}