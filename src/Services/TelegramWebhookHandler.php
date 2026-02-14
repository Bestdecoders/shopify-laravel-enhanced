<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Jobs\ProcessTelegramWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Webhook Handler
 *
 * Receives webhook updates from Telegram and dispatches background jobs
 * This ensures fast response times and prevents webhook timeouts
 */
class TelegramWebhookHandler
{
    /**
     * Handle webhook request from Telegram
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {
        $update = $request->all();
        $text = $update['message']['text'] ?? '';

        Log::info('Telegram webhook received', [
            'update_id' => $update['update_id'] ?? null,
            'text' => $text,
        ]);

        // Dispatch background job for processing
        // This prevents timeouts especially for AI commands
        ProcessTelegramWebhookJob::dispatch($update);

        // Respond immediately to Telegram
        return response()->json(['ok' => true]);
    }
}
