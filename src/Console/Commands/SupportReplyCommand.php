<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportReplyMail;

class SupportReplyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:reply
                            {id : The support expectation ID}
                            {message : The reply message}
                            {--admin-email= : Admin email (optional)}
                            {--status= : Update status (optional): pending, replied, resolved, closed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reply to a support expectation from the admin console';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = $this->argument('id');
        $message = $this->argument('message');
        $adminEmail = $this->option('admin-email') ?? 'admin@bestdecoders.com';
        $status = $this->option('status');

        try {
            $expectation = SupportExpectation::with('user')->findOrFail($id);

            $this->info("📋 Support Request Details:");
            $this->line("ID: #{$expectation->id}");
            $this->line("Shop: {$expectation->user->name}");
            $this->line("User Email: {$expectation->user->email}");
            $this->line("Status: {$expectation->status}");
            $this->line("Created: {$expectation->created_at->format('M d, Y \a\t h:i A')}");

            if ($expectation->last_reply_at) {
                $this->line("Last Reply: {$expectation->last_reply_at->format('M d, Y \a\t h:i A')}");
            }

            $this->newLine();
            $this->info("📝 Original Request:");
            $originalMessage = $expectation->expectation['message'] ?? 'No message available';
            $this->line($this->wrapText($originalMessage, 70));

            if ($expectation->replies && count($expectation->replies) > 0) {
                $this->newLine();
                $this->info("💬 Previous Replies:");
                foreach ($expectation->replies as $index => $reply) {
                    $this->line("#{" . ($index + 1) . "} [{$reply['admin_email']}] - {$reply['created_at']}:");
                    $this->line($this->wrapText($reply['message'], 70));
                    $this->newLine();
                }
            }

            $this->newLine();

            if (!$this->confirm("Do you want to reply to this support request?", true)) {
                $this->warn("Reply cancelled.");
                return Command::FAILURE;
            }

            // Add the reply
            $expectation->addReply($message, $adminEmail);

            // Update status if provided
            if ($status && in_array($status, ['pending', 'replied', 'resolved', 'closed'])) {
                $expectation->update(['status' => $status]);
            }

            // Send email notification to the user
            $latestReply = $expectation->fresh()->latest_reply;
            $emailSent = false;

            try {
                Mail::to($expectation->user->email)
                    ->send(new SupportReplyMail($expectation, $latestReply));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('Failed to send support reply email notification: ' . $e->getMessage());
                $this->warn("⚠️  Failed to send email notification: " . $e->getMessage());
            }

            $this->newLine();
            $this->line("✅ Reply added successfully!");
            $this->info("📧 From: {$adminEmail}");
            $this->info("💬 Reply: {$message}");
            $this->info("📊 Status: {$expectation->fresh()->status}");
            $this->info("⏰ Replied at: " . now()->format('M d, Y \a\t h:i A'));

            if ($emailSent) {
                $this->line("📧 Email notification sent to: {$expectation->user->email}");
                $this->info("💡 User can reply directly to the email to continue the conversation.");
            }

            return Command::SUCCESS;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("❌ Support expectation with ID #{$id} not found.");
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Wrap text to specified width
     */
    private function wrapText(string $text, int $width): string
    {
        return wordwrap($text, $width, "\n    ");
    }
}