<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookHandler
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle webhook request from Telegram
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        $text = $update['message']['text'] ?? '';

        Log::info('Telegram received: ' . $text);

        if (empty($text)) {
            return response()->json(['ok' => true]);
        }

        // Get command from config
        $command = ltrim($text, '/');

        if (isset(config('shopify-enhanced.telegram.commands')[$command])) {
            $commandClass = config('shopify-enhanced.telegram.commands')[$command];

            if (class_exists($commandClass)) {
                $handler = app($commandClass);
                $handler->handle($this->telegram);
            }
        }

        return response()->json(['ok' => true]);
    }
}
