<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Jobs;

use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Process Telegram Webhook Job
 *
 * Handles Telegram webhook updates in the background
 * This prevents webhook timeouts, especially for AI commands
 */
class ProcessTelegramWebhookJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The webhook update payload
     */
    public array $update;

    /**
     * Create a new job instance
     */
    public function __construct(array $update)
    {
        $this->update = $update;
    }

    /**
     * Execute the job
     */
    public function handle(TelegramService $telegram): void
    {
        $text = $this->update['message']['text'] ?? '';

        Log::info('Processing Telegram webhook', ['text' => $text]);

        // Store update for commands to access
        $telegram->setCurrentUpdate($this->update);

        // Get command from text
        $parts = explode(' ', trim($text));
        $command = ltrim($parts[0] ?? '', '/');

        if (empty($command)) {
            return;
        }

        Log::info('Telegram command extracted', ['command' => $command]);

        // Get command class from config
        $commandClass = config('shopify-enhanced.telegram.commands')[$command] ?? null;

        if (!$commandClass) {
            Log::info('Telegram command not registered', ['command' => $command]);
            return;
        }

        Log::info('Telegram command class', ['class' => $commandClass]);

        if (!class_exists($commandClass)) {
            Log::error('Telegram command class not found', ['class' => $commandClass]);
            return;
        }

        // Execute command
        try {
            $handler = app($commandClass);
            $handler->handle($telegram);
        } catch (Throwable $e) {
            Log::error('Telegram command execution failed', [
                'command' => $command,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get the unique ID for the job
     */
    public function uniqueId(): string
    {
        // Use update_id to prevent duplicate processing
        return 'telegram-webhook-' . ($this->update['update_id'] ?? md5(json_encode($this->update)));
    }

    /**
     * Get the tags for the job
     */
    public function tags(): array
    {
        return ['telegram', 'webhook'];
    }
}
